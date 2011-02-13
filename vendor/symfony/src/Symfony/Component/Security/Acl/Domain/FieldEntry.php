<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Acl\Domain;

use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Model\FieldAwareEntryInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

/**
 * Field-aware ACE implementation which is auditable
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class FieldEntry extends Entry implements FieldAwareEntryInterface
{
    protected $field;

    /**
     * Constructor
     *
     * @param integer $id
     * @param AclInterface $acl
     * @param string $field
     * @param SecurityIdentityInterface $sid
     * @param string $strategy
     * @param integer $mask
     * @param Boolean $granting
     * @param Boolean $auditFailure
     * @param Boolean $auditSuccess
     * @return void
     */
    public function __construct($id, AclInterface $acl, $field, SecurityIdentityInterface $sid, $strategy, $mask, $granting, $auditFailure, $auditSuccess)
    {
        parent::__construct($id, $acl, $sid, $strategy, $mask, $granting, $auditFailure, $auditSuccess);

        $this->field = $field;
    }

    /**
     * {@inheritDoc}
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * {@inheritDoc}
     */
    public function serialize()
    {
        return serialize(array(
            $this->field,
            $this->mask,
            $this->id,
            $this->securityIdentity,
            $this->strategy,
            $this->auditFailure,
            $this->auditSuccess,
            $this->granting,
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($serialized)
    {
        list($this->field,
             $this->mask,
             $this->id,
             $this->securityIdentity,
             $this->strategy,
             $this->auditFailure,
             $this->auditSuccess,
             $this->granting
        ) = unserialize($serialized);
    }
}