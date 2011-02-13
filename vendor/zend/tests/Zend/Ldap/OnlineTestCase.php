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
namespace ZendTest\Ldap;
use Zend\Ldap;

/**
 * @category   Zend
 * @package    Zend_LDAP
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_LDAP
 */
abstract class OnlineTestCase extends TestCase
{
    /**
     * @var Zend_LDAP
     */
    private $_ldap;

    /**
     * @var array
     */
    private $_nodes;

    /**
     * @return Zend_LDAP
     */
    protected function _getLDAP()
    {
        return $this->_ldap;
    }

    protected function setUp()
    {
        if (!constant('TESTS_ZEND_LDAP_ONLINE_ENABLED')) {
            $this->markTestSkipped("Zend_LDAP online tests are not enabled");
        }

        $options = array(
            'host'     => TESTS_ZEND_LDAP_HOST,
            'username' => TESTS_ZEND_LDAP_USERNAME,
            'password' => TESTS_ZEND_LDAP_PASSWORD,
            'baseDn'   => TESTS_ZEND_LDAP_WRITEABLE_SUBTREE,
        );
        if (defined('TESTS_ZEND_LDAP_PORT') && TESTS_ZEND_LDAP_PORT != 389)
            $options['port'] = TESTS_ZEND_LDAP_PORT;
        if (defined('TESTS_ZEND_LDAP_USE_START_TLS'))
            $options['useStartTls'] = TESTS_ZEND_LDAP_USE_START_TLS;
        if (defined('TESTS_ZEND_LDAP_USE_SSL'))
            $options['useSsl'] = TESTS_ZEND_LDAP_USE_SSL;
        if (defined('TESTS_ZEND_LDAP_BIND_REQUIRES_DN'))
            $options['bindRequiresDn'] = TESTS_ZEND_LDAP_BIND_REQUIRES_DN;
        if (defined('TESTS_ZEND_LDAP_ACCOUNT_FILTER_FORMAT'))
            $options['accountFilterFormat'] = TESTS_ZEND_LDAP_ACCOUNT_FILTER_FORMAT;
        if (defined('TESTS_ZEND_LDAP_ACCOUNT_DOMAIN_NAME'))
            $options['accountDomainName'] = TESTS_ZEND_LDAP_ACCOUNT_DOMAIN_NAME;
        if (defined('TESTS_ZEND_LDAP_ACCOUNT_DOMAIN_NAME_SHORT'))
            $options['accountDomainNameShort'] = TESTS_ZEND_LDAP_ACCOUNT_DOMAIN_NAME_SHORT;

        $this->_ldap=new Ldap\Ldap($options);
        $this->_ldap->bind();
    }

    protected function tearDown()
    {
        if ($this->_ldap!==null) {
            $this->_ldap->disconnect();
            $this->_ldap=null;
        }
    }

    protected function _createDn($dn)
    {
        if (substr($dn, -1)!==',') {
            $dn.=',';
        }
        $dn = $dn . TESTS_ZEND_LDAP_WRITEABLE_SUBTREE;
        return Ldap\Dn::fromString($dn)->toString(Ldap\Dn::ATTR_CASEFOLD_LOWER);
    }

    protected function _prepareLDAPServer()
    {
        $this->_nodes=array(
            $this->_createDn('ou=Node,') =>
                array("objectClass" => "organizationalUnit", "ou" => "Node", "postalCode" => "1234"),
            $this->_createDn('ou=Test1,ou=Node,') =>
                array("objectClass" => "organizationalUnit", "ou" => "Test1"),
            $this->_createDn('ou=Test2,ou=Node,') =>
                array("objectClass" => "organizationalUnit", "ou" => "Test2"),
            $this->_createDn('ou=Test1,') =>
                array("objectClass" => "organizationalUnit", "ou" => "Test1", "l" => "e"),
            $this->_createDn('ou=Test2,') =>
                array("objectClass" => "organizationalUnit", "ou" => "Test2", "l" => "d"),
            $this->_createDn('ou=Test3,') =>
                array("objectClass" => "organizationalUnit", "ou" => "Test3", "l" => "c"),
            $this->_createDn('ou=Test4,') =>
                array("objectClass" => "organizationalUnit", "ou" => "Test4", "l" => "b"),
            $this->_createDn('ou=Test5,') =>
                array("objectClass" => "organizationalUnit", "ou" => "Test5", "l" => "a"),
        );

        $ldap=$this->_ldap->getResource();
        foreach ($this->_nodes as $dn => $entry) {
            ldap_add($ldap, $dn, $entry);
        }
    }

    protected function _cleanupLDAPServer()
    {
        if (!constant('TESTS_ZEND_LDAP_ONLINE_ENABLED')) {
            return;
        }
        $ldap=$this->_ldap->getResource();
        foreach (array_reverse($this->_nodes) as $dn => $entry) {
            ldap_delete($ldap, $dn);
        }
    }
}
