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
 * @package    Zend_Service_Nirvanix
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @todo: temporary fix because test autoloader doesn't load Zend_* classes
 * from test tree
 */
require_once("Zend/Service/Nirvanix/FunctionalTestCase.php");

/**
 * @category   Zend
 * @package    Zend_Service_Nirvanix
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Service
 * @group      Zend_Service_Nirvanix
 */
class Zend_Service_Nirvanix_Namespace_BaseTest extends Zend_Service_Nirvanix_FunctionalTestCase
{
    public function testGetHttpClient()
    {
        $foo = $this->nirvanix->getService('Foo');
        $this->assertSame($this->httpClient, $foo->getHttpClient());
    }

}
