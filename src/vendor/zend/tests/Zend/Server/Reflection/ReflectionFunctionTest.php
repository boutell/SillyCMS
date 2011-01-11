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
namespace ZendTest\Server\Reflection;
use Zend\Server\Reflection;

/**
 * Test case for Zend_Server_Reflection_Function
 *
 * @category   Zend
 * @package    Zend_Server
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Server
 */
class ReflectionFunctionTest extends \PHPUnit_Framework_TestCase
{
    public function test__construct()
    {
        $function = new \ReflectionFunction('\ZendTest\Server\Reflection\function1');
        $r = new Reflection\ReflectionFunction($function);
        $this->assertTrue($r instanceof Reflection\ReflectionFunction);
        $this->assertTrue($r instanceof Reflection\AbstractFunction);
        $params = $r->getParameters();

        $r = new Reflection\ReflectionFunction($function, 'namespace');
        $this->assertEquals('namespace', $r->getNamespace());

        $argv = array('string1', 'string2');
        $r = new Reflection\ReflectionFunction($function, 'namespace', $argv);
        $this->assertTrue(is_array($r->getInvokeArguments()));
        $this->assertTrue($argv === $r->getInvokeArguments());

        $prototypes = $r->getPrototypes();
        $this->assertTrue(is_array($prototypes));
        $this->assertTrue(0 < count($prototypes));
    }
    
    public function testConstructorThrowsExceptionOnNonFunction()
    {
        $function = new \ReflectionFunction('\ZendTest\Server\Reflection\function1');
        $r = new Reflection\ReflectionFunction($function);
        $params = $r->getParameters();
        
        $this->setExpectedException('Zend\Server\Reflection\Exception\InvalidArgumentException', 'Invalid reflection class');
        $r = new Reflection\ReflectionFunction($params[0]);
    }

    public function test__getSet()
    {
        $function = new \ReflectionFunction('\ZendTest\Server\Reflection\function1');
        $r = new Reflection\ReflectionFunction($function);

        $r->system = true;
        $this->assertTrue($r->system);
    }


    public function testNamespace()
    {
        $function = new \ReflectionFunction('\ZendTest\Server\Reflection\function1');
        $r = new Reflection\ReflectionFunction($function, 'namespace');
        $this->assertEquals('namespace', $r->getNamespace());
        $r->setNamespace('framework');
        $this->assertEquals('framework', $r->getNamespace());
    }

    public function testDescription()
    {
        $function = new \ReflectionFunction('\ZendTest\Server\Reflection\function1');
        $r = new Reflection\ReflectionFunction($function);
        $this->assertContains('function for reflection', $r->getDescription());
        $r->setDescription('Testing setting descriptions');
        $this->assertEquals('Testing setting descriptions', $r->getDescription());
    }

    public function testGetPrototypes()
    {
        $function = new \ReflectionFunction('\ZendTest\Server\Reflection\function1');
        $r = new Reflection\ReflectionFunction($function);

        $prototypes = $r->getPrototypes();
        $this->assertTrue(is_array($prototypes));
        $this->assertTrue(0 < count($prototypes));
        $this->assertEquals(8, count($prototypes));

        foreach ($prototypes as $p) {
            $this->assertTrue($p instanceof Reflection\Prototype);
        }
    }

    public function testGetPrototypes2()
    {
        $function = new \ReflectionFunction('\ZendTest\Server\Reflection\function2');
        $r = new Reflection\ReflectionFunction($function);

        $prototypes = $r->getPrototypes();
        $this->assertTrue(is_array($prototypes));
        $this->assertTrue(0 < count($prototypes));
        $this->assertEquals(1, count($prototypes));

        foreach ($prototypes as $p) {
            $this->assertTrue($p instanceof Reflection\Prototype);
        }
    }


    public function testGetInvokeArguments()
    {
        $function = new \ReflectionFunction('\ZendTest\Server\Reflection\function1');
        $r = new Reflection\ReflectionFunction($function);
        $args = $r->getInvokeArguments();
        $this->assertTrue(is_array($args));
        $this->assertEquals(0, count($args));

        $argv = array('string1', 'string2');
        $r = new Reflection\ReflectionFunction($function, null, $argv);
        $args = $r->getInvokeArguments();
        $this->assertTrue(is_array($args));
        $this->assertEquals(2, count($args));
        $this->assertTrue($argv === $args);
    }

    public function test__wakeup()
    {
        $function = new \ReflectionFunction('\ZendTest\Server\Reflection\function1');
        $r = new Reflection\ReflectionFunction($function);
        $s = serialize($r);
        $u = unserialize($s);
        $this->assertTrue($u instanceof Reflection\ReflectionFunction);
        $this->assertEquals('', $u->getNamespace());
    }

    public function testMultipleWhitespaceBetweenDoctagsAndTypes()
    {
        $function = new \ReflectionFunction('\ZendTest\Server\Reflection\function3');
        $r = new Reflection\ReflectionFunction($function);

        $prototypes = $r->getPrototypes();
        $this->assertTrue(is_array($prototypes));
        $this->assertTrue(0 < count($prototypes));
        $this->assertEquals(1, count($prototypes));

        $proto = $prototypes[0];
        $params = $proto->getParameters();
        $this->assertTrue(is_array($params));
        $this->assertEquals(1, count($params));
        $this->assertEquals('string', $params[0]->getType());
    }

    /**
     * @group ZF-6996
     */
    public function testParameterReflectionShouldReturnTypeAndVarnameAndDescription()
    {
        $function = new \ReflectionFunction('\ZendTest\Server\Reflection\function1');
        $r = new Reflection\ReflectionFunction($function);

        $prototypes = $r->getPrototypes();
        $prototype  = $prototypes[0];
        $params = $prototype->getParameters();
        $param  = $params[0];
        $this->assertContains('Some description', $param->getDescription(), var_export($param, 1));
    }
}

/**
 * \ZendTest\Server\Reflection\function1
 *
 * Test function for reflection unit tests
 *
 * @param string $var1 Some description
 * @param string|array $var2
 * @param array $var3
 * @return null|array
 */
function function1($var1, $var2, $var3 = null)
{
}

/**
 * \ZendTest\Server\Reflection\function2
 *
 * Test function for reflection unit tests; test what happens when no return
 * value or params specified in docblock.
 */
function function2()
{
}

/**
 * \ZendTest\Server\Reflection\function3
 *
 * @param  string $var1
 * @return void
 */
function function3($var1)
{
}
