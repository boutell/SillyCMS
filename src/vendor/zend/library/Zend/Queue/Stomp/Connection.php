<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Queue
 * @subpackage Stomp
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace Zend\Queue\Stomp;

use Zend\Queue\Exception as QueueException;

/**
 * The Stomp client interacts with a Stomp server.
 *
 * @uses       \Zend\Queue\Exception
 * @uses       \Zend\Queue\Stomp\StompConnection
 * @category   Zend
 * @package    Zend_Queue
 * @subpackage Stomp
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Connection implements StompConnection
{
    const READ_TIMEOUT_DEFAULT_USEC = 0; // 0 microseconds
    const READ_TIMEOUT_DEFAULT_SEC = 5; // 5 seconds

    /**
     * Connection options
     * @var array
     */
    protected $_options;

    /**
     * tcp/udp socket
     *
     * @var resource
     */
    protected $_socket = false;

    /**
     * open() opens a socket to the Stomp server
     *
     * @param  array $options ('scheme', 'host', 'port')
     * @param  string $scheme
     * @param  string $host
     * @param  int $port
     * @param  array $options Accepts "timeout_sec" and "timeout_usec" keys
     * @return true;
     * @throws \Zend\Queue\Exception
     */
    public function open($scheme, $host, $port, array $options = array())
    {
        $str = $scheme . '://' . $host;
        $this->_socket = fsockopen($str, $port, $errno, $errstr);

        if ($this->_socket === false) {
            // aparently there is some reason that fsockopen will return false
            // but it normally throws an error.
            throw new QueueException("Unable to connect to $str; error = $errstr ( errno = $errno )");
        }

        stream_set_blocking($this->_socket, 0); // non blocking

        if (!isset($options['timeout_sec'])) {
            $options['timeout_sec'] = self::READ_TIMEOUT_DEFAULT_SEC;
        }
        if (! isset($options['timeout_usec'])) {
            $options['timeout_usec'] = self::READ_TIMEOUT_DEFAULT_USEC;
        }

        $this->_options = $options;

        return true;
    }

    /**
     * Close the socket explicitly when destructed
     *
     * @return void
     */
    public function __destruct()
    {
    }

    /**
     * Close connection
     *
     * @param  boolean $destructor
     * @return void
     */
    public function close($destructor = false)
    {
        // Gracefully disconnect
        if (!$destructor) {
            $frame = $this->createFrame();
            $frame->setCommand('DISCONNECT');
            $this->write($frame);
        }

        // @todo: Should be fixed.
        // When the socket is "closed", it will trigger the below error when php exits
        // Fatal error: Exception thrown without a stack frame in Unknown on line 0

        // Danlo: I suspect this is because this has already been claimed by the interpeter
        // thus trying to shutdown this resources, which is already shutdown is a problem.
        if (is_resource($this->_socket)) {
            // fclose($this->_socket);
        }

        // $this->_socket = null;
    }

    /**
     * Check whether we are connected to the server
     *
     * @return true
     * @throws \Zend\Queue\Exception
     */
    public function ping()
    {
        if (!is_resource($this->_socket)) {
            throw new QueueException('Not connected to Stomp server');
        }
        return true;
    }

    /**
     * Write a frame to the stomp server
     *
     * example: $response = $client->write($frame)->read();
     *
     * @param \Zend\Queue\Stom\StompFrame $frame
     * @return $this
     */
    public function write(StompFrame $frame)
    {
        $this->ping();
        $output = $frame->toFrame();

        $bytes = fwrite($this->_socket, $output, strlen($output));
        if ($bytes === false || $bytes == 0) {
            throw new QueueException('No bytes written');
        }

        return $this;
    }

    /**
     * Tests the socket to see if there is data for us
     *
     * @return boolean
     */
    public function canRead()
    {
        $read   = array($this->_socket);
        $write  = null;
        $except = null;

        return stream_select(
            $read,
            $write,
            $except,
            $this->_options['timeout_sec'],
            $this->_options['timeout_usec']
        ) == 1;
        // see http://us.php.net/manual/en/function.stream-select.php
    }

    /**
     * Reads in a frame from the socket or returns false.
     *
     * @return \Zend\Queue\Stomp\StompFrame|false
     * @throws \Zend\Queue\Exception
     */
    public function read()
    {
        $this->ping();

        $response = '';
        $prev     = '';

        // while not end of file.
        while (!feof($this->_socket)) {
            // read in one character until "\0\n" is found
            $data = fread($this->_socket, 1);

            // check to make sure that the connection is not lost.
            if ($data === false) {
                throw new QueueException('Connection lost');
            }

            // append last character read to $response
            $response .= $data;

            // is this \0 (prev) \n (data)? END_OF_FRAME
            if (ord($data) == 10 && ord($prev) == 0) {
                break;
            }
            $prev = $data;
        }

        if ($response === '') {
            return false;
        }

        $frame = $this->createFrame();
        $frame->fromFrame($response);
        return $frame;
    }

    /**
     * Set the frameClass to be used
     *
     * This must be a \Zend\Queue\Stomp\StompFrame.
     *
     * @param  string $classname - class is an instance of \Zend\Queue\Stomp\StompFrame
     * @return $this;
     */
    public function setFrameClass($classname)
    {
        $this->_options['frameClass'] = $classname;
        return $this;
    }

    /**
     * Get the frameClass
     *
     * @return string
     */
    public function getFrameClass()
    {
        return isset($this->_options['frameClass'])
            ? $this->_options['frameClass']
            : '\Zend\Queue\Stomp\Frame';
    }

    /**
     * Create an empty frame
     *
     * @return \Zend\Queue\Stomp\StompFrame
     */
    public function createFrame()
    {
        $class = $this->getFrameClass();

        $frame = new $class();

        if (!$frame instanceof StompFrame) {
            throw new QueueException('Invalid Frame class provided; must implement \Zend\Queue\Stomp\StompFrame');
        }

        return $frame;
    }
}
