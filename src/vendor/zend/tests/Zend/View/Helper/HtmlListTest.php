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
 * @package    Zend_View
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace ZendTest\View\Helper;
use Zend\View\Helper;

/**
 * @category   Zend
 * @package    Zend_View
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_View
 * @group      Zend_View_Helper
 */
class HtmlListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Zend_View_Helper_HtmlList
     */
    public $helper;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp()
    {
        $this->view = new \Zend\View\View();
        $this->helper = new Helper\HtmlList();
        $this->helper->setView($this->view);
    }

    public function tearDown()
    {
        unset($this->helper);
    }

    public function testMakeUnorderedList()
    {
        $items = array('one', 'two', 'three');

        $list = $this->helper->direct($items);

        $this->assertContains('<ul>', $list);
        $this->assertContains('</ul>', $list);
        foreach ($items as $item) {
            $this->assertContains('<li>' . $item . '</li>', $list);
        }
    }

    public function testMakeOrderedList()
    {
        $items = array('one', 'two', 'three');

        $list = $this->helper->direct($items, true);

        $this->assertContains('<ol>', $list);
        $this->assertContains('</ol>', $list);
        foreach ($items as $item) {
            $this->assertContains('<li>' . $item . '</li>', $list);
        }
    }

    public function testMakeUnorderedListWithAttribs()
    {
        $items = array('one', 'two', 'three');
        $attribs = array('class' => 'selected', 'name' => 'list');

        $list = $this->helper->direct($items, false, $attribs);

        $this->assertContains('<ul', $list);
        $this->assertContains('class="selected"', $list);
        $this->assertContains('name="list"', $list);
        $this->assertContains('</ul>', $list);
        foreach ($items as $item) {
            $this->assertContains('<li>' . $item . '</li>', $list);
        }
    }

    public function testMakeOrderedListWithAttribs()
    {
        $items = array('one', 'two', 'three');
        $attribs = array('class' => 'selected', 'name' => 'list');

        $list = $this->helper->direct($items, true, $attribs);

        $this->assertContains('<ol', $list);
        $this->assertContains('class="selected"', $list);
        $this->assertContains('name="list"', $list);
        $this->assertContains('</ol>', $list);
        foreach ($items as $item) {
            $this->assertContains('<li>' . $item . '</li>', $list);
        }
    }

    /*
     * @see ZF-5018
     */
    public function testMakeNestedUnorderedList()
    {
        $items = array('one', array('four', 'five', 'six'), 'two', 'three');

        $list = $this->helper->direct($items);

        $this->assertContains('<ul>' . Helper\HtmlList::EOL, $list);
        $this->assertContains('</ul>' . Helper\HtmlList::EOL, $list);
        $this->assertContains('one<ul>' . Helper\HtmlList::EOL.'<li>four', $list);
        $this->assertContains('<li>six</li>' . Helper\HtmlList::EOL . '</ul>' .
            Helper\HtmlList::EOL . '</li>' . Helper\HtmlList::EOL . '<li>two', $list);
    }

    /*
     * @see ZF-5018
     */
    public function testMakeNestedDeepUnorderedList()
    {
        $items = array('one', array('four', array('six', 'seven', 'eight'), 'five'), 'two', 'three');

        $list = $this->helper->direct($items);

        $this->assertContains('<ul>' . Helper\HtmlList::EOL, $list);
        $this->assertContains('</ul>' . Helper\HtmlList::EOL, $list);
        $this->assertContains('one<ul>' . Helper\HtmlList::EOL . '<li>four', $list);
        $this->assertContains('<li>four<ul>' . Helper\HtmlList::EOL . '<li>six', $list);
        $this->assertContains('<li>five</li>' . Helper\HtmlList::EOL . '</ul>' .
            Helper\HtmlList::EOL . '</li>' . Helper\HtmlList::EOL . '<li>two', $list);
    }

    public function testListWithValuesToEscapeForZF2283()
    {
        $items = array('one <small> test', 'second & third', 'And \'some\' "final" test');

        $list = $this->helper->direct($items);

        $this->assertContains('<ul>', $list);
        $this->assertContains('</ul>', $list);

        $this->assertContains('<li>one &lt;small&gt; test</li>', $list);
        $this->assertContains('<li>second &amp; third</li>', $list);
        $this->assertContains('<li>And \'some\' &quot;final&quot; test</li>', $list);
    }

    public function testListEscapeSwitchedOffForZF2283()
    {
        $items = array('one <b>small</b> test');

        $list = $this->helper->direct($items, false, false, false);

        $this->assertContains('<ul>', $list);
        $this->assertContains('</ul>', $list);

        $this->assertContains('<li>one <b>small</b> test</li>', $list);
    }

    /**
     * @see ZF-2527
     */
    public function testEscapeFlagHonoredForMultidimensionalLists()
    {
        $items = array('<b>one</b>', array('<b>four</b>', '<b>five</b>', '<b>six</b>'), '<b>two</b>', '<b>three</b>');

        $list = $this->helper->direct($items, false, false, false);

        foreach ($items[1] as $item) {
            $this->assertContains($item, $list);
        }
    }

    /**
     * @see ZF-2527
     * Added the s modifier to match newlines after @see ZF-5018
     */
    public function testAttribsPassedIntoMultidimensionalLists()
    {
        $items = array('one', array('four', 'five', 'six'), 'two', 'three');

        $list = $this->helper->direct($items, false, array('class' => 'foo'));

        foreach ($items[1] as $item) {
            $this->assertRegexp('#<ul[^>]*?class="foo"[^>]*>.*?(<li>' . $item . ')#s', $list);
        }

    }

    /**
     * @see ZF-2870
     */
    public function testEscapeFlagShouldBePassedRecursively()
    {
        $items = array(
            '<b>one</b>',
            array(
                '<b>four</b>',
                '<b>five</b>',
                '<b>six</b>',
                array(
                    '<b>two</b>',
                    '<b>three</b>',
                ),
            ),
        );

        $list = $this->helper->direct($items, false, false, false);

        $this->assertContains('<ul>', $list);
        $this->assertContains('</ul>', $list);

        $this->markTestSkipped('Wrong array_walk_recursive behavior.');

        array_walk_recursive($items, array($this, 'validateItems'), $list);
    }

    public function validateItems($value, $key, $userdata)
    {
        $this->assertContains('<li>' . $value, $userdata);
    }
}
