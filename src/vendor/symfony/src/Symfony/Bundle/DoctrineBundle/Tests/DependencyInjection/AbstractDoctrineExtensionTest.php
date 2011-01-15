<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\DoctrineBundle\Tests\DependencyInjection;

use Symfony\Bundle\DoctrineBundle\Tests\TestCase;
use Symfony\Bundle\DoctrineBundle\DependencyInjection\DoctrineExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

abstract class AbstractDoctrineExtensionTest extends TestCase
{
    abstract protected function loadFromFile(ContainerBuilder $container, $file);

    public function testDbalLoad()
    {
        $container = $this->getContainer();
        $loader = new DoctrineExtension();

        $loader->dbalLoad(array(), $container);
        $this->assertEquals('Symfony\\Bundle\\DoctrineBundle\\DataCollector\\DoctrineDataCollector', $container->getParameter('doctrine.data_collector.class'), '->dbalLoad() loads the dbal.xml file if not already loaded');

        // doctrine.dbal.default_connection
        $this->assertEquals('default', $container->getParameter('doctrine.dbal.default_connection'), '->dbalLoad() overrides existing configuration options');
        $loader->dbalLoad(array('default_connection' => 'foo'), $container);
        $this->assertEquals('foo', $container->getParameter('doctrine.dbal.default_connection'), '->dbalLoad() overrides existing configuration options');
        $loader->dbalLoad(array(), $container);
        $this->assertEquals('foo', $container->getParameter('doctrine.dbal.default_connection'), '->dbalLoad() overrides existing configuration options');

        $container = $this->getContainer();
        $loader = new DoctrineExtension();
        $loader->dbalLoad(array('password' => 'foo'), $container);

        $arguments = $container->getDefinition('doctrine.dbal.default_connection')->getArguments();
        $config = $arguments[0];

        $this->assertEquals('foo', $config['password']);
        $this->assertEquals('root', $config['user']);

        $loader->dbalLoad(array('user' => 'foo'), $container);
        $this->assertEquals('foo', $config['password']);
        $this->assertEquals('root', $config['user']);
    }

    public function testDbalLoadFromXmlMultipleConnections()
    {
        $container = $this->getContainer();
        $loader = new DoctrineExtension();
        $container->registerExtension($loader);

        $loadXml = new XmlFileLoader($container, __DIR__.'/Fixtures/config/xml');
        $loadXml->load('dbal_service_multiple_connections.xml');
        $loader->dbalLoad(array(), $container);

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->freeze();

        // doctrine.dbal.mysql_connection
        $arguments = $container->getDefinition('doctrine.dbal.mysql_connection')->getArguments();
        $config = $arguments[0];

        $this->assertEquals('mysql_s3cr3t', $config['password']);
        $this->assertEquals('mysql_user', $config['user']);
        $this->assertEquals('mysql_db', $config['dbname']);
        $this->assertEquals('/path/to/mysqld.sock', $config['unix_socket']);

        // doctrine.dbal.sqlite_connection
        $arguments = $container->getDefinition('doctrine.dbal.sqlite_connection')->getArguments();
        $container = $this->getContainer();
        $loader = new DoctrineExtension();
        $container->registerExtension($loader);

        $loadXml = new XmlFileLoader($container, __DIR__.'/Fixtures/config/xml');
        $loadXml->load('dbal_service_single_connection.xml');
        $loader->dbalLoad(array(), $container);

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->freeze();

        // doctrine.dbal.mysql_connection
        $arguments = $container->getDefinition('doctrine.dbal.mysql_connection')->getArguments();
        $config = $arguments[0];

        $this->assertEquals('mysql_s3cr3t', $config['password']);
        $this->assertEquals('mysql_user', $config['user']);
        $this->assertEquals('mysql_db', $config['dbname']);
        $this->assertEquals('/path/to/mysqld.sock', $config['unix_socket']);
    }

    public function testDependencyInjectionConfigurationDefaults()
    {
        $container = $this->getContainer();
        $loader = new DoctrineExtension();

        $loader->dbalLoad(array(), $container);
        $loader->ormLoad(array('mappings' => array('YamlBundle' => array())), $container);

        $this->assertEquals('Doctrine\DBAL\Connection', $container->getParameter('doctrine.dbal.connection_class'));
        $this->assertEquals('Doctrine\ORM\Configuration', $container->getParameter('doctrine.orm.configuration_class'));
        $this->assertEquals('Doctrine\ORM\EntityManager', $container->getParameter('doctrine.orm.entity_manager_class'));
        $this->assertEquals('Proxies', $container->getParameter('doctrine.orm.proxy_namespace'));
        $this->assertEquals(false, $container->getParameter('doctrine.orm.auto_generate_proxy_classes'));
        $this->assertEquals('Doctrine\Common\Cache\ArrayCache', $container->getParameter('doctrine.orm.cache.array_class'));
        $this->assertEquals('Doctrine\Common\Cache\ApcCache', $container->getParameter('doctrine.orm.cache.apc_class'));
        $this->assertEquals('Doctrine\Common\Cache\MemcacheCache', $container->getParameter('doctrine.orm.cache.memcache_class'));
        $this->assertEquals('localhost', $container->getParameter('doctrine.orm.cache.memcache_host'));
        $this->assertEquals('11211', $container->getParameter('doctrine.orm.cache.memcache_port'));
        $this->assertEquals('Memcache', $container->getParameter('doctrine.orm.cache.memcache_instance_class'));
        $this->assertEquals('Doctrine\Common\Cache\XcacheCache', $container->getParameter('doctrine.orm.cache.xcache_class'));
        $this->assertEquals('Doctrine\ORM\Mapping\Driver\DriverChain', $container->getParameter('doctrine.orm.metadata.driver_chain_class'));
        $this->assertEquals('Doctrine\ORM\Mapping\Driver\AnnotationDriver', $container->getParameter('doctrine.orm.metadata.annotation_class'));
        $this->assertEquals('Doctrine\Common\Annotations\AnnotationReader', $container->getParameter('doctrine.orm.metadata.annotation_reader_class'));
        $this->assertEquals('Doctrine\ORM\Mapping\\', $container->getParameter('doctrine.orm.metadata.annotation_default_namespace'));
        $this->assertEquals('Doctrine\ORM\Mapping\Driver\XmlDriver', $container->getParameter('doctrine.orm.metadata.xml_class'));
        $this->assertEquals('Doctrine\ORM\Mapping\Driver\YamlDriver', $container->getParameter('doctrine.orm.metadata.yml_class'));

        $config = array(
            'proxy_namespace' => 'MyProxies',
            'auto_generate_proxy_classes' => true,
            'mappings' => array('YamlBundle' => array()),
        );

        $loader->dbalLoad(array(), $container);
        $loader->ormLoad($config, $container);

        $this->assertEquals('MyProxies', $container->getParameter('doctrine.orm.proxy_namespace'));
        $this->assertEquals(true, $container->getParameter('doctrine.orm.auto_generate_proxy_classes'));

        $definition = $container->getDefinition('doctrine.dbal.default_connection');
        $this->assertEquals('Doctrine\DBAL\DriverManager', $definition->getClass());

        $args = $definition->getArguments();
        $this->assertEquals('Doctrine\DBAL\Driver\PDOMySql\Driver', $args[0]['driverClass']);
        $this->assertEquals('localhost', $args[0]['host']);
        $this->assertEquals('root', $args[0]['user']);
        $this->assertEquals('doctrine.dbal.default_connection.configuration', (string) $args[1]);
        $this->assertEquals('doctrine.dbal.default_connection.event_manager', (string) $args[2]);

        $definition = $container->getDefinition('doctrine.orm.default_entity_manager');
        $this->assertEquals('%doctrine.orm.entity_manager_class%', $definition->getClass());
        $this->assertEquals('create', $definition->getFactoryMethod());
        $this->assertArrayHasKey('doctrine.orm.entity_manager', $definition->getTags());

        $arguments = $definition->getArguments();
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $arguments[0]);
        $this->assertEquals('doctrine.dbal.default_connection', (string) $arguments[0]);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $arguments[1]);
        $this->assertEquals('doctrine.orm.default_configuration', (string) $arguments[1]);

        $definition = $container->getDefinition('doctrine.orm.default_configuration');
        $calls = array_values($definition->getMethodCalls());
        $this->assertEquals(array('YamlBundle' => 'DoctrineBundle\Tests\DependencyInjection\Fixtures\Bundles\YamlBundle\Entity'), $calls[0][1][0]);
        $this->assertEquals('doctrine.orm.default_metadata_cache', (string) $calls[1][1][0]);
        $this->assertEquals('doctrine.orm.default_query_cache', (string) $calls[2][1][0]);
        $this->assertEquals('doctrine.orm.default_result_cache', (string) $calls[3][1][0]);

        $definition = $container->getDefinition('doctrine.orm.default_metadata_cache');
        $this->assertEquals('%doctrine.orm.cache.array_class%', $definition->getClass());

        $definition = $container->getDefinition('doctrine.orm.default_query_cache');
        $this->assertEquals('%doctrine.orm.cache.array_class%', $definition->getClass());

        $definition = $container->getDefinition('doctrine.orm.default_result_cache');
        $this->assertEquals('%doctrine.orm.cache.array_class%', $definition->getClass());
    }

    public function testSingleEntityManagerConfiguration()
    {
        $container = $this->getContainer();
        $loader = new DoctrineExtension();

        $loader->dbalLoad(array(), $container);
        $loader->ormLoad(array(), $container);

        $definition = $container->getDefinition('doctrine.dbal.default_connection');
        $this->assertEquals('Doctrine\DBAL\DriverManager', $definition->getClass());

        $definition = $container->getDefinition('doctrine.orm.default_entity_manager');
        $this->assertEquals('%doctrine.orm.entity_manager_class%', $definition->getClass());
        $this->assertEquals('create', $definition->getFactoryMethod());
        $this->assertArrayHasKey('doctrine.orm.entity_manager', $definition->getTags());

        $this->assertDICConstructorArguments($definition, array(
            new Reference('doctrine.dbal.default_connection'), new Reference('doctrine.orm.default_configuration')
        ));
    }

    public function testLoadSimpleSingleConnection()
    {
        $container = $this->getContainer();
        $loader = new DoctrineExtension();
        $container->registerExtension($loader);

        $loader->dbalLoad(array(), $container);
        $loader->ormLoad(array(), $container);

        $this->loadFromFile($container, 'orm_service_simple_single_entity_manager');

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->freeze();

        $definition = $container->getDefinition('doctrine.dbal.default_connection');
        $this->assertEquals('Doctrine\DBAL\DriverManager', $definition->getClass());

        $this->assertDICConstructorArguments($definition, array(
            array(
                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                'driverOptions' => array(),
                'host' => 'localhost',
                'user' => 'root',
            ),
            new Reference('doctrine.dbal.default_connection.configuration'),
            new Reference('doctrine.dbal.default_connection.event_manager')
        ));

        $definition = $container->getDefinition('doctrine.orm.default_entity_manager');
        $this->assertEquals('%doctrine.orm.entity_manager_class%', $definition->getClass());
        $this->assertEquals('create', $definition->getFactoryMethod());
        $this->assertArrayHasKey('doctrine.orm.entity_manager', $definition->getTags());

        $this->assertDICConstructorArguments($definition, array(
            new Reference('doctrine.dbal.default_connection'), new Reference('doctrine.orm.default_configuration')
        ));
    }

    public function testLoadSingleConnection()
    {
        $container = $this->getContainer();
        $loader = new DoctrineExtension();
        $container->registerExtension($loader);

        $this->loadFromFile($container, 'orm_service_single_entity_manager');

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->freeze();

        $definition = $container->getDefinition('doctrine.dbal.default_connection');
        $this->assertEquals('Doctrine\DBAL\DriverManager', $definition->getClass());

        $this->assertDICConstructorArguments($definition, array(
            array(
                'driverClass' => 'Doctrine\DBAL\Driver\PDOSqlite\Driver',
                'driverOptions' => array(),
                'dbname' => 'sqlite_db',
                'host' => 'localhost',
                'user' => 'sqlite_user',
                'password' => 'sqlite_s3cr3t',
                'memory' => true,
            ),
            new Reference('doctrine.dbal.default_connection.configuration'),
            new Reference('doctrine.dbal.default_connection.event_manager')
        ));

        $definition = $container->getDefinition('doctrine.orm.default_entity_manager');
        $this->assertEquals('%doctrine.orm.entity_manager_class%', $definition->getClass());
        $this->assertEquals('create', $definition->getFactoryMethod());
        $this->assertArrayHasKey('doctrine.orm.entity_manager', $definition->getTags());

        $this->assertDICConstructorArguments($definition, array(
            new Reference('doctrine.dbal.default_connection'), new Reference('doctrine.orm.default_configuration')
        ));
    }

    public function testLoadMultipleConnections()
    {
        $container = $this->getContainer();
        $loader = new DoctrineExtension();
        $container->registerExtension($loader);

        $this->loadFromFile($container, 'orm_service_multiple_entity_managers');

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->freeze();

        $definition = $container->getDefinition('doctrine.dbal.conn1_connection');
        $this->assertEquals('Doctrine\DBAL\DriverManager', $definition->getClass());

        $args = $definition->getArguments();
        $this->assertEquals('Doctrine\DBAL\Driver\PDOSqlite\Driver', $args[0]['driverClass']);
        $this->assertEquals('localhost', $args[0]['host']);
        $this->assertEquals('sqlite_user', $args[0]['user']);
        $this->assertEquals('doctrine.dbal.conn1_connection.configuration', (string) $args[1]);
        $this->assertEquals('doctrine.dbal.conn1_connection.event_manager', (string) $args[2]);

        $this->assertEquals('doctrine.orm.dm2_entity_manager', $container->getAlias('doctrine.orm.entity_manager'));

        $definition = $container->getDefinition('doctrine.orm.dm1_entity_manager');
        $this->assertEquals('%doctrine.orm.entity_manager_class%', $definition->getClass());
        $this->assertEquals('create', $definition->getFactoryMethod());
        $this->assertArrayHasKey('doctrine.orm.entity_manager', $definition->getTags());

        $arguments = $definition->getArguments();
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $arguments[0]);
        $this->assertEquals('doctrine.dbal.conn1_connection', (string) $arguments[0]);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $arguments[1]);
        $this->assertEquals('doctrine.orm.dm1_configuration', (string) $arguments[1]);

        $definition = $container->getDefinition('doctrine.dbal.conn2_connection');
        $this->assertEquals('Doctrine\DBAL\DriverManager', $definition->getClass());

        $args = $definition->getArguments();
        $this->assertEquals('Doctrine\DBAL\Driver\PDOSqlite\Driver', $args[0]['driverClass']);
        $this->assertEquals('localhost', $args[0]['host']);
        $this->assertEquals('sqlite_user', $args[0]['user']);
        $this->assertEquals('doctrine.dbal.conn2_connection.configuration', (string) $args[1]);
        $this->assertEquals('doctrine.dbal.conn2_connection.event_manager', (string) $args[2]);

        $definition = $container->getDefinition('doctrine.orm.dm2_entity_manager');
        $this->assertEquals('%doctrine.orm.entity_manager_class%', $definition->getClass());
        $this->assertEquals('create', $definition->getFactoryMethod());
        $this->assertArrayHasKey('doctrine.orm.entity_manager', $definition->getTags());

        $arguments = $definition->getArguments();
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $arguments[0]);
        $this->assertEquals('doctrine.dbal.conn2_connection', (string) $arguments[0]);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $arguments[1]);
        $this->assertEquals('doctrine.orm.dm2_configuration', (string) $arguments[1]);

        $definition = $container->getDefinition('doctrine.orm.dm1_metadata_cache');
        $this->assertEquals('%doctrine.orm.cache.xcache_class%', $definition->getClass());

        $definition = $container->getDefinition('doctrine.orm.dm1_query_cache');
        $this->assertEquals('%doctrine.orm.cache.array_class%', $definition->getClass());

        $definition = $container->getDefinition('doctrine.orm.dm1_result_cache');
        $this->assertEquals('%doctrine.orm.cache.array_class%', $definition->getClass());
    }

    public function testBundleEntityAliases()
    {
        $container = $this->getContainer();
        $loader = new DoctrineExtension();

        $loader->dbalLoad(array(), $container);
        $loader->ormLoad(array('mappings' => array('YamlBundle' => array())), $container);

        $definition = $container->getDefinition('doctrine.orm.default_configuration');
        $this->assertDICDefinitionMethodCallOnce($definition, 'setEntityNamespaces',
            array(array('YamlBundle' => 'DoctrineBundle\Tests\DependencyInjection\Fixtures\Bundles\YamlBundle\Entity'))
        );
    }

    public function testOverwriteEntityAliases()
    {
        $container = $this->getContainer();
        $loader = new DoctrineExtension();

        $loader->dbalLoad(array(), $container);
        $loader->ormLoad(array('mappings' => array('YamlBundle' => array('alias' => 'yml'))), $container);

        $definition = $container->getDefinition('doctrine.orm.default_configuration');
        $this->assertDICDefinitionMethodCallOnce($definition, 'setEntityNamespaces',
            array(array('yml' => 'DoctrineBundle\Tests\DependencyInjection\Fixtures\Bundles\YamlBundle\Entity'))
        );
    }

    public function testYamlBundleMappingDetection()
    {
        $container = $this->getContainer('YamlBundle');
        $loader = new DoctrineExtension();

        $loader->dbalLoad(array(), $container);
        $loader->ormLoad(array('mappings' => array('YamlBundle' => array())), $container);

        $definition = $container->getDefinition('doctrine.orm.default_metadata_driver');
        $this->assertDICDefinitionMethodCallOnce($definition, 'addDriver', array(
            new Reference('doctrine.orm.default_yml_metadata_driver'),
            'DoctrineBundle\Tests\DependencyInjection\Fixtures\Bundles\YamlBundle\Entity'
        ));
    }

    public function testXmlBundleMappingDetection()
    {
        $container = $this->getContainer('XmlBundle');
        $loader = new DoctrineExtension();

        $loader->dbalLoad(array(), $container);
        $loader->ormLoad(array('mappings' => array('XmlBundle' => array())), $container);

        $definition = $container->getDefinition('doctrine.orm.default_metadata_driver');
        $this->assertDICDefinitionMethodCallOnce($definition, 'addDriver', array(
            new Reference('doctrine.orm.default_xml_metadata_driver'),
            'DoctrineBundle\Tests\DependencyInjection\Fixtures\Bundles\XmlBundle\Entity'
        ));
    }

    public function testAnnotationsBundleMappingDetection()
    {
        $container = $this->getContainer('AnnotationsBundle');
        $loader = new DoctrineExtension();

        $loader->dbalLoad(array(), $container);
        $loader->ormLoad(array('mappings' => array('AnnotationsBundle' => array())), $container);

        $definition = $container->getDefinition('doctrine.orm.default_metadata_driver');
        $this->assertDICDefinitionMethodCallOnce($definition, 'addDriver', array(
            new Reference('doctrine.orm.default_annotation_metadata_driver'),
            'DoctrineBundle\Tests\DependencyInjection\Fixtures\Bundles\AnnotationsBundle\Entity'
        ));
    }

    public function testMultipleOrmLoadCalls()
    {
        $container = $this->getContainer('AnnotationsBundle');
        $loader = new DoctrineExtension();

        $loader->dbalLoad(array(), $container);
        $loader->ormLoad(array(
            'auto_generate_proxy_dir' => true,
            'mappings' => array('AnnotationsBundle' => array())
        ), $container);
        $loader->ormLoad(array(
            'auto_generate_proxy_dir' => false,
            'mappings' => array('XmlBundle' => array())
        ), $container);

        $definition = $container->getDefinition('doctrine.orm.default_metadata_driver');
        $this->assertDICDefinitionMethodCallAt(0, $definition, 'addDriver', array(
            new Reference('doctrine.orm.default_annotation_metadata_driver'),
            'DoctrineBundle\Tests\DependencyInjection\Fixtures\Bundles\AnnotationsBundle\Entity'
        ));
        $this->assertDICDefinitionMethodCallAt(1, $definition, 'addDriver', array(
            new Reference('doctrine.orm.default_annotation_metadata_driver'),
            'DoctrineBundle\Tests\DependencyInjection\Fixtures\Bundles\XmlBundle\Entity'
        ));

        $configDef = $container->getDefinition('doctrine.orm.default_configuration');
        $this->assertDICDefinitionMethodCallOnce($configDef, 'setAutoGenerateProxyClasses', array(false));
    }

    public function testEntityManagerMetadataCacheDriverConfiguration()
    {
        $container = $this->getContainer();
        $loader = new DoctrineExtension();
        $container->registerExtension($loader);

        $this->loadFromFile($container, 'orm_service_multiple_entity_managers');

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->freeze();

        $definition = $container->getDefinition('doctrine.orm.dm1_metadata_cache');
        $this->assertDICDefinitionClass($definition, '%doctrine.orm.cache.xcache_class%');

        $definition = $container->getDefinition('doctrine.orm.dm2_metadata_cache');
        $this->assertDICDefinitionClass($definition, '%doctrine.orm.cache.apc_class%');
    }

    public function testEntityManagerMemcacheMetadataCacheDriverConfiguration()
    {
        $container = $this->getContainer();
        $loader = new DoctrineExtension();
        $container->registerExtension($loader);

        $this->loadFromFile($container, 'orm_service_simple_single_entity_manager');

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->freeze();

        $definition = $container->getDefinition('doctrine.orm.default_metadata_cache');
        $this->assertDICDefinitionClass($definition, 'Doctrine\Common\Cache\MemcacheCache');
        $this->assertDICDefinitionMethodCallOnce($definition, 'setMemcache',
            array(new Reference('doctrine.orm.default_memcache_instance'))
        );

        $definition = $container->getDefinition('doctrine.orm.default_memcache_instance');
        $this->assertDICDefinitionClass($definition, 'Memcache');
        $this->assertDICDefinitionMethodCallOnce($definition, 'connect', array('localhost', 11211));
    }

    public function testDependencyInjectionImportsOverrideDefaults()
    {
        $container = $this->getContainer();
        $loader = new DoctrineExtension();
        $container->registerExtension($loader);

        $this->loadFromFile($container, 'orm_imports');

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->freeze();

        $this->assertEquals('apc', $container->getParameter('doctrine.orm.metadata_cache_driver'));
        $this->assertTrue($container->getParameter('doctrine.orm.auto_generate_proxy_classes'));
    }

    public function testSingleEntityManagerMultipleMappingBundleDefinitions()
    {
        $container = $this->getContainer(array('YamlBundle', 'AnnotationsBundle', 'XmlBundle'));

        $loader = new DoctrineExtension();
        $container->registerExtension($loader);

        $this->loadFromFile($container, 'orm_single_em_bundle_mappings');

        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->freeze();

        $definition = $container->getDefinition('doctrine.orm.default_metadata_driver');

        $this->assertDICDefinitionMethodCallAt(0, $definition, 'addDriver', array(
            new Reference('doctrine.orm.default_annotation_metadata_driver'),
            'DoctrineBundle\Tests\DependencyInjection\Fixtures\Bundles\AnnotationsBundle\Entity'
        ));

        $this->assertDICDefinitionMethodCallAt(1, $definition, 'addDriver', array(
            new Reference('doctrine.orm.default_yml_metadata_driver'),
            'DoctrineBundle\Tests\DependencyInjection\Fixtures\Bundles\YamlBundle\Entity'
        ));

        $this->assertDICDefinitionMethodCallAt(2, $definition, 'addDriver', array(
            new Reference('doctrine.orm.default_xml_metadata_driver'),
            'DoctrineBundle\Tests\DependencyInjection\Fixtures\Bundles\XmlBundle'
        ));

        $annDef = $container->getDefinition('doctrine.orm.default_annotation_metadata_driver');
        $this->assertDICConstructorArguments($annDef, array(
            new Reference('doctrine.orm.metadata.annotation_reader'),
            array(__DIR__ . '/Fixtures/Bundles/AnnotationsBundle/Entity')
        ));

        $ymlDef = $container->getDefinition('doctrine.orm.default_yml_metadata_driver');
        $this->assertDICConstructorArguments($ymlDef, array(
            array(__DIR__ . '/Fixtures/Bundles/YamlBundle/Resources/config/doctrine/metadata')
        ));

        $xmlDef = $container->getDefinition('doctrine.orm.default_xml_metadata_driver');
        $this->assertDICConstructorArguments($xmlDef, array(
            array(__DIR__ . '/Fixtures/Bundles/XmlBundle/Resources/config/doctrine/metadata')
        ));
    }

    public function testMultipleEntityManagersMappingBundleDefinitions()
    {
        $container = $this->getContainer(array('YamlBundle', 'AnnotationsBundle', 'XmlBundle'));

        $loader = new DoctrineExtension();
        $container->registerExtension($loader);

        $this->loadFromFile($container, 'orm_multiple_em_bundle_mappings');

        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->freeze();

        $def1 = $container->getDefinition('doctrine.orm.em1_metadata_driver');
        $def2 = $container->getDefinition('doctrine.orm.em2_metadata_driver');

        $this->assertDICDefinitionMethodCallAt(0, $def1, 'addDriver', array(
            new Reference('doctrine.orm.em1_annotation_metadata_driver'),
            'DoctrineBundle\Tests\DependencyInjection\Fixtures\Bundles\AnnotationsBundle\Entity'
        ));

        $this->assertDICDefinitionMethodCallAt(0, $def2, 'addDriver', array(
            new Reference('doctrine.orm.em2_yml_metadata_driver'),
            'DoctrineBundle\Tests\DependencyInjection\Fixtures\Bundles\YamlBundle\Entity'
        ));

        $this->assertDICDefinitionMethodCallAt(1, $def2, 'addDriver', array(
            new Reference('doctrine.orm.em2_xml_metadata_driver'),
            'DoctrineBundle\Tests\DependencyInjection\Fixtures\Bundles\XmlBundle'
        ));

        $annDef = $container->getDefinition('doctrine.orm.em1_annotation_metadata_driver');
        $this->assertDICConstructorArguments($annDef, array(
            new Reference('doctrine.orm.metadata.annotation_reader'),
            array(__DIR__ . '/Fixtures/Bundles/AnnotationsBundle/Entity')
        ));

        $ymlDef = $container->getDefinition('doctrine.orm.em2_yml_metadata_driver');
        $this->assertDICConstructorArguments($ymlDef, array(
            array(__DIR__ . '/Fixtures/Bundles/YamlBundle/Resources/config/doctrine/metadata')
        ));

        $xmlDef = $container->getDefinition('doctrine.orm.em2_xml_metadata_driver');
        $this->assertDICConstructorArguments($xmlDef, array(
            array(__DIR__ . '/Fixtures/Bundles/XmlBundle/Resources/config/doctrine/metadata')
        ));
    }

    public function testAnnotationsBundleMappingDetectionWithVendorNamespace()
    {
        $container = $this->getContainer('AnnotationsBundle', 'Vendor');
        $loader = new DoctrineExtension();

        $loader->dbalLoad(array(), $container);
        $loader->ormLoad(array('mappings' => array('AnnotationsBundle' => array())), $container);

        $calls = $container->getDefinition('doctrine.orm.default_metadata_driver')->getMethodCalls();
        $this->assertEquals('doctrine.orm.default_annotation_metadata_driver', (string) $calls[0][1][0]);
        $this->assertEquals('DoctrineBundle\Tests\DependencyInjection\Fixtures\Bundles\Vendor\AnnotationsBundle\Entity', $calls[0][1][1]);
    }

    protected function getContainer($bundles = 'YamlBundle', $vendor = null)
    {
        if (!is_array($bundles)) {
            $bundles = array($bundles);
        }

        foreach ($bundles AS $key => $bundle) {
            require_once __DIR__.'/Fixtures/Bundles/'.($vendor ? $vendor.'/' : '').$bundle.'/'.$bundle.'.php';

            $bundles[$key] = 'DoctrineBundle\\Tests\DependencyInjection\\Fixtures\\Bundles\\'.($vendor ? $vendor.'\\' : '').$bundle.'\\'.$bundle;
        }

        return new ContainerBuilder(new ParameterBag(array(
            'kernel.bundle_dirs' => array('DoctrineBundle\\Tests\\DependencyInjection\\Fixtures\\Bundles' => __DIR__.'/Fixtures/Bundles'),
            'kernel.bundles'     => $bundles,
            'kernel.cache_dir'   => sys_get_temp_dir(),
            'kernel.root_dir'    => __DIR__ . "/../../../../../" // src dir
        )));
    }

    /**
     * Assertion on the Class of a DIC Service Definition.
     *
     * @param \Symfony\Component\DependencyInjection\Definition $definition
     * @param string $expectedClass
     */
    protected function assertDICDefinitionClass($definition, $expectedClass)
    {
        $this->assertEquals($expectedClass, $definition->getClass(), "Expected Class of the DIC Container Service Definition is wrong.");
    }

    protected function assertDICConstructorArguments($definition, $args)
    {
        $this->assertEquals($args, $definition->getArguments(), "Expected and actual DIC Service constructor arguments of definition '" . $definition->getClass()."' dont match.");
    }

    protected function assertDICDefinitionMethodCallAt($pos, $definition, $methodName, array $params = null)
    {
        $calls = $definition->getMethodCalls();
        if (isset($calls[$pos][0])) {
            $this->assertEquals($methodName, $calls[$pos][0], "Method '".$methodName."' is expected to be called at position $pos.");

            if ($params !== null) {
                $this->assertEquals($params, $calls[$pos][1], "Expected parameters to methods '" . $methodName . "' do not match the actual parameters.");
            }
        }
    }

    /**
     * Assertion for the DI Container, check if the given definition contains a method call with the given parameters.
     *
     * @param \Symfony\Component\DependencyInjection\Definition $definition
     * @param string $methodName
     * @param array $params
     * @return void
     */
    protected function assertDICDefinitionMethodCallOnce($definition, $methodName, array $params = null)
    {
        $calls = $definition->getMethodCalls();
        $called = false;
        foreach ($calls AS $call) {
            if ($call[0] == $methodName) {
                if ($called) {
                    $this->fail("Method '".$methodName."' is expected to be called only once, a second call was registered though.");
                } else {
                    $called = true;
                    if ($params !== null) {
                        $this->assertEquals($params, $call[1], "Expected parameters to methods '" . $methodName . "' do not match the actual parameters.");
                    }
                }
            }
        }
        if (!$called) {
            $this->fail("Method '" . $methodName . "' is expected to be called once, definition does not contain a call though.");
        }
    }
}
