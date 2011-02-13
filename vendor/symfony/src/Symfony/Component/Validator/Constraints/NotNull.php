<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Validator\Constraints;

class NotNull extends \Symfony\Component\Validator\Constraint
{
    public $message = 'This value should not be null';

    /**
     * {@inheritDoc}
     */
    public function targets()
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
