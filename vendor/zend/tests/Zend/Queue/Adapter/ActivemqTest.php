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
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace ZendTest\Queue\Adapter;
use Zend\Queue\Adapter;

/*
 * The adapter test class provides a universal test class for all of the
 * abstract methods.
 *
 * All methods marked not supported are explictly checked for for throwing
 * an exception.
 */

/**
 * @category   Zend
 * @package    Zend_Queue
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Queue
 */
class ActivemqTest extends AdapterTest
{
    public function setUp()
    {
        if (!constant('TESTS_ZEND_QUEUE_ACTIVEMQ_ENABLED')) {
            $this->markTestSkipped('Zend_Queue ActiveMQ adapter tests are not enabled');
        }
    }

    /**
     * getAdapterName() is an method to help make AdapterTest work with any
     * new adapters
     *
     * You must overload this method
     *
     * @return string
     */
    public function getAdapterName()
    {
        return 'Activemq';
    }

    public function getTestConfig()
    {
        $driverOptions = array();
        if (defined('TESTS_ZEND_QUEUE_ACTIVEMQ_HOST')) {
            $driverOptions['host'] = TESTS_ZEND_QUEUE_APACHEMQ_HOST;
        }
        if (defined('TESTS_ZEND_QUEUE_ACTIVEMQ_PORT')) {
            $driverOptions['port'] = TESTS_ZEND_QUEUE_APACHEMQ_PORT;
        }
        if (defined('TESTS_ZEND_QUEUE_ACTIVEMQ_SCHEME')) {
            $driverOptions['scheme'] = TESTS_ZEND_QUEUE_APACHEMQ_SCHEME;
        }
        return array('driverOptions' => $driverOptions);
    }

    /**
     * Stomped requires specific name types
     */
    public function createQueueName($name)
    {
        return '/temp-queue/' . $name;
    }

    public function testConst()
    {
        /**
         * @see Zend_Queue_Adapter_Activemq
         */
        $this->assertTrue(is_string(Adapter\Activemq::DEFAULT_SCHEME));
        $this->assertTrue(is_string(Adapter\Activemq::DEFAULT_HOST));
        $this->assertTrue(is_integer(Adapter\Activemq::DEFAULT_PORT));
    }
}
