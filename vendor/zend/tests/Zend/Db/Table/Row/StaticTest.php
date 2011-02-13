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
namespace ZendTest\Db\Table\Row;


/**
 * @category   Zend
 * @package    Zend_Db
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Db
 * @group      Zend_Db_Table
 * @group      Zend_Db_Table_Row
 */
class StaticTest extends \PHPUnit_Framework_TestCase
{

    public function setup()
    {
        $this->markTestSkipped('This suite is skipped until Zend\DB can be refactored.');
    }

    public function testTableRowTransformColumnNotUsedInConstructor()
    {
        $data = array(
            'column'         => 'value1',
            'column_foo'     => 'value2',
            'column_bar_baz' => 'value3'
        );
        $row = new \Zend_Db_Table_Asset_Row_TestMockRow(array('data' => $data));

        $array = $row->toArray();
        $this->assertEquals($data, $array);
    }

    public function testTableRowTransformColumnMagicGet()
    {
        $data = array(
            'column'         => 'value1',
            'column_foo'     => 'value2',
            'column_bar_baz' => 'value3'
        );
        $row = new \Zend_Db_Table_Asset_Row_TestMockRow(array('data' => $data));

        $this->assertEquals('value1', $row->column);
        $this->assertEquals('value2', $row->columnFoo);
        $this->assertEquals('value3', $row->columnBarBaz);
    }

    public function testTableRowTransformColumnMagicSet()
    {
        $data = array(
            'column'         => 'value1',
            'column_foo'     => 'value2',
            'column_bar_baz' => 'value3'
        );
        $row = new \Zend_Db_Table_Asset_Row_TestMockRow(array('data' => $data));

        $this->assertEquals('value1', $row->column);
        $this->assertEquals('value2', $row->columnFoo);
        $this->assertEquals('value3', $row->columnBarBaz);

        $row->column       = 'another value 1';
        $row->columnFoo    = 'another value 2';
        $row->columnBarBaz = 'another value 3';

        $array = $row->toArray();
        $this->assertEquals(
            array(
                'column'         => 'another value 1',
                'column_foo'     => 'another value 2',
                'column_bar_baz' => 'another value 3'
            ), $array);
    }

    public function getDriver()
    {
        return 'Static';
    }

}
