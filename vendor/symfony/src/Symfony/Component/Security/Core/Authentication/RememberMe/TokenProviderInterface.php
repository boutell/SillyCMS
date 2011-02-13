<?php

namespace Symfony\Component\Security\Core\Authentication\RememberMe;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Interface for TokenProviders
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface TokenProviderInterface
{
    /**
     * Loads the active token for the given series
     *
     * @throws TokenNotFoundException if the token is not found
     *
     * @param string $series
     * @return PersistentTokenInterface
     */
    function loadTokenBySeries($series);

    /**
     * Deletes all tokens belonging to series
     * @param string $series
     */
    function deleteTokenBySeries($series);

    /**
     * Updates the token according to this data
     *
     * @param string $series
     * @param string $tokenValue
     * @param DateTime $lastUsed
     */
    function updateToken($series, $tokenValue, \DateTime $lastUsed);

    /**
     * Creates a new token
     * @param PersistentTokenInterface $token
     */
    function createNewToken(PersistentTokenInterface $token);
}