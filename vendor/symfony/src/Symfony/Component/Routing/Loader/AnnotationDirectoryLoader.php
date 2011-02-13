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
 * AnnotationDirectoryLoader loads routing information from annotations set
 * on PHP classes and methods.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class AnnotationDirectoryLoader extends AnnotationFileLoader
{
    /**
     * Loads from annotations from a directory.
     *
     * @param string $path A directory path
     * @param string $type The resource type
     *
     * @return RouteCollection A RouteCollection instance
     *
     * @throws \InvalidArgumentException When the directory does not exist or its routes cannot be parsed
     */
    public function load($path, $type = null)
    {
        $dir = $this->locator->getAbsolutePath($path);
        if (!file_exists($dir)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" does not exist (in: %s).', $dir, implode(', ', $this->paths)));
        }

        $collection = new RouteCollection();
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir), \RecursiveIteratorIterator::LEAVES_ONLY) as $file) {
            if (!$file->isFile() || '.php' !== substr($file->getFilename(), -4)) {
                continue;
            }

            if ($class = $this->findClass($file)) {
                $collection->addCollection($this->loader->load($class, $type));
            }
        }

        return $collection;
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
        return is_string($resource) && is_dir($this->locator->getAbsolutePath($resource)) && (!$type || 'annotation' === $type);
    }
}
