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
 * @package    Zend_Filter
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace ZendTest\Filter;

use Zend\Filter\StripTags as StripTagsFilter;

/**
 * @category   Zend
 * @package    Zend_Filter
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Filter
 */
class StripTagsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Zend_Filter_StripTags object
     *
     * @var Zend_Filter_StripTags
     */
    protected $_filter;

    /**
     * Creates a new Zend_Filter_StripTags object for each test method
     *
     * @return void
     */
    public function setUp()
    {
        $this->_filter = new StripTagsFilter();
    }

    /**
     * Ensures that getTagsAllowed() returns expected default value
     *
     * @return void
     */
    public function testGetTagsAllowed()
    {
        $this->assertEquals(array(), $this->_filter->getTagsAllowed());
    }

    /**
     * Ensures that setTagsAllowed() follows expected behavior when provided a single tag
     *
     * @return void
     */
    public function testSetTagsAllowedString()
    {
        $this->_filter->setTagsAllowed('b');
        $this->assertEquals(array('b' => array()), $this->_filter->getTagsAllowed());
    }

    /**
     * Ensures that setTagsAllowed() follows expected behavior when provided an array of tags
     *
     * @return void
     */
    public function testSetTagsAllowedArray()
    {
        $tagsAllowed = array(
            'b',
            'a'   => 'href',
            'div' => array('id', 'class')
            );
        $this->_filter->setTagsAllowed($tagsAllowed);
        $tagsAllowedExpected = array(
            'b'   => array(),
            'a'   => array('href' => null),
            'div' => array('id' => null, 'class' => null)
            );
        $this->assertEquals($tagsAllowedExpected, $this->_filter->getTagsAllowed());
    }

    /**
     * Ensures that getAttributesAllowed() returns expected default value
     *
     * @return void
     */
    public function testGetAttributesAllowed()
    {
        $this->assertEquals(array(), $this->_filter->getAttributesAllowed());
    }

    /**
     * Ensures that setAttributesAllowed() follows expected behavior when provided a single attribute
     *
     * @return void
     */
    public function testSetAttributesAllowedString()
    {
        $this->_filter->setAttributesAllowed('class');
        $this->assertEquals(array('class' => null), $this->_filter->getAttributesAllowed());
    }

    /**
     * Ensures that setAttributesAllowed() follows expected behavior when provided an array of attributes
     *
     * @return void
     */
    public function testSetAttributesAllowedArray()
    {
        $attributesAllowed = array(
            'clAss',
            4    => 'inT',
            'ok' => 'String',
            null
            );
        $this->_filter->setAttributesAllowed($attributesAllowed);
        $attributesAllowedExpected = array(
            'class'  => null,
            'int'    => null,
            'string' => null
            );
        $this->assertEquals($attributesAllowedExpected, $this->_filter->getAttributesAllowed());
    }

    /**
     * Ensures that a single unclosed tag is stripped in its entirety
     *
     * @return void
     */
    public function testFilterTagUnclosed1()
    {
        $filter   = $this->_filter;
        $input    = '<a href="http://example.com" Some Text';
        $expected = '';
        $this->assertEquals($expected, $filter($input));
    }

    /**
     * Ensures that a single tag is stripped
     *
     * @return void
     */
    public function testFilterTag1()
    {
        $filter   = $this->_filter;
        $input    = '<a href="example.com">foo</a>';
        $expected = 'foo';
        $this->assertEquals($expected, $filter($input));
    }

    /**
     * Ensures that singly nested tags are stripped
     *
     * @return void
     */
    public function testFilterTagNest1()
    {
        $filter   = $this->_filter;
        $input    = '<a href="example.com"><b>foo</b></a>';
        $expected = 'foo';
        $this->assertEquals($expected, $filter($input));
    }

    /**
     * Ensures that two successive tags are stripped
     *
     * @return void
     */
    public function testFilterTag2()
    {
        $filter   = $this->_filter;
        $input    = '<a href="example.com">foo</a><b>bar</b>';
        $expected = 'foobar';
        $this->assertEquals($expected, $filter($input));
    }

    /**
     * Ensures that an allowed tag is returned as lowercase and with backward-compatible XHTML ending, where supplied
     *
     * @return void
     */
    public function testFilterTagAllowedBackwardCompatible()
    {
        $filter   = $this->_filter;
        $input    = '<BR><Br><bR><br/><br  /><br / ></br></bR>';
        $expected = '<br><br><br><br /><br /><br></br></br>';
        $this->_filter->setTagsAllowed('br');
        $this->assertEquals($expected, $filter($input));
    }

    /**
     * Ensures that any greater-than symbols '>' are removed from text preceding a tag
     *
     * @return void
     */
    public function testFilterTagPrefixGt()
    {
        $filter   = $this->_filter;
        $input    = '2 > 1 === true<br/>';
        $expected = '2  1 === true';
        $this->assertEquals($expected, $filter($input));
    }

    /**
     * Ensures that any greater-than symbols '>' are removed from text having no tags
     *
     * @return void
     */
    public function testFilterGt()
    {
        $filter   = $this->_filter;
        $input    = '2 > 1 === true ==> $object->property';
        $expected = '2  1 === true == $object-property';
        $this->assertEquals($expected, $filter($input));
    }

    /**
     * Ensures that any greater-than symbols '>' are removed from text wrapping a tag
     *
     * @return void
     */
    public function testFilterTagWrappedGt()
    {
        $filter   = $this->_filter;
        $input    = '2 > 1 === true <==> $object->property';
        $expected = '2  1 === true  $object-property';
        $this->assertEquals($expected, $filter($input));
    }

    /**
     * Ensures that an attribute for an allowed tag is stripped
     *
     * @return void
     */
    public function testFilterTagAllowedAttribute()
    {
        $filter = $this->_filter;
        $tagsAllowed = 'img';
        $this->_filter->setTagsAllowed($tagsAllowed);
        $input    = '<IMG alt="foo" />';
        $expected = '<img />';
        $this->assertEquals($expected, $filter($input));
    }

    /**
     * Ensures that an allowed tag with an allowed attribute is filtered as expected
     *
     * @return void
     */
    public function testFilterTagAllowedAttributeAllowed()
    {
        $filter = $this->_filter;
        $tagsAllowed = array(
            'img' => 'alt'
            );
        $this->_filter->setTagsAllowed($tagsAllowed);
        $input    = '<IMG ALT="FOO" />';
        $expected = '<img alt="FOO" />';
        $this->assertEquals($expected, $filter($input));
    }

    /**
     * Ensures expected behavior when a greater-than symbol '>' appears in an allowed attribute's value
     *
     * Currently this is not unsupported; these symbols should be escaped when used in an attribute value.
     *
     * @return void
     */
    public function testFilterTagAllowedAttributeAllowedGt()
    {
        $filter = $this->_filter;
        $tagsAllowed = array(
            'img' => 'alt'
            );
        $this->_filter->setTagsAllowed($tagsAllowed);
        $input    = '<img alt="$object->property" />';
        $expected = '<img>property" /';
        $this->assertEquals($expected, $filter($input));
    }

    /**
     * Ensures expected behavior when an escaped greater-than symbol '>' appears in an allowed attribute's value
     *
     * @return void
     */
    public function testFilterTagAllowedAttributeAllowedGtEscaped()
    {
        $filter = $this->_filter;
        $tagsAllowed = array(
            'img' => 'alt'
            );
        $this->_filter->setTagsAllowed($tagsAllowed);
        $input    = '<img alt="$object-&gt;property" />';
        $expected = '<img alt="$object-&gt;property" />';
        $this->assertEquals($expected, $filter($input));
    }

    /**
     * Ensures that an unterminated attribute value does not affect other attributes but causes the corresponding
     * attribute to be removed in its entirety.
     *
     * @return void
     */
    public function testFilterTagAllowedAttributeAllowedValueUnclosed()
    {
        $filter = $this->_filter;
        $tagsAllowed = array(
            'img' => array('alt', 'height', 'src', 'width')
            );
        $this->_filter->setTagsAllowed($tagsAllowed);
        $input    = '<img src="image.png" alt="square height="100" width="100" />';
        $expected = '<img src="image.png" alt="square height=" width="100" />';
        $this->assertEquals($expected, $filter($input));
    }

    /**
     * Ensures that an allowed attribute having no value is removed (XHTML disallows attributes with no values)
     *
     * @return void
     */
    public function testFilterTagAllowedAttributeAllowedValueMissing()
    {
        $filter = $this->_filter;
        $tagsAllowed = array(
            'input' => array('checked', 'name', 'type')
            );
        $this->_filter->setTagsAllowed($tagsAllowed);
        $input    = '<input name="foo" type="checkbox" checked />';
        $expected = '<input name="foo" type="checkbox" />';
        $this->assertEquals($expected, $filter($input));
    }

    /**
     * Ensures that the filter works properly for the data reported on fw-general on 2007-05-26
     *
     * @see    http://www.nabble.com/question-about-tag-filter-p10813688s16154.html
     * @return void
     */
    public function testFilter20070526()
    {
        $filter = $this->_filter;
        $tagsAllowed = array(
            'object' => array('width', 'height'),
            'param'  => array('name', 'value'),
            'embed'  => array('src', 'type', 'wmode', 'width', 'height'),
            );
        $this->_filter->setTagsAllowed($tagsAllowed);
        $input = '<object width="425" height="350"><param name="movie" value="http://www.example.com/path/to/movie">'
               . '</param><param name="wmode" value="transparent"></param><embed '
               . 'src="http://www.example.com/path/to/movie" type="application/x-shockwave-flash" '
               . 'wmode="transparent" width="425" height="350"></embed></object>';
        $expected = '<object width="425" height="350"><param name="movie" value="http://www.example.com/path/to/movie">'
               . '</param><param name="wmode" value="transparent"></param><embed '
               . 'src="http://www.example.com/path/to/movie" type="application/x-shockwave-flash" '
               . 'wmode="transparent" width="425" height="350"></embed></object>';
        $this->assertEquals($expected, $filter($input));
    }

    /**
     * Ensures that a comment is stripped
     *
     * @return void
     */
    public function testFilterComment()
    {
        $filter = $this->_filter;
        $input    = '<!-- a comment -->';
        $expected = '';
        $this->assertEquals($expected, $filter($input));
    }

    /**
     * Ensures that a comment wrapped with other strings is stripped
     *
     * @return void
     */
    public function testFilterCommentWrapped()
    {
        $filter = $this->_filter;
        $input    = 'foo<!-- a comment -->bar';
        $expected = 'foobar';
        $this->assertEquals($expected, $filter($input));
    }

    /**
     * Ensures that a comment IS removed when comments are flagged as allowed
     *
     * @group ZF-8473
     * @return void
     */
    public function testSpecifyingCommentsAllowedStillStripsComments()
    {
        $filter = $this->_filter;
        $input    = '<!-- a comment -->';
        $expected = '';
        $this->_filter->setCommentsAllowed(true);
        $this->assertEquals($expected, $filter($input));
    }

    /**
     * Ensures that a comment containing tags is untouched when comments are allowed
     *
     * @group ZF-8473
     * @return void
     */
    public function testSpecifyingCommentsAllowedStripsCommentsContainingTags()
    {
        $filter = $this->_filter;
        $input    = '<!-- a comment <br /> <h1>SuperLarge</h1> -->';
        $expected = '';
        $filter->setCommentsAllowed(true);
        $this->assertEquals($expected, $filter($input));
    }

    /**
     * Ensures expected behavior when comments are marked as allowed (in our
     * case, this should have no effect) and a comment contains tags and
     * linebreaks
     *
     * @group ZF-8473
     * @return void
     */
    public function testSpecifyingCommentsAllowedFiltersCommentsContainingTagsAndLinebreaks()
    {
        $filter = $this->_filter;
        $input    = "<br> test <p> text </p> with <!-- comments --> and <!-- hidd\n\nen <br> -->";
        $expected = " test  text  with  and ";
        $filter->setCommentsAllowed(true);
        $this->assertEquals($expected, $filter($input));
    }

    /**
     * Ensures expected behavior when comments are allowed but nested
     *
     * @group ZF-8473
     * @return void
     */
    public function testSpecifyingCommentsAllowedShouldStillStripNestedComments()
    {
        $filter = $this->_filter;
        $input    = '<a> <!-- <b> <!-- <c> --> <d> --> <e>';
        $expected = '  ';
        $filter->setCommentsAllowed(true);
        $this->assertEquals($expected, $filter($input));
    }

    /**
     * Ensures that space between double-hyphen and closing bracket still matches as a comment delimiter
     *
     * @group ZF-8473
     * @see    http://www.w3.org/TR/1999/REC-html401-19991224/intro/sgmltut.html#h-3.2.4
     * @return void
     */
    public function testFilterCommentsAllowedDelimiterEndingWhiteSpace()
    {
        $filter = $this->_filter;
        $input    = '<a> <!-- <b> --  > <c>';
        $expected = '  ';
        $filter->setCommentsAllowed(true);
        $this->assertEquals($expected, $filter($input));
    }

    /**
     * Ensures that a closing angle bracket in an allowed attribute does not break the parser
     *
     * @return void
     * @link   http://framework.zend.com/issues/browse/ZF-3278
     */
    public function testClosingAngleBracketInAllowedAttributeValue()
    {
        $filter = $this->_filter;
        $tagsAllowed = array(
            'a' => 'href'
            );
        $filter->setTagsAllowed($tagsAllowed);
        $input    = '<a href="Some &gt; Text">';
        $expected = '<a href="Some &gt; Text">';
        $this->assertEquals($expected, $filter($input));
    }

    /**
     * Ensures that an allowed attribute's value may end with an equals sign '='
     *
     * @group ZF-3293
     * @group ZF-5983
     */
    public function testAllowedAttributeValueMayEndWithEquals()
    {
        $filter = $this->_filter;
        $tagsAllowed = array(
            'element' => 'attribute'
        );
        $filter->setTagsAllowed($tagsAllowed);
        $input = '<element attribute="a=">contents</element>';
        $this->assertEquals($input, $filter($input));
    }

    /**
     * @group ZF-5983
     */
    public function testDisallowedAttributesSplitOverMultipleLinesShouldBeStripped()
    {
        $filter = $this->_filter;
        $tagsAllowed = array('a' => 'href');
        $filter->setTagsAllowed($tagsAllowed);
        $input = '<a href="http://framework.zend.com/issues" onclick
=
    "alert(&quot;Gotcha&quot;); return false;">http://framework.zend.com/issues</a>';
        $filtered = $filter($input);
        $this->assertNotContains('onclick', $filtered);
    }

    /**
     * @ZF-8828
     */
    public function testFilterIsoChars()
    {
        $filter = $this->_filter;
        $input    = 'äöü<!-- a comment -->äöü';
        $expected = 'äöüäöü';
        $this->assertEquals($expected, $filter($input));

        $input    = 'äöü<!-- a comment -->äöü';
        $input    = iconv("UTF-8", "ISO-8859-1", $input);
        $output   = $filter($input);
        $this->assertFalse(empty($output));
    }

    /**
     * @ZF-8828
     */
    public function testFilterIsoCharsInComment()
    {
        $filter = $this->_filter;
        $input    = 'äöü<!--üßüßüß-->äöü';
        $expected = 'äöüäöü';
        $this->assertEquals($expected, $filter($input));

        $input    = 'äöü<!-- a comment -->äöü';
        $input    = iconv("UTF-8", "ISO-8859-1", $input);
        $output   = $filter($input);
        $this->assertFalse(empty($output));
    }

    /**
     * @ZF-8828
     */
    public function testFilterSplitCommentTags()
    {
        $filter = $this->_filter;
        $input    = 'äöü<!-->üßüßüß<-->äöü';
        $expected = 'äöüäöü';
        $this->assertEquals($expected, $filter($input));
    }

    /**
     * @group ZF-9434
     */
    public function testCommentWithTagInSameLine()
    {
        $filter = $this->_filter;
        $input    = 'test <!-- testcomment --> test <div>div-content</div>';
        $expected = 'test  test div-content';
        $this->assertEquals($expected, $filter($input));
    }

    /**
     * @group ZF-9833
     */
    public function testMultiParamArray()
    {
        $filter = new StripTagsFilter(array("a","b","hr"),array(),true);

        $input    = 'test <a /> test <div>div-content</div>';
        $expected = 'test <a /> test div-content';
        $this->assertEquals($expected, $filter->filter($input));
    }

    /**
     * @group ZF-9828
     */
    public function testMultiQuoteInput()
    {
        $filter = new StripTagsFilter(
            array(
                'allowTags' => 'img',
                'allowAttribs' => array('width', 'height', 'src')
            )
        );

        $input    = '<img width="10" height="10" src=\'wont_be_matched.jpg\'>';
        $expected = '<img width="10" height="10" src=\'wont_be_matched.jpg\'>';
        $this->assertEquals($expected, $filter->filter($input));
    }
}
