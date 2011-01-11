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

namespace ZendTest\View;

use Zend\View\TemplatePathStack;

/**
 * @category   Zend
 * @package    Zend_View
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_View
 */
class TemplatePathStackTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->stack = new TemplatePathStack();
        $this->paths = array(
            TemplatePathStack::normalizePath(__DIR__),
            TemplatePathStack::normalizePath(__DIR__ . '/_templates'),
        );
    }

    public function testAddPathAddsPathToStack()
    {
        $this->stack->addPath(__DIR__);
        $paths = $this->stack->getPaths();
        $this->assertEquals(1, count($paths));
        $this->assertEquals(TemplatePathStack::normalizePath(__DIR__), $paths->pop());
    }

    public function testPathsAreProcessedAsStack()
    {
        $paths = array(
            TemplatePathStack::normalizePath(__DIR__),
            TemplatePathStack::normalizePath(__DIR__ . '/_files'),
        );
        foreach ($paths as $path) {
            $this->stack->addPath($path);
        }
        $test = $this->stack->getPaths()->toArray();
        $this->assertEquals(array_reverse($paths), $test);
    }

    public function testAddPathsAddsPathsToStack()
    {
        $this->stack->addPath(__DIR__ . '/Helper');
        $paths = array(
            TemplatePathStack::normalizePath(__DIR__),
            TemplatePathStack::normalizePath(__DIR__ . '/_files'),
        );
        $this->stack->addPaths($paths);
        array_unshift($paths, TemplatePathStack::normalizePath(__DIR__ . '/Helper'));
        $this->assertEquals(array_reverse($paths), $this->stack->getPaths()->toArray());
    }

    public function testSetPathsOverwritesStack()
    {
        $this->stack->addPath(__DIR__ . '/Helper');
        $paths = array(
            TemplatePathStack::normalizePath(__DIR__),
            TemplatePathStack::normalizePath(__DIR__ . '/_files'),
        );
        $this->stack->setPaths($paths);
        $this->assertEquals(array_reverse($paths), $this->stack->getPaths()->toArray());
    }

    public function testClearPathsClearsStack()
    {
        $paths = array(
            __DIR__,
            __DIR__ . '/_files',
        );
        $this->stack->setPaths($paths);
        $this->stack->clearPaths();
        $this->assertEquals(0, $this->stack->getPaths()->count());
    }

    public function testLfiProtectionEnabledByDefault()
    {
        $this->assertTrue($this->stack->isLfiProtectionOn());
    }

    public function testMayDisableLfiProtection()
    {
        $this->stack->setLfiProtection(false);
        $this->assertFalse($this->stack->isLfiProtectionOn());
    }

    public function testStreamWrapperDisabledByDefault()
    {
        $this->assertFalse($this->stack->useStreamWrapper());
    }

    public function testMayEnableStreamWrapper()
    {
        $flag = (bool) ini_get('short_open_tag');
        if (!$flag) {
            $this->markTestSkipped('Short tags are disabled; cannot test');
        }
        $this->stack->setUseStreamWrapper(true);
        $this->assertTrue($this->stack->useStreamWrapper());
    }

    public function testDoesNotAllowParentDirectoryTraversalByDefault()
    {
        $this->stack->addPath(__DIR__ . '/_templates');

        $this->setExpectedException('Zend\View\Exception', 'parent directory traversal');
        $test = $this->stack->getScriptPath('../_stubs/scripts/LfiProtectionCheck.phtml');
    }

    public function testDisablingLfiProtectionAllowsParentDirectoryTraversal()
    {
        $this->stack->setLfiProtection(false)
                    ->addPath(__DIR__ . '/_templates');

        $test = $this->stack->getScriptPath('../_stubs/scripts/LfiProtectionCheck.phtml');
        $this->assertContains('LfiProtectionCheck.phtml', $test);
    }

    public function testRaisesExceptionWhenRetrievingScriptIfNoPathsRegistered()
    {
        $this->setExpectedException('Zend\View\Exception', 'unable to determine');
        $this->stack->getScriptPath('test.phtml');
    }

    public function testRaisesExceptionWhenUnableToResolveScriptToPath()
    {
        $this->stack->addPath(__DIR__ . '/_templates');
        $this->setExpectedException('Zend\View\Exception', 'not found');
        $this->stack->getScriptPath('bogus-script.txt');
    }

    public function testReturnsFullPathNameWhenAbleToResolveScriptPath()
    {
        $this->stack->addPath(__DIR__ . '/_templates');
        $expected = realpath(__DIR__ . '/_templates/test.phtml');
        $test     = $this->stack->getScriptPath('test.phtml');
        $this->assertEquals($expected, $test);
    }

    public function testReturnsPathWithStreamProtocolWhenStreamWrapperEnabled()
    {
        $flag = (bool) ini_get('short_open_tag');
        if (!$flag) {
            $this->markTestSkipped('Short tags are disabled; cannot test');
        }
        $this->stack->setUseStreamWrapper(true)
                    ->addPath(__DIR__ . '/_templates');
        $expected = 'zend.view://' . realpath(__DIR__ . '/_templates/test.phtml');
        $test     = $this->stack->getScriptPath('test.phtml');
        $this->assertEquals($expected, $test);
    }

    public function invalidOptions()
    {
        return array(
            array(true),
            array(1),
            array(1.0),
            array('foo'),
            array(new \stdClass),
        );
    }

    /**
     * @dataProvider invalidOptions
     */
    public function testSettingOptionsWithInvalidArgumentRaisesException($arg)
    {
        $this->setExpectedException('Zend\View\Exception');
        $this->stack->setOptions($arg);
    }

    public function validOptions()
    {
        $options = array(
            'lfi_protection'     => false,
            'use_stream_wrapper' => true,
        );
        return array(
            array($options),
            array(new \ArrayObject($options)),
        );
    }

    /**
     * @dataProvider validOptions
     */
    public function testAllowsSettingOptions($arg)
    {
        $arg['script_paths'] = $this->paths;
        $this->stack->setOptions($arg);
        $this->assertFalse($this->stack->isLfiProtectionOn());

        $expected = (bool) ini_get('short_open_tag') ? false : true;
        $this->assertSame($expected, $this->stack->useStreamWrapper());

        $this->assertEquals(array_reverse($this->paths), $this->stack->getPaths()->toArray());
    }

    /**
     * @dataProvider validOptions
     */
    public function testAllowsPassingOptionsToConstructor($arg)
    {
        $arg['script_paths'] = $this->paths;
        $stack = new TemplatePathStack($arg);
        $this->assertFalse($stack->isLfiProtectionOn());

        $expected = (bool) ini_get('short_open_tag') ? false : true;
        $this->assertSame($expected, $stack->useStreamWrapper());

        $this->assertEquals(array_reverse($this->paths), $stack->getPaths()->toArray());
    }
}
