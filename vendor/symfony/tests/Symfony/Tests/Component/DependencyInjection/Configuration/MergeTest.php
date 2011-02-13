<?php

namespace Symfony\Tests\Component\DependencyInjection\Configuration;

use Symfony\Component\DependencyInjection\Configuration\Builder\TreeBuilder;

class MergeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Symfony\Component\DependencyInjection\Configuration\Exception\ForbiddenOverwriteException
     */
    public function testForbiddenOverwrite()
    {
        $tb = new TreeBuilder();
        $tree = $tb
            ->root('root', 'array')
                ->node('foo', 'scalar')
                    ->cannotBeOverwritten()
                ->end()
            ->end()
            ->buildTree()
        ;

        $a = array(
            'foo' => 'bar',
        );

        $b = array(
            'foo' => 'moo',
        );

        $tree->merge($a, $b);
    }

    public function testUnsetKey()
    {
        $tb = new TreeBuilder();
        $tree = $tb
            ->root('root', 'array')
                ->node('foo', 'scalar')->end()
                ->node('bar', 'scalar')->end()
                ->node('unsettable', 'array')
                    ->canBeUnset()
                    ->node('foo', 'scalar')->end()
                    ->node('bar', 'scalar')->end()
                ->end()
                ->node('unsetted', 'array')
                    ->canBeUnset()
                    ->prototype('scalar')->end()
                ->end()
            ->end()
            ->buildTree()
        ;

        $a = array(
            'foo' => 'bar',
            'unsettable' => array(
                'foo' => 'a',
                'bar' => 'b',
            ),
            'unsetted' => false,
        );

        $b = array(
            'foo' => 'moo',
            'bar' => 'b',
            'unsettable' => false,
            'unsetted' => array('a', 'b'),
        );

        $this->assertEquals(array(
            'foo' => 'moo',
            'bar' => 'b',
            'unsettable' => false,
            'unsetted' => array('a', 'b'),
        ), $tree->merge($a, $b));
    }

    /**
     * @expectedException Symfony\Component\DependencyInjection\Configuration\Exception\InvalidConfigurationException
     */
    public function testDoesNotAllowNewKeysInSubsequentConfigs()
    {
        $tb = new TreeBuilder();
        $tree = $tb
            ->root('config', 'array')
                ->node('test', 'array')
                    ->disallowNewKeysInSubsequentConfigs()
                    ->useAttributeAsKey('key')
                    ->prototype('array')
                        ->node('value', 'scalar')->end()
                    ->end()
                ->end()
            ->end()
            ->buildTree();

        $a = array(
            'test' => array(
                'a' => array('value' => 'foo')
            )
        );

        $b = array(
            'test' => array(
                'b' => array('value' => 'foo')
            )
        );

        $tree->merge($a, $b);
    }

    public function testPerformsNoDeepMerging()
    {
        $tb = new TreeBuilder();

        $tree = $tb
            ->root('config', 'array')
                ->node('no_deep_merging', 'array')
                    ->performNoDeepMerging()
                    ->node('foo', 'scalar')->end()
                    ->node('bar', 'scalar')->end()
                ->end()
            ->end()
            ->buildTree()
        ;

        $a = array(
            'no_deep_merging' => array(
                'foo' => 'a',
                'bar' => 'b',
            ),
        );

        $b = array(
            'no_deep_merging' => array(
                'c' => 'd',
            )
        );

        $this->assertEquals(array(
            'no_deep_merging' => array(
                'c' => 'd',
            )
        ), $tree->merge($a, $b));
    }

    public function testPrototypeWithoutAKeyAttribute()
    {
        $tb = new TreeBuilder();

        $tree = $tb
            ->root('config', 'array')
                ->node('append_elements', 'array')
                    ->prototype('scalar')->end()
                ->end()
            ->end()
            ->buildTree()
        ;

        $a = array(
            'append_elements' => array('a', 'b'),
        );

        $b = array(
            'append_elements' => array('c', 'd'),
        );

        $this->assertEquals(array('append_elements' => array('a', 'b', 'c', 'd')), $tree->merge($a, $b));
    }
}