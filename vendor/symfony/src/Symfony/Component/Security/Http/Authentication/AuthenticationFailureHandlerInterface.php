<?php

namespace Symfony\Component\Security\Http\Authentication;

use Symfony\Component\EventDispatcher\EventInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for custom authentication failure handlers.
 *
 * If you want to customize the failure handling process, instead of
 * overwriting the respective listener globally, you can set a custom failure
 * handler which implements this interface.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface AuthenticationFailureHandlerInterface
{
    /**
     * This is called when an interactive authentication attempt fails. This is
     * called by authentication listeners inheriting from
     * AbstractAuthenticationListener.
     *
     * @param EventInterface $event the "core.security" event, this event always
     *                              has the kernel as target
     * @param Request        $request
     * @param \Exception     $exception
     *
     * @return Response the response to return
     */
    function onAuthenticationFailure(EventInterface $event, Request $request, \Exception $exception);
}