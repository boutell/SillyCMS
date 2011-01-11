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
use Zend\View\Helper\Placeholder\Registry;
use Zend\View\Helper;
use Zend\View;

/**
 * Test class for Zend_View_Helper_HeadLink.
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_View
 * @group      Zend_View_Helper
 */
class HeadLinkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Zend_View_Helper_HeadLink
     */
    public $helper;

    /**
     * @var string
     */
    public $basePath;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        foreach (array(Registry::REGISTRY_KEY, 'Zend_View_Helper_Doctype') as $key) {
            if (\Zend\Registry::isRegistered($key)) {
                $registry = \Zend\Registry::getInstance();
                unset($registry[$key]);
            }
        }
        $this->basePath = __DIR__ . '/_files/modules';
        $this->view = new View\View();
        $this->helper = new Helper\HeadLink();
        $this->helper->setView($this->view);
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->helper);
    }

    public function testNamespaceRegisteredInPlaceholderRegistryAfterInstantiation()
    {
        $registry = Registry::getRegistry();
        if ($registry->containerExists('Zend_View_Helper_HeadLink')) {
            $registry->deleteContainer('Zend_View_Helper_HeadLink');
        }
        $this->assertFalse($registry->containerExists('Zend_View_Helper_HeadLink'));
        $helper = new Helper\HeadLink();
        $this->assertTrue($registry->containerExists('Zend_View_Helper_HeadLink'));
    }

    public function testHeadLinkReturnsObjectInstance()
    {
        $placeholder = $this->helper->direct();
        $this->assertTrue($placeholder instanceof Helper\HeadLink);
    }

    public function testPrependThrowsExceptionWithoutArrayArgument()
    {
        try {
            $this->helper->prepend('foo');
            $this->fail('prepend should raise exception without array argument');
        } catch (\Exception $e) {
        }
    }

    public function testAppendThrowsExceptionWithoutArrayArgument()
    {
        try {
            $this->helper->append('foo');
            $this->fail('append should raise exception without array argument');
        } catch (\Exception $e) {
        }
    }

    public function testSetThrowsExceptionWithoutArrayArgument()
    {
        try {
            $this->helper->set('foo');
            $this->fail('set should raise exception without array argument');
        } catch (\Exception $e) {
        }
    }

    public function testOffsetSetThrowsExceptionWithoutArrayArgument()
    {
        try {
            $this->helper->offsetSet(1, 'foo');
            $this->fail('set should raise exception without array argument');
        } catch (\Exception $e) {
        }
    }

    public function testCreatingLinkStackViaHeadScriptCreatesAppropriateOutput()
    {
        $links = array(
            'link1' => array('rel' => 'stylesheet', 'type' => 'text/css', 'href' => 'foo'),
            'link2' => array('rel' => 'stylesheet', 'type' => 'text/css', 'href' => 'bar'),
            'link3' => array('rel' => 'stylesheet', 'type' => 'text/css', 'href' => 'baz'),
        );
        $this->helper->direct($links['link1'])
                     ->direct($links['link2'], 'PREPEND')
                     ->direct($links['link3']);

        $string = $this->helper->toString();
        $lines  = substr_count($string, PHP_EOL);
        $this->assertEquals(2, $lines);
        $lines  = substr_count($string, '<link ');
        $this->assertEquals(3, $lines, $string);

        foreach ($links as $link) {
            $substr = ' href="' . $link['href'] . '"';
            $this->assertContains($substr, $string);
            $substr = ' rel="' . $link['rel'] . '"';
            $this->assertContains($substr, $string);
            $substr = ' type="' . $link['type'] . '"';
            $this->assertContains($substr, $string);
        }

        $order = array();
        foreach ($this->helper as $key => $value) {
            if (isset($value->href)) {
                $order[$key] = $value->href;
            }
        }
        $expected = array('bar', 'foo', 'baz');
        $this->assertSame($expected, $order);
    }

    public function testCreatingLinkStackViaStyleSheetMethodsCreatesAppropriateOutput()
    {
        $links = array(
            'link1' => array('rel' => 'stylesheet', 'type' => 'text/css', 'href' => 'foo'),
            'link2' => array('rel' => 'stylesheet', 'type' => 'text/css', 'href' => 'bar'),
            'link3' => array('rel' => 'stylesheet', 'type' => 'text/css', 'href' => 'baz'),
        );
        $this->helper->appendStylesheet($links['link1']['href'])
                     ->prependStylesheet($links['link2']['href'])
                     ->appendStylesheet($links['link3']['href']);

        $string = $this->helper->toString();
        $lines  = substr_count($string, PHP_EOL);
        $this->assertEquals(2, $lines);
        $lines  = substr_count($string, '<link ');
        $this->assertEquals(3, $lines, $string);

        foreach ($links as $link) {
            $substr = ' href="' . $link['href'] . '"';
            $this->assertContains($substr, $string);
            $substr = ' rel="' . $link['rel'] . '"';
            $this->assertContains($substr, $string);
            $substr = ' type="' . $link['type'] . '"';
            $this->assertContains($substr, $string);
        }

        $order = array();
        foreach ($this->helper as $key => $value) {
            if (isset($value->href)) {
                $order[$key] = $value->href;
            }
        }
        $expected = array('bar', 'foo', 'baz');
        $this->assertSame($expected, $order);
    }

    public function testCreatingLinkStackViaAlternateMethodsCreatesAppropriateOutput()
    {
        $links = array(
            'link1' => array('title' => 'stylesheet', 'type' => 'text/css', 'href' => 'foo'),
            'link2' => array('title' => 'stylesheet', 'type' => 'text/css', 'href' => 'bar'),
            'link3' => array('title' => 'stylesheet', 'type' => 'text/css', 'href' => 'baz'),
        );
        $where = 'append';
        foreach ($links as $link) {
            $method = $where . 'Alternate';
            $this->helper->$method($link['href'], $link['type'], $link['title']);
            $where = ('append' == $where) ? 'prepend' : 'append';
        }

        $string = $this->helper->toString();
        $lines  = substr_count($string, PHP_EOL);
        $this->assertEquals(2, $lines);
        $lines  = substr_count($string, '<link ');
        $this->assertEquals(3, $lines, $string);
        $lines  = substr_count($string, ' rel="alternate"');
        $this->assertEquals(3, $lines, $string);

        foreach ($links as $link) {
            $substr = ' href="' . $link['href'] . '"';
            $this->assertContains($substr, $string);
            $substr = ' title="' . $link['title'] . '"';
            $this->assertContains($substr, $string);
            $substr = ' type="' . $link['type'] . '"';
            $this->assertContains($substr, $string);
        }

        $order = array();
        foreach ($this->helper as $key => $value) {
            if (isset($value->href)) {
                $order[$key] = $value->href;
            }
        }
        $expected = array('bar', 'foo', 'baz');
        $this->assertSame($expected, $order);
    }

    public function testOverloadingThrowsExceptionWithNoArguments()
    {
        try {
            $this->helper->appendStylesheet();
            $this->fail('Helper should expect at least one argument');
        } catch (View\Exception $e) {}
    }

    public function testOverloadingShouldAllowSingleArrayArgument()
    {
        $this->helper->setStylesheet(array('href' => '/styles.css'));
        $link = $this->helper->getValue();
        $this->assertEquals('/styles.css', $link->href);
    }

    public function testOverloadingUsingSingleArrayArgumentWithInvalidValuesThrowsException()
    {
        try {
            $this->helper->setStylesheet(array('bogus' => 'unused'));
            $this->fail('Invalid attribute values should raise exception');
        } catch (View\Exception $e) { }
    }

    public function testOverloadingOffsetSetWorks()
    {
        $this->helper->offsetSetStylesheet(100, '/styles.css');
        $items = $this->helper->getArrayCopy();
        $this->assertTrue(isset($items[100]));
        $link = $items[100];
        $this->assertEquals('/styles.css', $link->href);
    }

    public function testOverloadingThrowsExceptionWithInvalidMethod()
    {
        try {
            $this->helper->bogusMethod();
            $this->fail('Invalid method should raise exception');
        } catch (View\Exception $e) { }
    }

    public function testStylesheetAttributesGetSet()
    {
        $this->helper->setStylesheet('/styles.css', 'projection', 'ie6');
        $item = $this->helper->getValue();
        $this->assertObjectHasAttribute('media', $item);
        $this->assertObjectHasAttribute('conditionalStylesheet', $item);

        $this->assertEquals('projection', $item->media);
        $this->assertEquals('ie6', $item->conditionalStylesheet);
    }

    public function testConditionalStylesheetNotCreatedByDefault()
    {
        $this->helper->setStylesheet('/styles.css');
        $item = $this->helper->getValue();
        $this->assertObjectHasAttribute('conditionalStylesheet', $item);
        $this->assertFalse($item->conditionalStylesheet);

        $string = $this->helper->toString();
        $this->assertContains('/styles.css', $string);
        $this->assertNotContains('<!--[if', $string);
        $this->assertNotContains(']>', $string);
        $this->assertNotContains('<![endif]-->', $string);
    }

    public function testConditionalStylesheetCreationOccursWhenRequested()
    {
        $this->helper->setStylesheet('/styles.css', 'screen', 'ie6');
        $item = $this->helper->getValue();
        $this->assertObjectHasAttribute('conditionalStylesheet', $item);
        $this->assertEquals('ie6', $item->conditionalStylesheet);

        $string = $this->helper->toString();
        $this->assertContains('/styles.css', $string);
        $this->assertContains('<!--[if ie6]>', $string);
        $this->assertContains('<![endif]-->', $string);
    }

    public function testSettingAlternateWithTooFewArgsRaisesException()
    {
        try {
            $this->helper->setAlternate('foo');
            $this->fail('Setting alternate with fewer than 3 args should raise exception');
        } catch (View\Exception $e) { }
        try {
            $this->helper->setAlternate('foo', 'bar');
            $this->fail('Setting alternate with fewer than 3 args should raise exception');
        } catch (View\Exception $e) { }
    }

    public function testIndentationIsHonored()
    {
        $this->helper->setIndent(4);
        $this->helper->appendStylesheet('/css/screen.css');
        $this->helper->appendStylesheet('/css/rules.css');
        $string = $this->helper->toString();

        $scripts = substr_count($string, '    <link ');
        $this->assertEquals(2, $scripts);
    }

    public function testLinkRendersAsPlainHtmlIfDoctypeNotXhtml()
    {
        $this->view->broker('doctype')->direct('HTML4_STRICT');
        $this->helper->direct(array('rel' => 'icon', 'src' => '/foo/bar'))
                     ->direct(array('rel' => 'foo', 'href' => '/bar/baz'));
        $test = $this->helper->toString();
        $this->assertNotContains(' />', $test);
    }

    public function testDoesNotAllowDuplicateStylesheets()
    {
        $this->helper->appendStylesheet('foo');
        $this->helper->appendStylesheet('foo');
        $this->assertEquals(1, count($this->helper), var_export($this->helper->getContainer()->getArrayCopy(), 1));
    }

    /**
     * test for ZF-2889
     */
    public function testBooleanStylesheet()
    {
        $this->helper->appendStylesheet(array('href' => '/bar/baz', 'conditionalStylesheet' => false));
        $test = $this->helper->toString();
        $this->assertNotContains('[if false]', $test);
    }

    /**
     * test for ZF-3271
     *
     */
    public function testBooleanTrueConditionalStylesheet()
    {
        $this->helper->appendStylesheet(array('href' => '/bar/baz', 'conditionalStylesheet' => true));
        $test = $this->helper->toString();
        $this->assertNotContains('[if 1]', $test);
        $this->assertNotContains('[if true]', $test);
    }

    /**
     * @issue ZF-3928
     * @link http://framework.zend.com/issues/browse/ZF-3928
     */
    public function testTurnOffAutoEscapeDoesNotEncodeAmpersand()
    {
        $this->helper->setAutoEscape(false)->appendStylesheet('/css/rules.css?id=123&foo=bar');
        $this->assertContains('id=123&foo=bar', $this->helper->toString());
    }

    public function testSetAlternateWithExtras()
    {
        $this->helper->setAlternate('/mydocument.pdf', 'application/pdf', 'foo', array('media' => array('print','screen')));
        $test = $this->helper->toString();
        $this->assertContains('media="print,screen"', $test);
    }

    public function testAppendStylesheetWithExtras()
    {
        $this->helper->appendStylesheet(array('href' => '/bar/baz', 'conditionalStylesheet' => false, 'extras' => array('id' => 'my_link_tag')));
        $test = $this->helper->toString();
        $this->assertContains('id="my_link_tag"', $test);
    }

    public function testSetStylesheetWithMediaAsArray()
    {
        $this->helper->appendStylesheet('/bar/baz', array('screen','print'));
        $test = $this->helper->toString();
        $this->assertContains(' media="screen,print"', $test);
    }

    /**
     * @issue ZF-5435
     */
    public function testContainerMaintainsCorrectOrderOfItems()
    {
        $this->helper->direct()->offsetSetStylesheet(1,'/test1.css');
        $this->helper->direct()->offsetSetStylesheet(10,'/test2.css');
        $this->helper->direct()->offsetSetStylesheet(20,'/test3.css');
        $this->helper->direct()->offsetSetStylesheet(5,'/test4.css');

        $test = $this->helper->toString();

        $expected = '<link href="/test1.css" media="screen" rel="stylesheet" type="text/css" >' . PHP_EOL
                  . '<link href="/test4.css" media="screen" rel="stylesheet" type="text/css" >' . PHP_EOL
                  . '<link href="/test2.css" media="screen" rel="stylesheet" type="text/css" >' . PHP_EOL
                  . '<link href="/test3.css" media="screen" rel="stylesheet" type="text/css" >';

        $this->assertEquals($expected, $test);
    }
}

