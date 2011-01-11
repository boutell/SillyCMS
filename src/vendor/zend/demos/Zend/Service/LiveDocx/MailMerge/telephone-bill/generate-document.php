<?php

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'Bootstrap.php';


use Zend\Date\Date;
use Zend\Service\LiveDocx\Helper;
use Zend\Service\LiveDocx\MailMerge;

$mailMerge = new MailMerge();

$mailMerge->setUsername(DEMOS_ZEND_SERVICE_LIVEDOCX_USERNAME)
          ->setPassword(DEMOS_ZEND_SERVICE_LIVEDOCX_PASSWORD);

/*
 * ALTERNATIVE: Specify username and password in constructor
 */
            
/*
$mailMerge = new MailMerge(
    array (
        'username' => DEMOS_ZEND_SERVICE_LIVEDOCX_USERNAME,
        'password' => DEMOS_ZEND_SERVICE_LIVEDOCX_PASSWORD
    )
);
*/

$mailMerge->setLocalTemplate('template.doc');

$mailMerge->assign('customer_number', sprintf("#%'10s",  rand(0,1000000000)))
          ->assign('invoice_number',  sprintf("#%'10s",  rand(0,1000000000)))
          ->assign('account_number',  sprintf("#%'10s",  rand(0,1000000000)));

$billData = array (  
    'phone'         => '+22 (0)333 444 555',
    'date'          => Date::now()->toString(Date::DATE_LONG),
    'name'          => 'James Henry Brown',
    'service_phone' => '+22 (0)333 444 559',
    'service_fax'   => '+22 (0)333 444 558',
    'month'         => sprintf('%s %s', Date::now()->toString(Date::MONTH_NAME),
                                        Date::now()->toString(Date::YEAR)),
    'monthly_fee'   =>  '15.00',
    'total_net'     =>  '19.60',
    'tax'           =>  '19.00',
    'tax_value'     =>   '3.72',
    'total'         =>  '23.32'
);

$mailMerge->assign($billData);

$billConnections = array(
    array(
        'connection_number'   => '+11 (0)222 333 441',
        'connection_duration' => '00:01:01',
        'fee'                 => '1.15'
    ),
    array(
        'connection_number'   => '+11 (0)222 333 442',
        'connection_duration' => '00:01:02',
        'fee'                 => '1.15'
    ),
    array(
        'connection_number'   => '+11 (0)222 333 443', 
        'connection_duration' => '00:01:03', 
        'fee'                 => '1.15'
    ),
    array(
        'connection_number'   => '+11 (0)222 333 444',
        'connection_duration' => '00:01:04',
        'fee'                 => '1.15'
    )
);

$mailMerge->assign('connection', $billConnections);

$mailMerge->createDocument();

$document = $mailMerge->retrieveDocument('pdf');

unset($mailMerge);

file_put_contents('document.pdf', $document);
