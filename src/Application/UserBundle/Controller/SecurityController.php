<?php

// Copied from http://docs.symfony-reloaded.org/master/guides/security/authentication.html

namespace Application\UserBundle\Controller;
use FOS\UserBundle\Controller\SecurityController as BaseSecurityController;


class SecurityController extends  BaseSecurityController
{
    /**
     *
     * @Template
     */
    public function statusAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        return array('user' => $user, 'security' => $this->get('security.context'));
    }
}