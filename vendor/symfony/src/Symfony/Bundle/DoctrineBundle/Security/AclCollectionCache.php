<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\DoctrineBundle\Security;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Acl\Model\AclProviderInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityRetrievalStrategyInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityRetrievalStrategyInterface;

/**
 * This service caches ACLs for an entire collection
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class AclCollectionCache
{
    protected $aclProvider;
    protected $objectIdentityRetrievalStrategy;
    protected $securityIdentityRetrievalStrategy;

    /**
     * Constructor
     *
     * @param AclProviderInterface $aclProvider
     * @param ObjectIdentityRetrievalStrategy $oidRetrievalStrategy
     * @param SecurityIdentityRetrievalStrategy $sidRetrievalStrategy
     * @return void
     */
    public function __construct(AclProviderInterface $aclProvider, ObjectIdentityRetrievalStrategyInterface $oidRetrievalStrategy, SecurityIdentityRetrievalStrategyInterface $sidRetrievalStrategy)
    {
        $this->aclProvider = $aclProvider;
        $this->objectIdentityRetrievalStrategy = $oidRetrievalStrategy;
        $this->securityIdentityRetrievalStrategy = $sidRetrievalStrategy;
    }

    /**
     * Batch loads ACLs for an entire collection; thus, it reduces the number
     * of required queries considerably.
     *
     * @param Collection $collection
     * @param array $tokens an array of TokenInterface implementations
     * @return void
     */
    public function cache(Collection $collection, array $tokens = array())
    {
        $sids = array();
        foreach ($tokens as $token) {
            $sids = array_merge($sids, $this->securityIdentityRetrievalStrategy->getSecurityIdentities($token));
        }

        $oids = array();
        foreach ($collection as $domainObject) {
            $oids[] = $this->objectIdentityRetrievalStrategy->getObjectIdentity($domainObject);
        }

        $this->aclProvider->findAcls($oids, $sids);
    }
}