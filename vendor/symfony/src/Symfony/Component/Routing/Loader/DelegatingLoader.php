<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Routing\Loader;

use Symfony\Component\Routing\RouteCollection;

/**
 * DelegatingLoader delegates route loading to other loaders using a loader resolver.
 *
 * This loader acts as an array of LoaderInterface objects - each having
 * a chance to load a given resource (handled by the resolver)
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class DelegatingLoader extends Loader
{
    /**
     * @var LoaderResolverInterface
     */
    protected $resolver;

    /**
     * Constructor.
     *
     * @param LoaderResolverInterface $resolver A LoaderResolverInterface instance
     */
    public function __construct(LoaderResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Loads a resource.
     *
     * @param mixed  $resource A resource
     * @param string $type     The resource type
     *
     * @return RouteCollection A RouteCollection instance
     *
     * @throws \InvalidArgumentException When the resource cannot be loaded
     */
    public function load($resource, $type = null)
    {
        $loader = $this->resolver->resolve($resource, $type);

        if (false === $loader) {
            throw new \InvalidArgumentException(sprintf('Unable to load the "%s" routing resource.', is_string($resource) ? $resource : (is_object($resource) ? get_class($resource) : 'RESOURCE')));
        }

        return $loader->load($resource, $type);
    }

    /**
     * Returns true if this class supports the given resource.
     *
     * @param mixed  $resource A resource
     * @param string $type     The resource type
     *
     * @return Boolean True if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null)
    {
        foreach ($this->resolver->getLoaders() as $loader) {
            if ($loader->supports($resource, $type)) {
                return true;
            }
        }

        return false;
    }
}
