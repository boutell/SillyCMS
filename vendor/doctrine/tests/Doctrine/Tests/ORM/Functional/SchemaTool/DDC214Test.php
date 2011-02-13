<?php

namespace Doctrine\Tests\ORM\Functional\SchemaTool;

use Doctrine\ORM\Tools;


require_once __DIR__ . '/../../../TestInit.php';

/**
 * WARNING: This test should be run as last test! It can affect others very easily!
 */
class DDC214Test extends \Doctrine\Tests\OrmFunctionalTestCase
{
    private $classes = array();
    private $schemaTool = null;

    public function setUp() {
        parent::setUp();

        $conn = $this->_em->getConnection();

        if (strpos($conn->getDriver()->getName(), "sqlite") !== false) {
            $this->markTestSkipped('SQLite does not support ALTER TABLE statements.');
        }
        $this->schemaTool = new Tools\SchemaTool($this->_em);
    }

    /**
     * @group DDC-214
     */
    public function testCmsAddressModel()
    {
        $this->classes = array(
            'Doctrine\Tests\Models\CMS\CmsUser',
            'Doctrine\Tests\Models\CMS\CmsPhonenumber',
            'Doctrine\Tests\Models\CMS\CmsAddress',
            'Doctrine\Tests\Models\CMS\CmsGroup',
            'Doctrine\Tests\Models\CMS\CmsArticle'
        );

        $this->assertCreatedSchemaNeedsNoUpdates($this->classes);
    }

    /**
     * @group DDC-214
     */
    public function testCompanyModel()
    {
        $this->classes = array(
            'Doctrine\Tests\Models\Company\CompanyPerson',
            'Doctrine\Tests\Models\Company\CompanyEmployee',
            'Doctrine\Tests\Models\Company\CompanyManager',
            'Doctrine\Tests\Models\Company\CompanyOrganization',
            'Doctrine\Tests\Models\Company\CompanyEvent',
            'Doctrine\Tests\Models\Company\CompanyAuction',
            'Doctrine\Tests\Models\Company\CompanyRaffle',
            'Doctrine\Tests\Models\Company\CompanyCar'
        );

        $this->assertCreatedSchemaNeedsNoUpdates($this->classes);
    }

    public function assertCreatedSchemaNeedsNoUpdates($classes)
    {
        $classMetadata = array();
        foreach ($classes AS $class) {
            $classMetadata[] = $this->_em->getClassMetadata($class);
        }

        $this->schemaTool->dropDatabase();
        $this->schemaTool->createSchema($classMetadata);

        $sm = $this->_em->getConnection()->getSchemaManager();

        $fromSchema = $sm->createSchema();
        $toSchema = $this->schemaTool->getSchemaFromMetadata($classMetadata);

        $comparator = new \Doctrine\DBAL\Schema\Comparator();
        $schemaDiff = $comparator->compare($fromSchema, $toSchema);

        $sql = $schemaDiff->toSql($this->_em->getConnection()->getDatabasePlatform());
        $this->assertEquals(0, count($sql), "SQL: " . implode(PHP_EOL, $sql));
    }
}