<?php

/*
 * This file is part of the Symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\Security\Authentication\Provider;

use Symfony\Component\Security\Authentication\Provider\PreAuthenticatedAuthenticationProvider;

class PreAuthenticatedAuthenticationProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testSupports()
    {
        $provider = $this->getProvider();

        $this->assertTrue($provider->supports($this->getSupportedToken()));
        $this->assertFalse($provider->supports($this->getMock('Symfony\Component\Security\Authentication\Token\TokenInterface')));
    }

    public function testAuthenticateWhenTokenIsNotSupported()
    {
        $provider = $this->getProvider();

        $this->assertNull($provider->authenticate($this->getMock('Symfony\Component\Security\Authentication\Token\TokenInterface')));
    }

    /**
     * @expectedException Symfony\Component\Security\Exception\BadCredentialsException
     */
    public function testAuthenticateWhenNoUserIsSet()
    {
        $provider = $this->getProvider();
        $provider->authenticate($this->getSupportedToken(''));
    }

    public function testAuthenticate()
    {
        $user = $this->getMock('Symfony\Component\Security\User\AccountInterface');
        $provider = $this->getProvider($user);

        $token = $provider->authenticate($this->getSupportedToken('fabien', 'pass'));
        $this->assertInstanceOf('Symfony\Component\Security\Authentication\Token\PreAuthenticatedToken', $token);
        $this->assertEquals('pass', $token->getCredentials());
        $this->assertEquals(array(), $token->getRoles());
        $this->assertSame($user, $token->getUser());
    }

    /**
     * @expectedException Symfony\Component\Security\Exception\LockedException
     */
    public function testAuthenticateWhenAccountCheckerThrowsException()
    {
        $user = $this->getMock('Symfony\Component\Security\User\AccountInterface');

        $userChecker = $this->getMock('Symfony\Component\Security\User\AccountCheckerInterface');
        $userChecker->expects($this->once())
                    ->method('checkPostAuth')
                    ->will($this->throwException($this->getMock('Symfony\Component\Security\Exception\LockedException', null, array(), '', false)))
        ;

        $provider = $this->getProvider($user, $userChecker);

        $provider->authenticate($this->getSupportedToken('fabien'));
    }

    protected function getSupportedToken($user = false, $credentials = false)
    {
        $token = $this->getMock('Symfony\Component\Security\Authentication\Token\PreAuthenticatedToken', array('getUser', 'getCredentials'), array(), '', false);
        if (false !== $user) {
            $token->expects($this->once())
                  ->method('getUser')
                  ->will($this->returnValue($user))
            ;
        }
        if (false !== $credentials) {
            $token->expects($this->once())
                  ->method('getCredentials')
                  ->will($this->returnValue($credentials))
            ;
        }

        return $token;
    }

    protected function getProvider($user = false, $userChecker = false)
    {
        $userProvider = $this->getMock('Symfony\Component\Security\User\UserProviderInterface');
        if (false !== $user) {
            $userProvider->expects($this->once())
                         ->method('loadUserByUsername')
                         ->will($this->returnValue($user))
            ;
        }

        if (false === $userChecker) {
            $userChecker = $this->getMock('Symfony\Component\Security\User\AccountCheckerInterface');
        }

        return new PreAuthenticatedAuthenticationProvider($userProvider, $userChecker);
    }
}
