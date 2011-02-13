<?php

namespace Symfony\Tests\Component\DependencyInjection\Configuration;

use Symfony\Component\DependencyInjection\Configuration\ArrayNode;

class ArrayNodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Symfony\Component\DependencyInjection\Configuration\Exception\InvalidTypeException
     */
    public function testNormalizeThrowsExceptionWhenFalseIsNotAllowed()
    {
        $node = new ArrayNode('root');
        $node->normalize(false);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetDefaultValueThrowsExceptionWhenNotAnArray()
    {
        $node = new ArrayNode('root');
        $node->setDefaultValue('test');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testSetDefaultValueThrowsExceptionWhenNotAnPrototype()
    {
        $node = new ArrayNode('root');
        $node->setDefaultValue(array ('test'));
    }

    public function testGetDefaultValueReturnsAnEmptyArrayForPrototypes()
    {
        $node = new ArrayNode('root');
        $prototype = new ArrayNode(null, $node);
        $node->setPrototype($prototype);
        $this->assertEmpty($node->getDefaultValue());
    }

    public function testGetDefaultValueReturnsDefaultValueForPrototypes()
    {
        $node = new ArrayNode('root');
        $prototype = new ArrayNode(null, $node);
        $node->setPrototype($prototype);
        $node->setDefaultValue(array ('test'));
        $this->assertEquals(array ('test'), $node->getDefaultValue());
    }
}