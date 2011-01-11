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
 * @package    UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace ZendTest\Feed\PubSubHubbub;
use Zend\Feed\PubSubHubbub;

/**
 * @category   Zend
 * @package    Zend_Feed
 * @subpackage UnitTests
 * @group      Zend_Feed
 * @group      Zend_Feed_Subsubhubbub
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class PubSubHubbubTest extends \PHPUnit_Framework_TestCase
{

    public function teardown()
    {
        PubSubHubbub\PubSubHubbub::clearHttpClient();
    }

    public function testCanSetCustomHttpClient()
    {
        PubSubHubbub\PubSubHubbub::setHttpClient(new Pubsub());
        $this->assertType('ZendTest\Feed\PubSubHubbub\Pubsub', PubSubHubbub\PubSubHubbub::getHttpClient());
    }

    public function testCanDetectHubs()
    {
        $feed = \Zend\Feed\Reader\Reader::importFile(__DIR__ . '/_files/rss20.xml');
        $this->assertEquals(array(
            'http://www.example.com/hub', 'http://www.example.com/hub2'
        ), PubSubHubbub\PubSubHubbub::detectHubs($feed));
    }

}

class Pubsub extends \Zend\Http\Client {}
