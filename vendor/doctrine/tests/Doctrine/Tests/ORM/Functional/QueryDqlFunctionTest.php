<?php

namespace Doctrine\Tests\ORM\Functional;

use Doctrine\Tests\Models\Company\CompanyManager;

require_once __DIR__ . '/../../TestInit.php';

/**
 * Functional Query tests.
 *
 * @author robo
 */
class QueryDqlFunctionTest extends \Doctrine\Tests\OrmFunctionalTestCase
{
    protected function setUp()
    {
        $this->useModelSet('company');
        parent::setUp();

        $this->generateFixture();
    }

    public function testAggregateSum()
    {
        $salarySum = $this->_em->createQuery('SELECT SUM(m.salary) AS salary FROM Doctrine\Tests\Models\Company\CompanyManager m')
                               ->getSingleResult();

        $this->assertEquals(1500000, $salarySum['salary']);
    }

    public function testAggregateAvg()
    {
        $salaryAvg = $this->_em->createQuery('SELECT AVG(m.salary) AS salary FROM Doctrine\Tests\Models\Company\CompanyManager m')
                               ->getSingleResult();

        $this->assertEquals(375000, round($salaryAvg['salary'], 0));
    }

    public function testAggregateMin()
    {
        $salary = $this->_em->createQuery('SELECT MIN(m.salary) AS salary FROM Doctrine\Tests\Models\Company\CompanyManager m')
                               ->getSingleResult();

        $this->assertEquals(100000, $salary['salary']);
    }

    public function testAggregateMax()
    {
        $salary = $this->_em->createQuery('SELECT MAX(m.salary) AS salary FROM Doctrine\Tests\Models\Company\CompanyManager m')
                               ->getSingleResult();

        $this->assertEquals(800000, $salary['salary']);
    }

    public function testAggregateCount()
    {
        $managerCount = $this->_em->createQuery('SELECT COUNT(m.id) AS managers FROM Doctrine\Tests\Models\Company\CompanyManager m')
                               ->getSingleResult();

        $this->assertEquals(4, $managerCount['managers']);
    }

    public function testFunctionAbs()
    {
        $result = $this->_em->createQuery('SELECT m, ABS(m.salary * -1) AS abs FROM Doctrine\Tests\Models\Company\CompanyManager m')
                         ->getResult();

        $this->assertEquals(4, count($result));
        $this->assertEquals(100000, $result[0]['abs']);
        $this->assertEquals(200000, $result[1]['abs']);
        $this->assertEquals(400000, $result[2]['abs']);
        $this->assertEquals(800000, $result[3]['abs']);
    }

    public function testFunctionConcat()
    {
        $arg = $this->_em->createQuery('SELECT m, CONCAT(m.name, m.department) AS namedep FROM Doctrine\Tests\Models\Company\CompanyManager m')
                         ->getArrayResult();

        $this->assertEquals(4, count($arg));
        $this->assertEquals('Roman B.IT', $arg[0]['namedep']);
        $this->assertEquals('Benjamin E.HR', $arg[1]['namedep']);
        $this->assertEquals('Guilherme B.Complaint Department', $arg[2]['namedep']);
        $this->assertEquals('Jonathan W.Administration', $arg[3]['namedep']);
    }

    public function testFunctionLength()
    {
        $result = $this->_em->createQuery('SELECT m, LENGTH(CONCAT(m.name, m.department)) AS namedeplength FROM Doctrine\Tests\Models\Company\CompanyManager m')
                         ->getArrayResult();

        $this->assertEquals(4, count($result));
        $this->assertEquals(10, $result[0]['namedeplength']);
        $this->assertEquals(13, $result[1]['namedeplength']);
        $this->assertEquals(32, $result[2]['namedeplength']);
        $this->assertEquals(25, $result[3]['namedeplength']);
    }

    public function testFunctionLocate()
    {
        $dql = "SELECT m, LOCATE('e', LOWER(m.name)) AS loc, LOCATE('e', LOWER(m.name), 7) AS loc2 ".
               "FROM Doctrine\Tests\Models\Company\CompanyManager m";

        $result = $this->_em->createQuery($dql)
                         ->getArrayResult();

        $this->assertEquals(4, count($result));
        $this->assertEquals(0, $result[0]['loc']);
        $this->assertEquals(2, $result[1]['loc']);
        $this->assertEquals(6, $result[2]['loc']);
        $this->assertEquals(0, $result[3]['loc']);
        $this->assertEquals(0, $result[0]['loc2']);
        $this->assertEquals(10, $result[1]['loc2']);
        $this->assertEquals(9, $result[2]['loc2']);
        $this->assertEquals(0, $result[3]['loc2']);
    }

    public function testFunctionLower()
    {
        $result = $this->_em->createQuery("SELECT m, LOWER(m.name) AS lowername FROM Doctrine\Tests\Models\Company\CompanyManager m")
                         ->getArrayResult();

        $this->assertEquals(4, count($result));
        $this->assertEquals('roman b.', $result[0]['lowername']);
        $this->assertEquals('benjamin e.', $result[1]['lowername']);
        $this->assertEquals('guilherme b.', $result[2]['lowername']);
        $this->assertEquals('jonathan w.', $result[3]['lowername']);
    }

    public function testFunctionMod()
    {
        $result = $this->_em->createQuery("SELECT m, MOD(m.salary, 3500) AS amod FROM Doctrine\Tests\Models\Company\CompanyManager m")
                         ->getArrayResult();

        $this->assertEquals(4, count($result));
        $this->assertEquals(2000, $result[0]['amod']);
        $this->assertEquals(500, $result[1]['amod']);
        $this->assertEquals(1000, $result[2]['amod']);
        $this->assertEquals(2000, $result[3]['amod']);
    }

    public function testFunctionSqrt()
    {
        $result = $this->_em->createQuery("SELECT m, SQRT(m.salary) AS sqrtsalary FROM Doctrine\Tests\Models\Company\CompanyManager m")
                         ->getArrayResult();

        $this->assertEquals(4, count($result));
        $this->assertEquals(316, round($result[0]['sqrtsalary']));
        $this->assertEquals(447,  round($result[1]['sqrtsalary']));
        $this->assertEquals(632, round($result[2]['sqrtsalary']));
        $this->assertEquals(894, round($result[3]['sqrtsalary']));
    }

    public function testFunctionUpper()
    {
        $result = $this->_em->createQuery("SELECT m, UPPER(m.name) AS uppername FROM Doctrine\Tests\Models\Company\CompanyManager m")
                         ->getArrayResult();

        $this->assertEquals(4, count($result));
        $this->assertEquals('ROMAN B.', $result[0]['uppername']);
        $this->assertEquals('BENJAMIN E.', $result[1]['uppername']);
        $this->assertEquals('GUILHERME B.', $result[2]['uppername']);
        $this->assertEquals('JONATHAN W.', $result[3]['uppername']);
    }

    public function testFunctionSubstring()
    {
        $dql = "SELECT m, SUBSTRING(m.name, 1, 3) AS str1, SUBSTRING(m.name, 5) AS str2 ".
                "FROM Doctrine\Tests\Models\Company\CompanyManager m";

        $result = $this->_em->createQuery($dql)
                         ->getArrayResult();

        $this->assertEquals(4, count($result));
        $this->assertEquals('Rom', $result[0]['str1']);
        $this->assertEquals('Ben', $result[1]['str1']);
        $this->assertEquals('Gui', $result[2]['str1']);
        $this->assertEquals('Jon', $result[3]['str1']);
        $this->assertEquals('n B.', $result[0]['str2']);
        $this->assertEquals('amin E.', $result[1]['str2']);
        $this->assertEquals('herme B.', $result[2]['str2']);
        $this->assertEquals('than W.', $result[3]['str2']);
    }

    public function testFunctionTrim()
    {
        $dql = "SELECT m, TRIM(TRAILING '.' FROM m.name) AS str1, ".
               " TRIM(LEADING '.' FROM m.name) AS str2, TRIM(CONCAT(' ', CONCAT(m.name, ' '))) AS str3 ".
               "FROM Doctrine\Tests\Models\Company\CompanyManager m";

        $result = $this->_em->createQuery($dql)->getArrayResult();

        $this->assertEquals(4, count($result));
        $this->assertEquals('Roman B', $result[0]['str1']);
        $this->assertEquals('Benjamin E', $result[1]['str1']);
        $this->assertEquals('Guilherme B', $result[2]['str1']);
        $this->assertEquals('Jonathan W', $result[3]['str1']);
        $this->assertEquals('Roman B.', $result[0]['str2']);
        $this->assertEquals('Benjamin E.', $result[1]['str2']);
        $this->assertEquals('Guilherme B.', $result[2]['str2']);
        $this->assertEquals('Jonathan W.', $result[3]['str2']);
        $this->assertEquals('Roman B.', $result[0]['str3']);
        $this->assertEquals('Benjamin E.', $result[1]['str3']);
        $this->assertEquals('Guilherme B.', $result[2]['str3']);
        $this->assertEquals('Jonathan W.', $result[3]['str3']);
    }

    public function testOperatorAdd()
    {
        $result = $this->_em->createQuery('SELECT m, m.salary+2500 AS add FROM Doctrine\Tests\Models\Company\CompanyManager m')
                ->getResult();
        
        $this->assertEquals(4, count($result));
        $this->assertEquals(102500, $result[0]['add']);
        $this->assertEquals(202500, $result[1]['add']);
        $this->assertEquals(402500, $result[2]['add']);
        $this->assertEquals(802500, $result[3]['add']);
    }

    public function testOperatorSub()
    {
        $result = $this->_em->createQuery('SELECT m, m.salary-2500 AS sub FROM Doctrine\Tests\Models\Company\CompanyManager m')
                ->getResult();

        $this->assertEquals(4, count($result));
        $this->assertEquals(97500, $result[0]['sub']);
        $this->assertEquals(197500, $result[1]['sub']);
        $this->assertEquals(397500, $result[2]['sub']);
        $this->assertEquals(797500, $result[3]['sub']);
    }

    public function testOperatorMultiply()
    {
        $result = $this->_em->createQuery('SELECT m, m.salary*2 AS op FROM Doctrine\Tests\Models\Company\CompanyManager m')
                ->getResult();

        $this->assertEquals(4, count($result));
        $this->assertEquals(200000, $result[0]['op']);
        $this->assertEquals(400000, $result[1]['op']);
        $this->assertEquals(800000, $result[2]['op']);
        $this->assertEquals(1600000, $result[3]['op']);
    }

    /**
     * @group test
     */
    public function testOperatorDiv()
    {
        $result = $this->_em->createQuery('SELECT m, (m.salary/0.5) AS op FROM Doctrine\Tests\Models\Company\CompanyManager m')
                ->getResult();

        $this->assertEquals(4, count($result));
        $this->assertEquals(200000, $result[0]['op']);
        $this->assertEquals(400000, $result[1]['op']);
        $this->assertEquals(800000, $result[2]['op']);
        $this->assertEquals(1600000, $result[3]['op']);
    }
    
    public function testConcatFunction()
    {
        $arg = $this->_em->createQuery('SELECT CONCAT(m.name, m.department) AS namedep FROM Doctrine\Tests\Models\Company\CompanyManager m order by namedep desc')
                ->getArrayResult();

        $this->assertEquals(4, count($arg));
        $this->assertEquals('Roman B.IT', $arg[0]['namedep']);
        $this->assertEquals('Jonathan W.Administration', $arg[1]['namedep']);
        $this->assertEquals('Guilherme B.Complaint Department', $arg[2]['namedep']);
        $this->assertEquals('Benjamin E.HR', $arg[3]['namedep']);
    }
    

    protected function generateFixture()
    {
        $manager1 = new CompanyManager();
        $manager1->setName('Roman B.');
        $manager1->setTitle('Foo');
        $manager1->setDepartment('IT');
        $manager1->setSalary(100000);

        $manager2 = new CompanyManager();
        $manager2->setName('Benjamin E.');
        $manager2->setTitle('Foo');
        $manager2->setDepartment('HR');
        $manager2->setSalary(200000);

        $manager3 = new CompanyManager();
        $manager3->setName('Guilherme B.');
        $manager3->setTitle('Foo');
        $manager3->setDepartment('Complaint Department');
        $manager3->setSalary(400000);

        $manager4 = new CompanyManager();
        $manager4->setName('Jonathan W.');
        $manager4->setTitle('Foo');
        $manager4->setDepartment('Administration');
        $manager4->setSalary(800000);

        $this->_em->persist($manager1);
        $this->_em->persist($manager2);
        $this->_em->persist($manager3);
        $this->_em->persist($manager4);
        $this->_em->flush();
        $this->_em->clear();
    }
}