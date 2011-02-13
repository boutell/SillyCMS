<?php

$container->loadFromExtension('security', 'config', array(
    'providers' => array(
        'default' => array(
            'users' => array(
                'foo' => array('password' => 'foo', 'roles' => 'ROLE_USER'),
            ),
        ),
        'digest' => array(
            'users' => array(
                'foo' => array('password' => 'foo', 'roles' => 'ROLE_USER, ROLE_ADMIN'),
            ),
        ),
        'basic' => array(
            'users' => array(
                'foo' => array('password' => '0beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33', 'roles' => 'ROLE_SUPER_ADMIN'),
                'bar' => array('password' => '0beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33', 'roles' => array('ROLE_USER', 'ROLE_ADMIN')),
            ),
        ),
        'doctrine' => array(
            'entity' => array('class' => 'SecurityBundle:User', 'property' => 'username')
        ),
        'service' => array(
            'id' => 'user.manager',
        ),
    ),

    'firewalls' => array(
        'simple' => array('pattern' => '/login', 'security' => false),
        'secure' => array('stateless' => true,
            'http_basic' => true,
            'http_digest' => true,
            'form_login' => true,
            'anonymous' => true,
            'switch_user' => true,
            'x509' => true,
            'logout' => true,
        ),
    ),

    'access_control' => array(
        array('path' => '/blog/524', 'role' => 'ROLE_USER', 'requires_channel' => 'https'),
        array('path' => '/blog/.*', 'attributes' => array('_controller' => '.*\\BlogBundle\\.*'), 'role' => 'IS_AUTHENTICATED_ANONYMOUSLY'),
    ),

    'role_hierarchy' => array(
        'ROLE_ADMIN' => 'ROLE_USER',
        'ROLE_SUPER_ADMIN' => array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_ALLOWED_TO_SWITCH'),
        'ROLE_REMOTE' => 'ROLE_USER,ROLE_ADMIN',
    ),
));
