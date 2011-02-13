<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form;

/**
 * Wraps errors in forms
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony-project.com>
 */
class Error
{
    /**
     * The template for the error message
     * @var string
     */
    protected $messageTemplate;

    /**
     * The parameters that should be substituted in the message template
     * @var array
     */
    protected $messageParameters;

    /**
     * Constructor
     *
     * @param string $messageTemplate      The template for the error message
     * @param array $messageParameters     The parameters that should be
     *                                     substituted in the message template.
     */
    public function __construct($messageTemplate, array $messageParameters = array())
    {
        $this->messageTemplate = $messageTemplate;
        $this->messageParameters = $messageParameters;
    }

    /**
     * Returns the error message template
     *
     * @return string
     */
    public function getMessageTemplate()
    {
        return $this->messageTemplate;
    }

    /**
     * Returns the parameters to be inserted in the message template
     *
     * @return array
     */
    public function getMessageParameters()
    {
        return $this->messageParameters;
    }
}