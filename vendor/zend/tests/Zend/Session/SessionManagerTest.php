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
 * @package    Zend_Session
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id:$
 */

namespace ZendTest\Session;

use Zend\Session\SessionManager,
    Zend\Session,
    Zend\Registry;

/**
 * @category   Zend
 * @package    Zend_Session
 * @subpackage UnitTests
 * @group      Zend_Session
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class SessionManagerTest extends \PHPUnit_Framework_TestCase
{
    public $error;

    public $cookieDateFormat = 'D, d-M-y H:i:s e';

    public function setUp()
    {
        $this->forceAutoloader();
        $this->error   = false;
        $this->manager = new SessionManager();
        Registry::_unsetInstance();
    }

    protected function forceAutoloader()
    {
        $splAutoloadFunctions = spl_autoload_functions();
        if (!$splAutoloadFunctions || !in_array('ZendTest_Autoloader', $splAutoloadFunctions)) {
            include __DIR__ . '/../../_autoload.php';
        }
    }

    /**
     * Hack to allow running tests in separate processes
     *
     * @see    http://matthewturland.com/2010/08/19/process-isolation-in-phpunit/
     * @param  PHPUnit_Framework_TestResult $result 
     * @return void
     */
    public function run(\PHPUnit_Framework_TestResult $result = NULL)
    {
        $this->setPreserveGlobalState(false);
        return parent::run($result);
    }

    public function handleErrors($errno, $errstr)
    {
        $this->error = $errstr;
    }

    public function getTimestampFromCookie($cookie)
    {
        if (preg_match('/expires=([^;]+)/', $cookie, $matches)) {
            $ts = new \DateTime($matches[1]);
            return $ts;
        }
        return false;
    }

    public function testManagerUsesSessionConfigurationByDefault()
    {
        $config = $this->manager->getConfig();
        $this->assertTrue($config instanceof Session\Configuration\SessionConfiguration);
    }

    public function testCanPassConfigurationToConstructor()
    {
        $config = new Session\Configuration\StandardConfiguration();
        $manager = new SessionManager($config);
        $this->assertSame($config, $manager->getConfig());
    }

    public function testPassingUnknownStringClassForConfigurationRaisesException()
    {
        $this->setExpectedException('Zend\Session\Exception\InvalidArgumentException', 'Configuration class provided is invalid; not found');
        $manager = new SessionManager('foobarbazbat');
    }

    public function testPassingInvalidStringClassForConfigurationRaisesException()
    {
        $this->setExpectedException('Zend\Session\Exception\InvalidArgumentException', 'Configuration type provided is invalid; must implement Zend\Session\Configuration');
        $manager = new SessionManager('Zend\Session\Storage\ArrayStorage');
    }

    public function testPassingValidStringClassForConfigurationInstantiatesThatConfiguration()
    {
        $manager = new SessionManager('Zend\\Session\\Configuration\\StandardConfiguration');
        $config = $manager->getConfig();
        $this->assertTrue($config instanceof Session\Configuration\StandardConfiguration);
    }

    public function testPassingValidStringClassInClassKeyOfArrayConfigurationInstantiatesThatConfiguration()
    {
        $manager = new SessionManager(array('class' => 'Zend\\Session\\Configuration\\StandardConfiguration'));
        $config = $manager->getConfig();
        $this->assertTrue($config instanceof Session\Configuration\StandardConfiguration);
    }

    public function testPassingInvalidStringClassInClassKeyOfArrayConfigurationRaisesException()
    {
        $this->setExpectedException('Zend\Session\Exception\InvalidArgumentException', 'Class provided for configuration is invalid; not found');
        $manager = new SessionManager(array('class' => 'foobarbaz'));
    }

    public function testPassingValidStringClassInClassKeyOfArrayConfigurationInstantiatesThatConfigurationWithOptionsProvided()
    {
        $manager = new SessionManager(array(
            'class'     => 'Zend\\Session\\Configuration\\StandardConfiguration',
            'save_path' => __DIR__,
        ));
        $config = $manager->getConfig();
        $this->assertTrue($config instanceof Session\Configuration\StandardConfiguration);
        $this->assertEquals(__DIR__, $config->getSavePath());
    }

    public function testPassingZendConfigObjectForConfigurationInstantiatesThatConfiguration()
    {
        $config = new \Zend\Config\Config(array(
            'class'     => 'Zend\\Session\\Configuration\\StandardConfiguration',
            'save_path' => __DIR__,
        ));
        $manager = new SessionManager($config);
        $config = $manager->getConfig();
        $this->assertTrue($config instanceof Session\Configuration\StandardConfiguration);
        $this->assertEquals(__DIR__, $config->getSavePath());
    }

    public function testManagerUsesSessionStorageByDefault()
    {
        $storage = $this->manager->getStorage();
        $this->assertTrue($storage instanceof Session\Storage\SessionStorage);
    }

    public function testCanPassStorageToConstructor()
    {
        $storage = new Session\Storage\ArrayStorage();
        $manager = new SessionManager(null, $storage);
        $this->assertSame($storage, $manager->getStorage());
    }

    public function testCanPassStringStorageNameToConstructor()
    {
        $manager = new SessionManager(null, 'Zend\\Session\\Storage\\ArrayStorage');
        $storage = $manager->getStorage();
        $this->assertTrue($storage instanceof Session\Storage\ArrayStorage);
    }

    public function testCanPassStorageClassToConfigurationOptions()
    {
        $manager = new SessionManager(array('storage' => 'Zend\\Session\\Storage\\ArrayStorage'));
        $storage = $manager->getStorage();
        $this->assertTrue($storage instanceof Session\Storage\ArrayStorage);
    }

    public function testPassingStorageViaParamOverridesStorageInConfig()
    {
        $storage = new Session\Storage\ArrayStorage();
        $manager = new TestAsset\TestManager(array(
            'class'   => 'Zend\\Session\\Configuration\\StandardConfiguration',
            'storage' => 'Zend\\Session\\Storage\\SessionStorage',
        ), $storage);
        $this->assertSame($storage, $manager->getStorage());
    }

    // Session-related functionality

    /**
     * @runInSeparateProcess
     */
    public function testSessionExistsReturnsFalseWhenNoSessionStarted()
    {
        $this->assertFalse($this->manager->sessionExists());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSessionExistsReturnsTrueWhenSessionStarted()
    {
        session_start();
        $this->assertTrue($this->manager->sessionExists());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSessionExistsReturnsTrueWhenSessionStartedThenWritten()
    {
        session_start();
        session_write_close();
        $this->assertTrue($this->manager->sessionExists());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSessionExistsReturnsFalseWhenSessionStartedThenDestroyed()
    {
        session_start();
        session_destroy();
        $this->assertFalse($this->manager->sessionExists());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSessionIsStartedAfterCallingStart()
    {
        $this->assertFalse($this->manager->sessionExists());
        $this->manager->start();
        $this->assertTrue($this->manager->sessionExists());
    }

    /**
     * @runInSeparateProcess
     */
    public function testStartDoesNothingWhenCalledAfterWriteCloseOperation()
    {
        $this->manager->start();
        $id1 = session_id();
        session_write_close();
        $this->manager->start();
        $id2 = session_id();
        $this->assertTrue($this->manager->sessionExists());
        $this->assertEquals($id1, $id2);
    }

    /**
     * @runInSeparateProcess
     */
    public function testStartCreatesNewSessionIfPreviousSessionHasBeenDestroyed()
    {
        $this->manager->start();
        $id1 = session_id();
        session_destroy();
        $this->manager->start();
        $id2 = session_id();
        $this->assertTrue($this->manager->sessionExists());
        $this->assertNotEquals($id1, $id2);
    }

    /**
     * @outputBuffering disabled
     */
    public function testStartWillNotBlockHeaderSentNotices()
    {
        if ('cli' == PHP_SAPI) {
            $this->markTestSkipped('session_start() will not raise headers_sent warnings in CLI');
        }
        set_error_handler(array($this, 'handleErrors'), E_WARNING);
        echo ' ';
        $this->assertTrue(headers_sent());
        $this->manager->start();
        restore_error_handler();
        $this->assertTrue(is_string($this->error));
        $this->assertContains('already sent', $this->error);
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetNameReturnsSessionName()
    {
        $ini = ini_get('session.name');
        $this->assertEquals($ini, $this->manager->getName());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSetNameRaisesExceptionOnInvalidName()
    {
        $this->setExpectedException('Zend\Session\Exception\InvalidArgumentException', 'Name provided contains invalid characters; must be alphanumeric only');
        $this->manager->setName('foo bar!');
    }

    /**
     * @runInSeparateProcess
     */
    public function testSetNameSetsSessionNameOnSuccess()
    {
        $this->manager->setName('foobar');
        $this->assertEquals('foobar', $this->manager->getName());
        $this->assertEquals('foobar', session_name());
    }

    /**
     * @runInSeparateProcess
     */
    public function testCanSetNewSessionNameAfterSessionDestroyed()
    {
        $this->manager->start();
        session_destroy();
        $this->manager->setName('foobar');
        $this->assertEquals('foobar', $this->manager->getName());
        $this->assertEquals('foobar', session_name());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSettingNameWhenAnActiveSessionExistsRaisesException()
    {
        $this->setExpectedException('Zend\Session\Exception\InvalidArgumentException', 'Cannot set session name after a session has already started');
        $this->manager->start();
        $this->manager->setName('foobar');
    }

    /**
     * @runInSeparateProcess
     */
    public function testDestroyByDefaultSendsAnExpireCookie()
    {
        if (!extension_loaded('xdebug')) {
            $this->markTestSkipped('Xdebug required for this test');
        }
        
        $config = $this->manager->getConfig();
        $config->setUseCookies(true);
        $this->manager->start();
        $this->manager->destroy();
        echo '';
        $headers = xdebug_get_headers();
        $found  = false;
        $sName  = $this->manager->getName();
        foreach ($headers as $header) {
            if (stristr($header, 'Set-Cookie:') && stristr($header, $sName)) {
                $found  = true;
            }
        }
        $this->assertTrue($found, 'No session cookie found: ' . var_export($headers, true));
    }

    /**
     * @runInSeparateProcess
     */
    public function testSendingFalseToSendExpireCookieWhenCallingDestroyShouldNotSendCookie()
    {
        if (!extension_loaded('xdebug')) {
            $this->markTestSkipped('Xdebug required for this test');
        }
        
        $config = $this->manager->getConfig();
        $config->setUseCookies(true);
        $this->manager->start();
        $this->manager->destroy(array('send_expire_cookie' => false));
        echo '';
        $headers = xdebug_get_headers();
        $found  = false;
        $sName  = $this->manager->getName();
        foreach ($headers as $header) {
            if (stristr($header, 'Set-Cookie:') && stristr($header, $sName)) {
                $found  = true;
            }
        }
        if ($found) {
            $this->assertNotContains('expires=', $header);
        } else {
            $this->assertFalse($found, 'Unexpected session cookie found: ' . var_export($headers, true));
        }
    }

    /**
     * @runInSeparateProcess
     */
    public function testDestroyDoesNotClearSessionStorageByDefault()
    {
        $this->manager->start();
        $storage = $this->manager->getStorage();
        $storage['foo'] = 'bar';
        $this->manager->destroy();
        $this->manager->start();
        $this->assertEquals('bar', $storage['foo']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testPassingClearStorageOptionWhenCallingDestroyClearsStorage()
    {
        $this->manager->start();
        $storage = $this->manager->getStorage();
        $storage['foo'] = 'bar';
        $this->manager->destroy(array('clear_storage' => true));
        $this->assertSame(array(), (array) $storage);
    }

    /**
     * @runInSeparateProcess
     */
    public function testCallingWriteCloseMarksStorageAsImmutable()
    {
        $this->manager->start();
        $storage = $this->manager->getStorage();
        $storage['foo'] = 'bar';
        $this->manager->writeClose();
        $this->assertTrue($storage->isImmutable());
    }

    /**
     * @runInSeparateProcess
     */
    public function testCallingWriteCloseShouldNotAlterSessionExistsStatus()
    {
        $this->manager->start();
        $this->manager->writeClose();
        $this->assertTrue($this->manager->sessionExists());
    }

    /**
     * @runInSeparateProcess
     */
    public function testIdShouldBeEmptyPriorToCallingStart()
    {
        $this->assertSame('', $this->manager->getId());
    }

    /**
     * @runInSeparateProcess
     */
    public function testIdShouldBeMutablePriorToCallingStart()
    {
        $this->manager->setId(__CLASS__);
        $this->assertSame(__CLASS__, $this->manager->getId());
        $this->assertSame(__CLASS__, session_id());
    }

    /**
     * @runInSeparateProcess
     */
    public function testIdShouldBeMutablePriorAfterSessionStarted()
    {
        $this->manager->start();
        $origId = $this->manager->getId();
        $this->manager->setId(__METHOD__);
        $this->assertNotSame($origId, $this->manager->getId());
        $this->assertSame(__METHOD__, $this->manager->getId());
        $this->assertSame(__METHOD__, session_id());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSettingIdAfterSessionStartedShouldSendExpireCookie()
    {
        if (!extension_loaded('xdebug')) {
            $this->markTestSkipped('Xdebug required for this test');
        }
        
        $config = $this->manager->getConfig();
        $config->setUseCookies(true);
        $this->manager->start();
        $origId = $this->manager->getId();
        $this->manager->setId(__METHOD__);
        $headers = xdebug_get_headers();
        $found  = false;
        $sName  = $this->manager->getName();
        foreach ($headers as $header) {
            if (stristr($header, 'Set-Cookie:') && stristr($header, $sName)) {
                $found  = true;
            }
        }
        $this->assertTrue($found, 'No session cookie found: ' . var_export($headers, true));
    }

    /**
     * @runInSeparateProcess
     */
    public function testRegenerateIdShouldWorkAfterSessionStarted()
    {
        $this->manager->start();
        $origId = $this->manager->getId();
        $this->manager->regenerateId();
        $this->assertNotSame($origId, $this->manager->getId());
    }

    /**
     * @runInSeparateProcess
     */
    public function testRegeneratingIdAfterSessionStartedShouldSendExpireCookie()
    {
        if (!extension_loaded('xdebug')) {
            $this->markTestSkipped('Xdebug required for this test');
        }
        
        $config = $this->manager->getConfig();
        $config->setUseCookies(true);
        $this->manager->start();
        $origId = $this->manager->getId();
        $this->manager->regenerateId();
        $headers = xdebug_get_headers();
        $found  = false;
        $sName  = $this->manager->getName();
        foreach ($headers as $header) {
            if (stristr($header, 'Set-Cookie:') && stristr($header, $sName)) {
                $found  = true;
            }
        }
        $this->assertTrue($found, 'No session cookie found: ' . var_export($headers, true));
    }

    /**
     * @runInSeparateProcess
     */
    public function testRememberMeShouldSendNewSessionCookieWithUpdatedTimestamp()
    {
        if (!extension_loaded('xdebug')) {
            $this->markTestSkipped('Xdebug required for this test');
        }
        
        $config = $this->manager->getConfig();
        $config->setUseCookies(true);
        $this->manager->start();
        $this->manager->rememberMe(18600);
        $headers = xdebug_get_headers();
        $found   = false;
        $sName   = $this->manager->getName();
        $cookie  = false;
        foreach ($headers as $header) {
            if (stristr($header, 'Set-Cookie:') && stristr($header, $sName) && !stristr($header, '=deleted')) {
                $found  = true;
                $cookie = $header;
            }
        }
        $this->assertTrue($found, 'No session cookie found: ' . var_export($headers, true));
        $ts = $this->getTimestampFromCookie($cookie);
        if (!$ts) {
            $this->fail('Cookie did not contain expiry? ' . var_export($headers, true));
        }
        $this->assertGreaterThan($_SERVER['REQUEST_TIME'], $ts->getTimestamp(), 'Session cookie: ' . var_export($headers, 1));
    }

    /**
     * @runInSeparateProcess
     */
    public function testRememberMeShouldSetTimestampBasedOnConfigurationByDefault()
    {
        if (!extension_loaded('xdebug')) {
            $this->markTestSkipped('Xdebug required for this test');
        }
        
        $config = $this->manager->getConfig();
        $config->setUseCookies(true);
        $config->setRememberMeSeconds(3600);
        $ttl = $config->getRememberMeSeconds();
        $this->manager->start();
        $this->manager->rememberMe();
        $headers = xdebug_get_headers();
        $found  = false;
        $sName  = $this->manager->getName();
        $cookie = false;
        foreach ($headers as $header) {
            if (stristr($header, 'Set-Cookie:') && stristr($header, $sName) && !stristr($header, '=deleted')) {
                $found  = true;
                $cookie = $header;
            }
        }
        $this->assertTrue($found, 'No session cookie found: ' . var_export($headers, true));
        $ts = $this->getTimestampFromCookie($cookie);
        if (!$ts) {
            $this->fail('Cookie did not contain expiry? ' . var_export($headers, true));
        }
        $compare = $_SERVER['REQUEST_TIME'] + $ttl;
        $cookieTs = $ts->getTimestamp();
        $this->assertTrue(in_array($cookieTs, range($compare, $compare + 10)), 'Session cookie: ' . var_export($headers, 1));
    }

    /**
     * @runInSeparateProcess
     */
    public function testForgetMeShouldSendCookieWithZeroTimestamp()
    {
        if (!extension_loaded('xdebug')) {
            $this->markTestSkipped('Xdebug required for this test');
        }
        
        $config = $this->manager->getConfig();
        $config->setUseCookies(true);
        $this->manager->start();
        $this->manager->forgetMe();
        $headers = xdebug_get_headers();
        $found  = false;
        $sName  = $this->manager->getName();
        foreach ($headers as $header) {
            if (stristr($header, 'Set-Cookie:') && stristr($header, $sName) && !stristr($header, '=deleted')) {
                $found  = true;
            }
        }
        $this->assertTrue($found, 'No session cookie found: ' . var_export($headers, true));
        $this->assertNotContains('expires=', $header);
    }

    /**
     * @runInSeparateProcess
     */
    public function testStartingSessionThatFailsAValidatorShouldRaiseException()
    {
        $chain = $this->manager->getValidatorChain();
        $chain->connect('session.validate', function() {
             return false;
        });
        $this->setExpectedException('Zend\Session\Exception\InvalidArgumentException', 'xxx');
        $this->manager->start();
    }
}
