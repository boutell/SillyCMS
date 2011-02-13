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
namespace ZendTest\Db\Statement;
use Zend\Db\Statement\PDO;


/*
 * @category   Zend
 * @package    Zend_Db
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Db
 * @group      Zend_Db_Statement
 */
abstract class AbstractPdoTest extends AbstractTest
{

    public function testStatementConstruct()
    {
        $select = $this->_db->select()
            ->from('zfproducts');
        $sql = $select->__toString();
        $stmt = new PDO($this->_db, $sql);
        $this->assertType('Zend\Db\Statement\PDO', $stmt);
        $stmt->closeCursor();
    }

    public function testStatementConstructWithSelectObject()
    {
        $select = $this->_db->select()
            ->from('zfproducts');
        $stmt = new PDO($this->_db, $select);
        $this->assertType('Zend\Db\Statement', $stmt);
        $stmt->closeCursor();
    }

    public function testStatementNextRowset()
    {
        $select = $this->_db->select()
            ->from('zfproducts');
        $stmt = $this->_db->prepare($select->__toString());
        try {
            $stmt->nextRowset();
            $this->fail('Expected to catch Zend_Db_Statement_Exception');
        } catch (\Zend\Exception $e) {
            $this->assertType('Zend\Db\Statement\Exception', $e,
                'Expecting object of type Zend_Db_Statement_Exception, got '.get_class($e));
            $this->assertEquals('SQLSTATE[IM001]: Driver does not support this function: driver does not support multiple rowsets', $e->getMessage());
        }
        $stmt->closeCursor();
    }

    /**
     * @group ZF-4486
     */
    public function testStatementIsIterableThroughtForeach()
    {
        $select = $this->_db->select()->from('zfproducts');
        $stmt = $this->_db->query($select);
        $stmt->setFetchMode(\Zend\Db\DB::FETCH_OBJ);
        foreach ($stmt as $test) {
            $this->assertTrue($test instanceof \stdClass);
        }
        $this->assertType('int', iterator_count($stmt));
    }

    public function testStatementConstructExceptionBadSql()
    {
        $sql = "SELECT * FROM *";
        try {
            $stmt = $this->_db->query($sql);
            $this->fail('Expected to catch Zend_Db_Statement_Exception');
        } catch (\Zend\Exception $e) {
            $this->assertType('Zend\Db\Statement\Exception', $e,
                'Expecting object of type Zend_Db_Statement_Exception, got '.get_class($e));
            $this->assertTrue($e->hasChainedException(), 'Missing Chained Exception');
            $this->assertType('PDOException', $e->getChainedException(), 'Wrong type of Exception');
        }
    }

    /**
     *
     * @group ZF-5868
     */
    public function testStatementWillPersistBindParamsInQueryProfilerAfterExecute()
    {
        $this->_db->getProfiler()->setEnabled(true);
        $products = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');

        $sql = "SELECT * FROM $products WHERE $product_id > :product_id ORDER BY $product_id ASC";
        $stmt = $this->_db->prepare($sql);
        $stmt->bindValue('product_id', 1);
        $stmt->execute();

        $params = $this->_db->getProfiler()->getLastQueryProfile()->getQueryParams();

        $target = array(':product_id' => 1);
        $this->assertEquals($target, $params);

    }

}
