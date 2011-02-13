<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Core\Authentication;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * AuthenticationManagerInterface is the interface for authentication managers,
 * which process Token authentication.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
interface AuthenticationManagerInterface
{
    /**
     * Attempts to authenticates a TokenInterface object.
     *
     * @param TokenInterface $token The TokenInterface instance to authenticate
     *
     * @return TokenInterface An authenticated TokenInterface instance
     *
     * @throws AuthenticationException if the authentication fails
     */
    function authenticate(TokenInterface $token);
}
