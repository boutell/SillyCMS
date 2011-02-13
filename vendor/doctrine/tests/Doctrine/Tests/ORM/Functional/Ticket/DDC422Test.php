<?php
namespace Doctrine\Tests\ORM\Functional\Ticket;

require_once __DIR__ . '/../../../TestInit.php';

class DDC422Test extends \Doctrine\Tests\OrmFunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        //$this->_em->getConnection()->getConfiguration()->setSQLLogger(new \Doctrine\DBAL\Logging\EchoSQLLogger);
        $this->_schemaTool->createSchema(array(
            $this->_em->getClassMetadata(__NAMESPACE__ . '\DDC422Guest'),
            $this->_em->getClassMetadata(__NAMESPACE__ . '\DDC422Customer'),
            $this->_em->getClassMetadata(__NAMESPACE__ . '\DDC422Contact')
        ));
    }

    /**
     * @group DDC-422
     */
    public function testIssue()
    {       
        $customer = new DDC422Customer;
        $this->_em->persist($customer);
        $this->_em->flush();
        $this->_em->clear();
        
        $customer = $this->_em->find(get_class($customer), $customer->id);
        
        $this->assertTrue($customer->contacts instanceof \Doctrine\ORM\PersistentCollection);
        $this->assertFalse($customer->contacts->isInitialized());
        $contact = new DDC422Contact;
        $customer->contacts->add($contact);
        $this->assertTrue($customer->contacts->isDirty());
        $this->assertFalse($customer->contacts->isInitialized());
        $this->_em->flush();
        
        $this->assertEquals(1, $this->_em->getConnection()->fetchColumn("select count(*) from ddc422_customers_contacts"));
    }
}

/**
 * @Entity
 * @InheritanceType("JOINED")
 * @DiscriminatorColumn(name="discr", type="string")
 * @DiscriminatorMap({"guest" = "DDC422Guest", "customer" = "DDC422Customer"})
 */
class DDC422Guest {
    /** @Id @Column(type="integer") @GeneratedValue */
    public $id;
}

/** @Entity */
class DDC422Customer extends DDC422Guest {
    /**
     * @ManyToMany(targetEntity="DDC422Contact", cascade={"persist","remove"})
     * @JoinTable(name="ddc422_customers_contacts",
     *      joinColumns={@JoinColumn(name="customer_id", referencedColumnName="id", onDelete="cascade" )},
     *      inverseJoinColumns={@JoinColumn(name="contact_id", referencedColumnName="id", onDelete="cascade" )}
     *      )
     */
    public $contacts;

    public function __construct() {
        $this->contacts = new \Doctrine\Common\Collections\ArrayCollection;
    }
}

/** @Entity */
class DDC422Contact {
    /** @Id @Column(type="integer") @GeneratedValue */
    public $id;
}

