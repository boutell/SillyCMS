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
namespace ZendTest\Paginator;

use Zend\Paginator,
    Zend\Controller\Front as FrontController,
    Zend\View\Helper,
    Zend\View,
    Zend\Config,
    Zend\Paginator\Adapter,
    Zend\Paginator\Exception;


/**
 * @category   Zend
 * @package    Zend_Paginator
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Paginator
 */
class PaginatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Paginator instance
     *
     * @var Zend_Paginator
     */
    protected $_paginator = null;

    protected $_testCollection = null;

    protected $_cache;

    protected $_query = null;

    protected $_config = null;

    protected $_adapter = null;

    protected function setUp()
    {
        if (!extension_loaded('pdo_sqlite')) {
           $this->markTestSkipped('Pdo_Sqlite extension is not loaded');
        }

        $this->_adapter = new \Zend\Db\Adapter\Pdo\Sqlite(array(
            'dbname' => __DIR__ . '/_files/test.sqlite'
        ));

        $this->_query = $this->_adapter->select()->from('test');

        $this->_testCollection = range(1, 101);
        $this->_paginator = Paginator\Paginator::factory($this->_testCollection);

        $this->_config = new Config\Xml(__DIR__ . '/_files/config.xml');
        // get a fresh new copy of ViewRenderer in each tests
        $this->front = FrontController::getInstance();
        $this->front->resetInstance();
        $this->broker = $this->front->getHelperBroker();

        $fO = array('lifetime' => 3600, 'automatic_serialization' => true);
        $bO = array('cache_dir'=> $this->_getTmpDir());

        $this->_cache = \Zend\Cache\Cache::factory('Core', 'File', $fO, $bO);

        Paginator\Paginator::setCache($this->_cache);

        $this->_restorePaginatorDefaults();
    }

    protected function tearDown()
    {
        $this->_dbConn = null;
        $this->_testCollection = null;
        $this->_paginator = null;
    }

    protected function _getTmpDir()
    {
        $tmpDir = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'zend_paginator';
        if (file_exists($tmpDir)) {
            $this->_rmDirRecursive($tmpDir);
        }
        mkdir($tmpDir);
        $this->cacheDir = $tmpDir;
        return $tmpDir;
    }

    protected function _rmDirRecursive($path)
    {
        $dir = new \DirectoryIterator($path);
        foreach ($dir as $file) {
            if (!$file->isDir()) {
                unlink($file->getPathname());
            } elseif (!in_array($file->getFilename(), array('.', '..'))) {
                $this->_rmDirRecursive($file->getPathname());
            }
        }
        unset($file, $dir); // required on windows to remove file handle
        if (!rmdir($path)) {
            throw new Exception('Unable to remove temporary directory ' . $path
                                . '; perhaps it has a nested structure?');
        }
    }

    protected function _restorePaginatorDefaults()
    {
        $this->_paginator->setItemCountPerPage(10);
        $this->_paginator->setCurrentPageNumber(1);
        $this->_paginator->setPageRange(10);
        $this->_paginator->setView();

        Paginator\Paginator::setDefaultScrollingStyle();
        Helper\PaginationControl::setDefaultViewPartial(null);

        Paginator\Paginator::setConfig($this->_config->default);

        Paginator\Paginator::setScrollingStyleBroker(new Paginator\ScrollingStyleBroker());

        $this->_cache->clean();
        $this->_paginator->setCacheEnabled(true);
    }

    public function testFactoryReturnsArrayAdapter()
    {
        $paginator = Paginator\Paginator::factory($this->_testCollection);
        $this->assertType('Zend\Paginator\Adapter\ArrayAdapter', $paginator->getAdapter());
    }

    public function testFactoryReturnsDbSelectAdapter()
    {
        $paginator = Paginator\Paginator::factory($this->_query);

        $this->assertType('Zend\Paginator\Adapter\DbSelect', $paginator->getAdapter());
    }

    /**
     * @group ZF-4607
     */
    public function testFactoryReturnsDbTableSelectAdapter()
    {
        $table = new \ZendTest\Paginator\TestAsset\TestTable($this->_adapter);

        $paginator = Paginator\Paginator::factory($table->select());

        $this->assertType('Zend\Paginator\Adapter\DbSelect', $paginator->getAdapter());
    }

    public function testFactoryReturnsIteratorAdapter()
    {
        $paginator = Paginator\Paginator::factory(new \ArrayIterator($this->_testCollection));
        $this->assertType('Zend\Paginator\Adapter\Iterator', $paginator->getAdapter());
    }

    public function testFactoryReturnsNullAdapter()
    {
        $paginator = Paginator\Paginator::factory(101);
        $this->assertType('Zend\Paginator\Adapter\Null', $paginator->getAdapter());
    }

    public function testFactoryThrowsInvalidClassExceptionAdapter()
    {
        $this->setExpectedException('Zend\Paginator\Exception\InvalidArgumentException', 'No adapter for type stdClass');
        $paginator = Paginator\Paginator::factory(new \stdClass());
    }

    public function testFactoryThrowsInvalidTypeExceptionAdapter()
    {
        $this->setExpectedException('Zend\Paginator\Exception\InvalidArgumentException', 'No adapter for type string');
        $paginator = Paginator\Paginator::factory('invalid argument');
    }

    public function testGetsAndSetsDefaultScrollingStyle()
    {
        $this->assertEquals(Paginator\Paginator::getDefaultScrollingStyle(), 'Sliding');
        Paginator\Paginator::setDefaultScrollingStyle('Scrolling');
        $this->assertEquals(Paginator\Paginator::getDefaultScrollingStyle(), 'Scrolling');
        Paginator\Paginator::setDefaultScrollingStyle('Sliding');
    }

    public function testHasCorrectCountAfterInit()
    {
        $paginator = Paginator\Paginator::factory(range(1, 101));
        $this->assertEquals(11, $paginator->count());
    }

    public function testHasCorrectCountOfAllItemsAfterInit()
    {
        $paginator = Paginator\Paginator::factory(range(1, 101));
        $this->assertEquals(101, $paginator->getTotalItemCount());
    }

    public function testLoadsFromConfig()
    {
        Paginator\Paginator::setConfig($this->_config->testing);
        $this->assertEquals('Scrolling', Paginator\Paginator::getDefaultScrollingStyle());

        $broker = Paginator\Paginator::getScrollingStyleBroker();
        $this->assertType('ZendTest\Paginator\TestAsset\ScrollingStyleBroker', $broker);

        $broker = Paginator\Paginator::getAdapterBroker();
        $this->assertType('ZendTest\Paginator\TestAsset\AdapterBroker', $broker);

        $paginator = Paginator\Paginator::factory(range(1, 101));
        $this->assertEquals(3, $paginator->getItemCountPerPage());
        $this->assertEquals(7, $paginator->getPageRange());
    }

    public function testGetsPagesForPageOne()
    {
        $expected = new \stdClass();
        $expected->pageCount        = 11;
        $expected->itemCountPerPage = 10;
        $expected->first            = 1;
        $expected->current          = 1;
        $expected->last             = 11;
        $expected->next             = 2;
        $expected->pagesInRange     = array_combine(range(1, 10), range(1, 10));
        $expected->firstPageInRange = 1;
        $expected->lastPageInRange  = 10;
        $expected->currentItemCount = 10;
        $expected->totalItemCount   = 101;
        $expected->firstItemNumber  = 1;
        $expected->lastItemNumber   = 10;

        $actual = $this->_paginator->getPages();

        $this->assertEquals($expected, $actual);
    }

    public function testGetsPagesForPageTwo()
    {
        $expected = new \stdClass();
        $expected->pageCount        = 11;
        $expected->itemCountPerPage = 10;
        $expected->first            = 1;
        $expected->current          = 2;
        $expected->last             = 11;
        $expected->previous         = 1;
        $expected->next             = 3;
        $expected->pagesInRange     = array_combine(range(1, 10), range(1, 10));
        $expected->firstPageInRange = 1;
        $expected->lastPageInRange  = 10;
        $expected->currentItemCount = 10;
        $expected->totalItemCount   = 101;
        $expected->firstItemNumber  = 11;
        $expected->lastItemNumber   = 20;

        $this->_paginator->setCurrentPageNumber(2);
        $actual = $this->_paginator->getPages();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @todo Why is this one causing a segfault?
     * @group disable
     */
    public function testRendersWithoutPartial()
    {
        $this->_paginator->setView(new View\PhpRenderer());
        $string = @$this->_paginator->__toString();
        $this->assertEquals('', $string);
    }

    public function testRendersWithPartial()
    {
        $view = new View\PhpRenderer();
        $view->resolver()->addPath(__DIR__ . '/_files/scripts');

        Helper\PaginationControl::setDefaultViewPartial('partial.phtml');

        $this->_paginator->setView($view);

        $string = $this->_paginator->__toString();
        $this->assertEquals('partial rendered successfully', $string);
    }

    public function testGetsPageCount()
    {
        $this->assertEquals(11, $this->_paginator->count());
    }

    public function testGetsAndSetsItemCountPerPage()
    {
        Paginator\Paginator::setConfig(new Config\Config(array()));
        $this->_paginator = new Paginator\Paginator(new Adapter\ArrayAdapter(range(1, 101)));
        $this->assertEquals(10, $this->_paginator->getItemCountPerPage());
        $this->_paginator->setItemCountPerPage(15);
        $this->assertEquals(15, $this->_paginator->getItemCountPerPage());
        $this->_paginator->setItemCountPerPage(0);
        $this->assertEquals(101, $this->_paginator->getItemCountPerPage());
        $this->_paginator->setItemCountPerPage(10);
    }

    /**
     * @group ZF-5376
     */
    public function testGetsAndSetsItemCounterPerPageOfNegativeOne()
    {
        Paginator\Paginator::setConfig(new Config\Config(array()));
        $this->_paginator = new Paginator\Paginator(new Paginator\Adapter\ArrayAdapter(range(1, 101)));
        $this->_paginator->setItemCountPerPage(-1);
        $this->assertEquals(101, $this->_paginator->getItemCountPerPage());
        $this->_paginator->setItemCountPerPage(10);
    }

    /**
     * @group ZF-5376
     */
    public function testGetsAndSetsItemCounterPerPageOfZero()
    {
        Paginator\Paginator::setConfig(new Config\Config(array()));
        $this->_paginator = new Paginator\Paginator(new Paginator\Adapter\ArrayAdapter(range(1, 101)));
        $this->_paginator->setItemCountPerPage(0);
        $this->assertEquals(101, $this->_paginator->getItemCountPerPage());
        $this->_paginator->setItemCountPerPage(10);
    }

    /**
     * @group ZF-5376
     */
    public function testGetsAndSetsItemCounterPerPageOfNull()
    {
        Paginator\Paginator::setConfig(new Config\Config(array()));
        $this->_paginator = new Paginator\Paginator(new Paginator\Adapter\ArrayAdapter(range(1, 101)));
        $this->_paginator->setItemCountPerPage();
        $this->assertEquals(101, $this->_paginator->getItemCountPerPage());
        $this->_paginator->setItemCountPerPage(10);
    }

    public function testGetsCurrentItemCount()
    {
        $this->_paginator->setItemCountPerPage(10);
        $this->_paginator->setPageRange(10);

        $this->assertEquals(10, $this->_paginator->getCurrentItemCount());

        $this->_paginator->setCurrentPageNumber(11);

        $this->assertEquals(1, $this->_paginator->getCurrentItemCount());

        $this->_paginator->setCurrentPageNumber(1);
    }

    public function testGetsCurrentItems()
    {
        $items = $this->_paginator->getCurrentItems();
        $this->assertType('ArrayIterator', $items);

        $count = 0;

        foreach ($items as $item) {
            $count++;
        }

        $this->assertEquals(10, $count);
    }

    public function testGetsIterator()
    {
        $items = $this->_paginator->getIterator();
        $this->assertType('ArrayIterator', $items);

        $count = 0;

        foreach ($items as $item) {
            $count++;
        }

        $this->assertEquals(10, $count);
    }

    public function testGetsAndSetsCurrentPageNumber()
    {
        $this->assertEquals(1, $this->_paginator->getCurrentPageNumber());
        $this->_paginator->setCurrentPageNumber(-1);
        $this->assertEquals(1, $this->_paginator->getCurrentPageNumber());
        $this->_paginator->setCurrentPageNumber(11);
        $this->assertEquals(11, $this->_paginator->getCurrentPageNumber());
        $this->_paginator->setCurrentPageNumber(111);
        $this->assertEquals(11, $this->_paginator->getCurrentPageNumber());
        $this->_paginator->setCurrentPageNumber(1);
        $this->assertEquals(1, $this->_paginator->getCurrentPageNumber());
    }

    public function testGetsAbsoluteItemNumber()
    {
        $this->assertEquals(1, $this->_paginator->getAbsoluteItemNumber(1));
        $this->assertEquals(11, $this->_paginator->getAbsoluteItemNumber(1, 2));
        $this->assertEquals(24, $this->_paginator->getAbsoluteItemNumber(4, 3));
    }

    public function testGetsItem()
    {
        $this->assertEquals(1, $this->_paginator->getItem(1));
        $this->assertEquals(11, $this->_paginator->getItem(1, 2));
        $this->assertEquals(24, $this->_paginator->getItem(4, 3));
    }

    public function testThrowsExceptionWhenCollectionIsEmpty()
    {
        $paginator = Paginator\Paginator::factory(array());

        $this->setExpectedException('Zend\Paginator\Exception\InvalidArgumentException', 'Page 1 does not exist');
        $paginator->getItem(1);
    }

    public function testThrowsExceptionWhenRetrievingNonexistentItemFromLastPage()
    {
        $this->setExpectedException('Zend\Paginator\Exception\InvalidArgumentException', 'Page 11 does not contain item number 10');
        $this->_paginator->getItem(10, 11);
    }

    public function testNormalizesPageNumber()
    {
        $this->assertEquals(1, $this->_paginator->normalizePageNumber(0));
        $this->assertEquals(1, $this->_paginator->normalizePageNumber(1));
        $this->assertEquals(2, $this->_paginator->normalizePageNumber(2));
        $this->assertEquals(5, $this->_paginator->normalizePageNumber(5));
        $this->assertEquals(10, $this->_paginator->normalizePageNumber(10));
        $this->assertEquals(11, $this->_paginator->normalizePageNumber(11));
        $this->assertEquals(11, $this->_paginator->normalizePageNumber(12));
    }

    public function testNormalizesItemNumber()
    {
        $this->assertEquals(1, $this->_paginator->normalizeItemNumber(0));
        $this->assertEquals(1, $this->_paginator->normalizeItemNumber(1));
        $this->assertEquals(2, $this->_paginator->normalizeItemNumber(2));
        $this->assertEquals(5, $this->_paginator->normalizeItemNumber(5));
        $this->assertEquals(9, $this->_paginator->normalizeItemNumber(9));
        $this->assertEquals(10, $this->_paginator->normalizeItemNumber(10));
        $this->assertEquals(10, $this->_paginator->normalizeItemNumber(11));
    }

    /**
     * @group ZF-8656
     */
    public function testNormalizesPageNumberWhenGivenAFloat()
    {
        $this->assertEquals(1, $this->_paginator->normalizePageNumber(0.5));
        $this->assertEquals(1, $this->_paginator->normalizePageNumber(1.99));
        $this->assertEquals(2, $this->_paginator->normalizePageNumber(2.3));
        $this->assertEquals(5, $this->_paginator->normalizePageNumber(5.1));
        $this->assertEquals(10, $this->_paginator->normalizePageNumber(10.06));
        $this->assertEquals(11, $this->_paginator->normalizePageNumber(11.5));
        $this->assertEquals(11, $this->_paginator->normalizePageNumber(12.7889));
    }

    /**
     * @group ZF-8656
     */
    public function testNormalizesItemNumberWhenGivenAFloat()
    {
        $this->assertEquals(1, $this->_paginator->normalizeItemNumber(0.5));
        $this->assertEquals(1, $this->_paginator->normalizeItemNumber(1.99));
        $this->assertEquals(2, $this->_paginator->normalizeItemNumber(2.3));
        $this->assertEquals(5, $this->_paginator->normalizeItemNumber(5.1));
        $this->assertEquals(9, $this->_paginator->normalizeItemNumber(9.06));
        $this->assertEquals(10, $this->_paginator->normalizeItemNumber(10.5));
        $this->assertEquals(10, $this->_paginator->normalizeItemNumber(11.7889));
    }

    public function testGetsPagesInSubsetRange()
    {
        $actual = $this->_paginator->getPagesInRange(3, 8);
        $this->assertEquals(array_combine(range(3, 8), range(3, 8)), $actual);
    }

    public function testGetsPagesInOutOfBoundsRange()
    {
        $actual = $this->_paginator->getPagesInRange(-1, 12);
        $this->assertEquals(array_combine(range(1, 11), range(1, 11)), $actual);
    }

    public function testGetsItemsByPage()
    {
        $expected = new \ArrayIterator(range(1, 10));

        $page1 = $this->_paginator->getItemsByPage(1);

        $this->assertEquals($page1, $expected);
        $this->assertEquals($page1, $this->_paginator->getItemsByPage(1));
    }

    public function testGetsItemCount()
    {
        $this->assertEquals(101, $this->_paginator->getItemCount(range(1, 101)));

        $limitIterator = new \LimitIterator(new \ArrayIterator(range(1, 101)));
        $this->assertEquals(101, $this->_paginator->getItemCount($limitIterator));
    }

    public function testGetsViewFromViewRenderer()
    {
        $viewRenderer = $this->broker->load('viewRenderer');
        $viewRenderer->setView(new View\PhpRenderer());

        $this->assertType('Zend\View\Renderer', $this->_paginator->getView());
    }

    public function testGeneratesViewIfNonexistent()
    {
        $this->assertType('Zend\View\Renderer', $this->_paginator->getView());
    }

    public function testGetsAndSetsView()
    {
        $this->_paginator->setView(new View\PhpRenderer());
        $this->assertType('Zend\View\Renderer', $this->_paginator->getView());
    }

    public function testRenders()
    {
        $this->setExpectedException('Zend\View\Exception', 'view partial');
        $this->_paginator->render(new View\PhpRenderer());
    }

    public function testGetsAndSetsPageRange()
    {
        $this->assertEquals(10, $this->_paginator->getPageRange());
        $this->_paginator->setPageRange(15);
        $this->assertEquals(15, $this->_paginator->getPageRange());
    }

    /**
     * @group ZF-3720
     */
    public function testGivesCorrectItemCount()
    {
        $paginator = Paginator\Paginator::factory(range(1, 101));
        $paginator->setCurrentPageNumber(5)
                  ->setItemCountPerPage(5);
        $expected = new \ArrayIterator(range(21, 25));

        $this->assertEquals($expected, $paginator->getCurrentItems());
    }

    /**
     * @group ZF-3737
     */
    public function testKeepsCurrentPageNumberAfterItemCountPerPageSet()
    {
        $paginator = Paginator\Paginator::factory(array('item1', 'item2'));
        $paginator->setCurrentPageNumber(2)
                  ->setItemCountPerPage(1);

        $items = $paginator->getCurrentItems();

        $this->assertEquals('item2', $items[0]);
    }

    /**
     * @group ZF-4193
     */
    public function testCastsIntegerValuesToInteger()
    {
        // Current page number
        $this->_paginator->setCurrentPageNumber(3.3);
        $this->assertTrue($this->_paginator->getCurrentPageNumber() == 3);

        // Item count per page
        $this->_paginator->setItemCountPerPage(3.3);
        $this->assertTrue($this->_paginator->getItemCountPerPage() == 3);

        // Page range
        $this->_paginator->setPageRange(3.3);
        $this->assertTrue($this->_paginator->getPageRange() == 3);
    }

    /**
     * @group ZF-4207
     */
    public function testAcceptsTraversableInstanceFromAdapter()
    {
        $paginator = new Paginator\Paginator(new \ZendTest\Paginator\TestAsset\TestAdapter());
        $this->assertType('ArrayObject', $paginator->getCurrentItems());
    }

    public function testCachedItem()
    {
        $this->_paginator->setCurrentPageNumber(1)->getCurrentItems();
        $this->_paginator->setCurrentPageNumber(2)->getCurrentItems();
        $this->_paginator->setCurrentPageNumber(3)->getCurrentItems();

        $pageItems = $this->_paginator->getPageItemCache();
        $expected = array(
           1 => new \ArrayIterator(range(1, 10)),
           2 => new \ArrayIterator(range(11, 20)),
           3 => new \ArrayIterator(range(21, 30))
        );
        $this->assertEquals($expected, $pageItems);
    }

    public function testClearPageItemCache()
    {
        $this->_paginator->setCurrentPageNumber(1)->getCurrentItems();
        $this->_paginator->setCurrentPageNumber(2)->getCurrentItems();
        $this->_paginator->setCurrentPageNumber(3)->getCurrentItems();

        // clear only page 2 items
        $this->_paginator->clearPageItemCache(2);
        $pageItems = $this->_paginator->getPageItemCache();
        $expected = array(
           1 => new \ArrayIterator(range(1, 10)),
           3 => new \ArrayIterator(range(21, 30))
        );
        $this->assertEquals($expected, $pageItems);

        // clear all
        $this->_paginator->clearPageItemCache();
        $pageItems = $this->_paginator->getPageItemCache();
        $this->assertEquals(array(), $pageItems);
    }

    public function testWithCacheDisabled()
    {
        $this->_paginator->setCacheEnabled(false);
        $this->_paginator->setCurrentPageNumber(1)->getCurrentItems();

        $cachedPageItems = $this->_paginator->getPageItemCache();
        $expected = new \ArrayIterator(range(1, 10));

        $this->assertEquals(array(), $cachedPageItems);

        $pageItems = $this->_paginator->getCurrentItems();

        $this->assertEquals($expected, $pageItems);
    }

    public function testCacheDoesNotDisturbResultsWhenChangingParam()
    {
        $this->_paginator->setCurrentPageNumber(1)->getCurrentItems();
        $pageItems = $this->_paginator->setItemCountPerPage(5)->getCurrentItems();

        $expected = new \ArrayIterator(range(1, 5));
        $this->assertEquals($expected, $pageItems);

        $pageItems = $this->_paginator->getItemsByPage(2);
        $expected = new \ArrayIterator(range(6, 10));
        $this->assertEquals($expected, $pageItems);

        // change the inside Paginator scale
        $pageItems = $this->_paginator->setItemCountPerPage(8)->setCurrentPageNumber(3)->getCurrentItems();

        $pageItems = $this->_paginator->getPageItemCache();
        $expected = array(3 => new \ArrayIterator(range(17, 24)));
        $this->assertEquals($expected, $pageItems);

        // get back to already cached data
        $this->_paginator->setItemCountPerPage(5);
        $pageItems = $this->_paginator->getPageItemCache();
        $expected =array(1 => new \ArrayIterator(range(1, 5)),
                         2 => new \ArrayIterator(range(6, 10)));
        $this->assertEquals($expected, $pageItems);
    }

    public function testToJson()
    {
        $this->_paginator->setCurrentPageNumber(1);

        $json = $this->_paginator->toJson();

        $expected = '"0":1,"1":2,"2":3,"3":4,"4":5,"5":6,"6":7,"7":8,"8":9,"9":10';

        $this->assertContains($expected, $json);
    }

    // ZF-5519
    public function testFilter()
    {
        $filter = new \Zend\Filter\Callback(array($this, 'filterCallback'));
        $paginator = Paginator\Paginator::factory(range(1, 10));
        $paginator->setFilter($filter);

        $page = $paginator->getCurrentItems();

        $this->assertEquals(new \ArrayIterator(range(10, 100, 10)), $page);
    }

    public function filterCallback($value)
    {
        $data = array();

        foreach ($value as $number) {
            $data[] = ($number * 10);
        }

        return $data;
    }

    /**
     * @group ZF-5785
     */
    public function testGetSetDefaultItemCountPerPage()
    {
        Paginator\Paginator::setConfig(new Config\Config(array()));

        $paginator = Paginator\Paginator::factory(range(1, 10));
        $this->assertEquals(10, $paginator->getItemCountPerPage());

        Paginator\Paginator::setDefaultItemCountPerPage(20);
        $this->assertEquals(20, Paginator\Paginator::getDefaultItemCountPerPage());

        $paginator = Paginator\Paginator::factory(range(1, 10));
        $this->assertEquals(20, $paginator->getItemCountPerPage());

        $this->_restorePaginatorDefaults();
    }

    /**
     * @group ZF-7207
     */
    public function testItemCountPerPageByDefault()
    {
        $paginator = Paginator\Paginator::factory(range(1,20));
        $this->assertEquals(2, $paginator->count());
    }

    /**
     * @group ZF-5427
     */
    public function testNegativeItemNumbers()
    {
        $this->assertEquals(10, $this->_paginator->getItem(-1, 1));
        $this->assertEquals(9, $this->_paginator->getItem(-2, 1));
        $this->assertEquals(101, $this->_paginator->getItem(-1, -1));
    }

    /**
     * @group ZF-7602
     */
    public function testAcceptAndHandlePaginatorAdapterAggregateDataInFactory()
    {
        $p = Paginator\Paginator::factory(new TestArrayAggregate());

        $this->assertEquals(1, count($p));
        $this->assertType('Zend\Paginator\Adapter\ArrayAdapter', $p->getAdapter());
        $this->assertEquals(4, count($p->getAdapter()));
    }

    /**
     * @group ZF-7602
     */
    public function testAcceptAndHandlePaginatorAdapterAggreageInConstructor()
    {
        $p = new Paginator\Paginator(new TestArrayAggregate());

        $this->assertEquals(1, count($p));
        $this->assertType('Zend\Paginator\Adapter\ArrayAdapter', $p->getAdapter());
        $this->assertEquals(4, count($p->getAdapter()));
    }

    /**
     * @group ZF-7602
     */
    public function testInvalidDataInConstructor_ThrowsException()
    {
        $this->setExpectedException("Zend\Paginator\Exception");

        $p = new Paginator\Paginator(array());
    }

    /**
     * @group ZF-9396
     */
    public function testArrayAccessInClassSerializableLimitIterator()
    {
        $iterator  = new \ArrayIterator(array('zf9396', 'foo', null));
        $paginator = Paginator\Paginator::factory($iterator);

        $this->assertEquals('zf9396', $paginator->getItem(1));

        $items = $paginator->getAdapter()
                           ->getItems(0, 10);

        $this->assertEquals('foo', $items[1]);
        $this->assertEquals(0, $items->key());
        $this->assertFalse(isset($items[2]));
        $this->assertTrue(isset($items[1]));
        $this->assertFalse(isset($items[3]));
        $this->assertEquals(0, $items->key());
    }
}

class TestArrayAggregate implements Paginator\AdapterAggregate
{
    public function getPaginatorAdapter()
    {
        return new Adapter\ArrayAdapter(array(1, 2, 3, 4));
    }
}
