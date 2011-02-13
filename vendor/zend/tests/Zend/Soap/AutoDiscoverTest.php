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
 * @package    Zend_Soap
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace ZendTest\Soap;

/** Include Common TestTypes */
require_once 'TestAsset/commontypes.php';

use Zend\Soap\AutoDiscover,
    Zend\Soap\AutoDiscoverException;

/** PHPUnit Test Case */

/**
 * Test cases for Zend_Soap_AutoDiscover
 *
 * @category   Zend
 * @package    Zend_Soap
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Soap
 */
class AutoDiscoverTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // This has to be done because some CLI setups don't have $_SERVER variables
        // to simuulate that we have an actual webserver.
        if(!isset($_SERVER) || !is_array($_SERVER)) {
            $_SERVER = array();
        }
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REQUEST_URI'] = '/my_script.php?wsdl';
        $_SERVER['SCRIPT_NAME'] = '/my_script.php';
        $_SERVER['HTTPS'] = "off";
    }

    protected function sanitizeWsdlXmlOutputForOsCompability($xmlstring)
    {
        $xmlstring = str_replace(array("\r", "\n"), "", $xmlstring);
        $xmlstring = preg_replace('/(>[\s]{1,}<)/', '', $xmlstring);
        return $xmlstring;
    }

    function testSetClass()
    {
        $scriptUri = 'http://localhost/my_script.php';

        $server = new AutoDiscover();
        $server->setClass('\ZendTest\Soap\TestAsset\Test');
        $dom = new \DOMDocument();
        ob_start();
        $server->handle();
        $dom->loadXML(ob_get_clean());

        $wsdl = '<?xml version="1.0"?>'
              . '<definitions xmlns="http://schemas.xmlsoap.org/wsdl/" '
              .              'xmlns:tns="' . $scriptUri . '" '
              .              'xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" '
              .              'xmlns:xsd="http://www.w3.org/2001/XMLSchema" '
              .              'xmlns:soap-enc="http://schemas.xmlsoap.org/soap/encoding/" '
              .              'xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" '
              .              'name="ZendTest.Soap.TestAsset.Test" '
              .              'targetNamespace="' . $scriptUri . '">'
              .     '<types>'
              .         '<xsd:schema targetNamespace="' . $scriptUri . '"/>'
              .     '</types>'
              .     '<portType name="ZendTest.Soap.TestAsset.TestPort">'
              .         '<operation name="testFunc1">'
              .             '<documentation>Test Function 1</documentation>'
              .             '<input message="tns:testFunc1In"/>'
              .             '<output message="tns:testFunc1Out"/>'
              .         '</operation>'
              .         '<operation name="testFunc2">'
              .             '<documentation>Test Function 2</documentation>'
              .             '<input message="tns:testFunc2In"/>'
              .             '<output message="tns:testFunc2Out"/>'
              .         '</operation>'
              .         '<operation name="testFunc3">'
              .             '<documentation>Test Function 3</documentation>'
              .             '<input message="tns:testFunc3In"/>'
              .             '<output message="tns:testFunc3Out"/>'
              .         '</operation><operation name="testFunc4">'
              .             '<documentation>Test Function 4</documentation>'
              .             '<input message="tns:testFunc4In"/>'
              .             '<output message="tns:testFunc4Out"/>'
              .         '</operation>'
              .     '</portType>'
              .     '<binding name="ZendTest.Soap.TestAsset.TestBinding" type="tns:ZendTest.Soap.TestAsset.TestPort">'
              .         '<soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http"/>'
              .         '<operation name="testFunc1">'
              .             '<soap:operation soapAction="' . $scriptUri . '#testFunc1"/>'
              .             '<input><soap:body use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="' . $scriptUri . '"/></input>'
              .             '<output><soap:body use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="' . $scriptUri . '"/></output>'
              .         '</operation>'
              .         '<operation name="testFunc2">'
              .             '<soap:operation soapAction="' . $scriptUri . '#testFunc2"/>'
              .             '<input><soap:body use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="' . $scriptUri . '"/></input>'
              .             '<output><soap:body use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="' . $scriptUri . '"/></output>'
              .         '</operation>'
              .         '<operation name="testFunc3">'
              .             '<soap:operation soapAction="' . $scriptUri . '#testFunc3"/>'
              .             '<input><soap:body use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="' . $scriptUri . '"/></input>'
              .             '<output><soap:body use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="' . $scriptUri . '"/></output>'
              .         '</operation>'
              .         '<operation name="testFunc4">'
              .             '<soap:operation soapAction="' . $scriptUri . '#testFunc4"/>'
              .             '<input><soap:body use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="' . $scriptUri . '"/></input>'
              .             '<output><soap:body use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="' . $scriptUri . '"/></output>'
              .         '</operation>'
              .     '</binding>'
              .     '<service name="ZendTest.Soap.TestAsset.TestService">'
              .         '<port name="ZendTest.Soap.TestAsset.TestPort" binding="tns:ZendTest.Soap.TestAsset.TestBinding">'
              .             '<soap:address location="' . $scriptUri . '"/>'
              .         '</port>'
              .     '</service>'
              .     '<message name="testFunc1In"/>'
              .     '<message name="testFunc1Out"><part name="return" type="xsd:string"/></message>'
              .     '<message name="testFunc2In"><part name="who" type="xsd:string"/></message>'
              .     '<message name="testFunc2Out"><part name="return" type="xsd:string"/></message>'
              .     '<message name="testFunc3In"><part name="who" type="xsd:string"/><part name="when" type="xsd:int"/></message>'
              .     '<message name="testFunc3Out"><part name="return" type="xsd:string"/></message>'
              .     '<message name="testFunc4In"/>'
              .     '<message name="testFunc4Out"><part name="return" type="xsd:string"/></message>'
              . '</definitions>';

        $dom->save(__DIR__.'/TestAsset/setclass.wsdl');
        $this->assertEquals($wsdl, $this->sanitizeWsdlXmlOutputForOsCompability($dom->saveXML()));
        $this->assertTrue($dom->schemaValidate(__DIR__ .'/schemas/wsdl.xsd'), "WSDL Did not validate");

        unlink(__DIR__.'/TestAsset/setclass.wsdl');
    }

    function testSetClassWithDifferentStyles()
    {
        $scriptUri = 'http://localhost/my_script.php';

        $server = new AutoDiscover();
        $server->setBindingStyle(array('style' => 'document', 'transport' => 'http://framework.zend.com'));
        $server->setOperationBodyStyle(array('use' => 'literal', 'namespace' => 'http://framework.zend.com'));
        $server->setClass('\ZendTest\Soap\TestAsset\Test');
        $dom = new \DOMDocument();
        ob_start();
        $server->handle();
        $dom->loadXML(ob_get_clean());

        $wsdl = '<?xml version="1.0"?>'
              . '<definitions xmlns="http://schemas.xmlsoap.org/wsdl/" '
              .              'xmlns:tns="' . $scriptUri . '" '
              .              'xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" '
              .              'xmlns:xsd="http://www.w3.org/2001/XMLSchema" '
              .              'xmlns:soap-enc="http://schemas.xmlsoap.org/soap/encoding/" '
              .              'xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" '
              .              'name="ZendTest.Soap.TestAsset.Test" '
              .              'targetNamespace="' . $scriptUri . '">'
              .     '<types>'
              .         '<xsd:schema targetNamespace="' . $scriptUri . '">'
              .           '<xsd:element name="testFunc1">'
              .             '<xsd:complexType/>'
              .           '</xsd:element>'
              .           '<xsd:element name="testFunc1Response">'
              .             '<xsd:complexType>'
              .               '<xsd:sequence>'
              .                 '<xsd:element name="testFunc1Result" type="xsd:string"/>'
              .               '</xsd:sequence>'
              .             '</xsd:complexType>'
              .           '</xsd:element>'
              .           '<xsd:element name="testFunc2">'
              .             '<xsd:complexType>'
              .               '<xsd:sequence>'
              .                 '<xsd:element name="who" type="xsd:string"/>'
              .               '</xsd:sequence>'
              .             '</xsd:complexType>'
              .           '</xsd:element>'
              .           '<xsd:element name="testFunc2Response">'
              .             '<xsd:complexType>'
              .               '<xsd:sequence>'
              .                 '<xsd:element name="testFunc2Result" type="xsd:string"/>'
              .               '</xsd:sequence>'
              .             '</xsd:complexType>'
              .           '</xsd:element>'
              .           '<xsd:element name="testFunc3">'
              .             '<xsd:complexType>'
              .               '<xsd:sequence>'
              .                 '<xsd:element name="who" type="xsd:string"/>'
              .                 '<xsd:element name="when" type="xsd:int"/>'
              .               '</xsd:sequence>'
              .             '</xsd:complexType>'
              .           '</xsd:element>'
              .           '<xsd:element name="testFunc3Response">'
              .             '<xsd:complexType>'
              .               '<xsd:sequence>'
              .                 '<xsd:element name="testFunc3Result" type="xsd:string"/>'
              .               '</xsd:sequence>'
              .             '</xsd:complexType>'
              .           '</xsd:element>'
              .           '<xsd:element name="testFunc4">'
              .             '<xsd:complexType/>'
              .           '</xsd:element>'
              .           '<xsd:element name="testFunc4Response">'
              .             '<xsd:complexType>'
              .               '<xsd:sequence>'
              .                 '<xsd:element name="testFunc4Result" type="xsd:string"/>'
              .               '</xsd:sequence>'
              .             '</xsd:complexType>'
              .           '</xsd:element>'
              .         '</xsd:schema>'
              .     '</types>'
              .     '<portType name="ZendTest.Soap.TestAsset.TestPort">'
              .         '<operation name="testFunc1">'
              .             '<documentation>Test Function 1</documentation>'
              .             '<input message="tns:testFunc1In"/>'
              .             '<output message="tns:testFunc1Out"/>'
              .         '</operation>'
              .         '<operation name="testFunc2">'
              .             '<documentation>Test Function 2</documentation>'
              .             '<input message="tns:testFunc2In"/>'
              .             '<output message="tns:testFunc2Out"/>'
              .         '</operation>'
              .         '<operation name="testFunc3">'
              .             '<documentation>Test Function 3</documentation>'
              .             '<input message="tns:testFunc3In"/>'
              .             '<output message="tns:testFunc3Out"/>'
              .         '</operation><operation name="testFunc4">'
              .             '<documentation>Test Function 4</documentation>'
              .             '<input message="tns:testFunc4In"/>'
              .             '<output message="tns:testFunc4Out"/>'
              .         '</operation>'
              .     '</portType>'
              .     '<binding name="ZendTest.Soap.TestAsset.TestBinding" type="tns:ZendTest.Soap.TestAsset.TestPort">'
              .         '<soap:binding style="document" transport="http://framework.zend.com"/>'
              .         '<operation name="testFunc1">'
              .             '<soap:operation soapAction="' . $scriptUri . '#testFunc1"/>'
              .             '<input><soap:body use="literal" namespace="http://framework.zend.com"/></input>'
              .             '<output><soap:body use="literal" namespace="http://framework.zend.com"/></output>'
              .         '</operation>'
              .         '<operation name="testFunc2">'
              .             '<soap:operation soapAction="' . $scriptUri . '#testFunc2"/>'
              .             '<input><soap:body use="literal" namespace="http://framework.zend.com"/></input>'
              .             '<output><soap:body use="literal" namespace="http://framework.zend.com"/></output>'
              .         '</operation>'
              .         '<operation name="testFunc3">'
              .             '<soap:operation soapAction="' . $scriptUri . '#testFunc3"/>'
              .             '<input><soap:body use="literal" namespace="http://framework.zend.com"/></input>'
              .             '<output><soap:body use="literal" namespace="http://framework.zend.com"/></output>'
              .         '</operation>'
              .         '<operation name="testFunc4">'
              .             '<soap:operation soapAction="' . $scriptUri . '#testFunc4"/>'
              .             '<input><soap:body use="literal" namespace="http://framework.zend.com"/></input>'
              .             '<output><soap:body use="literal" namespace="http://framework.zend.com"/></output>'
              .         '</operation>'
              .     '</binding>'
              .     '<service name="ZendTest.Soap.TestAsset.TestService">'
              .         '<port name="ZendTest.Soap.TestAsset.TestPort" binding="tns:ZendTest.Soap.TestAsset.TestBinding">'
              .             '<soap:address location="' . $scriptUri . '"/>'
              .         '</port>'
              .     '</service>'
              .     '<message name="testFunc1In">'
              .       '<part name="parameters" element="tns:testFunc1"/>'
              .     '</message>'
              .     '<message name="testFunc1Out">'
              .       '<part name="parameters" element="tns:testFunc1Response"/>'
              .     '</message>'
              .     '<message name="testFunc2In">'
              .       '<part name="parameters" element="tns:testFunc2"/>'
              .     '</message>'
              .     '<message name="testFunc2Out">'
              .       '<part name="parameters" element="tns:testFunc2Response"/>'
              .     '</message>'
              .     '<message name="testFunc3In">'
              .       '<part name="parameters" element="tns:testFunc3"/>'
              .     '</message>'
              .     '<message name="testFunc3Out">'
              .       '<part name="parameters" element="tns:testFunc3Response"/>'
              .     '</message>'
              .     '<message name="testFunc4In">'
              .       '<part name="parameters" element="tns:testFunc4"/>'
              .     '</message>'
              .     '<message name="testFunc4Out">'
              .       '<part name="parameters" element="tns:testFunc4Response"/>'
              .     '</message>'
              . '</definitions>';

        $dom->save(__DIR__.'/TestAsset/setclass.wsdl');
        $this->assertEquals($wsdl, $this->sanitizeWsdlXmlOutputForOsCompability($dom->saveXML()));
        $this->assertTrue($dom->schemaValidate(__DIR__ .'/schemas/wsdl.xsd'), "WSDL Did not validate");

        unlink(__DIR__.'/TestAsset/setclass.wsdl');
    }

    /**
     * @group ZF-5072
     */
    function testSetClassWithResponseReturnPartCompabilityMode()
    {
        $scriptUri = 'http://localhost/my_script.php';

        $server = new AutoDiscover();
        $server->setClass('\ZendTest\Soap\TestAsset\Test');
        $dom = new \DOMDocument();
        ob_start();
        $server->handle();
        $dom->loadXML(ob_get_clean());

        $dom->save(__DIR__.'/TestAsset/setclass.wsdl');
        $this->assertContains('<message name="testFunc1Out"><part name="return"', $this->sanitizeWsdlXmlOutputForOsCompability($dom->saveXML()));
        $this->assertContains('<message name="testFunc2Out"><part name="return"', $this->sanitizeWsdlXmlOutputForOsCompability($dom->saveXML()));
        $this->assertContains('<message name="testFunc3Out"><part name="return"', $this->sanitizeWsdlXmlOutputForOsCompability($dom->saveXML()));
        $this->assertContains('<message name="testFunc4Out"><part name="return"', $this->sanitizeWsdlXmlOutputForOsCompability($dom->saveXML()));

        unlink(__DIR__.'/TestAsset/setclass.wsdl');
    }


    function testAddFunctionSimple()
    {
        $scriptUri = 'http://localhost/my_script.php';

        $server = new AutoDiscover();
        $server->addFunction('\ZendTest\Soap\TestAsset\TestFunc');
        $dom = new \DOMDocument();
        ob_start();
        $server->handle();
        $dom->loadXML(ob_get_clean());
        $dom->save(__DIR__.'/TestAsset/addfunction.wsdl');

        $parts = explode('.', basename($_SERVER['SCRIPT_NAME']));
        $name = $parts[0];

        $wsdl = '<?xml version="1.0"?>'.
                '<definitions xmlns="http://schemas.xmlsoap.org/wsdl/" xmlns:tns="' . $scriptUri . '" xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap-enc="http://schemas.xmlsoap.org/soap/encoding/" xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" name="' .$name. '" targetNamespace="' . $scriptUri . '">'.
                '<types><xsd:schema targetNamespace="' . $scriptUri . '"/></types>'.
                '<portType name="' .$name. 'Port">'.
                '<operation name="ZendTest.Soap.TestAsset.TestFunc"><documentation>Test Function</documentation><input message="tns:ZendTest.Soap.TestAsset.TestFuncIn"/><output message="tns:ZendTest.Soap.TestAsset.TestFuncOut"/></operation>'.
                '</portType>'.
                '<binding name="' .$name. 'Binding" type="tns:' .$name. 'Port">'.
                '<soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http"/>'.
                '<operation name="ZendTest.Soap.TestAsset.TestFunc">'.
                '<soap:operation soapAction="' . $scriptUri . '#ZendTest.Soap.TestAsset.TestFunc"/>'.
                '<input><soap:body use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="http://localhost/my_script.php"/></input>'.
                '<output><soap:body use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="http://localhost/my_script.php"/></output>'.
                '</operation>'.
                '</binding>'.
                '<service name="' .$name. 'Service">'.
                '<port name="' .$name. 'Port" binding="tns:' .$name. 'Binding">'.
                '<soap:address location="' . $scriptUri . '"/>'.
                '</port>'.
                '</service>'.
                '<message name="ZendTest.Soap.TestAsset.TestFuncIn"><part name="who" type="xsd:string"/></message>'.
                '<message name="ZendTest.Soap.TestAsset.TestFuncOut"><part name="return" type="xsd:string"/></message>'.
                '</definitions>';
        $this->assertEquals($wsdl, $this->sanitizeWsdlXmlOutputForOsCompability($dom->saveXML()), "Bad WSDL generated");
        $this->assertTrue($dom->schemaValidate(__DIR__ .'/schemas/wsdl.xsd'), "WSDL Did not validate");

        unlink(__DIR__.'/TestAsset/addfunction.wsdl');
    }

    function testAddFunctionSimpleWithDifferentStyle()
    {
        $scriptUri = 'http://localhost/my_script.php';

        $server = new AutoDiscover();
        $server->setBindingStyle(array('style' => 'document', 'transport' => 'http://framework.zend.com'));
        $server->setOperationBodyStyle(array('use' => 'literal', 'namespace' => 'http://framework.zend.com'));
        $server->addFunction('\ZendTest\Soap\TestAsset\TestFunc');
        $dom = new \DOMDocument();
        ob_start();
        $server->handle();
        $dom->loadXML(ob_get_clean());
        $dom->save(__DIR__.'/TestAsset/addfunction.wsdl');

        $parts = explode('.', basename($_SERVER['SCRIPT_NAME']));
        $name = $parts[0];

        $wsdl = '<?xml version="1.0"?>'.
                '<definitions xmlns="http://schemas.xmlsoap.org/wsdl/" xmlns:tns="' . $scriptUri . '" xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap-enc="http://schemas.xmlsoap.org/soap/encoding/" xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" name="' .$name. '" targetNamespace="' . $scriptUri . '">'.
                '<types>'.
                '<xsd:schema targetNamespace="' . $scriptUri . '">'.
                '<xsd:element name="ZendTest.Soap.TestAsset.TestFunc"><xsd:complexType><xsd:sequence><xsd:element name="who" type="xsd:string"/></xsd:sequence></xsd:complexType></xsd:element>'.
                '<xsd:element name="ZendTest.Soap.TestAsset.TestFuncResponse"><xsd:complexType><xsd:sequence><xsd:element name="ZendTest.Soap.TestAsset.TestFuncResult" type="xsd:string"/></xsd:sequence></xsd:complexType></xsd:element>'.
                '</xsd:schema>'.
                '</types>'.
                '<portType name="' .$name. 'Port">'.
                '<operation name="ZendTest.Soap.TestAsset.TestFunc"><documentation>Test Function</documentation><input message="tns:ZendTest.Soap.TestAsset.TestFuncIn"/><output message="tns:ZendTest.Soap.TestAsset.TestFuncOut"/></operation>'.
                '</portType>'.
                '<binding name="' .$name. 'Binding" type="tns:' .$name. 'Port">'.
                '<soap:binding style="document" transport="http://framework.zend.com"/>'.
                '<operation name="ZendTest.Soap.TestAsset.TestFunc">'.
                '<soap:operation soapAction="' . $scriptUri . '#ZendTest.Soap.TestAsset.TestFunc"/>'.
                '<input><soap:body use="literal" namespace="http://framework.zend.com"/></input>'.
                '<output><soap:body use="literal" namespace="http://framework.zend.com"/></output>'.
                '</operation>'.
                '</binding>'.
                '<service name="' .$name. 'Service">'.
                '<port name="' .$name. 'Port" binding="tns:' .$name. 'Binding">'.
                '<soap:address location="' . $scriptUri . '"/>'.
                '</port>'.
                '</service>'.
                '<message name="ZendTest.Soap.TestAsset.TestFuncIn"><part name="parameters" element="tns:ZendTest.Soap.TestAsset.TestFunc"/></message>'.
                '<message name="ZendTest.Soap.TestAsset.TestFuncOut"><part name="parameters" element="tns:ZendTest.Soap.TestAsset.TestFuncResponse"/></message>'.
                '</definitions>';
        $this->assertEquals($wsdl, $this->sanitizeWsdlXmlOutputForOsCompability($dom->saveXML()), "Bad WSDL generated");
        $this->assertTrue($dom->schemaValidate(__DIR__ .'/schemas/wsdl.xsd'), "WSDL Did not validate");

        unlink(__DIR__.'/TestAsset/addfunction.wsdl');
    }

    /**
     * @group ZF-5072
     */
    function testAddFunctionSimpleInReturnNameCompabilityMode()
    {
        $scriptUri = 'http://localhost/my_script.php';

        $server = new AutoDiscover();
        $server->addFunction('\ZendTest\Soap\TestAsset\TestFunc');
        $dom = new \DOMDocument();
        ob_start();
        $server->handle();
        $dom->loadXML(ob_get_clean());
        $dom->save(__DIR__.'/TestAsset/addfunction.wsdl');

        $parts = explode('.', basename($_SERVER['SCRIPT_NAME']));
        $name = $parts[0];

        $wsdl = $this->sanitizeWsdlXmlOutputForOsCompability($dom->saveXML());
        $this->assertContains('<message name="ZendTest.Soap.TestAsset.TestFuncOut"><part name="return" type="xsd:string"/>', $wsdl);
        $this->assertNotContains('<message name="ZendTest.Soap.TestAsset.TestFuncOut"><part name="ZendTest.Soap.TestAsset.TestFuncReturn"', $wsdl);
        $this->assertTrue($dom->schemaValidate(__DIR__ .'/schemas/wsdl.xsd'), "WSDL Did not validate");

        unlink(__DIR__.'/TestAsset/addfunction.wsdl');
    }

    function testAddFunctionMultiple()
    {
        $scriptUri = 'http://localhost/my_script.php';

        $server = new AutoDiscover();
        $server->addFunction('\ZendTest\Soap\TestAsset\TestFunc');
        $server->addFunction('\ZendTest\Soap\TestAsset\TestFunc2');
        $server->addFunction('\ZendTest\Soap\TestAsset\TestFunc3');
        $server->addFunction('\ZendTest\Soap\TestAsset\TestFunc4');
        $server->addFunction('\ZendTest\Soap\TestAsset\TestFunc5');
        $server->addFunction('\ZendTest\Soap\TestAsset\TestFunc6');
        $server->addFunction('\ZendTest\Soap\TestAsset\TestFunc7');
        $server->addFunction('\ZendTest\Soap\TestAsset\TestFunc9');

        $dom = new \DOMDocument();
        ob_start();
        $server->handle();
        $dom->loadXML(ob_get_clean());
        $dom->save(__DIR__.'/TestAsset/addfunction2.wsdl');

        $parts = explode('.', basename($_SERVER['SCRIPT_NAME']));
        $name = $parts[0];

        $wsdl = '<?xml version="1.0"?>'.
                '<definitions xmlns="http://schemas.xmlsoap.org/wsdl/" xmlns:tns="' . $scriptUri . '" xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap-enc="http://schemas.xmlsoap.org/soap/encoding/" xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" name="' .$name. '" targetNamespace="' . $scriptUri . '">'.
                '<types><xsd:schema targetNamespace="' . $scriptUri . '"/></types>'.
                '<portType name="' .$name. 'Port">'.
                '<operation name="ZendTest.Soap.TestAsset.TestFunc"><documentation>Test Function</documentation><input message="tns:ZendTest.Soap.TestAsset.TestFuncIn"/><output message="tns:ZendTest.Soap.TestAsset.TestFuncOut"/></operation>'.
                '<operation name="ZendTest.Soap.TestAsset.TestFunc2"><documentation>Test Function 2</documentation><input message="tns:ZendTest.Soap.TestAsset.TestFunc2In"/></operation>'.
                '<operation name="ZendTest.Soap.TestAsset.TestFunc3"><documentation>Return false</documentation><input message="tns:ZendTest.Soap.TestAsset.TestFunc3In"/><output message="tns:ZendTest.Soap.TestAsset.TestFunc3Out"/></operation>'.
                '<operation name="ZendTest.Soap.TestAsset.TestFunc4"><documentation>Return true</documentation><input message="tns:ZendTest.Soap.TestAsset.TestFunc4In"/><output message="tns:ZendTest.Soap.TestAsset.TestFunc4Out"/></operation>'.
                '<operation name="ZendTest.Soap.TestAsset.TestFunc5"><documentation>Return integer</documentation><input message="tns:ZendTest.Soap.TestAsset.TestFunc5In"/><output message="tns:ZendTest.Soap.TestAsset.TestFunc5Out"/></operation>'.
                '<operation name="ZendTest.Soap.TestAsset.TestFunc6"><documentation>Return string</documentation><input message="tns:ZendTest.Soap.TestAsset.TestFunc6In"/><output message="tns:ZendTest.Soap.TestAsset.TestFunc6Out"/></operation>'.
                '<operation name="ZendTest.Soap.TestAsset.TestFunc7"><documentation>Return array</documentation><input message="tns:ZendTest.Soap.TestAsset.TestFunc7In"/><output message="tns:ZendTest.Soap.TestAsset.TestFunc7Out"/></operation>'.
                '<operation name="ZendTest.Soap.TestAsset.TestFunc9"><documentation>Multiple Args</documentation><input message="tns:ZendTest.Soap.TestAsset.TestFunc9In"/><output message="tns:ZendTest.Soap.TestAsset.TestFunc9Out"/></operation>'.
                '</portType>'.
                '<binding name="' .$name. 'Binding" type="tns:' .$name. 'Port">'.
                '<soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http"/>'.
                '<operation name="ZendTest.Soap.TestAsset.TestFunc">'.
                '<soap:operation soapAction="' . $scriptUri . '#ZendTest.Soap.TestAsset.TestFunc"/>'.
                '<input><soap:body use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="' . $scriptUri . '"/></input>'.
                '<output><soap:body use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="' . $scriptUri . '"/></output>'.
                '</operation>'.
                '<operation name="ZendTest.Soap.TestAsset.TestFunc2">'.
                '<soap:operation soapAction="' . $scriptUri . '#ZendTest.Soap.TestAsset.TestFunc2"/>'.
                '<input><soap:body use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="' . $scriptUri . '"/></input>'.
                '<output><soap:body use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="' . $scriptUri . '"/></output>'.
                '</operation>'.
                '<operation name="ZendTest.Soap.TestAsset.TestFunc3">'.
                '<soap:operation soapAction="' . $scriptUri . '#ZendTest.Soap.TestAsset.TestFunc3"/>'.
                '<input><soap:body use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="' . $scriptUri . '"/></input>'.
                '<output><soap:body use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="' . $scriptUri . '"/></output>'.
                '</operation>'.
                '<operation name="ZendTest.Soap.TestAsset.TestFunc4">'.
                '<soap:operation soapAction="' . $scriptUri . '#ZendTest.Soap.TestAsset.TestFunc4"/>'.
                '<input><soap:body use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="' . $scriptUri . '"/></input>'.
                '<output><soap:body use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="' . $scriptUri . '"/></output>'.
                '</operation>'.
                '<operation name="ZendTest.Soap.TestAsset.TestFunc5">'.
                '<soap:operation soapAction="' . $scriptUri . '#ZendTest.Soap.TestAsset.TestFunc5"/>'.
                '<input><soap:body use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="' . $scriptUri . '"/></input>'.
                '<output><soap:body use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="' . $scriptUri . '"/></output>'.
                '</operation>'.
                '<operation name="ZendTest.Soap.TestAsset.TestFunc6">'.
                '<soap:operation soapAction="' . $scriptUri . '#ZendTest.Soap.TestAsset.TestFunc6"/>'.
                '<input><soap:body use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="' . $scriptUri . '"/></input>'.
                '<output><soap:body use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="' . $scriptUri . '"/></output>'.
                '</operation>'.
                '<operation name="ZendTest.Soap.TestAsset.TestFunc7">'.
                '<soap:operation soapAction="' . $scriptUri . '#ZendTest.Soap.TestAsset.TestFunc7"/>'.
                '<input><soap:body use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="' . $scriptUri . '"/></input>'.
                '<output><soap:body use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="' . $scriptUri . '"/></output>'.
                '</operation>'.
                '<operation name="ZendTest.Soap.TestAsset.TestFunc9">'.
                '<soap:operation soapAction="' . $scriptUri . '#ZendTest.Soap.TestAsset.TestFunc9"/>'.
                '<input><soap:body use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="' . $scriptUri . '"/></input>'.
                '<output><soap:body use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="' . $scriptUri . '"/></output>'.
                '</operation>'.
                '</binding>'.
                '<service name="' .$name. 'Service">'.
                '<port name="' .$name. 'Port" binding="tns:' .$name. 'Binding">'.
                '<soap:address location="' . $scriptUri . '"/>'.
                '</port>'.
                '</service>'.
                '<message name="ZendTest.Soap.TestAsset.TestFuncIn"><part name="who" type="xsd:string"/></message>'.
                '<message name="ZendTest.Soap.TestAsset.TestFuncOut"><part name="return" type="xsd:string"/></message>'.
                '<message name="ZendTest.Soap.TestAsset.TestFunc2In"/>'.
                '<message name="ZendTest.Soap.TestAsset.TestFunc3In"/>'.
                '<message name="ZendTest.Soap.TestAsset.TestFunc3Out"><part name="return" type="xsd:boolean"/></message>'.
                '<message name="ZendTest.Soap.TestAsset.TestFunc4In"/>'.
                '<message name="ZendTest.Soap.TestAsset.TestFunc4Out"><part name="return" type="xsd:boolean"/></message>'.
                '<message name="ZendTest.Soap.TestAsset.TestFunc5In"/>'.
                '<message name="ZendTest.Soap.TestAsset.TestFunc5Out"><part name="return" type="xsd:int"/></message>'.
                '<message name="ZendTest.Soap.TestAsset.TestFunc6In"/>'.
                '<message name="ZendTest.Soap.TestAsset.TestFunc6Out"><part name="return" type="xsd:string"/></message>'.
                '<message name="ZendTest.Soap.TestAsset.TestFunc7In"/>'.
                '<message name="ZendTest.Soap.TestAsset.TestFunc7Out"><part name="return" type="soap-enc:Array"/></message>'.
                '<message name="ZendTest.Soap.TestAsset.TestFunc9In"><part name="foo" type="xsd:string"/><part name="bar" type="xsd:string"/></message>'.
                '<message name="ZendTest.Soap.TestAsset.TestFunc9Out"><part name="return" type="xsd:string"/></message>'.
                '</definitions>';
        $this->assertEquals($wsdl, $this->sanitizeWsdlXmlOutputForOsCompability($dom->saveXML()), "Generated WSDL did not match expected XML");
        $this->assertTrue($dom->schemaValidate(__DIR__ .'/schemas/wsdl.xsd'), "WSDL Did not validate");



        unlink(__DIR__.'/TestAsset/addfunction2.wsdl');
    }

    /**
     * @group ZF-4117
     */
    public function testUseHttpsSchemaIfAccessedThroughHttps()
    {
        $_SERVER['HTTPS'] = "on";
        $httpsScriptUri = 'https://localhost/my_script.php';

        $server = new AutoDiscover();
        $server->addFunction('\ZendTest\Soap\TestAsset\TestFunc');

        ob_start();
        $server->handle();
        $wsdlOutput = ob_get_clean();

        $this->assertContains($httpsScriptUri, $wsdlOutput);
    }

    /**
     * @group ZF-4117
     */
    public function testChangeWsdlUriInConstructor()
    {
        $scriptUri = 'http://localhost/my_script.php';

        $server = new AutoDiscover(true, "http://example.com/service.php");
        $server->addFunction('\ZendTest\Soap\TestAsset\TestFunc');

        ob_start();
        $server->handle();
        $wsdlOutput = ob_get_clean();

        $this->assertNotContains($scriptUri, $wsdlOutput);
        $this->assertContains("http://example.com/service.php", $wsdlOutput);
    }

    /**
     * @group ZF-4117
     */
    public function testChangeWsdlUriViaSetUri()
    {
        $scriptUri = 'http://localhost/my_script.php';

        $server = new AutoDiscover(true);
        $server->setUri("http://example.com/service.php");
        $server->addFunction('\ZendTest\Soap\TestAsset\TestFunc');

        ob_start();
        $server->handle();
        $wsdlOutput = ob_get_clean();

        $this->assertNotContains($scriptUri, $wsdlOutput);
        $this->assertContains("http://example.com/service.php", $wsdlOutput);
    }

    public function testSetNonStringNonZendUriUriThrowsException()
    {
        $server = new AutoDiscover();
        
        $this->setExpectedException('Zend\Soap\Exception\InvalidArgumentException', 'No uri given to');
        $server->setUri(array("bogus"));
    }

    /**
     * @group ZF-4117
     */
    public function testChangingWsdlUriAfterGenerationIsPossible()
    {
        $scriptUri = 'http://localhost/my_script.php';

        $server = new AutoDiscover(true);
        $server->setUri("http://example.com/service.php");
        $server->addFunction('\ZendTest\Soap\TestAsset\TestFunc');

        ob_start();
        $server->handle();
        $wsdlOutput = ob_get_clean();

        $this->assertNotContains($scriptUri, $wsdlOutput);
        $this->assertContains("http://example.com/service.php", $wsdlOutput);

        $server->setUri("http://example2.com/service2.php");

        ob_start();
        $server->handle();
        $wsdlOutput = ob_get_clean();

        $this->assertNotContains($scriptUri, $wsdlOutput);
        $this->assertNotContains("http://example.com/service.php", $wsdlOutput);
        $this->assertContains("http://example2.com/service2.php", $wsdlOutput);
    }

    /**
     * @group ZF-4688
     * @group ZF-4125
     *
     */
    public function testUsingClassWithMultipleMethodPrototypesProducesValidWsdl()
    {
        $scriptUri = 'http://localhost/my_script.php';

        $server = new AutoDiscover();
        $server->setClass('\ZendTest\Soap\TestAsset\TestFixingMultiplePrototypes');

        ob_start();
        $server->handle();
        $wsdlOutput = ob_get_clean();

        $this->assertEquals(1, substr_count($wsdlOutput, '<message name="testFuncIn">'));
        $this->assertEquals(1, substr_count($wsdlOutput, '<message name="testFuncOut">'));
    }

    public function testUnusedFunctionsOfAutoDiscoverThrowExceptionOnBadPersistence()
    {
        $server = new AutoDiscover();
        
        $this->setExpectedException('Zend\Soap\Exception\RuntimeException', 'Function has no use in AutoDiscover');
        $server->setPersistence("bogus");
    }

    
    public function testUnusedFunctionsOfAutoDiscoverThrowExceptionOnFault()
    {
        $server = new AutoDiscover();
        
        $this->setExpectedException('Zend\Soap\Exception\UnexpectedValueException', 'Function has no use in AutoDiscover');
        $server->fault();
    }
    
    public function testUnusedFunctionsOfAutoDiscoverThrowExceptionOnLoadFunctionsCall()
    {
        $server = new AutoDiscover();
        
        $this->setExpectedException('Zend\Soap\Exception\RuntimeException', 'Function has no use in AutoDiscover');
        $server->loadFunctions("bogus");
    }

    public function testGetFunctions()
    {
        $server = new AutoDiscover();
        $server->addFunction('\ZendTest\Soap\TestAsset\TestFunc');
        $server->setClass('\ZendTest\Soap\TestAsset\Test');

        $functions = $server->getFunctions();
        $this->assertEquals(
            array('ZendTest\Soap\TestAsset\TestFunc', 'testFunc1', 'testFunc2', 'testFunc3', 'testFunc4'),
            $functions
        );
    }

    /**
     * @group ZF-4835
     */
    public function testUsingRequestUriWithoutParametersAsDefault()
    {
        // Apache
        $_SERVER = array('REQUEST_URI' => '/my_script.php?wsdl', 'HTTP_HOST' => 'localhost');
        $server = new AutoDiscover();
        $uri = $server->getUri()->generate();
        $this->assertNotContains("?wsdl", $uri);
        $this->assertEquals("http://localhost/my_script.php", $uri);

        // Apache plus SSL
        $_SERVER = array('REQUEST_URI' => '/my_script.php?wsdl', 'HTTP_HOST' => 'localhost', 'HTTPS' => 'on');
        $server = new AutoDiscover();
        $uri = $server->getUri()->generate();
        $this->assertNotContains("?wsdl", $uri);
        $this->assertEquals("https://localhost/my_script.php", $uri);

        // IIS 5 + PHP as FastCGI
        $_SERVER = array('ORIG_PATH_INFO' => '/my_script.php?wsdl', 'SERVER_NAME' => 'localhost');
        $server = new AutoDiscover();
        $uri = $server->getUri()->generate();
        $this->assertNotContains("?wsdl", $uri);
        $this->assertEquals("http://localhost/my_script.php", $uri);

        // IIS
        $_SERVER = array('HTTP_X_REWRITE_URL' => '/my_script.php?wsdl', 'SERVER_NAME' => 'localhost');
        $server = new AutoDiscover();
        $uri = $server->getUri()->generate();
        $this->assertNotContains("?wsdl", $uri);
        $this->assertEquals("http://localhost/my_script.php", $uri);
    }

    /**
     * @group ZF-4937
     */
    public function testComplexTypesThatAreUsedMultipleTimesAreRecoginzedOnce()
    {
        $server = new AutoDiscover('Zend\Soap\Wsdl\Strategy\ArrayOfTypeComplex');
        $server->setClass('\ZendTest\Soap\TestAsset\AutoDiscoverTestClass2');

        ob_start();
        $server->handle();
        $wsdlOutput = ob_get_clean();

        $this->assertEquals(1,
            substr_count($wsdlOutput, 'wsdl:arrayType="tns:ZendTest.Soap.TestAsset.AutoDiscoverTestClass1[]"'),
            'wsdl:arrayType definition of TestClass1 has to occour once.'
        );
        $this->assertEquals(1,
            substr_count($wsdlOutput, '<xsd:complexType name="ZendTest.Soap.TestAsset.AutoDiscoverTestClass1">'),
            '\ZendTest\Soap\TestAsset\AutoDiscoverTestClass1 has to be defined once.'
        );
        $this->assertEquals(1,
            substr_count($wsdlOutput, '<xsd:complexType name="ArrayOfZendTest.Soap.TestAsset.AutoDiscoverTestClass1">'),
            '\ZendTest\Soap\TestAsset\AutoDiscoverTestClass1 should be defined once.'
        );
        $this->assertTrue(
            substr_count($wsdlOutput, '<part name="test" type="tns:ZendTest.Soap.TestAsset.AutoDiscoverTestClass1"/>') >= 1,
            '\ZendTest\Soap\TestAsset\AutoDiscoverTestClass1 appears once or more than once in the message parts section.'
        );
    }

    /**
     * @group ZF-5330
     */
    public function testDumpOrXmlOfAutoDiscover()
    {
        $server = new AutoDiscover();
        $server->addFunction('\ZendTest\Soap\TestAsset\TestFunc');

        ob_start();
        $server->handle();
        $wsdlOutput = ob_get_clean();

        $this->assertEquals(
            $this->sanitizeWsdlXmlOutputForOsCompability($wsdlOutput),
            $this->sanitizeWsdlXmlOutputForOsCompability($server->toXml())
        );

        ob_start();
        $server->dump(false);
        $wsdlOutput = ob_get_clean();

        $this->assertEquals(
            $this->sanitizeWsdlXmlOutputForOsCompability($wsdlOutput),
            $this->sanitizeWsdlXmlOutputForOsCompability($server->toXml())
        );
    }

    /**
     * @group ZF-5330
     */
    public function testDumpOnlyAfterGeneratedAutoDiscoverWsdl()
    {
        $server = new AutoDiscover();
        
        $this->setExpectedException('Zend\Soap\Exception\RuntimeException', 'Cannot dump autodiscovered contents');
        $server->dump(false);
    }
    
    /**
     * @group ZF-5330
     */
    public function testXmlOnlyAfterGeneratedAutoDiscoverWsdl()
    {
        $server = new AutoDiscover();
        
        $this->setExpectedException('Zend\Soap\Exception\RuntimeException', 'Cannot return autodiscovered contents');
        $server->toXml();
    }

    /**
     * @group ZF-5604
     */
    public function testReturnSameArrayOfObjectsResponseOnDifferentMethodsWhenArrayComplex()
    {
        $autodiscover = new AutoDiscover('Zend\Soap\Wsdl\Strategy\ArrayOfTypeComplex');
        $autodiscover->setClass('\ZendTest\Soap\TestAsset\MyService');
        $wsdl = $autodiscover->toXml();

        $this->assertEquals(1, substr_count($wsdl, '<xsd:complexType name="ArrayOfZendTest.Soap.TestAsset.MyResponse">'));

        $this->assertEquals(0, substr_count($wsdl, 'tns:My_Response[]'));
    }

    /**
     * @group ZF-5430
     */
    public function testReturnSameArrayOfObjectsResponseOnDifferentMethodsWhenArraySequence()
    {
        $autodiscover = new AutoDiscover('Zend\Soap\Wsdl\Strategy\ArrayOfTypeSequence');
        $autodiscover->setClass('\ZendTest\Soap\TestAsset\MyServiceSequence');
        $wsdl = $autodiscover->toXml();

        $this->assertEquals(1, substr_count($wsdl, '<xsd:complexType name="ArrayOfString">'));
        $this->assertEquals(1, substr_count($wsdl, '<xsd:complexType name="ArrayOfArrayOfString">'));
        $this->assertEquals(1, substr_count($wsdl, '<xsd:complexType name="ArrayOfArrayOfArrayOfString">'));

        $this->assertEquals(0, substr_count($wsdl, 'tns:string[]'));
    }

    /**
     * @group ZF-5736
     */
    public function testAmpersandInUrlIsCorrectlyEncoded()
    {
        $autodiscover = new AutoDiscover();
        $autodiscover->setUri("http://example.com/?a=b&amp;b=c");

        $autodiscover->setClass('\ZendTest\Soap\TestAsset\Test');
        $wsdl = $autodiscover->toXml();

        $this->assertContains("http://example.com/?a=b&amp;b=c", $wsdl);
    }

    /**
     * @group ZF-6689
     */
    public function testNoReturnIsOneWayCallInSetClass()
    {
        $autodiscover = new AutoDiscover();
        $autodiscover->setClass('\ZendTest\Soap\TestAsset\NoReturnType');
        $wsdl = $autodiscover->toXml();

        $this->assertContains(
            '<operation name="pushOneWay"><documentation>@param string $message</documentation><input message="tns:pushOneWayIn"/></operation>',
            $wsdl
        );
    }

    /**
     * @group ZF-6689
     */
    public function testNoReturnIsOneWayCallInAddFunction()
    {
        $autodiscover = new AutoDiscover();
        $autodiscover->addFunction('\ZendTest\Soap\TestAsset\OneWay');
        $wsdl = $autodiscover->toXml();

        $this->assertContains(
            '<operation name="ZendTest.Soap.TestAsset.OneWay"><documentation>@param string $message</documentation><input message="tns:ZendTest.Soap.TestAsset.OneWayIn"/></operation>',
            $wsdl
        );
    }

    /**
     * @group ZF-8948
     * @group ZF-5766
     */
    public function testRecursiveWsdlDependencies()
    {
        $autodiscover = new AutoDiscover('\Zend\Soap\Wsdl\Strategy\ArrayOfTypeComplex');
        $autodiscover->setClass('\ZendTest\Soap\TestAsset\Recursion');
        $wsdl = $autodiscover->toXml();

        //  <types>
        //      <xsd:schema targetNamespace="http://localhost/my_script.php">
        //          <xsd:complexType name="Zend_Soap_AutoDiscover_Recursion">
        //              <xsd:all>
        //                  <xsd:element name="recursion" type="tns:Zend_Soap_AutoDiscover_Recursion"/>


        $path = '//wsdl:types/xsd:schema/xsd:complexType[@name="ZendTest.Soap.TestAsset.Recursion"]/xsd:all/xsd:element[@name="recursion" and @type="tns:ZendTest.Soap.TestAsset.Recursion"]';
        $this->assertWsdlPathExists($wsdl, $path);
    }

    public function assertWsdlPathExists($xml, $path)
    {
        $doc = new \DOMDocument('UTF-8');
        $doc->loadXML($xml);

        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('wsdl', 'http://schemas.xmlsoap.org/wsdl/');

        $nodes = $xpath->query($path);

        $this->assertTrue($nodes->length >= 1, "Could not assert that XML Document contains a node that matches the XPath Expression: " . $path);
    }
}
