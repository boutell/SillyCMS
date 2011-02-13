<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\Debug;

/**
 * ErrorHandler.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class ErrorHandler
{
    protected $levels = array(
        E_WARNING           => 'Warning',
        E_NOTICE            => 'Notice',
        E_USER_ERROR        => 'User Error',
        E_USER_WARNING      => 'User Warning',
        E_USER_NOTICE       => 'User Notice',
        E_STRICT            => 'Runtime Notice',
        E_RECOVERABLE_ERROR => 'Catchable Fatal Error',
    );

    protected $level;

    /**
     * Constructor.
     *
     * @param integer $level The level at which the conversion to Exception is done (null to use the error_reporting() value and 0 to disable)
     */
    public function __construct($level = null)
    {
        $this->level = null === $level ? error_reporting() : $level;
    }

    public function register()
    {
        set_error_handler(array($this, 'handle'));
    }

    /**
     * @throws \ErrorException When error_reporting returns error
     */
    public function handle($level, $message, $file, $line, $context)
    {
        if (0 === $this->level) {
            return false;
        }

        if (error_reporting() & $level && $this->level & $level) {
            throw new \ErrorException(sprintf('%s: %s in %s line %d', isset($this->levels[$level]) ? $this->levels[$level] : $level, $message, $file, $line));
        }

        return false;
    }
}
