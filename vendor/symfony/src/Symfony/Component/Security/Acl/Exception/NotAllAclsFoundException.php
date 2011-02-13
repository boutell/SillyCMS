<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Acl\Exception;

/**
 * This exception is thrown when you have requested ACLs for multiple object
 * identities, but the AclProvider implementation failed to find ACLs for all
 * identities.
 *
 * This exception contains the partial result.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class NotAllAclsFoundException extends AclNotFoundException
{
    protected $partialResult;

    /**
     * Sets the partial result
     *
     * @param \SplObjectStorage $result
     * @return void
     */
    public function setPartialResult(\SplObjectStorage $result)
    {
        $this->partialResult = $result;
    }

    /**
     * Returns the partial result
     *
     * @return \SplObjectStorage
     */
    public function getPartialResult()
    {
        return $this->partialResult;
    }
}