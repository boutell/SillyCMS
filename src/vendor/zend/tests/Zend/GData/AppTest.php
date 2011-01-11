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
 * @package    Zend_GData
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace ZendTest\GData;
use Zend\GData\App;
use Zend\GData\App\Extension;
use ZendTest\GData\TestAsset;

/**
 * @category   Zend
 * @package    Zend_GData_App
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_GData
 * @group      Zend_GData_App
 */
class AppTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->fileName = 'Zend/GData/App/_files/FeedSample1.xml';
        $this->expectedEtag = 'W/"CkcHQH8_fCp7ImA9WxRTGEw."';
        $this->expectedMajorProtocolVersion = 1;
        $this->expectedMinorProtocolVersion = 2;
        $this->httpEntrySample = file_get_contents(
                'Zend/GData/_files/AppSample1.txt',
                true);
        $this->httpEntrySampleWithoutVersion = file_get_contents(
                'Zend/GData/_files/AppSample2.txt',
                true);
        $this->httpFeedSample = file_get_contents(
                'Zend/GData/_files/AppSample3.txt',
                true);
        $this->httpFeedSampleWithoutVersion = file_get_contents(
                'Zend/GData/_files/AppSample4.txt',
                true);

        $this->adapter = new TestAsset\MockHttpClient();
        $this->client = new \Zend\GData\HttpClient();
        $this->client->setAdapter($this->adapter);
        $this->service = new App($this->client);
    }

    public function testImportFile()
    {
        $feed = App::importFile($this->fileName,
                'Zend\GData\App\Feed', true);
        $this->assertEquals('dive into mark', $feed->title->text);
    }

    public function testSetAndGetHttpMethodOverride()
    {
        App::setHttpMethodOverride(true);
        $this->assertEquals(true, App::getHttpMethodOverride());
    }

    public function testSetAndGetProtocolVersion()
    {
        $this->service->setMajorProtocolVersion(2);
        $this->service->setMinorProtocolVersion(1);
        $this->assertEquals(2, $this->service->getMajorProtocolVersion());
        $this->assertEquals(1, $this->service->getMinorProtocolVersion());
    }

    public function testDefaultProtocolVersionIs1X()
    {
        $this->assertEquals(1, $this->service->getMajorProtocolVersion());
        $this->assertEquals(null, $this->service->getMinorProtocolVersion());
    }

    public function testMajorProtocolVersionCannotBeLessThanOne()
    {
        $exceptionCaught = false;
        try {
            $this->service->setMajorProtocolVersion(0);
        } catch (App\InvalidArgumentException $e) {
            $exceptionCaught = true;
        }
        $this->assertTrue($exceptionCaught, 'Expected exception not caught: '
                + 'Zend_GData_App_InvalidArgumentException');
    }

    public function testMajorProtocolVersionCannotBeNull()
    {
        $exceptionCaught = false;
        try {
            $this->service->setMajorProtocolVersion(null);
        } catch (App\InvalidArgumentException $e) {
            $exceptionCaught = true;
        }
        $this->assertTrue($exceptionCaught, 'Expected exception not caught: '
                + 'Zend_GData_App_InvalidArgumentException');
    }

    public function testMinorProtocolVersionCannotBeLessThanZero()
    {
        $exceptionCaught = false;
        try {
            $this->service->setMinorProtocolVersion(-1);
        } catch (App\InvalidArgumentException $e) {
            $exceptionCaught = true;
        }
        $this->assertTrue($exceptionCaught, 'Expected exception not caught: '
                + 'Zend_GData_App_InvalidArgumentException');
    }

    public function testNoGDataVersionHeaderSentWhenUsingV1()
    {
        $this->adapter->setResponse(array('HTTP/1.1 200 OK\r\n\r\n'));

        $this->service->setMajorProtocolVersion(1);
        $this->service->setMinorProtocolVersion(NULL);
        $this->service->get('http://www.example.com');

        $headers = $this->adapter->popRequest()->headers;
        $found = false;
        foreach ($headers as $header) {
            if (strstr($header, 'GData-Version:'))
                $found = true;
        }
        $this->assertFalse($found, 'Version header found in V1 feed');
    }

    public function testNoGDataVersionHeaderSentWhenUsingV1X()
    {
        $this->adapter->setResponse(array('HTTP/1.1 200 OK\r\n\r\n'));

        $this->service->setMajorProtocolVersion(1);
        $this->service->setMinorProtocolVersion(1);
        $this->service->get('http://www.example.com');

        $headers = $this->adapter->popRequest()->headers;
        $found = false;
        foreach ($headers as $header) {
            if (strstr($header, 'GData-Version:'))
                $found = true;
        }
        $this->assertTrue(!$found, 'Version header found in V1 feed');
    }

    public function testGDataVersionHeaderSentWhenUsingV2()
    {
        $this->adapter->setResponse(array('HTTP/1.1 200 OK\r\n\r\n'));

        $this->service->setMajorProtocolVersion(2);
        $this->service->setMinorProtocolVersion(NULL);
        $this->service->get('http://www.example.com');

        $headers = $this->adapter->popRequest()->headers;
        $found = false;
        foreach ($headers as $header) {
            if ($header == 'GData-Version: 2')
                $found = true;
        }
        $this->assertTrue($found, 'Version header not found or incorrect');
    }

    public function testGDataVersionHeaderSentWhenUsingV2X()
    {
        $this->adapter->setResponse(array('HTTP/1.1 200 OK\r\n\r\n'));

        $this->service->setMajorProtocolVersion(2);
        $this->service->setMinorProtocolVersion(1);
        $this->service->get('http://www.example.com');

        $headers = $this->adapter->popRequest()->headers;
        $found = false;
        foreach ($headers as $header) {
            if ($header == 'GData-Version: 2')
                $found = true;
        }
        $this->assertTrue($found, 'Version header not found or incorrect');
    }

    public function testHttpETagsPropagateToEntriesOnGet()
    {
        $this->adapter->setResponse($this->httpEntrySample);
        $entry = $this->service->getEntry('http://www.example.com');
        $this->assertEquals($this->expectedEtag, $entry->getEtag());
    }

    public function testHttpETagsPropagateToEntriesOnUpdate()
    {
        $this->adapter->setResponse($this->httpEntrySample);
        $entry = new App\Entry();
        $newEntry = $this->service->updateEntry($entry, 'http://www.example.com');
        $this->assertEquals($this->expectedEtag, $newEntry->getEtag());
    }

    public function testHttpEtagsPropagateToEntriesOnInsert()
    {
        $this->adapter->setResponse($this->httpEntrySample);
        $entry = new App\Entry();
        $newEntry = $this->service->insertEntry($entry, 'http://www.example.com');
        $this->assertEquals($this->expectedEtag, $newEntry->getEtag());
    }

    public function testIfMatchHttpHeaderSetOnUpdate()
    {
        $etag = 'ABCD1234';
        $this->adapter->setResponse("HTTP/1.1 201 Created");
        $this->service->setMajorProtocolVersion(2);
        $entry = new App\Entry();
        $entry->link = array(new Extension\Link(
                'http://www.example.com',
                'edit',
                'application/atom+xml'));
        $entry->setEtag($etag);
        $this->service->updateEntry($entry);
        $headers = $this->adapter->popRequest()->headers;
        $found = false;
        foreach ($headers as $header) {
            if ($header == 'If-Match: ' . $etag)
                $found = true;
        }
        $this->assertTrue($found, 'If-Match header not found or incorrect');
    }

    public function testIfMatchHttpHeaderSetOnUpdateIfWeak()
    {
        $etag = 'W/ABCD1234';
        $this->adapter->setResponse("HTTP/1.1 201 Created");
        $this->service->setMajorProtocolVersion(2);
        $entry = new App\Entry();
        $entry->link = array(new Extension\Link(
                'http://www.example.com',
                'edit',
                'application/atom+xml'));
        $entry->setEtag($etag);
        $this->service->updateEntry($entry);
        $headers = $this->adapter->popRequest()->headers;
        $found = false;
        foreach ($headers as $header) {
            if ($header == 'If-Match: ' . $etag)
                $found = true;
        }
        $this->assertFalse($found, 'If-Match header found');
    }

    public function testIfMatchHttpHeaderSetOnSave()
    {
        $etag = 'ABCD1234';
        $this->adapter->setResponse("HTTP/1.1 201 Created");
        $this->service->setMajorProtocolVersion(2);
        $entry = $this->service->newEntry();
        $entry->link = array(new Extension\Link(
                'http://www.example.com',
                'edit',
                'application/atom+xml'));
        $entry->setEtag($etag);
        $entry->setService($this->service);
        $entry->save();
        $headers = $this->adapter->popRequest()->headers;
        $found = false;
        foreach ($headers as $header) {
            if ($header == 'If-Match: ' . $etag)
                $found = true;
        }
        $this->assertTrue($found, 'If-Match header not found or incorrect');
    }

    public function testIfMatchHttpHeaderNotSetOnDelete()
    {
        $etag = 'ABCD1234';
        $this->adapter->setResponse("HTTP/1.1 201 Created");
        $this->service->setMajorProtocolVersion(2);
        $entry = $this->service->newEntry();
        $entry->link = array(new Extension\Link(
                'http://www.example.com',
                'edit',
                'application/atom+xml'));
        $entry->setEtag($etag);
        $entry->setService($this->service);
        $entry->delete();
        $headers = $this->adapter->popRequest()->headers;
        $found = false;
        foreach ($headers as $header) {
            if ($header == 'If-Match: ' . $etag)
                $found = true;
        }
        $this->assertFalse($found, 'If-Match header found on delete');
    }

    public function testIfMatchHttpHeaderSetOnManualPost()
    {
        $etag = 'ABCD1234';
        $this->adapter->setResponse("HTTP/1.1 201 Created");
        $this->service->setMajorProtocolVersion(2);
        $entry = $this->service->newEntry();
        $entry->setEtag($etag);
        $entry->setService($this->service);
        $this->service->post($entry, 'http://www.example.com');
        $headers = $this->adapter->popRequest()->headers;
        $found = false;
        foreach ($headers as $header) {
            if ($header == 'If-Match: ' . $etag)
                $found = true;
        }
        $this->assertTrue($found, 'If-Match header not found or incorrect');
    }

    public function testIfMatchHttpHeaderSetOnManualPut()
    {
        $etag = 'ABCD1234';
        $this->adapter->setResponse("HTTP/1.1 201 Created");
        $this->service->setMajorProtocolVersion(2);
        $entry = $this->service->newEntry();
        $entry->link = array(new Extension\Link(
                'http://www.example.com',
                'edit',
                'application/atom+xml'));
        $entry->setEtag($etag);
        $entry->setService($this->service);
        $this->service->put($entry);
        $headers = $this->adapter->popRequest()->headers;
        $found = false;
        foreach ($headers as $header) {
            if ($header == 'If-Match: ' . $etag)
                $found = true;
        }
        $this->assertTrue($found, 'If-Match header not found or incorrect');
    }

    public function testIfMatchHttpHeaderSetOnManualDelete()
    {
        $etag = 'ABCD1234';
        $this->adapter->setResponse("HTTP/1.1 201 Created");
        $this->service->setMajorProtocolVersion(2);
        $entry = $this->service->newEntry();
        $entry->link = array(new Extension\Link(
                'http://www.example.com',
                'edit',
                'application/atom+xml'));
        $entry->setEtag($etag);
        $entry->setService($this->service);
        $this->service->delete($entry);
        $headers = $this->adapter->popRequest()->headers;
        $found = false;
        foreach ($headers as $header) {
            if ($header == 'If-Match: ' . $etag)
                $found = true;
        }
        $this->assertFalse($found, 'If-Match header found on delete');
    }

    public function testIfMatchHeaderCanBeSetOnInsert() {
        $etagOverride = 'foo';
        $etag = 'ABCD1234';
        $this->service->setMajorProtocolVersion(2);
        $this->adapter->setResponse($this->httpEntrySample);
        $entry = new App\Entry();
        $entry->setEtag($etag);
        $newEntry = $this->service->insertEntry($entry,
                'http://www.example.com',
                '\Zend\GData\App\Entry',
                array('If-Match' => $etagOverride));
        $headers = $this->adapter->popRequest()->headers;
        $found = false;
        foreach ($headers as $header) {
            if ($header == 'If-Match: ' . $etagOverride)
                $found = true;
        }
        $this->assertTrue($found, 'If-Match header not found or incorrect');
    }

    public function testIfNoneMatchHeaderCanBeSetOnInsert() {
        $etagOverride = 'foo';
        $etag = 'ABCD1234';
        $this->service->setMajorProtocolVersion(2);
        $this->adapter->setResponse($this->httpEntrySample);
        $entry = new App\Entry();
        $entry->setEtag($etag);
        $newEntry = $this->service->insertEntry($entry,
                'http://www.example.com',
                '\Zend\GData\App\Entry',
                array('If-None-Match' => $etagOverride));
        $headers = $this->adapter->popRequest()->headers;
        $found = false;
        foreach ($headers as $header) {
            if ($header == 'If-None-Match: ' . $etagOverride)
                $found = true;
        }
        $this->assertTrue($found, 'If-None-Match header not found or incorrect ');
    }

    public function testIfMatchHeaderCanBeSetOnUpdate() {
        $etagOverride = 'foo';
        $etag = 'ABCD1234';
        $this->service->setMajorProtocolVersion(2);
        $this->adapter->setResponse($this->httpEntrySample);
        $entry = new App\Entry();
        $entry->setEtag($etag);
        $newEntry = $this->service->updateEntry($entry,
                'http://www.example.com',
                '\Zend\GData\App\Entry',
                array('If-Match' => $etagOverride));
        $headers = $this->adapter->popRequest()->headers;
        $found = false;
        foreach ($headers as $header) {
            if ($header == 'If-Match: ' . $etagOverride)
                $found = true;
        }
        $this->assertTrue($found, 'If-Match header not found or incorrect or incorrect');
    }

    public function testIfNoneMatchHeaderCanBeSetOnUpdate() {
        $etagOverride = 'foo';
        $etag = 'ABCD1234';
        $this->service->setMajorProtocolVersion(2);
        $this->adapter->setResponse($this->httpEntrySample);
        $entry = new App\Entry();
        $entry->setEtag($etag);
        $newEntry = $this->service->updateEntry($entry,
                'http://www.example.com',
                '\Zend\GData\App\Entry',
                array('If-None-Match' => $etagOverride));
        $headers = $this->adapter->popRequest()->headers;
        $found = false;
        foreach ($headers as $header) {
            if ($header == 'If-None-Match: ' . $etagOverride)
                $found = true;
        }
        $this->assertTrue($found, 'If-None-Match header not found or incorrect');
    }

    /**
     * @group ZF-8397
     */
    public function testIfMatchHttpHeaderIsResetEachRequest()
    {
        // Update an entry
        $etag = 'ABCD1234';
        $this->adapter->setResponse("HTTP/1.1 201 Created");
        $this->service->setMajorProtocolVersion(2);
        $entry = new App\Entry();
        $entry->link = array(new Extension\Link(
                'http://www.example.com',
                'edit',
                'application/atom+xml'));
        $entry->setEtag($etag);
        $this->service->updateEntry($entry);

        // Get another entry without ETag set,
        // Previous value of If-Match HTTP header should not be sent
        $this->adapter->setResponse($this->httpEntrySample);
        $entry = $this->service->getEntry('http://www.example.com');
        $headers = $this->adapter->popRequest()->headers;
        $found = false;
        foreach ($headers as $header) {
            if ($header == 'If-Match: ' . $etag)
                $found = true;
        }
        $this->assertFalse($found, 'If-Match header found');
    }

    public function testGenerateIfMatchHeaderDataReturnsEtagIfV2() {
        $etag = 'ABCD1234';
        $this->service->setMajorProtocolVersion(2);
        $entry = new App\Entry();
        $entry->setEtag($etag);
        $result = $this->service->generateIfMatchHeaderData($entry, false);
        $this->assertEquals($etag, $result);
    }

    public function testGenerateIfMatchHeaderDataReturnsNullIfV1() {
        $etag = 'ABCD1234';
        $this->service->setMajorProtocolVersion(1);
        $entry = new App\Entry();
        $entry->setEtag($etag);
        $result = $this->service->generateIfMatchHeaderData($entry, false);
        $this->assertEquals(null, $result);
    }

    public function testGenerateIfMatchHeaderDataReturnsNullIfNotEntry() {
        $this->service->setMajorProtocolVersion(2);
        $result = $this->service->generateIfMatchHeaderData("Hello world", false);
        $this->assertEquals(null, $result);
    }

    public function testGenerateIfMatchHeaderDataReturnsNullIfWeak() {
        $etag = 'W/ABCD1234';
        $this->service->setMajorProtocolVersion(2);
        $entry = new App\Entry();
        $entry->setEtag($etag);
        $result = $this->service->generateIfMatchHeaderData($entry, false);
        $this->assertEquals(null, $result);
    }

    public function testGenerateIfMatchHeaderDataReturnsEtagIfWeakAndFlagSet() {
        $etag = 'W/ABCD1234';
        $this->service->setMajorProtocolVersion(2);
        $entry = new App\Entry();
        $entry->setEtag($etag);
        $result = $this->service->generateIfMatchHeaderData($entry, true);
        $this->assertEquals($etag, $result);
    }

    public function testGenerateIfMatchHeaderDataReturnsEtagIfNotWeakAndFlagSet() {
        $etag = 'ABCD1234';
        $this->service->setMajorProtocolVersion(2);
        $entry = new App\Entry();
        $entry->setEtag($etag);
        $result = $this->service->generateIfMatchHeaderData($entry, true);
        $this->assertEquals($etag, $result);
    }

    public function testImportUrlSetsMajorProtocolVersionOnEntry() {
        $this->adapter->setResponse($this->httpEntrySample);
        $entry = $this->service->getEntry('http://www.example.com');
        $this->assertEquals($this->expectedMajorProtocolVersion, $entry->getMajorProtocolVersion());
    }

    public function testImportUrlSetsMinorProtocolVersionOnEntry() {
        $this->adapter->setResponse($this->httpEntrySample);
        $entry = $this->service->getEntry('http://www.example.com');
        $this->assertEquals($this->expectedMinorProtocolVersion, $entry->getMinorProtocolVersion());
    }

    public function testImportUrlSetsNullVersionIfNoVersionHeaderOnEntry() {
        $this->adapter->setResponse($this->httpEntrySampleWithoutVersion);
        $entry = $this->service->getEntry('http://www.example.com');
        $this->assertEquals(null, $entry->getMinorProtocolVersion());
        $this->assertEquals(null, $entry->getMinorProtocolVersion());
    }

    public function testImportUrlSetsMajorProtocolVersionOnFeed() {
        $this->adapter->setResponse($this->httpFeedSample);
        $feed = $this->service->getFeed('http://www.example.com');
        $this->assertEquals($this->expectedMajorProtocolVersion, $feed->getMajorProtocolVersion());
        foreach ($feed as $entry) {
            $this->assertEquals($this->expectedMajorProtocolVersion, $entry->getMajorProtocolVersion());
        }
    }

    public function testImportUrlSetsMinorProtocolVersionOnFeed() {
        $this->adapter->setResponse($this->httpFeedSample);
        $feed = $this->service->getFeed('http://www.example.com');
        $this->assertEquals($this->expectedMinorProtocolVersion, $feed->getMinorProtocolVersion());
        foreach ($feed as $entry) {
            $this->assertEquals($this->expectedMinorProtocolVersion, $entry->getMinorProtocolVersion());
        }
    }

    public function testImportUrlSetsNullVersionIfNoVersionHeaderOnFeed() {
        $this->adapter->setResponse($this->httpFeedSampleWithoutVersion);
        $feed = $this->service->getFeed('http://www.example.com');
        $this->assertEquals(null, $feed->getMajorProtocolVersion());
        $this->assertEquals(null, $feed->getMinorProtocolVersion());
        foreach ($feed as $entry) {
            $this->assertEquals(null, $entry->getMajorProtocolVersion());
            $this->assertEquals(null, $entry->getMinorProtocolVersion());
        }
    }

    public function testMagicConstructorsPropogateMajorVersion() {
        $v = 42;
        $this->service->setMajorProtocolVersion($v);
        $feed = $this->service->newFeed();
        $this->assertEquals($v, $feed->getMajorProtocolVersion());
    }

    public function testMagicConstructorsPropogateMinorVersion() {
        $v = 84;
        $this->service->setMinorProtocolVersion($v);
        $feed = $this->service->newFeed();
        $this->assertEquals($v, $feed->getMinorProtocolVersion());
    }
}
