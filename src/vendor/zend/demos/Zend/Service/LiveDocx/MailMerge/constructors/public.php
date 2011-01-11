<?php

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'Bootstrap.php';


use Zend\Service\LiveDocx\Helper;
use Zend\Service\LiveDocx\MailMerge;

Helper::printLine(
    PHP_EOL . 'Using the Free Public Server' .
    PHP_EOL . 
    PHP_EOL . 'This sample application illustrates how to use the Zend Framework LiveDocx component with the free, public LiveDocx server.' .
    PHP_EOL .
    PHP_EOL
);

$mailMerge = new MailMerge();

$mailMerge->setUsername(DEMOS_ZEND_SERVICE_LIVEDOCX_USERNAME)
          ->setPassword(DEMOS_ZEND_SERVICE_LIVEDOCX_PASSWORD);

$mailMerge->getTemplateFormats(); // then call methods as usual

printf('Username : %s%sPassword : %s%s    WSDL : %s%s%s',
    $mailMerge->getUsername(),
    PHP_EOL,
    $mailMerge->getPassword(),
    PHP_EOL,
    $mailMerge->getWSDL(),
    PHP_EOL,
    PHP_EOL
);

unset($mailMerge);

// -----------------------------------------------------------------------------

// Alternatively, you can pass username and password in the constructor.

$mailMerge = new MailMerge(
    array (
        'username' => DEMOS_ZEND_SERVICE_LIVEDOCX_USERNAME,
        'password' => DEMOS_ZEND_SERVICE_LIVEDOCX_PASSWORD,
    )
);

$mailMerge->getTemplateFormats(); // then call methods as usual

printf('Username : %s%sPassword : %s%s    WSDL : %s%s%s',
    $mailMerge->getUsername(),
    PHP_EOL,
    $mailMerge->getPassword(),
    PHP_EOL,
    $mailMerge->getWSDL(),
    PHP_EOL,
    PHP_EOL
);

unset($mailMerge);
