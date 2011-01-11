<?php

namespace Doctrine\Tests\ORM\Hydration;

use PDO, Doctrine\ORM\Internal\Hydration\AbstractHydrator;

require_once __DIR__ . '/../../TestInit.php';

class CustomHydratorTest extends HydrationTestCase
{
    public function testCustomHydrator()
    {
        $em = $this->_getTestEntityManager();
        $config = $em->getConfiguration();
        $config->addCustomHydrationMode('CustomHydrator', 'Doctrine\Tests\ORM\Hydration\CustomHydrator');

        $hydrator = $em->newHydrator('CustomHydrator');
        $this->assertTrue($hydrator instanceof \Doctrine\Tests\ORM\Hydration\CustomHydrator);
        $this->assertNull($config->getCustomHydrationMode('does not exist'));
    }
}

class CustomHydrator extends AbstractHydrator
{
    protected function _hydrateAll()
    {
        return $this->_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}