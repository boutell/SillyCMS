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

/**
 * Represents a single violation of a constraint.
 */
class ConstraintViolation
{
    protected $messageTemplate;
    protected $messageParameters;
    protected $root;
    protected $propertyPath;
    protected $invalidValue;

    public function __construct($messageTemplate, array $messageParameters, $root, $propertyPath, $invalidValue)
    {
        $this->messageTemplate = $messageTemplate;
        $this->messageParameters = $messageParameters;
        $this->root = $root;
        $this->propertyPath = $propertyPath;
        $this->invalidValue = $invalidValue;
    }

    /**
     * @return string
     */
    public function getMessageTemplate()
    {
        return $this->messageTemplate;
    }

    /**
     * @return array
     */
    public function getMessageParameters()
    {
        return $this->messageParameters;
    }

    /**
     * Returns the violation message.
     *
     * @return string
     */
    public function getMessage()
    {
        return str_replace(array_keys($this->messageParameters), array_values($this->messageParameters), $this->messageTemplate);
    }

    public function getRoot()
    {
        return $this->root;
    }

    public function getPropertyPath()
    {
        return $this->propertyPath;
    }

    public function getInvalidValue()
    {
        return $this->invalidValue;
    }
}