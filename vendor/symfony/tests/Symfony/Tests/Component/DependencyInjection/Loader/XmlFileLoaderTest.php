<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\DependencyInjection\Loader;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\Loader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Loader\IniFileLoader;
use Symfony\Component\DependencyInjection\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\Loader\FileLocator;

class XmlFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    static protected $fixturesPath;

    static public function setUpBeforeClass()
    {
        self::$fixturesPath = realpath(__DIR__.'/../Fixtures/');
        require_once self::$fixturesPath.'/includes/foo.php';
        require_once self::$fixturesPath.'/includes/ProjectExtension.php';
        require_once self::$fixturesPath.'/includes/ProjectWithXsdExtension.php';
        require_once self::$fixturesPath.'/includes/ProjectWithXsdExtensionInPhar.phar';
    }

    public function testLoad()
    {
        $loader = new ProjectLoader2(new ContainerBuilder(), new FileLocator(self::$fixturesPath.'/ini'));

        try {
            $loader->load('foo.xml');
            $this->fail('->load() throws an InvalidArgumentException if the loaded file does not exist');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\InvalidArgumentException', $e, '->load() throws an InvalidArgumentException if the loaded file does not exist');
            $this->assertStringStartsWith('The file "foo.xml" does not exist (in:', $e->getMessage(), '->load() throws an InvalidArgumentException if the loaded file does not exist');
        }
    }

    public function testParseFile()
    {
        $loader = new ProjectLoader2(new ContainerBuilder(), new FileLocator(self::$fixturesPath.'/ini'));

        try {
            $loader->parseFile(self::$fixturesPath.'/ini/parameters.ini');
            $this->fail('->parseFile() throws an InvalidArgumentException if the loaded file is not a valid XML file');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\InvalidArgumentException', $e, '->parseFile() throws an InvalidArgumentException if the loaded file is not a valid XML file');
            $this->assertStringStartsWith('[ERROR 4] Start tag expected, \'<\' not found (in', $e->getMessage(), '->parseFile() throws an InvalidArgumentException if the loaded file is not a valid XML file');
        }

        $loader = new ProjectLoader2(new ContainerBuilder(), new FileLocator(self::$fixturesPath.'/xml'));

        try {
            $loader->parseFile(self::$fixturesPath.'/xml/nonvalid.xml');
            $this->fail('->parseFile() throws an InvalidArgumentException if the loaded file does not validate the XSD');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\InvalidArgumentException', $e, '->parseFile() throws an InvalidArgumentException if the loaded file does not validate the XSD');
            $this->assertStringStartsWith('[ERROR 1845] Element \'nonvalid\': No matching global declaration available for the validation root. (in', $e->getMessage(), '->parseFile() throws an InvalidArgumentException if the loaded file does not validate the XSD');
        }

        $xml = $loader->parseFile(self::$fixturesPath.'/xml/services1.xml');
        $this->assertEquals('Symfony\\Component\\DependencyInjection\\SimpleXMLElement', get_class($xml), '->parseFile() returns an SimpleXMLElement object');
    }

    public function testLoadParameters()
    {
        $container = new ContainerBuilder();
        $loader = new ProjectLoader2($container, new FileLocator(self::$fixturesPath.'/xml'));
        $loader->load('services2.xml');

        $actual = $container->getParameterBag()->all();
        $expected = array('a string', 'foo' => 'bar', 'values' => array(0, 'integer' => 4, 100 => null, 'true', true, false, 'on', 'off', 'float' => 1.3, 1000.3, 'a string', array('foo', 'bar')), 'foo_bar' => new Reference('foo_bar'));

        $this->assertEquals($expected, $actual, '->load() converts XML values to PHP ones');
    }

    public function testLoadImports()
    {
        $container = new ContainerBuilder();
        $resolver = new LoaderResolver(array(
            new IniFileLoader($container, new FileLocator(self::$fixturesPath.'/xml')),
            new YamlFileLoader($container, new FileLocator(self::$fixturesPath.'/xml')),
            $loader = new ProjectLoader2($container, new FileLocator(self::$fixturesPath.'/xml')),
        ));
        $loader->setResolver($resolver);
        $loader->load('services4.xml');

        $actual = $container->getParameterBag()->all();
        $expected = array('a string', 'foo' => 'bar', 'values' => array(true, false), 'foo_bar' => new Reference('foo_bar'), 'bar' => '%foo%', 'imported_from_ini' => true, 'imported_from_yaml' => true);

        $this->assertEquals(array_keys($expected), array_keys($actual), '->load() imports and merges imported files');
    }

    public function testLoadAnonymousServices()
    {
        $container = new ContainerBuilder();
        $loader = new ProjectLoader2($container, new FileLocator(self::$fixturesPath.'/xml'));
        $loader->load('services5.xml');
        $services = $container->getDefinitions();
        $this->assertEquals(3, count($services), '->load() attributes unique ids to anonymous services');
        $args = $services['foo']->getArguments();
        $this->assertEquals(1, count($args), '->load() references anonymous services as "normal" ones');
        $this->assertEquals('Symfony\\Component\\DependencyInjection\\Reference', get_class($args[0]), '->load() converts anonymous services to references to "normal" services');
        $this->assertTrue(isset($services[(string) $args[0]]), '->load() makes a reference to the created ones');
        $inner = $services[(string) $args[0]];
        $this->assertEquals('BarClass', $inner->getClass(), '->load() uses the same configuration as for the anonymous ones');

        $args = $inner->getArguments();
        $this->assertEquals(1, count($args), '->load() references anonymous services as "normal" ones');
        $this->assertEquals('Symfony\\Component\\DependencyInjection\\Reference', get_class($args[0]), '->load() converts anonymous services to references to "normal" services');
        $this->assertTrue(isset($services[(string) $args[0]]), '->load() makes a reference to the created ones');
        $inner = $services[(string) $args[0]];
        $this->assertEquals('BazClass', $inner->getClass(), '->load() uses the same configuration as for the anonymous ones');
    }

    public function testLoadServices()
    {
        $container = new ContainerBuilder();
        $loader = new ProjectLoader2($container, new FileLocator(self::$fixturesPath.'/xml'));
        $loader->load('services6.xml');
        $services = $container->getDefinitions();
        $this->assertTrue(isset($services['foo']), '->load() parses <service> elements');
        $this->assertEquals('Symfony\\Component\\DependencyInjection\\Definition', get_class($services['foo']), '->load() converts <service> element to Definition instances');
        $this->assertEquals('FooClass', $services['foo']->getClass(), '->load() parses the class attribute');
        $this->assertEquals('container', $services['scope.container']->getScope());
        $this->assertEquals('custom', $services['scope.custom']->getScope());
        $this->assertEquals('prototype', $services['scope.prototype']->getScope());
        $this->assertEquals('getInstance', $services['constructor']->getFactoryMethod(), '->load() parses the factory-method attribute');
        $this->assertEquals('%path%/foo.php', $services['file']->getFile(), '->load() parses the file tag');
        $this->assertEquals(array('foo', new Reference('foo'), array(true, false)), $services['arguments']->getArguments(), '->load() parses the argument tags');
        $this->assertEquals('sc_configure', $services['configurator1']->getConfigurator(), '->load() parses the configurator tag');
        $this->assertEquals(array(new Reference('baz', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, false), 'configure'), $services['configurator2']->getConfigurator(), '->load() parses the configurator tag');
        $this->assertEquals(array('BazClass', 'configureStatic'), $services['configurator3']->getConfigurator(), '->load() parses the configurator tag');
        $this->assertEquals(array(array('setBar', array())), $services['method_call1']->getMethodCalls(), '->load() parses the method_call tag');
        $this->assertEquals(array(array('setBar', array('foo', new Reference('foo'), array(true, false)))), $services['method_call2']->getMethodCalls(), '->load() parses the method_call tag');
        $this->assertNull($services['factory_service']->getClass());
        $this->assertEquals('getInstance', $services['factory_service']->getFactoryMethod());
        $this->assertEquals('baz_factory', $services['factory_service']->getFactoryService());

        $aliases = $container->getAliases();
        $this->assertTrue(isset($aliases['alias_for_foo']), '->load() parses <service> elements');
        $this->assertEquals('foo', (string) $aliases['alias_for_foo'], '->load() parses aliases');
        $this->assertTrue($aliases['alias_for_foo']->isPublic());
        $this->assertTrue(isset($aliases['another_alias_for_foo']));
        $this->assertEquals('foo', (string) $aliases['another_alias_for_foo']);
        $this->assertFalse($aliases['another_alias_for_foo']->isPublic());
    }

    public function testConvertDomElementToArray()
    {
        $doc = new \DOMDocument("1.0");
        $doc->loadXML('<foo>bar</foo>');
        $this->assertEquals('bar', ProjectLoader2::convertDomElementToArray($doc->documentElement), '::convertDomElementToArray() converts a \DomElement to an array');

        $doc = new \DOMDocument("1.0");
        $doc->loadXML('<foo foo="bar" />');
        $this->assertEquals(array('foo' => 'bar'), ProjectLoader2::convertDomElementToArray($doc->documentElement), '::convertDomElementToArray() converts a \DomElement to an array');

        $doc = new \DOMDocument("1.0");
        $doc->loadXML('<foo><foo>bar</foo></foo>');
        $this->assertEquals(array('foo' => 'bar'), ProjectLoader2::convertDomElementToArray($doc->documentElement), '::convertDomElementToArray() converts a \DomElement to an array');

        $doc = new \DOMDocument("1.0");
        $doc->loadXML('<foo><foo>bar<foo>bar</foo></foo></foo>');
        $this->assertEquals(array('foo' => array('value' => 'bar', 'foo' => 'bar')), ProjectLoader2::convertDomElementToArray($doc->documentElement), '::convertDomElementToArray() converts a \DomElement to an array');

        $doc = new \DOMDocument("1.0");
        $doc->loadXML('<foo><foo></foo></foo>');
        $this->assertEquals(array('foo' => null), ProjectLoader2::convertDomElementToArray($doc->documentElement), '::convertDomElementToArray() converts a \DomElement to an array');

        $doc = new \DOMDocument("1.0");
        $doc->loadXML('<foo><foo><!-- foo --></foo></foo>');
        $this->assertEquals(array('foo' => null), ProjectLoader2::convertDomElementToArray($doc->documentElement), '::convertDomElementToArray() converts a \DomElement to an array');

        $doc = new \DOMDocument("1.0");
        $doc->loadXML('<foo><foo foo="bar"/><foo foo="bar"/></foo>');
        $this->assertEquals(array('foo' => array(array('foo' => 'bar'), array('foo' => 'bar'))), ProjectLoader2::convertDomElementToArray($doc->documentElement), '::convertDomElementToArray() converts a \DomElement to an array');
    }

    public function testExtensions()
    {
        $container = new ContainerBuilder();
        $container->registerExtension(new \ProjectExtension());
        $container->registerExtension(new \ProjectWithXsdExtension());
        $loader = new ProjectLoader2($container, new FileLocator(self::$fixturesPath.'/xml'));

        // extension without an XSD
        $loader->load('extensions/services1.xml');
        $container->compile();
        $services = $container->getDefinitions();
        $parameters = $container->getParameterBag()->all();

        $this->assertTrue(isset($services['project.service.bar']), '->load() parses extension elements');
        $this->assertTrue(isset($parameters['project.parameter.bar']), '->load() parses extension elements');

        $this->assertEquals('BAR', $services['project.service.foo']->getClass(), '->load() parses extension elements');
        $this->assertEquals('BAR', $parameters['project.parameter.foo'], '->load() parses extension elements');

        // extension with an XSD
        $container = new ContainerBuilder();
        $container->registerExtension(new \ProjectExtension());
        $container->registerExtension(new \ProjectWithXsdExtension());
        $loader = new ProjectLoader2($container, new FileLocator(self::$fixturesPath.'/xml'));
        $loader->load('extensions/services2.xml');
        $container->compile();
        $services = $container->getDefinitions();
        $parameters = $container->getParameterBag()->all();

        $this->assertTrue(isset($services['project.service.bar']), '->load() parses extension elements');
        $this->assertTrue(isset($parameters['project.parameter.bar']), '->load() parses extension elements');

        $this->assertEquals('BAR', $services['project.service.foo']->getClass(), '->load() parses extension elements');
        $this->assertEquals('BAR', $parameters['project.parameter.foo'], '->load() parses extension elements');

        $loader = new ProjectLoader2(new ContainerBuilder(), new FileLocator(self::$fixturesPath.'/xml'));

        // extension with an XSD (does not validate)
        try {
            $loader->load('extensions/services3.xml');
            $this->fail('->load() throws an InvalidArgumentException if the configuration does not validate the XSD');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\InvalidArgumentException', $e, '->load() throws an InvalidArgumentException if the configuration does not validate the XSD');
            $this->assertRegexp('/The attribute \'bar\' is not allowed/', $e->getMessage(), '->load() throws an InvalidArgumentException if the configuration does not validate the XSD');
        }

        // non-registered extension
        try {
            $loader->load('extensions/services4.xml');
            $this->fail('->load() throws an InvalidArgumentException if the tag is not valid');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\InvalidArgumentException', $e, '->load() throws an InvalidArgumentException if the tag is not valid');
            $this->assertStringStartsWith('There is no extension able to load the configuration for "project:bar" (in', $e->getMessage(), '->load() throws an InvalidArgumentException if the tag is not valid');
        }
    }

    public function testExtensionInPhar()
    {
        // extension with an XSD in PHAR archive
        $container = new ContainerBuilder();
        $container->registerExtension(new \ProjectWithXsdExtensionInPhar());
        $loader = new ProjectLoader2($container, new FileLocator(self::$fixturesPath.'/xml'));
        $loader->load('extensions/services6.xml');

        // extension with an XSD in PHAR archive (does not validate)
        try {
            $loader->load('extensions/services7.xml');
            $this->fail('->load() throws an InvalidArgumentException if the configuration does not validate the XSD');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\InvalidArgumentException', $e, '->load() throws an InvalidArgumentException if the configuration does not validate the XSD');
            $this->assertRegexp('/The attribute \'bar\' is not allowed/', $e->getMessage(), '->load() throws an InvalidArgumentException if the configuration does not validate the XSD');
        }
    }

    /**
     * @covers Symfony\Component\DependencyInjection\Loader\XmlFileLoader::supports
     */
    public function testSupports()
    {
        $loader = new XmlFileLoader(new ContainerBuilder(), new FileLocator());

        $this->assertTrue($loader->supports('foo.xml'), '->supports() returns true if the resource is loadable');
        $this->assertFalse($loader->supports('foo.foo'), '->supports() returns true if the resource is loadable');
    }

    public function testLoadInterfaceInjectors()
    {
        $container = new ContainerBuilder();
        $loader = new ProjectLoader2($container, new FileLocator(self::$fixturesPath.'/xml'));
        $loader->load('interfaces1.xml');
        $interfaces = $container->getInterfaceInjectors('FooClass');
        $this->assertEquals(1, count($interfaces), '->load() parses <interface> elements');
        $interface = $interfaces['FooClass'];
        $this->assertTrue($interface->hasMethodCall('setBar'), '->load() applies method calls correctly');
    }
}

class ProjectLoader2 extends XmlFileLoader
{
    public function parseFile($file)
    {
        return parent::parseFile($file);
    }
}
