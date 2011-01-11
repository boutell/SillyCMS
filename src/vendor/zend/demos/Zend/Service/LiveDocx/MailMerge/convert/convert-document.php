<?php

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'Bootstrap.php';


/**
 * Converting documents between supported formats
 * 
 * The primary goal of the Zend Framework LiveDocx component is to populate templates
 * with textual data to generate word processing documents. It can, however,
 * also be used to convert word processing documents between supported formats.
 * 
 * For a list of supported file formats see: http://is.gd/6YKDu
 * 
 * In this demo application, the file 'document.doc' is converted to 'document.pdf'
 * 
 * In a future version of the LiveDocx service, a converter component will be
 * made available.
 */

use Zend\Service\LiveDocx\MailMerge;

$mailMerge = new MailMerge();

$mailMerge->setUsername(DEMOS_ZEND_SERVICE_LIVEDOCX_USERNAME)
          ->setPassword(DEMOS_ZEND_SERVICE_LIVEDOCX_PASSWORD);

$mailMerge->setLocalTemplate('document.doc');

$mailMerge->assign('dummyFieldName', 'dummyFieldValue'); // necessary as of LiveDocx 1.2

$mailMerge->createDocument();

$document = $mailMerge->retrieveDocument('pdf');

file_put_contents('document.pdf', $document);

unset($mailMerge);
