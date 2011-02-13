<?php

namespace Application\SillyCMSBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SillyCMSBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return __DIR__;
    }
}
