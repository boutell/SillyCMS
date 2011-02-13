<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\Form\CsrfProvider;

use Symfony\Component\Form\CsrfProvider\SessionCsrfProvider;

class SessionCsrfProviderTest extends \PHPUnit_Framework_TestCase
{
    protected $provider;

    protected function setUp()
    {
        $this->session = $this->getMock(
            'Symfony\Component\HttpFoundation\Session',
            array(),
            array(),
            '',
            false // don't call constructor
        );
        $this->provider = new SessionCsrfProvider($this->session, 'SECRET');
    }

    public function testGenerateCsrfToken()
    {
        $this->session->expects($this->once())
                ->method('start');

        $this->session->expects($this->once())
                ->method('getId')
                ->will($this->returnValue('ABCDEF'));

        $token = $this->provider->generateCsrfToken('foo');

        $this->assertEquals(sha1('SECRET'.'foo'.'ABCDEF'), $token);
    }

    public function testIsCsrfTokenValidSucceeds()
    {
        $this->session->expects($this->once())
                ->method('start');

        $this->session->expects($this->once())
                ->method('getId')
                ->will($this->returnValue('ABCDEF'));

        $token = sha1('SECRET'.'foo'.'ABCDEF');

        $this->assertTrue($this->provider->isCsrfTokenValid('foo', $token));
    }

    public function testIsCsrfTokenValidFails()
    {
        $this->session->expects($this->once())
                ->method('start');

        $this->session->expects($this->once())
                ->method('getId')
                ->will($this->returnValue('ABCDEF'));

        $token = sha1('SECRET'.'bar'.'ABCDEF');

        $this->assertFalse($this->provider->isCsrfTokenValid('foo', $token));
    }
}