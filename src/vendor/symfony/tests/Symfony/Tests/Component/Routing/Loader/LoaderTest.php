<?php

/*
 * This file is part of the Symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\Routing\Loader;

use Symfony\Component\Routing\Loader\LoaderResolver;
use Symfony\Component\Routing\Loader\Loader;
use Symfony\Component\Routing\Loader\XmlFileLoader;

class LoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Symfony\Component\Routing\Loader\Loader::getResolver
     * @covers Symfony\Component\Routing\Loader\Loader::setResolver
     */
    public function testGetSetResolver()
    {
        $resolver = new LoaderResolver();
        $loader = new ProjectLoader1();
        $loader->setResolver($resolver);
        $this->assertSame($resolver, $loader->getResolver(), '->setResolver() sets the resolver loader');
    }

    /**
     * @covers Symfony\Component\Routing\Loader\Loader::resolve
     */
    public function testResolve()
    {
        $resolver = new LoaderResolver(array(
            $ini = new XmlFileLoader(array()),
        ));
        $loader = new ProjectLoader1();
        $loader->setResolver($resolver);

        $this->assertSame($ini, $loader->resolve('foo.xml'), '->resolve() finds a loader');
        $this->assertSame($loader, $loader->resolve('foo.foo'), '->resolve() finds a loader');

        try {
            $loader->resolve(new \stdClass());
            $this->fail('->resolve() throws an \InvalidArgumentException if the resource cannot be loaded');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\InvalidArgumentException', $e, '->resolve() throws an \InvalidArgumentException if the resource cannot be loaded');
        }
    }
}

class ProjectLoader1 extends Loader
{
    public function load($resource, $type = null)
    {
    }

    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'foo' === pathinfo($resource, PATHINFO_EXTENSION);
    }

    public function getType()
    {
    }
}
