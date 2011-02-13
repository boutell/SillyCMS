<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\Bundle;

/**
 * BundleInterface.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
interface BundleInterface
{
    /**
     * Boots the Bundle.
     */
    function boot();

    /**
     * Shutdowns the Bundle.
     */
    function shutdown();

    /**
     * Returns the bundle parent name.
     *
     * @return string The Bundle parent name it overrides or null if no parent
     */
    function getParent();

    /**
     * Returns the bundle name (the class short name).
     *
     * @return string The Bundle name
     */
    function getName();

    /**
     * Gets the Bundle namespace.
     *
     * @return string The Bundle namespace
     */
    function getNamespace();

    /**
     * Gets the Bundle directory path.
     *
     * The path should always be returned as a Unix path (with /).
     *
     * @return string The Bundle absolute path
     */
    function getPath();
}
