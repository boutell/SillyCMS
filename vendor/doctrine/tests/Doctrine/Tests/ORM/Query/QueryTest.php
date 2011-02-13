<?php

namespace Doctrine\Tests\ORM\Query;

require_once __DIR__ . '/../../TestInit.php';

class QueryTest extends \Doctrine\Tests\OrmTestCase
{
    protected $_em = null;

    protected function setUp()
    {
        $this->_em = $this->_getTestEntityManager();
    }

    /**
     * @expectedException Doctrine\ORM\Query\QueryException
     */
    public function testParameterIndexZeroThrowsException()
    {
        $query = $this->_em->createQuery("select u from Doctrine\Tests\Models\CMS\CmsUser u where u.username = ?1");
        $query->execute(array(42)); // same as array(0 => 42), 0 is invalid parameter position
    }

    public function testGetParameters()
    {
        $query = $this->_em->createQuery("select u from Doctrine\Tests\Models\CMS\CmsUser u where u.username = ?1");
        $this->assertEquals(array(), $query->getParameters());
    }

    public function testGetParameters_HasSomeAlready()
    {
        $query = $this->_em->createQuery("select u from Doctrine\Tests\Models\CMS\CmsUser u where u.username = ?1");
        $query->setParameter(2, 84);
        $this->assertEquals(array(2 => 84), $query->getParameters());
    }

    public function testSetParameters()
    {
        $query = $this->_em->createQuery("select u from Doctrine\Tests\Models\CMS\CmsUser u where u.username = ?1");
        $query->setParameters(array(1 => 'foo', 2 => 'bar'));
        $this->assertEquals(array(1 => 'foo', 2 => 'bar'), $query->getParameters());
    }

    public function testFree()
    {
        $query = $this->_em->createQuery("select u from Doctrine\Tests\Models\CMS\CmsUser u where u.username = ?1");
        $query->setParameter(2, 84, \PDO::PARAM_INT);

        $query->free();

        $this->assertEquals(array(), $query->getParameters());
    }

    public function testClone()
    {
        $dql = "select u from Doctrine\Tests\Models\CMS\CmsUser u where u.username = ?1";

        $query = $this->_em->createQuery($dql);
        $query->setParameter(2, 84, \PDO::PARAM_INT);
        $query->setHint('foo', 'bar');

        $cloned = clone $query;

        $this->assertEquals($dql, $cloned->getDql());
        $this->assertEquals(array(), $cloned->getParameters());
        $this->assertFalse($cloned->getHint('foo'));
    }

    public function testFluentQueryInterface()
    {
        $q = $this->_em->createQuery("select a from Doctrine\Tests\Models\CMS\CmsArticle a");
        $q2 = $q->expireQueryCache(true)
          ->setQueryCacheLifetime(3600)
          ->setQueryCacheDriver(null)
          ->expireResultCache(true)
          ->setHint('foo', 'bar')
          ->setHint('bar', 'baz')
          ->setParameter(1, 'bar')
          ->setParameters(array(2 => 'baz'))
          ->setResultCacheDriver(null)
          ->setResultCacheId('foo')
          ->setDql('foo')
          ->setFirstResult(10)
          ->setMaxResults(10);

        $this->assertSame($q2, $q);
    }
}