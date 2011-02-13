<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection;

/**
 * Reference represents a service reference.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class Reference
{
    protected $id;
    protected $invalidBehavior;
    protected $strict;

    /**
     * Constructor.
     *
     * @param string  $id              The service identifier
     * @param int     $invalidBehavior The behavior when the service does not exist
     * @param Boolean $strict          Sets how this reference is validated
     *
     * @see Container
     */
    public function __construct($id, $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $strict = true)
    {
        $this->id = $id;
        $this->invalidBehavior = $invalidBehavior;
        $this->strict = $strict;
    }

    /**
     * __toString.
     *
     * @return string The service identifier
     */
    public function __toString()
    {
        return (string) $this->id;
    }

    public function getInvalidBehavior()
    {
        return $this->invalidBehavior;
    }

    public function isStrict()
    {
        return $this->strict;
    }
}
