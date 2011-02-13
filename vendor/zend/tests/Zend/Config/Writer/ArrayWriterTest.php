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
 * @package    Zend_Config
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace ZendTest\Config\Writer;

use \Zend\Config\Writer\ArrayWriter,
    \Zend\Config\Config;

/**
 * @category   Zend
 * @package    Zend_Config
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Config
 */
class ArrayWriterTest extends \PHPUnit_Framework_TestCase
{
    protected $_tempName;

    public function setUp()
    {
        $this->_tempName = tempnam(__DIR__ . '/temp', 'tmp');
    }

    public function tearDown()
    {
        @unlink($this->_tempName);
    }

    public function testNoFilenameSet()
    {
        $writer = new ArrayWriter(array('config' => new Config(array())));
        $this->setExpectedException('Zend\Config\Exception\InvalidArgumentException', 'No filename was set');
        $writer->write();
    }

    public function testNoConfigSet()
    {
        $writer = new ArrayWriter(array('filename' => $this->_tempName));
        $this->setExpectedException('Zend\Config\Exception\InvalidArgumentException', 'No config was set');
        $writer->write();
    }

    public function testFileNotWritable()
    {
        $writer = new ArrayWriter(array('config' => new Config(array()), 'filename' => '/../../../'));
        $this->setExpectedException('Zend\Config\Exception\RuntimeException', 'Could not write to file');
        $writer->write();
    }

    public function testWriteAndRead()
    {
        $config = new Config(array('test' => 'foo'));
        $writer = new ArrayWriter(array('config' => $config, 'filename' => $this->_tempName));
        $writer->write();

        $config = new Config(include $this->_tempName);
        $this->assertEquals('foo', $config->test);
    }

    public function testArgumentOverride()
    {
        $config = new Config(array('test' => 'foo'));
        $writer = new ArrayWriter();
        $writer->write($this->_tempName, $config);

        $config = new Config(include $this->_tempName);
        $this->assertEquals('foo', $config->test);
    }

    /**
     * @group ZF-8234
     */
    public function testRender()
    {
        $config = new Config(array('test' => 'foo', 'bar' => array(0 => 'baz', 1 => 'foo')));

        $writer = new ArrayWriter();
        $configString = $writer->setConfig($config)->render();


        // build string line by line as we are trailing-whitespace sensitive.
        $expected = "<?php\n";
        $expected .= "return array (\n";
        $expected .= "  'test' => 'foo',\n";
        $expected .= "  'bar' => \n";
        $expected .= "  array (\n";
        $expected .= "    0 => 'baz',\n";
        $expected .= "    1 => 'foo',\n";
        $expected .= "  ),\n";
        $expected .= ");\n";
        
        $this->assertEquals($expected, $configString);
    }
}
