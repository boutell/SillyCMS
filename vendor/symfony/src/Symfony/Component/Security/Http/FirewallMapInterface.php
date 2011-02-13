<?php

namespace Symfony\Component\Security\Http;

use Symfony\Component\HttpFoundation\Request;

/**
 * This interface must be implemented by firewall maps.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface FirewallMapInterface
{
    /**
     * Returns the authentication listeners, and the exception listener to use
     * for the given request.
     *
     * If there are no authentication listeners, the first inner are must be
     * empty.
     *
     * If there is no exception listener, the second element of the outer array
     * must be null.
     *
     * @param Request $request
     * @return array of the format array(array(AuthenticationListener), ExceptionListener)
     */
    function getListeners(Request $request);
}