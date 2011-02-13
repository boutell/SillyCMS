<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Core\User;

/**
 * UserProviderInterface is the implementation that all user provider must
 * implement.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
interface UserProviderInterface
{
    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @throws UsernameNotFoundException if the user is not found
     * @param string $username The username
     *
     * @return AccountInterface
     */
    function loadUserByUsername($username);

    /**
     * Loads the user for the account interface.
     *
     * It is up to the implementation if it decides to reload the user data
     * from the database, or if it simply merges the passed User into the
     * identity map of an entity manager.
     *
     * @throws UnsupportedAccountException if the account is not supported
     * @param AccountInterface $account
     *
     * @return AccountInterface
     */
    function loadUserByAccount(AccountInterface $account);

    /**
     * Whether this provider supports the given user class
     *
     * @param string $class
     *
     * @return Boolean
     */
    function supportsClass($class);
}