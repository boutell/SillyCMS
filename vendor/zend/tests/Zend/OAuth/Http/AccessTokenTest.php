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
 * @package    Zend_OAuth
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace ZendTest\OAuth\Http;

use Zend\OAuth\Http,
    Zend\OAuth;

/**
 * @category   Zend
 * @package    Zend_OAuth
 * @subpackage UnitTests
 * @group      Zend_OAuth
 * @group      disable
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class AccessTokenTest extends \PHPUnit_Framework_TestCase
{

    protected $stubConsumer = null;

    public function setup()
    {
        $this->stubConsumer = new Consumer39745;
        $this->stubHttpUtility = new HTTPUtility39745;
        OAuth\OAuth::setHttpClient(new HTTPClient39745);
    }

    public function teardown()
    {
        OAuth\OAuth::clearHttpClient();
    }

    public function testConstructorSetsConsumerInstance()
    {
        $request = new Http\AccessToken($this->stubConsumer, null, $this->stubHttpUtility);
        $this->assertType('\\ZendTest\\OAuth\\Http\\Consumer39745', $request->getConsumer());
    }

    public function testConstructorSetsCustomServiceParameters()
    {
        $request = new Http\AccessToken($this->stubConsumer, array(1,2,3), $this->stubHttpUtility);
        $this->assertEquals(array(1,2,3), $request->getParameters());
    }

    public function testAssembleParametersCorrectlyAggregatesOauthParameters()
    {
        $request = new Http\AccessToken($this->stubConsumer, null, $this->stubHttpUtility);
        $expectedParams = array (
            'oauth_consumer_key' => '1234567890',
            'oauth_nonce' => 'e807f1fcf82d132f9bb018ca6738a19f',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '12345678901',
            'oauth_token' => '0987654321',
            'oauth_version' => '1.0',
            'oauth_signature' => '6fb42da0e32e07b61c9f0251fe627a9c'
        );
        $this->assertEquals($expectedParams, $request->assembleParams());
    }
    public function testAssembleParametersCorrectlyIgnoresCustomParameters()
    {
        $request = new Http\AccessToken($this->stubConsumer, array(
            'custom_param1'=>'foo',
            'custom_param2'=>'bar'
        ), $this->stubHttpUtility);
        $expectedParams = array (
            'oauth_consumer_key' => '1234567890',
            'oauth_nonce' => 'e807f1fcf82d132f9bb018ca6738a19f',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '12345678901',
            'oauth_token' => '0987654321',
            'oauth_version' => '1.0',
            'custom_param1'=>'foo',
            'custom_param2'=>'bar',
            'oauth_signature' => '6fb42da0e32e07b61c9f0251fe627a9c'
        );
        $this->assertEquals($expectedParams, $request->assembleParams());
    }

    public function testGetRequestSchemeHeaderClientSetsCorrectlyEncodedAuthorizationHeader()
    {
        $request = new Http\AccessToken($this->stubConsumer, null, $this->stubHttpUtility);
        $params = array (
            'oauth_consumer_key' => '1234567890',
            'oauth_nonce' => 'e807f1fcf82d132f9bb018ca6738a19f',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '12345678901',
            'oauth_token' => '0987654321',
            'oauth_version' => '1.0',
            'oauth_signature' => '6fb42da0e32e07b61c9f0251fe627a9c~',
            'custom_param1' => 'foo',
            'custom_param2' => 'bar'
        );
        $client = $request->getRequestSchemeHeaderClient($params);
        $this->assertEquals(
        'OAuth realm="",oauth_consumer_key="1234567890",oauth_nonce="e807f1fcf82d132f9b'
        .'b018ca6738a19f",oauth_signature_method="HMAC-SHA1",oauth_timestamp="'
        .'12345678901",oauth_token="0987654321",oauth_version="1.0",oauth_sign'
        .'ature="6fb42da0e32e07b61c9f0251fe627a9c~"',
            $client->getHeader('Authorization')
        );
    }

    public function testGetRequestSchemePostBodyClientSetsCorrectlyEncodedRawData()
    {
        $request = new Http\AccessToken($this->stubConsumer, null, $this->stubHttpUtility);
        $params = array (
            'oauth_consumer_key' => '1234567890',
            'oauth_nonce' => 'e807f1fcf82d132f9bb018ca6738a19f',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '12345678901',
            'oauth_token' => '0987654321',
            'oauth_version' => '1.0',
            'oauth_signature' => '6fb42da0e32e07b61c9f0251fe627a9c~',
            'custom_param1' => 'foo',
            'custom_param2' => 'bar'
        );
        $client = $request->getRequestSchemePostBodyClient($params);
        $this->assertEquals(
            'oauth_consumer_key=1234567890&oauth_nonce=e807f1fcf82d132f9bb018c'
            .'a6738a19f&oauth_signature_method=HMAC-SHA1&oauth_timestamp=12345'
            .'678901&oauth_token=0987654321&oauth_version=1.0&oauth_signature='
            .'6fb42da0e32e07b61c9f0251fe627a9c~',
            $client->getRawData()
        );
    }

    public function testGetRequestSchemeQueryStringClientSetsCorrectlyEncodedQueryString()
    {
        $request = new Http\AccessToken($this->stubConsumer, null, $this->stubHttpUtility);
        $params = array (
            'oauth_consumer_key' => '1234567890',
            'oauth_nonce' => 'e807f1fcf82d132f9bb018ca6738a19f',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '12345678901',
            'oauth_token' => '0987654321',
            'oauth_version' => '1.0',
            'oauth_signature' => '6fb42da0e32e07b61c9f0251fe627a9c',
            'custom_param1' => 'foo',
            'custom_param2' => 'bar'
        );
        $client = $request->getRequestSchemeQueryStringClient($params, 'http://www.example.com');
        $this->assertEquals(
            'oauth_consumer_key=1234567890&oauth_nonce=e807f1fcf82d132f9bb018c'
            .'a6738a19f&oauth_signature_method=HMAC-SHA1&oauth_timestamp=12345'
            .'678901&oauth_token=0987654321&oauth_version=1.0&oauth_signature='
            .'6fb42da0e32e07b61c9f0251fe627a9c',
            $client->getUri()->getQuery()
        );
    }

}

class Consumer39745 extends OAuth\Consumer
{
    public function getConsumerKey(){return '1234567890';}
    public function getSignatureMethod(){return 'HMAC-SHA1';}
    public function getVersion(){return '1.0';}
    public function getAccessTokenUrl(){return 'http://www.example.com/access';}
    public function getLastRequestToken()
    {
        $return = new RequestToken39745;
        return $return;
    }
}

class HTTPUtility39745 extends OAuth\Http\Utility
{
    public function __construct(){}
    public function generateNonce(){return md5('1234567890');}
    public function generateTimestamp(){return '12345678901';}
    public function sign(array $params, $signatureMethod, $consumerSecret,
        $accessTokenSecret = null, $method = null, $url = null)
    {
        return md5('0987654321');
    }
}

class HTTPClient39745 extends \Zend\Http\Client
{
    public function getRawData(){return $this->raw_post_data;}
}

class RequestToken39745 extends OAuth\Token\Request
{
    public function getToken(){return '0987654321';}
}
