<?php

namespace Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * Sets the classes to compile in the cache for the container.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class AddClassesToCachePass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $classes = array();
        foreach ($container->getExtensionConfigs() as $name => $configs) {
            list($namespace, $tag) = explode(':', $name);

            $extension = $container->getExtension($namespace);

            if (method_exists($extension, 'getClassesToCompile')) {
                $classes = array_merge($classes, $extension->getClassesToCompile());
            }
        }

        $container->setParameter('kernel.compiled_classes', array_unique($classes));
    }
}
