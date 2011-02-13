<?php

namespace Application\UserBundle\Controller;
use FOS\UserBundle\Controller\UserController as BaseUserController;
use FOS\UserBundle\Model\UserInterface;
use ZendPaginatorAdapter\DoctrineMongoDBAdapter;
use Zend\Paginator\Paginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends BaseUserController
{

}