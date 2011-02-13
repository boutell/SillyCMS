<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Http\Firewall;

use Symfony\Component\EventDispatcher\Event;

use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\RememberMe\RememberMeServicesInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * The AbstractAuthenticationListener is the preferred base class for all
 * browser-/HTTP-based authentication requests.
 *
 * Subclasses likely have to implement the following:
 * - an TokenInterface to hold authentication related data
 * - an AuthenticationProvider to perform the actual authentication of the
 *   token, retrieve the AccountInterface implementation from a database, and
 *   perform the specific account checks using the AccountChecker
 *
 * By default, this listener only is active for a specific path, e.g.
 * /login_check. If you want to change this behavior, you can overwrite the
 * requiresAuthentication() method.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
abstract class AbstractAuthenticationListener implements ListenerInterface
{
    protected $securityContext;
    protected $authenticationManager;
    protected $sessionStrategy;
    protected $providerKey;
    protected $eventDispatcher;
    protected $options;
    protected $successHandler;
    protected $failureHandler;
    protected $logger;
    protected $rememberMeServices;

    /**
     * Constructor.
     *
     * @param SecurityContextInterface       $securityContext       A SecurityContext instance
     * @param AuthenticationManagerInterface $authenticationManager An AuthenticationManagerInterface instance
     * @param array                          $options               An array of options for the processing of a successful, or failed authentication attempt
     * @param LoggerInterface                $logger                A LoggerInterface instance
     */
    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager, SessionAuthenticationStrategyInterface $sessionStrategy, $providerKey, array $options = array(), AuthenticationSuccessHandlerInterface $successHandler = null, AuthenticationFailureHandlerInterface $failureHandler = null, LoggerInterface $logger = null)
    {
        if (empty($providerKey)) {
            throw new \InvalidArgumentException('$providerKey must not be empty.');
        }

        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->sessionStrategy = $sessionStrategy;
        $this->providerKey = $providerKey;
        $this->successHandler = $successHandler;
        $this->failureHandler = $failureHandler;
        $this->options = array_merge(array(
            'check_path'                     => '/login_check',
            'login_path'                     => '/login',
            'always_use_default_target_path' => false,
            'default_target_path'            => '/',
            'target_path_parameter'          => '_target_path',
            'use_referer'                    => false,
            'failure_path'                   => null,
            'failure_forward'                => false,
        ), $options);
        $this->logger = $logger;
    }

    /**
     * Sets the RememberMeServices implementation to use
     *
     * @param RememberMeServicesInterface $rememberMeServices
     */
    public function setRememberMeServices(RememberMeServicesInterface $rememberMeServices)
    {
        $this->rememberMeServices = $rememberMeServices;
    }

    /**
     * Subscribe to the core.security event
     *
     * @param EventDispatcher $dispatcher An EventDispatcher instance
     * @param integer         $priority   The priority
     */
    public function register(EventDispatcherInterface $dispatcher)
    {
        $dispatcher->connect('core.security', array($this, 'handle'), 0);

        $this->eventDispatcher = $dispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function unregister(EventDispatcherInterface $dispatcher)
    {
    }

    /**
     * Handles form based authentication.
     *
     * @param Event $event An Event instance
     */
    public function handle(EventInterface $event)
    {
        $request = $event->get('request');

        if (!$this->requiresAuthentication($request)) {
            return;
        }

        try {
            if (null === $returnValue = $this->attemptAuthentication($request)) {
                return;
            }

            if ($returnValue instanceof TokenInterface) {
                $this->sessionStrategy->onAuthentication($request, $returnValue);

                $response = $this->onSuccess($event, $request, $returnValue);
            } else if ($returnValue instanceof Response) {
                $response = $returnValue;
            } else {
                throw new \RuntimeException('attemptAuthentication() must either return a Response, an implementation of TokenInterface, or null.');
            }
        } catch (AuthenticationException $failed) {
            $response = $this->onFailure($event, $request, $failed);
        }

        $event->setProcessed();

        return $response;
    }

    /**
     * Whether this request requires authentication.
     *
     * The default implementation only processed requests to a specific path,
     * but a subclass could change this to only authenticate requests where a
     * certain parameters is present.
     *
     * @param Request $request
     *
     * @return Boolean
     */
    protected function requiresAuthentication(Request $request)
    {
        return $this->options['check_path'] === $request->getPathInfo();
    }

    protected function onFailure($event, Request $request, \Exception $failed)
    {
        if (null !== $this->logger) {
            $this->logger->debug(sprintf('Authentication request failed: %s', $failed->getMessage()));
        }

        $this->securityContext->setToken(null);

        if (null !== $this->failureHandler) {
            return $this->failureHandler->onAuthenticationFailure($event, $request, $failed);
        }

        if (null === $this->options['failure_path']) {
            $this->options['failure_path'] = $this->options['login_path'];
        }

        if ($this->options['failure_forward']) {
            if (null !== $this->logger) {
                $this->logger->debug(sprintf('Forwarding to %s', $this->options['failure_path']));
            }

            $subRequest = Request::create($this->options['failure_path']);
            $subRequest->attributes->set(SecurityContextInterface::AUTHENTICATION_ERROR, $failed->getMessage());

            return $event->getSubject()->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        } else {
            if (null !== $this->logger) {
                $this->logger->debug(sprintf('Redirecting to %s', $this->options['failure_path']));
            }

            $request->getSession()->set(SecurityContextInterface::AUTHENTICATION_ERROR, $failed->getMessage());

            $response = new Response();
            $response->setRedirect(0 !== strpos($this->options['failure_path'], 'http') ? $request->getUriForPath($this->options['failure_path']) : $this->options['failure_path'], 302);

            return $response;
        }
    }

    protected function onSuccess(EventInterface $event, Request $request, TokenInterface $token)
    {
        if (null !== $this->logger) {
            $this->logger->debug('User has been authenticated successfully');
        }

        $this->securityContext->setToken($token);

        $session = $request->getSession();
        $session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
        $session->remove(SecurityContextInterface::LAST_USERNAME);

        if (null !== $this->eventDispatcher) {
            $this->eventDispatcher->notify(new Event($this, 'security.interactive_login', array('request' => $request, 'token' => $token)));
        }

        if (null !== $this->successHandler) {
            $response = $this->successHandler->onAuthenticationSuccess($request, $token);
        } else {
            $response = new Response();
            $path = $this->determineTargetUrl($request);
            $response->setRedirect(0 !== strpos($path, 'http') ? $request->getUriForPath($path) : $path, 302);
        }

        if (null !== $this->rememberMeServices) {
            $this->rememberMeServices->loginSuccess($request, $response, $token);
        }

        return $response;
    }

    /**
     * Builds the target URL according to the defined options.
     *
     * @param Request $request
     *
     * @return string
     */
    protected function determineTargetUrl(Request $request)
    {
        if ($this->options['always_use_default_target_path']) {
            return $this->options['default_target_path'];
        }

        if ($targetUrl = $request->get($this->options['target_path_parameter'])) {
            return $targetUrl;
        }

        $session = $request->getSession();
        if ($targetUrl = $session->get('_security.target_path')) {
            $session->remove('_security.target_path');

            return $targetUrl;
        }

        if ($this->options['use_referer'] && $targetUrl = $request->headers->get('Referer')) {
            return $targetUrl;
        }

        return $this->options['default_target_path'];
    }

    /**
     * Performs authentication.
     *
     * @param  Request $request A Request instance
     *
     * @return TokenInterface The authenticated token, or null if full authentication is not possible
     *
     * @throws AuthenticationException if the authentication fails
     */
    abstract protected function attemptAuthentication(Request $request);
}
