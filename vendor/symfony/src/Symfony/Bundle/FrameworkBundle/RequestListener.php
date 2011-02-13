<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\FrameworkBundle;

use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * RequestListener.
 *
 * The handle method must be connected to the core.request event.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class RequestListener
{
    protected $router;
    protected $logger;
    protected $container;

    public function __construct(ContainerInterface $container, RouterInterface $router, LoggerInterface $logger = null)
    {
        $this->container = $container;
        $this->router = $router;
        $this->logger = $logger;
    }

    public function handle(EventInterface $event)
    {
        $request = $event->get('request');
        $master = HttpKernelInterface::MASTER_REQUEST === $event->get('request_type');

        $this->initializeSession($request, $master);

        $this->initializeRequestAttributes($request, $master);
    }

    protected function initializeSession(Request $request, $master)
    {
        if (!$master) {
            return;
        }

        // inject the session object if none is present
        if (null === $request->getSession() && $this->container->has('session')) {
            $request->setSession($this->container->get('session'));
        }

        // starts the session if a session cookie already exists in the request...
        if ($request->hasSession()) {
            $request->getSession()->start();
        }
    }

    protected function initializeRequestAttributes(Request $request, $master)
    {
        if ($master) {
            // set the context even if the parsing does not need to be done
            // to have correct link generation
            $this->router->setContext(array(
                'base_url'  => $request->getBaseUrl(),
                'method'    => $request->getMethod(),
                'host'      => $request->getHost(),
                'port'      => $request->getPort(),
                'is_secure' => $request->isSecure(),
            ));
        }

        if ($request->attributes->has('_controller')) {
            // routing is already done
            return;
        }

        // add attributes based on the path info (routing)
        if (false !== $parameters = $this->router->match($request->getPathInfo())) {
            if (null !== $this->logger) {
                $this->logger->info(sprintf('Matched route "%s" (parameters: %s)', $parameters['_route'], json_encode($parameters)));
            }

            $request->attributes->add($parameters);

            if ($locale = $request->attributes->get('_locale')) {
                $request->getSession()->setLocale($locale);
            }
        } elseif (null !== $this->logger) {
            $this->logger->err(sprintf('No route found for %s', $request->getPathInfo()));
        }
    }
}
