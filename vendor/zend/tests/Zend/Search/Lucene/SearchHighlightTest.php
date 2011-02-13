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
 * @package    Zend_Search_Lucene
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace ZendTest\Search\Lucene;
use Zend\Search\Lucene\Search\Query;
use Zend\Search\Lucene\Search;

/**
 * Zend_Search_Lucene
 */

/**
 * PHPUnit test case
 */

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Search_Lucene
 */
class SearchHighlightTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Wildcard pattern minimum preffix
     *
     * @var integer
     */
    protected $_wildcardMinPrefix;

    /**
     * Fuzzy search default preffix length
     *
     * @var integer
     */
    protected $_defaultPrefixLength;

    public function setUp()
    {
        $this->_wildcardMinPrefix = Query\Wildcard::getMinPrefixLength();
        Query\Wildcard::setMinPrefixLength(0);

        $this->_defaultPrefixLength = Query\Fuzzy::getDefaultPrefixLength();
        Query\Fuzzy::setDefaultPrefixLength(0);
    }

    public function tearDown()
    {
        Query\Wildcard::setMinPrefixLength($this->_wildcardMinPrefix);
        Query\Fuzzy::setDefaultPrefixLength($this->_defaultPrefixLength);
    }


    public function testHtmlFragmentHighlightMatches()
    {
        $query = Search\QueryParser::parse('title:"The Right Way" AND text:go');

        $highlightedHtmlFragment = $query->htmlFragmentHighlightMatches('Text highlighting using Zend_Search_Lucene is the right way to go!');

        $this->assertEquals($highlightedHtmlFragment,
                            'Text highlighting using Zend_Search_Lucene is <b style="color:black;background-color:#66ffff">the</b> <b style="color:black;background-color:#66ffff">right</b> <b style="color:black;background-color:#66ffff">way</b> to <b style="color:black;background-color:#ff66ff">go</b>!');
    }

//    public function testHtmlFragmentHighlightMatchesCyrillic()
//    {
//        $query = Search\QueryParser::parse('title:"некоторый текст" AND text:поехали');
//
//        $highlightedHtmlFragment = $query->htmlFragmentHighlightMatches('Подсвечиваем некоторый текст с использованием Zend_Search_Lucene. Поехали!');
//
//        $this->assertEquals($highlightedHtmlFragment,
//                            'Text highlighting using Zend_Search_Lucene is <b style="color:black;background-color:#66ffff">the</b> <b style="color:black;background-color:#66ffff">right</b> <b style="color:black;background-color:#66ffff">way</b> to <b style="color:black;background-color:#ff66ff">go</b>!');
//    }
//
//    public function testHtmlFragmentHighlightMatchesCyrillicWindows()
//    {
//        $query = Search\QueryParser::parse('title:"Некоторый текст" AND text:поехали');
//
//        $highlightedHtmlFragment =
//                $query->htmlFragmentHighlightMatches(iconv('UTF-8',
//                                                           'Windows-1251',
//                                                           'Подсвечиваем некоторый текст с использованием Zend_Search_Lucene. Поехали!'),
//                                                     'Windows-1251');
//
//        $this->assertEquals($highlightedHtmlFragment,
//                            'Text highlighting using Zend_Search_Lucene is <b style="color:black;background-color:#66ffff">the</b> <b style="color:black;background-color:#66ffff">right</b> <b style="color:black;background-color:#66ffff">way</b> to <b style="color:black;background-color:#ff66ff">go</b>!');
//    }

    public function testHighlightPhrasePlusTerm()
    {
        $query = Search\QueryParser::parse('title:"The Right Way" AND text:go');

        $html = '<HTML>'
                . '<HEAD><TITLE>Page title</TITLE></HEAD>'
                . '<BODY>'
                .   'Text highlighting using Zend_Search_Lucene is the right way to go!'
                . '</BODY>'
              . '</HTML>';

        $highlightedHTML = $query->highlightMatches($html);

        $this->assertTrue(strpos($highlightedHTML, '<b style="color:black;background-color:#66ffff">the</b>') !== false);
        $this->assertTrue(strpos($highlightedHTML, '<b style="color:black;background-color:#66ffff">right</b>') !== false);
        $this->assertTrue(strpos($highlightedHTML, '<b style="color:black;background-color:#66ffff">way</b>') !== false);
        $this->assertTrue(strpos($highlightedHTML, '<b style="color:black;background-color:#ff66ff">go</b>') !== false);
    }

    public function testHighlightMultitermWithProhibitedTerms()
    {
        $query = Search\QueryParser::parse('+text +highlighting -using -right +go');

        $html = '<HTML>'
                . '<HEAD><TITLE>Page title</TITLE></HEAD>'
                . '<BODY>'
                .   'Text highlighting using Zend_Search_Lucene is the right way to go!'
                . '</BODY>'
              . '</HTML>';

        $highlightedHTML = $query->highlightMatches($html);

        $this->assertTrue(strpos($highlightedHTML, '<b style="color:black;background-color:#66ffff">Text</b>') !== false);
        $this->assertTrue(strpos($highlightedHTML, '<b style="color:black;background-color:#ff66ff">highlighting</b>') !== false);
        $this->assertTrue(strpos($highlightedHTML, 'using Zend_Search_Lucene is the right way to') !== false);
        $this->assertTrue(strpos($highlightedHTML, '<b style="color:black;background-color:#ffff66">go</b>') !== false);
    }

    public function testHighlightWildcard1()
    {
        $query = Search\QueryParser::parse('te?t');

        $html = '<HTML>'
                . '<HEAD><TITLE>Page title</TITLE></HEAD>'
                . '<BODY>'
                .   'Test of text highlighting using wildcard query with question mark. Testing...'
                . '</BODY>'
              . '</HTML>';

        $highlightedHTML = $query->highlightMatches($html);

        $this->assertTrue(strpos($highlightedHTML, '<b style="color:black;background-color:#66ffff">Test</b>') !== false);
        $this->assertTrue(strpos($highlightedHTML, '<b style="color:black;background-color:#66ffff">text</b>') !== false);
        // Check that 'Testing' word is not highlighted
        $this->assertTrue(strpos($highlightedHTML, 'mark. Testing...') !== false);
    }

    public function testHighlightWildcard2()
    {
        $query = Search\QueryParser::parse('te?t*');

        $html = '<HTML>'
                . '<HEAD><TITLE>Page title</TITLE></HEAD>'
                . '<BODY>'
                .   'Test of text highlighting using wildcard query with question mark. Testing...'
                . '</BODY>'
              . '</HTML>';

        $highlightedHTML = $query->highlightMatches($html);

        $this->assertTrue(strpos($highlightedHTML, '<b style="color:black;background-color:#66ffff">Test</b>') !== false);
        $this->assertTrue(strpos($highlightedHTML, '<b style="color:black;background-color:#66ffff">text</b>') !== false);
        // Check that 'Testing' word is also highlighted
        $this->assertTrue(strpos($highlightedHTML, '<b style="color:black;background-color:#66ffff">Testing</b>') !== false);
    }

    public function testHighlightFuzzy1()
    {
        $query = Search\QueryParser::parse('test~');

        $html = '<HTML>'
                . '<HEAD><TITLE>Page title</TITLE></HEAD>'
                . '<BODY>'
                .   'Test of text fuzzy search terms highlighting. '
                .   'Words: test, text, latest, left, list, next, ...'
                . '</BODY>'
              . '</HTML>';

        $highlightedHTML = $query->highlightMatches($html);

        $this->assertTrue(strpos($highlightedHTML, '<b style="color:black;background-color:#66ffff">Test</b>') !== false);
        $this->assertTrue(strpos($highlightedHTML, '<b style="color:black;background-color:#66ffff">test</b>') !== false);
        $this->assertTrue(strpos($highlightedHTML, '<b style="color:black;background-color:#66ffff">text</b>') !== false);
        // Check that other words are not highlighted
        $this->assertTrue(strpos($highlightedHTML, 'latest, left, list, next, ...') !== false);
    }

    public function testHighlightFuzzy2()
    {
        $query = Search\QueryParser::parse('test~0.4');

        $html = '<HTML>'
                . '<HEAD><TITLE>Page title</TITLE></HEAD>'
                . '<BODY>'
                .   'Test of text fuzzy search terms highlighting. '
                .   'Words: test, text, latest, left, list, next, ...'
                . '</BODY>'
              . '</HTML>';

        $highlightedHTML = $query->highlightMatches($html);

        $this->assertTrue(strpos($highlightedHTML, '<b style="color:black;background-color:#66ffff">Test</b>') !== false);
        $this->assertTrue(strpos($highlightedHTML, '<b style="color:black;background-color:#66ffff">test</b>') !== false);
        // Check that other words are also highlighted
        $this->assertTrue(strpos($highlightedHTML, '<b style="color:black;background-color:#66ffff">text</b>') !== false);
        $this->assertTrue(strpos($highlightedHTML, '<b style="color:black;background-color:#66ffff">latest</b>') !== false);
        $this->assertTrue(strpos($highlightedHTML, '<b style="color:black;background-color:#66ffff">left</b>') !== false);
        $this->assertTrue(strpos($highlightedHTML, '<b style="color:black;background-color:#66ffff">list</b>') !== false);
        $this->assertTrue(strpos($highlightedHTML, '<b style="color:black;background-color:#66ffff">next</b>') !== false);
    }

    public function testHighlightRangeInclusive()
    {
        $query = Search\QueryParser::parse('[business TO by]');

        $html = '<HTML>'
                . '<HEAD><TITLE>Page title</TITLE></HEAD>'
                . '<BODY>'
                .   'Test of text using range query. '
                .   'It has to match "business", "by", "buss" and "but" words, but has to skip "bus"'
                . '</BODY>'
              . '</HTML>';

        $highlightedHTML = $query->highlightMatches($html);

        $this->assertTrue(strpos($highlightedHTML, '<b style="color:black;background-color:#66ffff">business</b>') !== false);
        $this->assertTrue(strpos($highlightedHTML, '<b style="color:black;background-color:#66ffff">by</b>') !== false);
        $this->assertTrue(strpos($highlightedHTML, '<b style="color:black;background-color:#66ffff">buss</b>') !== false);
        $this->assertTrue(strpos($highlightedHTML, '<b style="color:black;background-color:#66ffff">but</b>') !== false);
        // Check that "bus" word is skipped
        $this->assertTrue(strpos($highlightedHTML, 'has to skip "bus"') !== false);
    }

    public function testHighlightRangeNonInclusive()
    {
        $query = Search\QueryParser::parse('{business TO by}');

        $html = '<HTML>'
                . '<HEAD><TITLE>Page title</TITLE></HEAD>'
                . '<BODY>'
                .   'Test of text using range query. '
                .   'It has to match "buss" and "but" words, but has to skip "business", "by" and "bus"'
                . '</BODY>'
              . '</HTML>';

        $highlightedHTML = $query->highlightMatches($html);

        $this->assertTrue(strpos($highlightedHTML, '<b style="color:black;background-color:#66ffff">buss</b>') !== false);
        $this->assertTrue(strpos($highlightedHTML, '<b style="color:black;background-color:#66ffff">but</b>') !== false);
        // Check that "bus" word is skipped
        $this->assertTrue(strpos($highlightedHTML, 'has to skip "business", "by" and "bus"') !== false);
    }
}
