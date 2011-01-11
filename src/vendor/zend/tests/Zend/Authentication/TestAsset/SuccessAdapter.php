<?php

namespace ZendTest\Authentication\TestAsset;

use Zend\Authentication\Adapter as AuthenticationAdapter,
    Zend\Authentication\Result as AuthenticationResult;

class SuccessAdapter implements AuthenticationAdapter
{
    public function authenticate()
    {
        return new AuthenticationResult(true, 'someIdentity');
    }
}
