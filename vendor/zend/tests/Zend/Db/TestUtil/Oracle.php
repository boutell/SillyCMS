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
 * @package    Zend_Db
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace ZendTest\Db\TestUtil;
use Zend\DB;

/**
 * @category   Zend
 * @package    Zend_Db
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Oracle extends Pdo\Oci
{

    protected function _rawQuery($sql)
    {
        $conn = $this->_db->getConnection();
        $stmt = oci_parse($conn, $sql);
        if (!$stmt) {
            $e = oci_error($conn);
            throw new Db\Exception("SQL parse error for \"$sql\": ".$e['message']);
        }
        $retval = oci_execute($stmt);
        if (!$retval) {
            $e = oci_error($conn);
            throw new Db\Exception("SQL execute error for \"$sql\": ".$e['message']);
        }
    }

}
