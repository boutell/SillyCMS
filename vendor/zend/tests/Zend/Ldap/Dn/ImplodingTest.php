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
 * @package    Zend_LDAP
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace ZendTest\Ldap\Dn;
use Zend\Ldap;

/**
 * @category   Zend
 * @package    Zend_LDAP
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_LDAP
 * @group      Zend_LDAP_Dn
 */
class ImplodingTest extends \PHPUnit_Framework_TestCase
{
    public function testDnWithMultiValuedRdnRoundTrip()
    {
        $dn1='cn=Surname\, Firstname+uid=userid,cn=name2,dc=example,dc=org';
        $dnArray=Ldap\Dn::explodeDn($dn1);
        $dn2=Ldap\Dn::implodeDn($dnArray);
        $this->assertEquals($dn1, $dn2);
    }

    public function testImplodeDn()
    {
        $expected='cn=name1,cn=name2,dc=example,dc=org';
        $dnArray=array(
            array("cn" => "name1"),
            array("cn" => "name2"),
            array("dc" => "example"),
            array("dc" => "org")
        );
        $dn=Ldap\Dn::implodeDn($dnArray);
        $this->assertEquals($expected, $dn);

        $dn=Ldap\Dn::implodeDn($dnArray, Ldap\Dn::ATTR_CASEFOLD_UPPER, ';');
        $this->assertEquals('CN=name1;CN=name2;DC=example;DC=org', $dn);
    }

    public function testImplodeDnWithUtf8Characters()
    {
        $expected='uid=rogasawara,ou=営業部,o=Airius';
        $dnArray=array(
            array("uid" => "rogasawara"),
            array("ou" => "営業部"),
            array("o" => "Airius"),
        );
        $dn=Ldap\Dn::implodeDn($dnArray);
        $this->assertEquals($expected, $dn);
    }

    public function testImplodeRdn()
    {
        $a=array('cn' => 'value');
        $expected='cn=value';
        $this->assertEquals($expected, Ldap\Dn::implodeRdn($a));
    }

    public function testImplodeRdnMultiValuedRdn()
    {
        $a=array('cn' => 'value', 'uid' => 'testUser');
        $expected='cn=value+uid=testUser';
        $this->assertEquals($expected, Ldap\Dn::implodeRdn($a));
    }

    public function testImplodeRdnMultiValuedRdn2()
    {
        $a=array('cn' => 'value', 'uid' => 'testUser', 'ou' => 'myDep');
        $expected='cn=value+ou=myDep+uid=testUser';
        $this->assertEquals($expected, Ldap\Dn::implodeRdn($a));
    }

    public function testImplodeRdnCaseFold()
    {
        $a=array('cn' => 'value');
        $expected='CN=value';
        $this->assertEquals($expected,
            Ldap\Dn::implodeRdn($a, Ldap\Dn::ATTR_CASEFOLD_UPPER));
        $a=array('CN' => 'value');
        $expected='cn=value';
        $this->assertEquals($expected,
            Ldap\Dn::implodeRdn($a, Ldap\Dn::ATTR_CASEFOLD_LOWER));
    }

    public function testImplodeRdnMultiValuedRdnCaseFold()
    {
        $a=array('cn' => 'value', 'uid' => 'testUser', 'ou' => 'myDep');
        $expected='CN=value+OU=myDep+UID=testUser';
        $this->assertEquals($expected,
            Ldap\Dn::implodeRdn($a, Ldap\Dn::ATTR_CASEFOLD_UPPER));
        $a=array('CN' => 'value', 'uID' => 'testUser', 'ou' => 'myDep');
        $expected='cn=value+ou=myDep+uid=testUser';
        $this->assertEquals($expected,
            Ldap\Dn::implodeRdn($a, Ldap\Dn::ATTR_CASEFOLD_LOWER));
    }

    /**
     * @expectedException Zend\Ldap\Exception
     */
    public function testImplodeRdnInvalidOne()
    {
        $a=array('cn');
        Ldap\Dn::implodeRdn($a);
    }

    /**
     * @expectedException Zend\Ldap\Exception
     */
    public function testImplodeRdnInvalidThree()
    {
        $a=array('cn' => 'value', 'ou');
        Ldap\Dn::implodeRdn($a);
    }
}
