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
 * @package    TimeSync
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace ZendTest\TimeSync;

use Zend\TimeSync;

/**
 * @category   Zend
 * @package    TimeSync
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      TimeSync
 */
class TimeSyncTest extends \PHPUnit_Framework_TestCase
{
    public $timeservers = array(
        // invalid servers
        'server_a'  => 'ntp://be.foo.bar.org',
        'server_b'  => 'sntp://be.foo.bar.org',
        'server_c'  => 'sntp://foo:bar@be.foo.bar.org:123',

        // valid servers
        'server_d'  => 'ntp://be.pool.ntp.org',
        'server_e'  => 'ntp://time.windows.com',
        'server_f'  => 'sntp://time-C.timefreq.bldrdoc.gov'
    );

    /**
     * Test for object initialisation
     *
     * @return void
     */
    public function testInitTimeserver()
    {
        $server = new TimeSync\TimeSync();

        $this->assertTrue($server instanceof TimeSync\TimeSync);
    }

    /**
     * Test for object initialisation with multiple timeservers
     *
     * @return void
     */
    public function testInitTimeservers()
    {
        $server = new TimeSync\TimeSync($this->timeservers);
        $result = $server->getServer('server_f');

        $this->assertTrue($result instanceof TimeSync\Protocol);
    }

    /**
     * Test for object initialisation with a single timeserver that will
     * default to the default scheme (ntp), because no scheme is supplied
     *
     * @return void
     */
    public function testInitDefaultScheme()
    {
        $server = new TimeSync\TimeSync('time.windows.com', 'windows_time');
        $server->setServer('windows_time');
        $result = $server->getServer();

        $this->assertTrue($result instanceof TimeSync\Ntp);
    }

    /**
     * Test for object initialisation with a single NTP timeserver
     *
     * @return void
     */
    public function testInitNtpScheme()
    {
        $server = new TimeSync\TimeSync('ntp://time.windows.com', 'windows_time');
        $server->setServer('windows_time');
        $result = $server->getServer();

        $this->assertTrue($result instanceof TimeSync\Ntp);
    }

    /**
     * Test for object initialisation with a single SNTP timeserver
     *
     * @return void
     */
    public function testInitSntpScheme()
    {
        $server = new TimeSync\TimeSync('sntp://time.zend.com', 'windows_time');
        $server->setServer('windows_time');
        $result = $server->getServer();

        $this->assertTrue($result instanceof TimeSync\Sntp);
    }

    /**
     * Test for object initialisation with an unsupported scheme. This will
     * cause the default scheme to be used (ntp)
     *
     * @return void
     */
    public function testInitUnknownScheme()
    {
        $this->setExpectedException('Zend\TimeSync\Exception\RuntimeException');
        $server = new TimeSync\TimeSync('http://time.windows.com', 'windows_time');
    }

    /**
     * Test setting a single option
     *
     * @return void
     */
    public function testSetOption()
    {
        $timeout = 5;

        $server = new TimeSync\TimeSync();
        $server->setOptions(array('timeout' => $timeout));

        $this->assertEquals($timeout, $server->getOptions('timeout'));
    }

    /**
     * Test setting an array of options
     *
     * @return void
     */
    public function testSetOptions()
    {
        $options = array(
            'timeout' => 5,
            'foo'     => 'bar'
        );

        $server = new TimeSync\TimeSync();
        $server->setOptions($options);

        $this->assertEquals($options['timeout'], $server->getOptions('timeout'));
        $this->assertEquals($options['foo'], $server->getOptions('foo'));
    }

    /**
     * Test getting an option that is not set
     *
     * @return void
     */
    public function testGetInvalidOptionKey()
    {
        $server = new TimeSync\TimeSync();
        $this->setExpectedException('Zend\TimeSync\Exception\OutOfBoundsException');
        $result = $server->getOptions('foobar');
    }

    /**
     * Test marking a none existing timeserver as current
     *
     * @return void
     */
    public function testSetUnknownCurrent()
    {
        $server = new TimeSync\TimeSync();
        $this->setExpectedException('Zend\TimeSync\Exception\InvalidArgumentException');
        $server->setServer('unkown_alias');
    }

    /**
     * Test getting the current timeserver when none is set
     *
     * @return void
     */
    public function testGetUnknownCurrent()
    {
        $server = new TimeSync\TimeSync();
        $this->setExpectedException('Zend\TimeSync\Exception\InvalidArgumentException');
        $result = $server->getServer();
    }

    /**
     * Test getting a none existing timeserver
     *
     * @return void
     */
    public function testGetUnknownServer()
    {
        $server = new TimeSync\TimeSync();
        $this->setExpectedException('Zend\TimeSync\Exception\InvalidArgumentException');
        $result = $server->getServer('none_existing_server_alias');
    }

    /**
     * Test getting a date using the fallback mechanism, will try to
     * return the date from the first server that returns a valid result
     *
     * @return void
     */
    public function testGetDate()
    {
        $server = new TimeSync\TimeSync($this->timeservers);

        try {
            $result = $server->getDate();
            $this->assertTrue($result instanceof \Zend\Date\Date);
        } catch (TimeSync\Exception\InvalidArgumentException $e) {
            $this->assertContains('All timeservers are bogus', $e->getMessage());
        }
    }

    /**
     * Test getting a date from an ntp timeserver
     *
     * @return void
     */
    public function testGetNtpDate()
    {
        $server = new TimeSync\TimeSync('ntp://time.windows.com', 'time_windows');

        try {
            $result = $server->getDate();
            $this->assertTrue($result instanceof \Zend\Date\Date);
        } catch (TimeSync\Exception\RuntimeException $e) {
            $this->assertContains('All timeservers are bogus', $e->getMessage());
        }
    }

    /**
     * Test getting a date from an sntp timeserver
     *
     * @return void
     */
    public function testGetSntpDate()
    {
        $server = new TimeSync\TimeSync('sntp://time-C.timefreq.bldrdoc.gov');

        try {
            $result = $server->getDate();
            $this->assertTrue($result instanceof \Zend\Date\Date);
        } catch (TimeSync\Exception\InvalidArgumentException $e) {
            $this->assertContains('All timeservers are bogus', $e->getMessage());
        }
    }

    /**
     * Test getting a date from an invalid timeserver
     *
     * @return void
     */
    public function testGetInvalidDate()
    {
        $servers = array(
            'server_a' => 'dummy-ntp-timeserver.com',
            'server_b' => 'another-dummy-ntp-timeserver.com'
        );

        $server = new TimeSync\TimeSync($servers);

        try {
            $result = $server->getDate();
        } catch (TimeSync\Exception $e) {
            $i = 0;
            while($e = $e->getPrevious()) {
                $i++;
                $this->assertTrue($e instanceof TimeSync\Exception);
            }
            $this->assertEquals(2, $i);
        }
    }

    /**
     * Test walking through the server list
     *
     * @return void
     */
    public function testWalkServers()
    {
        $servers = new TimeSync\TimeSync($this->timeservers);

        foreach ($servers as $key => $server) {
            $this->assertTrue($server instanceof TimeSync\Protocol);
        }
    }

    /**
     * Test getting info returned from the server
     *
     * @return void
     */
    public function testGetInfo()
    {
        $server = new TimeSync\TimeSync('time.windows.com');
        try {
            $date   = $server->getDate();
            $result = $server->getInfo();

            $this->assertTrue(count($result) > 0);
        } catch (TimeSync\Exception  $e) {
            // nothing
        }
    }
}
