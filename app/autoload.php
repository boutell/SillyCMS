<?php

$vendorDir = __DIR__.'/../vendor';
$srcDir = __DIR__.'/../src';


use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony'                        => $vendorDir.'/symfony/src',
    'Application'                    => $srcDir,
    'Bundle'                         => $srcDir,
    'Sensio'                         => $srcDir,
    'FOS'                            => $srcDir,
    'Doctrine\\Common\\DataFixtures' => $vendorDir.'/doctrine-data-fixtures/lib',
    'Doctrine\\Common'               => $vendorDir.'/doctrine-common/lib',
    'Doctrine\\DBAL\\Migrations'     => $vendorDir.'/doctrine-migrations/lib',
    'Doctrine\\MongoDB'              => $vendorDir.'/doctrine-mongodb/lib',
    'Doctrine\\ODM\\MongoDB'         => $vendorDir.'/doctrine-mongodb-odm/lib',
    'Doctrine\\DBAL'                 => $vendorDir.'/doctrine-dbal/lib',
    'Doctrine'                       => $vendorDir.'/doctrine/lib',
    'Zend'                           => $vendorDir.'/zend/library',
));
$loader->registerPrefixes(array(
    'Swift_'           => $vendorDir.'/swiftmailer/lib/classes',
    'Twig_'            => $vendorDir.'/twig/lib',
    'Twig_Extensions_' => $vendorDir.'/twig-extensions/lib',
));
$loader->register();
