<?php

namespace Symfony\Component\Validator;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Symfony\Component\Validator\Constraint;

/**
 * Default implementation of the ConstraintValidatorFactoryInterface.
 *
 * This enforces the convention that the validatedBy() method on any
 * Constrain will return the class name of the ConstraintValidator that
 * should validate the Constraint.
 */
class ConstraintValidatorFactory implements ConstraintValidatorFactoryInterface
{
    protected $validators = array();

    /**
     * {@inheritDoc}
     */
    public function getInstance(Constraint $constraint)
    {
        $className = $constraint->validatedBy();

        if (!isset($this->validators[$className])) {
            $this->validators[$className] = new $className();
        }

        return $this->validators[$className];
    }
}