<?php

namespace Symfony\Tests\Component\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\DependencyInjection\DefinitionDecorator;

use Symfony\Component\DependencyInjection\Compiler\ResolveDefinitionTemplatesPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class ResolveDefinitionTemplatesPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder();
        $container->register('parent', 'foo')->setArguments(array('moo', 'b'));
        $container->setDefinition('child', new DefinitionDecorator('parent'))
            ->setArgument(0, 'a')
            ->setClass('bar')
        ;

        $this->process($container);

        $def = $container->getDefinition('child');
        $this->assertNotInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $def);
        $this->assertEquals('bar', $def->getClass());
        $this->assertEquals(array('a', 'b'), $def->getArguments());
    }

    public function testProcessAppendsMethodCallsAlways()
    {
        $container = new ContainerBuilder();

        $container
            ->register('parent')
            ->addMethodCall('foo', array('bar'))
        ;

        $container
            ->setDefinition('child', new DefinitionDecorator('parent'))
            ->addMethodCall('bar', array('foo'))
        ;

        $this->process($container);

        $def = $container->getDefinition('child');
        $this->assertEquals(array(
            array('foo', array('bar')),
            array('bar', array('foo')),
        ), $def->getMethodCalls());
    }

    public function testProcessDoesNotCopyAbstract()
    {
        $container = new ContainerBuilder();

        $container
            ->register('parent')
            ->setAbstract(true)
        ;

        $container
            ->setDefinition('child', new DefinitionDecorator('parent'))
        ;

        $this->process($container);

        $def = $container->getDefinition('child');
        $this->assertFalse($def->isAbstract());
    }

    public function testProcessDoesNotCopyScope()
    {
        $container = new ContainerBuilder();

        $container
            ->register('parent')
            ->setScope('foo')
        ;

        $container
            ->setDefinition('child', new DefinitionDecorator('parent'))
        ;

        $this->process($container);

        $def = $container->getDefinition('child');
        $this->assertEquals(ContainerInterface::SCOPE_CONTAINER, $def->getScope());
    }

    public function testProcessDoesNotCopyTags()
    {
        $container = new ContainerBuilder();

        $container
            ->register('parent')
            ->addTag('foo')
        ;

        $container
            ->setDefinition('child', new DefinitionDecorator('parent'))
        ;

        $this->process($container);

        $def = $container->getDefinition('child');
        $this->assertEquals(array(), $def->getTags());
    }

    public function testProcessHandlesMultipleInheritance()
    {
        $container = new ContainerBuilder();

        $container
            ->register('parent', 'foo')
            ->setArguments(array('foo', 'bar', 'c'))
        ;

        $container
            ->setDefinition('child2', new DefinitionDecorator('child1'))
            ->setArgument(1, 'b')
        ;

        $container
            ->setDefinition('child1', new DefinitionDecorator('parent'))
            ->setArgument(0, 'a')
        ;

        $this->process($container);

        $def = $container->getDefinition('child2');
        $this->assertEquals(array('a', 'b', 'c'), $def->getArguments());
        $this->assertEquals('foo', $def->getClass());
    }

    protected function process(ContainerBuilder $container)
    {
        $pass = new ResolveDefinitionTemplatesPass();
        $pass->process($container);
    }
}