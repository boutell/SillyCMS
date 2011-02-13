<?php

namespace Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * Registers the cache warmers.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class AddCacheWarmerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('cache_warmer')) {
            return;
        }

        $warmers = array();
        foreach ($container->findTaggedServiceIds('kernel.cache_warmer') as $id => $attributes) {
            $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
            $warmers[$priority][] = new Reference($id);
        }

        // sort by priority and flatten
        krsort($warmers);
        $warmers = call_user_func_array('array_merge', $warmers);

        $container->getDefinition('cache_warmer')->setArgument(0, $warmers);

        if ('full' === $container->getParameter('kernel.cache_warmup')) {
            $container->getDefinition('cache_warmer')->addMethodCall('enableOptionalWarmers', array());
        }
    }
}
