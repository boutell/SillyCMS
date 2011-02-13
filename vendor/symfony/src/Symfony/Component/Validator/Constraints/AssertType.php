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

class AssertType extends \Symfony\Component\Validator\Constraint
{
    public $message = 'This value should be of type {{ type }}';
    public $type;

    /**
     * {@inheritDoc}
     */
    public function defaultOption()
    {
        return 'type';
    }

    /**
     * {@inheritDoc}
     */
    public function requiredOptions()
    {
        return array('type');
    }

    /**
     * {@inheritDoc}
     */
    public function targets()
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
