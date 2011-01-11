<?php

namespace Doctrine\Tests\ORM\Functional\SchemaTool;

use Doctrine\ORM\Tools\SchemaTool,
    Doctrine\ORM\Mapping\ClassMetadata;

require_once __DIR__ . '/../../../TestInit.php';

class MySqlSchemaToolTest extends \Doctrine\Tests\OrmFunctionalTestCase
{
    protected function setUp() {
        parent::setUp();
        if ($this->_em->getConnection()->getDatabasePlatform()->getName() !== 'mysql') {
            $this->markTestSkipped('The ' . __CLASS__ .' requires the use of mysql.');
        }
    }
    
    public function testGetCreateSchemaSql()
    {
        $classes = array(
            $this->_em->getClassMetadata('Doctrine\Tests\Models\CMS\CmsAddress'),
            $this->_em->getClassMetadata('Doctrine\Tests\Models\CMS\CmsUser'),
            $this->_em->getClassMetadata('Doctrine\Tests\Models\CMS\CmsPhonenumber'),
        );

        $tool = new SchemaTool($this->_em);
        $sql = $tool->getCreateSchemaSql($classes);
        $this->assertEquals("CREATE TABLE cms_addresses (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, country VARCHAR(50) NOT NULL, zip VARCHAR(50) NOT NULL, city VARCHAR(50) NOT NULL, UNIQUE INDEX cms_addresses_user_id_uniq (user_id), PRIMARY KEY(id)) ENGINE = InnoDB", $sql[0]);
        $this->assertEquals("CREATE TABLE cms_users (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(50) NOT NULL, username VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX cms_users_username_uniq (username), PRIMARY KEY(id)) ENGINE = InnoDB", $sql[1]);
        $this->assertEquals("CREATE TABLE cms_users_groups (user_id INT NOT NULL, group_id INT NOT NULL, INDEX cms_users_groups_user_id_idx (user_id), INDEX cms_users_groups_group_id_idx (group_id), PRIMARY KEY(user_id, group_id)) ENGINE = InnoDB", $sql[2]);
        $this->assertEquals("CREATE TABLE cms_phonenumbers (phonenumber VARCHAR(50) NOT NULL, user_id INT DEFAULT NULL, INDEX cms_phonenumbers_user_id_idx (user_id), PRIMARY KEY(phonenumber)) ENGINE = InnoDB", $sql[3]);
        $this->assertEquals("ALTER TABLE cms_addresses ADD FOREIGN KEY (user_id) REFERENCES cms_users(id)", $sql[4]);
        $this->assertEquals("ALTER TABLE cms_users_groups ADD FOREIGN KEY (user_id) REFERENCES cms_users(id)", $sql[5]);
        $this->assertEquals("ALTER TABLE cms_users_groups ADD FOREIGN KEY (group_id) REFERENCES cms_groups(id)", $sql[6]);
        $this->assertEquals("ALTER TABLE cms_phonenumbers ADD FOREIGN KEY (user_id) REFERENCES cms_users(id)", $sql[7]);
        
        $this->assertEquals(8, count($sql));
    }
    
    public function testGetCreateSchemaSql2()
    {
        $classes = array(
            $this->_em->getClassMetadata('Doctrine\Tests\Models\Generic\DecimalModel')
        );

        $tool = new SchemaTool($this->_em);
        $sql = $tool->getCreateSchemaSql($classes);
        
        $this->assertEquals(1, count($sql));
        $this->assertEquals("CREATE TABLE decimal_model (id INT AUTO_INCREMENT NOT NULL, `decimal` NUMERIC(5, 2) NOT NULL, `high_scale` NUMERIC(14, 4) NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB", $sql[0]);
    }
    
    public function testGetCreateSchemaSql3()
    {
        $classes = array(
            $this->_em->getClassMetadata('Doctrine\Tests\Models\Generic\BooleanModel')
        );

        $tool = new SchemaTool($this->_em);
        $sql = $tool->getCreateSchemaSql($classes);
        
        $this->assertEquals(1, count($sql));
        $this->assertEquals("CREATE TABLE boolean_model (id INT AUTO_INCREMENT NOT NULL, booleanField TINYINT(1) NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB", $sql[0]);
    }
}