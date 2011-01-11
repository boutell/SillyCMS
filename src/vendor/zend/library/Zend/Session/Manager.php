<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-webat this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Session
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace Zend\Session;

use Zend\SignalSlot\SignalSlot;

/**
 * Session manager interface
 *
 * @category   Zend
 * @package    Zend_Session
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Manager
{
    public function __construct($config = null, $storage = null);

    public function getConfig();
    public function getStorage();
    
    public function sessionExists();
    public function start();
    public function destroy();
    public function writeClose();

    public function getName();
    public function setName($name);
    public function getId();
    public function setId($id);
    public function regenerateId();

    public function rememberMe($ttl = null);
    public function forgetMe();
    public function expireSessionCookie();

    public function setValidatorChain(SignalSlot $chain);
    public function getValidatorChain();
    public function isValid();
}
