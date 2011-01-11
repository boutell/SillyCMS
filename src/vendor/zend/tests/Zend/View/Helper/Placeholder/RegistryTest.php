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
 * @package    Zend_View
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace ZendTest\View\Helper\Placeholder;
use Zend\View\Helper\Placeholder\Registry;
use Zend\View\Helper\Placeholder\Container;


/**
 * Test class for Zend_View_Helper_Placeholder_Registry.
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_View
 * @group      Zend_View_Helper
 */
class RegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Zend_View_Helper_Placeholder_Registry
     */
    public $registry;


    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        $registry = \Zend\Registry::getInstance();
        if (isset($registry[Registry::REGISTRY_KEY])) {
            unset($registry[Registry::REGISTRY_KEY]);
        }
        $this->registry = new Registry();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->registry);
    }

    /**
     * @return void
     */
    public function testCreateContainer()
    {
        $this->assertFalse($this->registry->containerExists('foo'));
        $this->registry->createContainer('foo');
        $this->assertTrue($this->registry->containerExists('foo'));
    }

    /**
     * @return void
     */
    public function testCreateContainerCreatesDefaultContainerClass()
    {
        $this->assertFalse($this->registry->containerExists('foo'));
        $container = $this->registry->createContainer('foo');
        $this->assertTrue($container instanceof Container);
    }

    /**
     * @return void
     */
    public function testGetContainerCreatesContainerIfNonExistent()
    {
        $this->assertFalse($this->registry->containerExists('foo'));
        $container = $this->registry->getContainer('foo');
        $this->assertTrue($container instanceof Container\AbstractContainer);
        $this->assertTrue($this->registry->containerExists('foo'));
    }

    /**
     * @return void
     */
    public function testSetContainerCreatesRegistryEntry()
    {
        $foo = new Container(array('foo', 'bar'));
        $this->assertFalse($this->registry->containerExists('foo'));
        $this->registry->setContainer('foo', $foo);
        $this->assertTrue($this->registry->containerExists('foo'));
    }

    public function testSetContainerCreatesRegistersContainerInstance()
    {
        $foo = new Container(array('foo', 'bar'));
        $this->assertFalse($this->registry->containerExists('foo'));
        $this->registry->setContainer('foo', $foo);
        $container = $this->registry->getContainer('foo');
        $this->assertSame($foo, $container);
    }

    public function testContainerClassAccessorsSetState()
    {
        $this->assertEquals('\Zend\View\Helper\Placeholder\Container', $this->registry->getContainerClass());
        $this->registry->setContainerClass('ZendTest\View\Helper\Placeholder\MockContainer');
        $this->assertEquals('ZendTest\View\Helper\Placeholder\MockContainer', $this->registry->getContainerClass());
    }

    public function testSetContainerClassThrowsExceptionWithInvalidContainerClass()
    {
        try {
            $this->registry->setContainerClass('ZendTest\View\Helper\Placeholder\BogusContainer');
            $this->fail('Invalid container classes should not be accepted');
        } catch (\Exception $e) {
        }
    }

    public function testDeletingContainerRemovesFromRegistry()
    {
        $this->registry->createContainer('foo');
        $this->assertTrue($this->registry->containerExists('foo'));
        $result = $this->registry->deleteContainer('foo');
        $this->assertFalse($this->registry->containerExists('foo'));
        $this->assertTrue($result);
    }

    public function testDeleteContainerReturnsFalseIfContainerDoesNotExist()
    {
        $result = $this->registry->deleteContainer('foo');
        $this->assertFalse($result);
    }

    public function testUsingCustomContainerClassCreatesContainersOfCustomClass()
    {
        $this->registry->setContainerClass('ZendTest\View\Helper\Placeholder\MockContainer');
        $container = $this->registry->createContainer('foo');
        $this->assertTrue($container instanceof MockContainer);
    }

    public function testGetRegistryReturnsRegistryInstance()
    {
        $registry = Registry::getRegistry();
        $this->assertTrue($registry instanceof Registry);
    }

    public function testGetRegistrySubsequentTimesReturnsSameInstance()
    {
        $registry1 = Registry::getRegistry();
        $registry2 = Registry::getRegistry();
        $this->assertSame($registry1, $registry2);
    }

    public function testGetRegistryRegistersWithGlobalRegistry()
    {
        $this->assertFalse(\Zend\Registry::isRegistered(Registry::REGISTRY_KEY));
        $registry = Registry::getRegistry();
        $this->assertTrue(\Zend\Registry::isRegistered(Registry::REGISTRY_KEY));

        $registered = \Zend\Registry::get(Registry::REGISTRY_KEY);
        $this->assertSame($registry, $registered);
    }
}

class MockContainer extends Container\AbstractContainer
{
}

class BogusContainer
{
}
