<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection;

/**
 * ContainerInterface is the interface implemented by service container classes.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface ContainerInterface
{
    const EXCEPTION_ON_INVALID_REFERENCE = 1;
    const NULL_ON_INVALID_REFERENCE      = 2;
    const IGNORE_ON_INVALID_REFERENCE    = 3;
    const SCOPE_CONTAINER                = 'container';
    const SCOPE_PROTOTYPE                = 'prototype';

    /**
     * Sets a service.
     *
     * @param string $id      The service identifier
     * @param object $service The service instance
     * @param string $scope   The scope of the service
     */
    function set($id, $service, $scope = self::SCOPE_CONTAINER);

    /**
     * Gets a service.
     *
     * @param  string $id              The service identifier
     * @param  int    $invalidBehavior The behavior when the service does not exist
     *
     * @return object The associated service
     *
     * @throws \InvalidArgumentException if the service is not defined
     *
     * @see Reference
     */
    function get($id, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE);

    /**
     * Returns true if the given service is defined.
     *
     * @param  string  $id      The service identifier
     *
     * @return Boolean true if the service is defined, false otherwise
     */
    function has($id);

    /**
     * Gets a parameter.
     *
     * @param  string $name The parameter name
     *
     * @return mixed  The parameter value
     *
     * @throws  \InvalidArgumentException if the parameter is not defined
     */
    function getParameter($name);

    /**
     * Checks if a parameter exists.
     *
     * @param  string $name The parameter name
     *
     * @return Boolean The presence of parameter in container
     */
    function hasParameter($name);

    /**
     * Sets a parameter.
     *
     * @param string $name       The parameter name
     * @param mixed  $parameters The parameter value
     */
    function setParameter($name, $value);

    /**
     * Enters the given scope
     *
     * @param string $name
     * @return void
     */
    function enterScope($name);

    /**
     * Leaves the current scope, and re-enters the parent scope
     *
     * @param string $name
     * @return void
     */
    function leaveScope($name);

    /**
     * Adds a scope to the container
     *
     * @param ScopeInterface $scope
     * @return void
     */
    function addScope(ScopeInterface $scope);

    /**
     * Whether this container has the given scope
     *
     * @param string $name
     * @return Boolean
     */
    function hasScope($name);

    /**
     * Determines whether the given scope is currently active.
     *
     * It does however not check if the scope actually exists.
     *
     * @return Boolean
     */
    function isScopeActive($name);
}
