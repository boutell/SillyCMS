<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Core\Encoder;

/**
 * PasswordEncoderInterface is the interface for all encoders.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
interface PasswordEncoderInterface
{
    /**
     * Encodes the raw password.
     *
     * @param string $raw  The password to encode
     * @param string $salt The salt
     *
     * @return string The encoded password
     */
    function encodePassword($raw, $salt);

    /**
     * Checks a raw password against an encoded password.
     *
     * @param string $encoded An encoded password
     * @param string $raw     A raw password
     * @param string $salt    The salt
     *
     * @return Boolean true if the password is valid, false otherwise
     */
    function isPasswordValid($encoded, $raw, $salt);
}
