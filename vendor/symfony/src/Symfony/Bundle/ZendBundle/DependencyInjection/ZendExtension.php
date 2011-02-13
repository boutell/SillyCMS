<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\ZendBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * ZendExtension is an extension for the Zend Framework libraries.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class ZendExtension extends Extension
{
    /**
     * Loads the Zend Framework configuration.
     *
     * Usage example:
     *
     *      <zend:config>
     *          <zend:logger priority="info" path="/path/to/some.log" />
     *      </zend:config>
     *
     * @param array            $config    An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function configLoad(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('logger.xml');
        $container->setAlias('logger', 'zend.logger');

        foreach ($configs as $config) {
            if (isset($config['logger'])) {
                $this->registerLoggerConfiguration($config, $container);
            }
        }
    }

    /**
     * Loads the logger configuration.
     *
     * Usage example:
     *
     *      <zend:logger priority="info" path="/path/to/some.log" />
     *
     * @param array            $config    An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    protected function registerLoggerConfiguration($config, ContainerBuilder $container)
    {
        $config = $config['logger'];

        if (isset($config['priority'])) {
            $container->setParameter('zend.logger.priority', is_int($config['priority']) ? $config['priority'] : constant('\\Zend\\Log\\Logger::'.strtoupper($config['priority'])));
        }

        if (isset($config['path'])) {
            $container->setParameter('zend.logger.path', $config['path']);
        }

        if (isset($config['log_errors'])) {
            $definition = $container->findDefinition('zend.logger');
            if (false === $config['log_errors'] && $definition->hasMethodCall('registerErrorHandler')) {
                $container->findDefinition('zend.logger')->removeMethodCall('registerErrorHandler');
            } else {
                $container->findDefinition('zend.logger')->addMethodCall('registerErrorHandler');
            }
        }
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/schema';
    }

    public function getNamespace()
    {
        return 'http://www.symfony-project.org/schema/dic/zend';
    }

    public function getAlias()
    {
        return 'zend';
    }
}
