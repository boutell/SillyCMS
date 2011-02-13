<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\Templating;

require_once __DIR__.'/Fixtures/SimpleHelper.php';

use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\Loader\Loader;
use Symfony\Component\Templating\Storage\Storage;
use Symfony\Component\Templating\Storage\StringStorage;
use Symfony\Component\Templating\Helper\SlotsHelper;
use Symfony\Component\Templating\TemplateNameParser;

class PhpEngineTest extends \PHPUnit_Framework_TestCase
{
    protected $loader;

    public function setUp()
    {
        $this->loader = new ProjectTemplateLoader();
    }

    public function testConstructor()
    {
        $engine = new ProjectTemplateEngine(new TemplateNameParser(), $this->loader);
        $this->assertEquals($this->loader, $engine->getLoader(), '__construct() takes a loader instance as its second first argument');
    }

    public function testOffsetGet()
    {
        $engine = new ProjectTemplateEngine(new TemplateNameParser(), $this->loader);
        $engine->set($helper = new \SimpleHelper('bar'), 'foo');
        $this->assertEquals($helper, $engine['foo'], '->offsetGet() returns the value of a helper');

        try {
            $engine['bar'];
            $this->fail('->offsetGet() throws an InvalidArgumentException if the helper is not defined');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\InvalidArgumentException', $e, '->offsetGet() throws an InvalidArgumentException if the helper is not defined');
            $this->assertEquals('The helper "bar" is not defined.', $e->getMessage(), '->offsetGet() throws an InvalidArgumentException if the helper is not defined');
        }
    }

    public function testGetSetHas()
    {
        $engine = new ProjectTemplateEngine(new TemplateNameParser(), $this->loader);
        $foo = new \SimpleHelper('foo');
        $engine->set($foo);
        $this->assertEquals($foo, $engine->get('foo'), '->set() sets a helper');

        $engine->set($foo, 'bar');
        $this->assertEquals($foo, $engine->get('bar'), '->set() takes an alias as a second argument');

        try {
            $engine->get('foobar');
            $this->fail('->get() throws an InvalidArgumentException if the helper is not defined');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\InvalidArgumentException', $e, '->get() throws an InvalidArgumentException if the helper is not defined');
            $this->assertEquals('The helper "foobar" is not defined.', $e->getMessage(), '->get() throws an InvalidArgumentException if the helper is not defined');
        }

        $this->assertTrue($engine->has('foo'), '->has() returns true if the helper exists');
        $this->assertFalse($engine->has('foobar'), '->has() returns false if the helper does not exist');
    }

    public function testExtendRender()
    {
        $engine = new ProjectTemplateEngine(new TemplateNameParser(), $this->loader, array(), array(new SlotsHelper()));
        try {
            $engine->render('name');
            $this->fail('->render() throws an InvalidArgumentException if the template does not exist');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\InvalidArgumentException', $e, '->render() throws an InvalidArgumentException if the template does not exist');
            $this->assertEquals('The template "name" does not exist.', $e->getMessage(), '->render() throws an InvalidArgumentException if the template does not exist');
        }

        $engine = new ProjectTemplateEngine(new TemplateNameParser(), $this->loader, array(new SlotsHelper()));
        $engine->set(new \SimpleHelper('bar'));
        $this->loader->setTemplate('foo.php', '<?php $view->extend("layout.php"); echo $view[\'foo\'].$foo ?>');
        $this->loader->setTemplate('layout.php', '-<?php echo $view[\'slots\']->get("_content") ?>-');
        $this->assertEquals('-barfoo-', $engine->render('foo.php', array('foo' => 'foo')), '->render() uses the decorator to decorate the template');

        $engine = new ProjectTemplateEngine(new TemplateNameParser(), $this->loader, array(new SlotsHelper()));
        $engine->set(new \SimpleHelper('bar'));
        $this->loader->setTemplate('bar.php', 'bar');
        $this->loader->setTemplate('foo.php', '<?php $view->extend("layout.php"); echo $foo ?>');
        $this->loader->setTemplate('layout.php', '<?php echo $view->render("bar.php") ?>-<?php echo $view[\'slots\']->get("_content") ?>-');
        $this->assertEquals('bar-foo-', $engine->render('foo.php', array('foo' => 'foo', 'bar' => 'bar')), '->render() supports render() calls in templates');
    }

    public function testEscape()
    {
        $engine = new ProjectTemplateEngine(new TemplateNameParser(), $this->loader);
        $this->assertEquals('&lt;br /&gt;', $engine->escape('<br />'), '->escape() escapes strings');
        $foo = new \stdClass();
        $this->assertEquals($foo, $engine->escape($foo), '->escape() does nothing on non strings');
    }

    public function testGetSetCharset()
    {
        $engine = new ProjectTemplateEngine(new TemplateNameParser(), $this->loader);
        $this->assertEquals('UTF-8', $engine->getCharset(), '->getCharset() returns UTF-8 by default');
        $engine->setCharset('ISO-8859-1');
        $this->assertEquals('ISO-8859-1', $engine->getCharset(), '->setCharset() changes the default charset to use');
    }

    public function testGlobalVariables()
    {
        $engine = new ProjectTemplateEngine(new TemplateNameParser(), $this->loader);
        $engine->addGlobal('global_variable', 'lorem ipsum');

        $this->assertEquals(array(
            'global_variable' => 'lorem ipsum',
        ), $engine->getGlobals());
    }

    public function testGlobalsGetPassedToTemplate()
    {
        $engine = new ProjectTemplateEngine(new TemplateNameParser(), $this->loader);
        $engine->addGlobal('global', 'global variable');

        $this->loader->setTemplate('global.php', '<?php echo $global; ?>');

        $this->assertEquals($engine->render('global.php'), 'global variable');

        $this->assertEquals($engine->render('global.php', array('global' => 'overwritten')), 'overwritten');
    }
}

class ProjectTemplateEngine extends PhpEngine
{
    public function getLoader()
    {
        return $this->loader;
    }
}

class ProjectTemplateLoader extends Loader
{
    public $templates = array();

    public function setTemplate($name, $template)
    {
        $this->templates[$this->getKey(array('name' => $name, 'engine' => 'php'))] = $template;
    }

    public function load($name)
    {
        if (isset($this->templates[$this->getKey($name)])) {
            return new StringStorage($this->templates[$this->getKey($name)]);
        }

        return false;
    }

    public function isFresh($template, $time)
    {
        return false;
    }

    protected function getKey($template)
    {
        return md5(serialize($template));
    }
}
