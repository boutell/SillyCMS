<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\ResolveInvalidReferencesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ResolveInvalidReferencesPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder();
        $def = $container
            ->register('foo')
            ->setArguments(array(new Reference('bar', ContainerInterface::NULL_ON_INVALID_REFERENCE)))
            ->addMethodCall('foo', array(new Reference('moo', ContainerInterface::IGNORE_ON_INVALID_REFERENCE)))
        ;

        $this->process($container);

        $arguments = $def->getArguments();
        $this->assertNull($arguments[0]);
        $this->assertEquals(0, count($def->getMethodCalls()));
    }

    public function testProcessIgnoreNonExistentServices()
    {
        $container = new ContainerBuilder();
        $def = $container
            ->register('foo')
            ->setArguments(array(new Reference('bar')))
        ;

        $this->process($container);

        $arguments = $def->getArguments();
        $this->assertEquals('bar', (string) $arguments[0]);
    }

    public function testProcessIgnoresExceptions()
    {
        $container = new ContainerBuilder();
        $def = $container
            ->register('foo')
            ->setArguments(array(new Reference('bar', ContainerInterface::NULL_ON_INVALID_REFERENCE)))
        ;

        $this->process($container, array('bar'));

        $arguments = $def->getArguments();
        $this->assertEquals('bar', (string) $arguments[0]);
    }

    protected function process(ContainerBuilder $container, array $exceptions = array())
    {
        $pass = new ResolveInvalidReferencesPass($exceptions);
        $pass->process($container);
    }
}