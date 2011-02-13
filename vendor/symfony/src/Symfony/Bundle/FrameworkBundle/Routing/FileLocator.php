<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\FrameworkBundle\Routing;

use Symfony\Component\Routing\Loader\FileLocator as BaseFileLocator;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * FileLocator uses the KernelInterface to locate resources in bundles.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class FileLocator extends BaseFileLocator
{
    protected $kernel;

    /**
     * Constructor.
     *
     * @param KernelInterface $kernel A KernelInterface instance
     * @param string|array    $paths  A path or an array of paths where to look for resources
     */
    public function __construct(KernelInterface $kernel, array $paths = array())
    {
        $this->kernel = $kernel;

        parent::__construct($paths);
    }

    /**
     * {@inheritdoc}
     */
    public function locate($file, $currentPath = null)
    {
        if ('@' === $file[0]) {
            return $this->kernel->locateResource($file);
        }

        return parent::locate($file, $currentPath);
    }
}
