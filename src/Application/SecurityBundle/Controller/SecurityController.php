<?php

// Copied from http://docs.symfony-reloaded.org/master/guides/security/authentication.html

namespace Application\SecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\SecurityContext;

class SecurityController extends Controller
{
    public function loginAction()
    {
        // get the error if any (works with forward and redirect -- see below)
        if ($this->get('request')->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $this->get('request')->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $this->get('request')->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
        }

        return $this->render('SecurityBundle:Security:login.twig.html', array(
            // last username entered by the user
            'last_username' => $this->get('request')->getSession()->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
        ));
    }
    
    public function statusAction()
    {
        return $this->render('SecurityBundle:Security:status.twig.html', array('security' => $this->get('security.context')));
    }
}