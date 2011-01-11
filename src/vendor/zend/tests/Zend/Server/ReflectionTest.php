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
 * @package    Zend_Server
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace ZendTest\Server;
use Zend\Server\Reflection;

/**
 * Test case for Zend\Server\Reflection
 *
 * @category   Zend
 * @package    Zend_Server
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Server
 */
class ReflectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * reflectClass() test
     */
    public function testReflectClass()
    {
        $reflection = Reflection\Reflection::reflectClass('ZendTest\Server\ReflectionTestClass');
        $this->assertTrue($reflection instanceof Reflection\ReflectionClass);

        $reflection = Reflection\Reflection::reflectClass(new ReflectionTestClass());
        $this->assertTrue($reflection instanceof Reflection\ReflectionClass);
    }
    
    public function testReflectClassThrowsExceptionOnInvalidClass()
    {
        $this->setExpectedException('Zend\Server\Reflection\Exception\InvalidArgumentException', 'Invalid argv argument passed to reflectClass');
        $reflection = Reflection\Reflection::reflectClass('ZendTest\Server\ReflectionTestClass', 'string');
    }

    public function testReflectClassThrowsExceptionOnInvalidParameter()
    {
        $this->setExpectedException('Zend\Server\Reflection\Exception\InvalidArgumentException', 'Invalid class or object passed to attachClass');
        $reflection = Reflection\Reflection::reflectClass(false);
    }
    
    /**
     * reflectClass() test; test namespaces
     */
    public function testReflectClass2()
    {
        $reflection = Reflection::reflectClass('ZendTest\Server\ReflectionTestClass', false, 'zsr');
        $this->assertEquals('zsr', $reflection->getNamespace());
    }

    /**
     * reflectFunction() test
     */
    public function testReflectFunction()
    {
        $reflection = Reflection\Reflection::reflectFunction('ZendTest\Server\reflectionTestFunction');
        $this->assertTrue($reflection instanceof Reflection\ReflectionFunction);
    }
    
    public function testReflectFunctionThrowsExceptionOnInvalidFunction()
    {
        $this->setExpectedException('Zend\Server\Reflection\Exception\InvalidArgumentException', 'Invalid function');
        $reflection = Reflection\Reflection::reflectFunction('ZendTest\Server\reflectionTestClass', 'string');
    }

    public function testReflectFunctionThrowsExceptionOnInvalidParam()
    {
        $this->setExpectedException('Zend\Server\Reflection\Exception\InvalidArgumentException', 'Invalid function');
        $reflection = Reflection\Reflection::reflectFunction(false);
    }
    
    /**
     * reflectFunction() test; test namespaces
     */
    public function testReflectFunction2()
    {
        $reflection = Reflection::reflectFunction('ZendTest\Server\reflectionTestFunction', false, 'zsr');
        $this->assertEquals('zsr', $reflection->getNamespace());
    }
}

/**
 * \ZendTest\Server\reflectionTestClass
 *
 * Used to test reflectFunction generation of signatures
 *
 * @param boolean $arg1
 * @param string|array $arg2
 * @param string $arg3 Optional argument
 * @param string|struct|false $arg4 Optional argument
 * @return boolean|array
 */
function reflectionTestFunction($arg1, $arg2, $arg3 = 'string', $arg4 = 'array')
{
}

/**
 * \ZendTest\Server\ReflectionTestClass -- test class reflection
 */
class ReflectionTestClass
{
    /**
     * Constructor
     *
     * This shouldn't be reflected
     *
     * @param mixed $arg
     * @return void
     */
    public function __construct($arg = null)
    {
    }

    /**
     * Public one
     *
     * @param string $arg1
     * @param array $arg2
     * @return string
     */
    public function one($arg1, $arg2 = null)
    {
    }

    /**
     * Protected _one
     *
     * Should not be reflected
     *
     * @param string $arg1
     * @param array $arg2
     * @return string
     */
    protected function _one($arg1, $arg2 = null)
    {
    }

    /**
     * Public two
     *
     * @param string $arg1
     * @param string $arg2
     * @return boolean|array
     */
    public static function two($arg1, $arg2)
    {
    }
}
