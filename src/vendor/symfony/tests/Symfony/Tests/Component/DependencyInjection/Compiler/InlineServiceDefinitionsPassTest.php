<?php

namespace Symfony\Tests\Component\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\InlineServiceDefinitionsPass;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class InlineServiceDefinitionsPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder();
        $container
            ->register('inlinable.service')
            ->setPublic(false)
        ;

        $container
            ->register('service')
            ->setArguments(array(new Reference('inlinable.service')))
        ;

        $this->process($container);

        $arguments = $container->getDefinition('service')->getArguments();
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Definition', $arguments[0]);
        $this->assertSame($container->getDefinition('inlinable.service'), $arguments[0]);
    }

    public function testProcessDoesNotInlinesWhenAliasedServiceIsNotShared()
    {
        $container = new ContainerBuilder();
        $container
            ->register('foo')
            ->setPublic(false)
        ;
        $container->setAlias('moo', 'foo');

        $container
            ->register('service')
            ->setArguments(array($ref = new Reference('foo')))
        ;

        $this->process($container);

        $arguments = $container->getDefinition('service')->getArguments();
        $this->assertSame($ref, $arguments[0]);
    }

    public function testProcessDoesInlineNonSharedService()
    {
        $container = new ContainerBuilder();
        $container
            ->register('foo')
            ->setShared(false)
        ;
        $container
            ->register('bar')
            ->setPublic(false)
            ->setShared(false)
        ;
        $container->setAlias('moo', 'bar');

        $container
            ->register('service')
            ->setArguments(array(new Reference('foo'), $ref = new Reference('moo'), new Reference('bar')))
        ;

        $this->process($container);

        $arguments = $container->getDefinition('service')->getArguments();
        $this->assertSame($container->getDefinition('foo'), $arguments[0]);
        $this->assertSame($ref, $arguments[1]);
        $this->assertSame($container->getDefinition('bar'), $arguments[2]);
    }

    protected function process(ContainerBuilder $container)
    {
        $pass = new InlineServiceDefinitionsPass();
        $pass->process($container);
    }
}