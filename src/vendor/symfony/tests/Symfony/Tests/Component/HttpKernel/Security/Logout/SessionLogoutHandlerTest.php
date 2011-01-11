<?php

namespace Symfony\Tests\Component\HttpKernel\Security\Logout;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Security\Logout\SessionLogoutHandler;

class SessionLogoutHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testLogout()
    {
        $handler = new SessionLogoutHandler();
        
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $response = new Response();
        $session = $this->getMock('Symfony\Component\HttpFoundation\Session', array(), array(), '', false);
        
        $request
            ->expects($this->once())
            ->method('getSession')
            ->will($this->returnValue($session))
        ;
        
        $session
            ->expects($this->once())
            ->method('invalidate')
        ;
        
        $handler->logout($request, $response, $this->getMock('Symfony\Component\Security\Authentication\Token\TokenInterface'));
    }
}