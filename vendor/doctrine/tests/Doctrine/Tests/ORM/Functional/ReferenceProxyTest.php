<?php

namespace Doctrine\Tests\ORM\Functional;

use Doctrine\ORM\Proxy\ProxyFactory;
use Doctrine\ORM\Proxy\ProxyClassGenerator;
use Doctrine\Tests\Models\ECommerce\ECommerceProduct;

require_once __DIR__ . '/../../TestInit.php';

/**
 * Tests the generation of a proxy object for lazy loading.
 * @author Giorgio Sironi <piccoloprincipeazzurro@gmail.com>
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class ReferenceProxyTest extends \Doctrine\Tests\OrmFunctionalTestCase
{
    protected function setUp()
    {
        $this->useModelSet('ecommerce');
        parent::setUp();
        $this->_factory = new ProxyFactory(
                $this->_em,
                __DIR__ . '/../../Proxies',
                'Doctrine\Tests\Proxies',
                true);
    }

    public function testLazyLoadsFieldValuesFromDatabase()
    {
        $product = new ECommerceProduct();
        $product->setName('Doctrine Cookbook');
        $this->_em->persist($product);

        $this->_em->flush();
        $this->_em->clear();
        
        $id = $product->getId();

        $productProxy = $this->_factory->getProxy('Doctrine\Tests\Models\ECommerce\ECommerceProduct', array('id' => $id));
        $this->assertEquals('Doctrine Cookbook', $productProxy->getName());
    }

    /**
     * @group DDC-727
     */
    public function testAccessMetatadaForProxy()
    {
        $entity = $this->_em->getReference('Doctrine\Tests\Models\ECommerce\ECommerceProduct' , 1);
        $class = $this->_em->getClassMetadata(get_class($entity));

        $this->assertEquals('Doctrine\Tests\Models\ECommerce\ECommerceProduct', $class->name);
    }
}
