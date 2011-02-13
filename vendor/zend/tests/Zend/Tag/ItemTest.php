<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Tag
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace ZendTest\Tag;

use Zend\Tag,
	Zend\Tag\Exception\InvalidArgumentException;

/**
 * @category   Zend
 * @package    Zend_Tag
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Tag
 */
class ItemTest extends \PHPUnit_Framework_TestCase
{
    public function testConstuctor()
    {
        $tag = new Tag\Item(array(
            'title' => 'foo',
            'weight' => 10,
            'params' => array(
                'bar' => 'baz'
            )
        ));

        $this->assertEquals('foo', $tag->getTitle());
        $this->assertEquals(10, $tag->getWeight());
        $this->assertEquals('baz', $tag->getParam('bar'));
    }

    public function testSetOptions()
    {
        $tag = new Tag\Item(array('title' => 'foo', 'weight' => 1));
        $tag->setOptions(array(
            'title' => 'bar',
            'weight' => 10,
            'params' => array(
                'bar' => 'baz'
            )
        ));

        $this->assertEquals('bar', $tag->getTitle());
        $this->assertEquals(10, $tag->getWeight());
        $this->assertEquals('baz', $tag->getParam('bar'));
    }

    public function testSetParam()
    {
        $tag = new Tag\Item(array('title' => 'foo', 'weight' => 1));
        $tag->setParam('bar', 'baz');

        $this->assertEquals('baz', $tag->getParam('bar'));
    }

    public function testSetTitle()
    {
        $tag = new Tag\Item(array('title' => 'foo', 'weight' => 1));
        $tag->setTitle('baz');

        $this->assertEquals('baz', $tag->getTitle());
    }

    public function testInvalidTitle()
    {
        $this->setExpectedException('\Zend\Tag\Exception\InvalidArgumentException', 'Title must be a string');
        $tag = new Tag\Item(array('title' => 10, 'weight' => 1));
    }

    public function testSetWeight()
    {
        $tag = new Tag\Item(array('title' => 'foo', 'weight' => 1));
        $tag->setWeight('10');

        $this->assertEquals(10.0, $tag->getWeight());
        $this->assertTrue(is_float($tag->getWeight()));
    }

    public function testInvalidWeight()
    {
        $this->setExpectedException('\Zend\Tag\Exception\InvalidArgumentException', 'Weight must be numeric');
        $tag = new Tag\Item(array('title' => 'foo', 'weight' => 'foobar'));
    }

    public function testSkipOptions()
    {
        $tag = new Tag\Item(array('title' => 'foo', 'weight' => 1, 'param' => 'foobar'));
        // In case would fail due to an error
    }

    public function testInvalidOptions()
    {
        $this->setExpectedException('\Zend\Tag\Exception\InvalidArgumentException', 'Invalid options provided to constructor');
        $tag = new Tag\Item('test');
    }

    public function testMissingTitle()
    {
        $this->setExpectedException('\Zend\Tag\Exception\InvalidArgumentException', 'Title was not set');
        $tag = new Tag\Item(array('weight' => 1));
    }

    public function testMissingWeight()
    {
        $this->setExpectedException('\Zend\Tag\Exception\InvalidArgumentException', 'Weight was not set');
        $tag = new Tag\Item(array('title' => 'foo'));
    }

    public function testConfigOptions()
    {
        $tag = new Tag\Item(new \Zend\Config\Config(array('title' => 'foo', 'weight' => 1)));

        $this->assertEquals($tag->getTitle(), 'foo');
        $this->assertEquals($tag->getWeight(), 1);
    }

    public function testGetNonSetParam()
    {
        $tag = new Tag\Item(array('title' => 'foo', 'weight' => 1));

        $this->assertNull($tag->getParam('foo'));
    }
}
