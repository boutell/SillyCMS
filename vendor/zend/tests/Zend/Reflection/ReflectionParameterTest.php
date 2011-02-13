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
 * @package    Zend_Reflection
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace ZendTest\Reflection;
use Zend\Reflection;

/**
 * @category   Zend
 * @package    Zend_Reflection
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Reflection
 * @group      Zend_Reflection_Parameter
 */
class ReflectionParameterTest extends \PHPUnit_Framework_TestCase
{
    public function testDeclaringClassReturn()
    {
        $parameter = new Reflection\ReflectionParameter(array('ZendTest\Reflection\TestAsset\TestSampleClass2', 'getProp2'), 0);
        $this->assertEquals(get_class($parameter->getDeclaringClass()), 'Zend\Reflection\ReflectionClass');
    }

    public function testClassReturn_NoClassGiven_ReturnsNull()
    {
        $parameter = new Reflection\ReflectionParameter(array('ZendTest\Reflection\TestAsset\TestSampleClass2', 'getProp2'), 'param1');
        $this->assertNull($parameter->getClass());
    }

    public function testClassReturn()
    {
        $parameter = new Reflection\ReflectionParameter(array('ZendTest\Reflection\TestAsset\TestSampleClass2', 'getProp2'), 'param2');
        $this->assertEquals(get_class($parameter->getClass()), 'Zend\Reflection\ReflectionClass');
    }

    /**
     * @dataProvider paramTypeTestProvider
     */
    public function testTypeReturn($param, $type)
    {
        $parameter = new Reflection\ReflectionParameter(array('ZendTest\Reflection\TestAsset\TestSampleClass5', 'doSomething'), $param);
        $this->assertEquals($parameter->getType(), $type);
    }

    public function paramTypeTestProvider()
    {
        return array(
            array('one','int'),
            array('two','int'),
            array('three','string'),
        );
    }
}

