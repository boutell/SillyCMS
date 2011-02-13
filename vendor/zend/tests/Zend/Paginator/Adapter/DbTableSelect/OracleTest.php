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
 * @package    Zend_Paginator
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace ZendTest\Paginator\Adapter\DbTableSelect;


/**
 * @category   Zend
 * @package    Zend_Paginator
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Paginator
 */
class OracleTest extends \ZendTest\Paginator\Adapter\DbSelect\OracleTest
{
    public function setup()
    {
        $this->markTestSkipped('Skipping until Zend\Db is refactored.');
    }
    
    /**
     * @group ZF-3775
     */
    public function testSelectDoesReturnZendDbTableRowset()
    {
        $query   = $this->_table->select();
        $adapter = new \Zend\Paginator\Adapter\DbTableSelect($query);
        $items   = $adapter->getItems(0, 10);

        $this->assertType('Zend\Db\Table\Rowset', $items);
    }
}
