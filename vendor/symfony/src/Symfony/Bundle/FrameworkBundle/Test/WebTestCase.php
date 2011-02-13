<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\FrameworkBundle\Test;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Test\WebTestCase as BaseWebTestCase;

/**
 * WebTestCase is the base class for functional tests.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
abstract class WebTestCase extends BaseWebTestCase
{
    protected $kernel;

    /**
     * Creates a Client.
     *
     * @param array   $options An array of options to pass to the createKernel class
     * @param array   $server  An array of server parameters
     *
     * @return Client A Client instance
     */
    public function createClient(array $options = array(), array $server = array())
    {
        $this->kernel = $this->createKernel($options);
        $this->kernel->boot();

        $client = $this->kernel->getContainer()->get('test.client');
        $client->setServerParameters($server);

        return $client;
    }

    /**
     * Finds the directory where the phpunit.xml(.dist) is stored.
     *
     * If you run tests with the PHPUnit CLI tool, everything will work as expected.
     * If not, override this method in your test classes.
     *
     * @return string The directory where phpunit.xml(.dist) is stored
     */
    protected function getPhpUnitXmlDir()
    {
        $dir = getcwd();
        if (!isset($_SERVER['argv']) || false === strpos($_SERVER['argv'][0], 'phpunit')) {
            throw new \RuntimeException('You must override the WebTestCase::createKernel() method.');
        }

        // find the --configuration flag from PHPUnit
        $cli = implode(' ', $_SERVER['argv']);
        if (preg_match('/\-\-configuration[= ]+([^ ]+)/', $cli, $matches)) {
            $dir = realpath($matches[1]);
        } elseif (preg_match('/\-c +([^ ]+)/', $cli, $matches)) {
            $dir = realpath($matches[1]);
        } elseif (file_exists(getcwd().'/phpunit.xml') || file_exists(getcwd().'/phpunit.xml.dist')) {
            $dir = getcwd();
        } else {
            throw new \RuntimeException('Unable to guess the Kernel directory.');
        }

        if (!is_dir($dir)) {
            $dir = dirname($dir);
        }

        return $dir;
    }

    /**
     * Attempts to guess the kernel location.
     *
     * When the Kernel is located, the file is required.
     *
     * @return string The Kernel class name
     */
    protected function getKernelClass()
    {
        $dir = isset($_SERVER['KERNEL_DIR']) ? $_SERVER['KERNEL_DIR'] : $this->getPhpUnitXmlDir();

        $finder = new Finder();
        $finder->name('*Kernel.php')->in($dir);
        if (!count($finder)) {
            throw new \RuntimeException('You must override the WebTestCase::createKernel() method.');
        }

        $file = current(iterator_to_array($finder));
        $class = $file->getBasename('.php');

        require_once $file;

        return $class;
    }

    /**
     * Creates a Kernel.
     *
     * Available options:
     *
     *  * environment
     *  * debug
     *
     * @param array $options An array of options
     *
     * @return HttpKernelInterface A HttpKernelInterface instance
     */
    protected function createKernel(array $options = array())
    {
        $class = $this->getKernelClass();

        return new $class(
            isset($options['environment']) ? $options['environment'] : 'test',
            isset($options['debug']) ? $options['debug'] : true
        );
    }
}
