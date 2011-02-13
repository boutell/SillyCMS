<?php

namespace Doctrine\Tests\ORM\Functional;

use Doctrine\Tests\Models\CMS\CmsUser,
    Doctrine\Tests\Models\CMS\CmsArticle;

require_once __DIR__ . '/../../TestInit.php';

/**
 * Functional Query tests.
 *
 * @author robo
 */
class QueryTest extends \Doctrine\Tests\OrmFunctionalTestCase
{
    protected function setUp()
    {
        $this->useModelSet('cms');
        parent::setUp();
    }

    public function testSimpleQueries()
    {
        $user = new CmsUser;
        $user->name = 'Guilherme';
        $user->username = 'gblanco';
        $user->status = 'developer';
        $this->_em->persist($user);
        $this->_em->flush();
        $this->_em->clear();

        $query = $this->_em->createQuery("select u, upper(u.name) from Doctrine\Tests\Models\CMS\CmsUser u where u.username = 'gblanco'");
        
        $result = $query->getResult();
        
        $this->assertEquals(1, count($result));
        $this->assertTrue($result[0][0] instanceof CmsUser);
        $this->assertEquals('Guilherme', $result[0][0]->name);
        $this->assertEquals('gblanco', $result[0][0]->username);
        $this->assertEquals('developer', $result[0][0]->status);
        $this->assertEquals('GUILHERME', $result[0][1]);

        $resultArray = $query->getArrayResult();
        $this->assertEquals(1, count($resultArray));
        $this->assertTrue(is_array($resultArray[0][0]));
        $this->assertEquals('Guilherme', $resultArray[0][0]['name']);
        $this->assertEquals('gblanco', $resultArray[0][0]['username']);
        $this->assertEquals('developer', $resultArray[0][0]['status']);
        $this->assertEquals('GUILHERME', $resultArray[0][1]);

        $scalarResult = $query->getScalarResult();
        $this->assertEquals(1, count($scalarResult));
        $this->assertEquals('Guilherme', $scalarResult[0]['u_name']);
        $this->assertEquals('gblanco', $scalarResult[0]['u_username']);
        $this->assertEquals('developer', $scalarResult[0]['u_status']);
        $this->assertEquals('GUILHERME', $scalarResult[0][1]);

        $query = $this->_em->createQuery("select upper(u.name) from Doctrine\Tests\Models\CMS\CmsUser u where u.username = 'gblanco'");
        $this->assertEquals('GUILHERME', $query->getSingleScalarResult());
    }

    public function testJoinQueries()
    {
        $user = new CmsUser;
        $user->name = 'Guilherme';
        $user->username = 'gblanco';
        $user->status = 'developer';

        $article1 = new CmsArticle;
        $article1->topic = "Doctrine 2";
        $article1->text = "This is an introduction to Doctrine 2.";
        $user->addArticle($article1);

        $article2 = new CmsArticle;
        $article2->topic = "Symfony 2";
        $article2->text = "This is an introduction to Symfony 2.";
        $user->addArticle($article2);

        $this->_em->persist($user);
        $this->_em->persist($article1);
        $this->_em->persist($article2);

        $this->_em->flush();
        $this->_em->clear();

        $query = $this->_em->createQuery("select u, a from Doctrine\Tests\Models\CMS\CmsUser u join u.articles a");
        $users = $query->getResult();
        $this->assertEquals(1, count($users));
        $this->assertTrue($users[0] instanceof CmsUser);
        $this->assertEquals(2, count($users[0]->articles));
        $this->assertEquals('Doctrine 2', $users[0]->articles[0]->topic);
        $this->assertEquals('Symfony 2', $users[0]->articles[1]->topic);
    }

    public function testUsingUnknownQueryParameterShouldThrowException()
    {
        $this->setExpectedException(
            "Doctrine\ORM\Query\QueryException",
            "Invalid parameter: token 2 is not defined in the query."
        );

        $q = $this->_em->createQuery('SELECT u FROM Doctrine\Tests\Models\CMS\CmsUser u WHERE u.name = ?1');
        $q->setParameter(2, 'jwage');
        $user = $q->getSingleResult();
    }

    public function testMismatchingParamExpectedParamCount()
    {
        $this->setExpectedException(
            "Doctrine\ORM\Query\QueryException",
            "Invalid parameter number: number of bound variables does not match number of tokens"
        );

        $q = $this->_em->createQuery('SELECT u FROM Doctrine\Tests\Models\CMS\CmsUser u WHERE u.name = ?1');
        $q->setParameter(1, 'jwage');
        $q->setParameter(2, 'jwage');

        $user = $q->getSingleResult();
    }

    public function testInvalidInputParameterThrowsException()
    {
        $this->setExpectedException("Doctrine\ORM\Query\QueryException");

        $q = $this->_em->createQuery('SELECT u FROM Doctrine\Tests\Models\CMS\CmsUser u WHERE u.name = ?');
        $q->setParameter(1, 'jwage');
        $user = $q->getSingleResult();
    }

    public function testSetParameters()
    {
        $q = $this->_em->createQuery('SELECT u FROM Doctrine\Tests\Models\CMS\CmsUser u WHERE u.name = ?1 AND u.status = ?2');
        $q->setParameters(array(1 => 'jwage', 2 => 'active'));
        $users = $q->getResult();
    }

    public function testIterateResult_IterativelyBuildUpUnitOfWork()
    {
        $article1 = new CmsArticle;
        $article1->topic = "Doctrine 2";
        $article1->text = "This is an introduction to Doctrine 2.";

        $article2 = new CmsArticle;
        $article2->topic = "Symfony 2";
        $article2->text = "This is an introduction to Symfony 2.";

        $this->_em->persist($article1);
        $this->_em->persist($article2);

        $this->_em->flush();
        $this->_em->clear();

        $query = $this->_em->createQuery("select a from Doctrine\Tests\Models\CMS\CmsArticle a");
        $articles = $query->iterate();

        $iteratedCount = 0;
        $topics = array();
        foreach($articles AS $row) {
            $article = $row[0];
            $topics[] = $article->topic;

            $identityMap = $this->_em->getUnitOfWork()->getIdentityMap();
            $identityMapCount = count($identityMap['Doctrine\Tests\Models\CMS\CmsArticle']);
            $this->assertTrue($identityMapCount>$iteratedCount);
            
            $iteratedCount++;
        }

        $this->assertEquals(array("Doctrine 2", "Symfony 2"), $topics);
        $this->assertEquals(2, $iteratedCount);

        $this->_em->flush();
        $this->_em->clear();
    }

    /**
     * @expectedException \Doctrine\ORM\Query\QueryException
     */
    public function testIterateResult_FetchJoinedCollection_ThrowsException()
    {
        $query = $this->_em->createQuery("SELECT u, a FROM Doctrine\Tests\Models\CMS\CmsUser u JOIN u.articles a");
        $articles = $query->iterate();
    }
    
    /**
     * @expectedException Doctrine\ORM\NoResultException
     */
    public function testGetSingleResultThrowsExceptionOnNoResult()
    {
        $this->_em->createQuery("select a from Doctrine\Tests\Models\CMS\CmsArticle a")
             ->getSingleResult();
    }

    /**
     * @expectedException Doctrine\ORM\NoResultException
     */
    public function testGetSingleScalarResultThrowsExceptionOnNoResult()
    {
        $this->_em->createQuery("select a from Doctrine\Tests\Models\CMS\CmsArticle a")
             ->getSingleScalarResult();
    }

    /**
     * @expectedException Doctrine\ORM\NonUniqueResultException
     */
    public function testGetSingleScalarResultThrowsExceptionOnNonUniqueResult()
    {
        $user = new CmsUser;
        $user->name = 'Guilherme';
        $user->username = 'gblanco';
        $user->status = 'developer';

        $article1 = new CmsArticle;
        $article1->topic = "Doctrine 2";
        $article1->text = "This is an introduction to Doctrine 2.";
        $user->addArticle($article1);

        $article2 = new CmsArticle;
        $article2->topic = "Symfony 2";
        $article2->text = "This is an introduction to Symfony 2.";
        $user->addArticle($article2);

        $this->_em->persist($user);
        $this->_em->persist($article1);
        $this->_em->persist($article2);

        $this->_em->flush();
        $this->_em->clear();

        $this->_em->createQuery("select a from Doctrine\Tests\Models\CMS\CmsArticle a")
             ->getSingleScalarResult();
    }

    public function testModifiedLimitQuery()
    {
        for ($i = 0; $i < 5; $i++) {
            $user = new CmsUser;
            $user->name = 'Guilherme' . $i;
            $user->username = 'gblanco' . $i;
            $user->status = 'developer';
            $this->_em->persist($user);
        }

        $this->_em->flush();
        $this->_em->clear();

        $data = $this->_em->createQuery('SELECT u FROM Doctrine\Tests\Models\CMS\CmsUser u')
                  ->setFirstResult(1)
                  ->setMaxResults(2)
                  ->getResult();

        $this->assertEquals(2, count($data));
        $this->assertEquals('gblanco1', $data[0]->username);
        $this->assertEquals('gblanco2', $data[1]->username);

        $data = $this->_em->createQuery('SELECT u FROM Doctrine\Tests\Models\CMS\CmsUser u')
                  ->setFirstResult(3)
                  ->setMaxResults(2)
                  ->getResult();

        $this->assertEquals(2, count($data));
        $this->assertEquals('gblanco3', $data[0]->username);
        $this->assertEquals('gblanco4', $data[1]->username);

        $data = $this->_em->createQuery('SELECT u FROM Doctrine\Tests\Models\CMS\CmsUser u')
                  ->setFirstResult(3)
                  ->setMaxResults(2)
                  ->getScalarResult();
    }

    public function testSupportsQueriesWithEntityNamespaces()
    {
        $this->_em->getConfiguration()->addEntityNamespace('CMS', 'Doctrine\Tests\Models\CMS');

        try {
            $query = $this->_em->createQuery('UPDATE CMS:CmsUser u SET u.name = ?1');
            $this->assertEquals('UPDATE cms_users SET name = ?', $query->getSql());
            $query->free();
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }

        $this->_em->getConfiguration()->setEntityNamespaces(array());
    }

    /**
     * @group DDC-604
     */
    public function testEntityParameters()
    {
        $article = new CmsArticle;
        $article->topic = "dr. dolittle";
        $article->text = "Once upon a time ...";
        $author = new CmsUser;
        $author->name = "anonymous";
        $author->username = "anon";
        $author->status = "here";
        $article->user = $author;
        $this->_em->persist($author);
        $this->_em->persist($article);
        $this->_em->flush();
        $this->_em->clear();
        //$this->_em->getConnection()->getConfiguration()->setSQLLogger(new \Doctrine\DBAL\Logging\EchoSQLLogger);
        $q = $this->_em->createQuery("select a from Doctrine\Tests\Models\CMS\CmsArticle a where a.topic = :topic and a.user = :user")
                ->setParameter("user", $this->_em->getReference('Doctrine\Tests\Models\CMS\CmsUser', $author->id))
                ->setParameter("topic", "dr. dolittle");

        $result = $q->getResult();
        $this->assertEquals(1, count($result));
        $this->assertTrue($result[0] instanceof CmsArticle);
        $this->assertEquals("dr. dolittle", $result[0]->topic);
        $this->assertTrue($result[0]->user instanceof \Doctrine\ORM\Proxy\Proxy);
        $this->assertFalse($result[0]->user->__isInitialized__);
    }
}