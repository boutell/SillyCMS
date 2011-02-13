<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\CssSelector\Node;

use Symfony\Component\CssSelector\Node\OrNode;
use Symfony\Component\CssSelector\Node\ElementNode;

class OrNodeTest extends \PHPUnit_Framework_TestCase
{
    public function testToXpath()
    {
        // h1, h2, h3
        $element1 = new ElementNode('*', 'h1');
        $element2 = new ElementNode('*', 'h2');
        $element3 = new ElementNode('*', 'h3');
        $or = new OrNode(array($element1, $element2, $element3));

        $this->assertEquals("h1 | h2 | h3", (string) $or->toXpath(), '->toXpath() returns the xpath representation of the node');
    }
}
