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
 * @package    Zend_Test
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace ZendTest\Test\PHPUnit\Db\Operation;
use Zend\Test\PHPUnit\Db;

/**
 * @category   Zend
 * @package    Zend_Test
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Test
 */
class TruncateTest extends \PHPUnit_Framework_TestCase
{
    private $operation = null;

    public function setUp()
    {
        $this->operation = new \Zend\Test\PHPUnit\Db\Operation\Truncate();
    }

    public function testTruncateTablesExecutesAdapterQuery()
    {
        $dataSet = new \PHPUnit_Extensions_Database_DataSet_FlatXmlDataSet(__DIR__."/_files/truncateFixture.xml");

        $testAdapter = $this->getMock('Zend\Test\DbAdapter');
        $testAdapter->expects($this->at(0))
                    ->method('quoteIdentifier')
                    ->with('bar')->will($this->returnValue('bar'));
        $testAdapter->expects($this->at(1))
                    ->method('query')
                    ->with('TRUNCATE bar');
        $testAdapter->expects($this->at(2))
                    ->method('quoteIdentifier')
                    ->with('foo')->will($this->returnValue('foo'));
        $testAdapter->expects($this->at(3))
                    ->method('query')
                    ->with('TRUNCATE foo');

        $connection = new Db\Connection($testAdapter, "schema");

        $this->operation->execute($connection, $dataSet);
    }

    public function testTruncateTableInvalidQueryTransformsException()
    {
        $this->setExpectedException('PHPUnit_Extensions_Database_Operation_Exception');

        $dataSet = new \PHPUnit_Extensions_Database_DataSet_FlatXmlDataSet(__DIR__."/_files/insertFixture.xml");

        $testAdapter = $this->getMock('Zend\Test\DbAdapter');
        $testAdapter->expects($this->any())->method('query')->will($this->throwException(new \Exception()));

        $connection = new Db\Connection($testAdapter, "schema");

        $this->operation->execute($connection, $dataSet);
    }

    public function testInvalidConnectionGivenThrowsException()
    {
        $this->setExpectedException("Zend\Test\PHPUnit\Db\Exception\InvalidArgumentException");

        $dataSet = $this->getMock('PHPUnit_Extensions_Database_DataSet_IDataSet');
        $connection = $this->getMock('PHPUnit_Extensions_Database_DB_IDatabaseConnection');

        $this->operation->execute($connection, $dataSet);
    }

    /**
     * @group ZF-7936
     */
    public function testTruncateAppliedToTablesInReverseOrder()
    {
        $testAdapter = new \Zend\Test\DbAdapter();
        $connection = new Db\Connection($testAdapter, "schema");

        $dataSet = new \PHPUnit_Extensions_Database_DataSet_FlatXmlDataSet(__DIR__."/_files/truncateFixture.xml");

        $this->operation->execute($connection, $dataSet);

        $profiler = $testAdapter->getProfiler();
        $queries = $profiler->getQueryProfiles();

        $this->assertEquals(2, count($queries));
        $this->assertContains('bar', $queries[0]->getQuery());
        $this->assertContains('foo', $queries[1]->getQuery());
    }
}
