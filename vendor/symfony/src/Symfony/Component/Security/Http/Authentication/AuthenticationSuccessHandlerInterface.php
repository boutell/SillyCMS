<?php

namespace Symfony\Component\Security\Http\Authentication;

use Symfony\Component\EventDispatcher\EventInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for a custom authentication success handler
 *
 * If you want to customize the success handling process, instead of
 * overwriting the respective listener globally, you can set a custom success
 * handler which implements this interface.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface AuthenticationSuccessHandlerInterface
{
    /**
     * This is called when an interactive authentication attempt succeeds. This
     * is called by authentication listeners inheriting from
     * AbstractAuthenticationListener.
     *
     * @param EventInterface $event the "core.security" event, this event always
     *                              has the kernel as target
     * @param Request        $request
     * @param TokenInterface $token
     *
     * @return Response the response to return
     */
    function onAuthenticationSuccess(EventInterface $event, Request $request, TokenInterface $token);
}