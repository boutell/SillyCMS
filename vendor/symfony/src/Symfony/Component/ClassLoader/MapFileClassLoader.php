<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\ClassLoader;

/**
 * A class loader that uses a mapping file to look up paths.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.org>
 */
class MapFileClassLoader
{
    protected $map = array();

    /**
     * Constructor.
     *
     * @param string $file Path to class mapping file
     */
    public function __construct($file)
    {
        $this->map = require $file;
    }

    /**
     * Registers this instance as an autoloader.
     *
     * @param Boolean $prepend Whether to prepend the autoloader or not
     */
    public function register($prepend = false)
    {
        spl_autoload_register(array($this, 'loadClass'), true, $prepend);
    }

    /**
     * Loads the given class or interface.
     *
     * @param string $class The name of the class
     */
    public function loadClass($class)
    {
        if ('\\' === $class[0]) {
            $class = substr($class, 1);
        }

        if (isset($this->map[$class])) {
            require $this->map[$class];
        }
    }
}
