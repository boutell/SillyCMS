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
 * @package    Zend_GData_GBase
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace ZendTest\GData\GBase;
use Zend\GData\GBase;

/**
 * @category   Zend
 * @package    Zend_GData_GBase
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_GData
 * @group      Zend_GData_GBase
 */
class SnippetFeedTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->snippetFeed = new GBase\SnippetFeed(
                file_get_contents(__DIR__ . '/_files/TestDataGBaseSnippetFeedSample1.xml'),
                true);
    }

    public function testToAndFromString()
    {
        $this->assertEquals(count($this->snippetFeed->entries), 1);
        foreach($this->snippetFeed->entries as $entry)
        {
            $this->assertTrue($entry instanceof GBase\SnippetEntry);
        }

        $newSnippetFeed = new GBase\SnippetFeed();
        $doc = new \DOMDocument();
        $doc->loadXML($this->snippetFeed->saveXML());
        $newSnippetFeed->transferFromDom($doc->documentElement);

        $this->assertEquals(count($newSnippetFeed->entries), 1);
        foreach($newSnippetFeed->entries as $entry)
        {
            $this->assertTrue($entry instanceof GBase\SnippetEntry);
        }
    }

}
