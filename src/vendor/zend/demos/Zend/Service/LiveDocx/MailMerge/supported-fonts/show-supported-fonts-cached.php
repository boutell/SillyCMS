<?php

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'Bootstrap.php';


use Zend\Cache\Cache;
use Zend\Service\LiveDocx\Helper;
use Zend\Service\LiveDocx\MailMerge;

$cacheId = md5(__FILE__);

$cacheFrontendOptions = array(
    'lifetime' => 2592000, // 30 days
    'automatic_serialization' => true
);

$cacheBackendOptions = array(
    'cache_dir' => __DIR__ . '/cache'
);

if (!is_dir($cacheBackendOptions['cache_dir'])) {
    mkdir($cacheBackendOptions['cache_dir']);
}

$cache = Cache::factory('Core', 'File', $cacheFrontendOptions, $cacheBackendOptions);

if (! $fonts = $cache->load($cacheId)) {
    
    // Cache miss. Connect to backend service (expensive).
    
    $mailMerge = new MailMerge();
    
    $mailMerge->setUsername(DEMOS_ZEND_SERVICE_LIVEDOCX_USERNAME)
              ->setPassword(DEMOS_ZEND_SERVICE_LIVEDOCX_PASSWORD);
    
    $fonts = $mailMerge->getFontNames();
    
    $cache->save($fonts, $cacheId);
    
    unset($mailMerge);
    
} else {
    
    // Cache hit. Continue.
    
}

unset($cache);

Helper::printLine(
    PHP_EOL . 'Supported Fonts' .
    PHP_EOL . 
    PHP_EOL . 'The following fonts are installed on the backend server and may be used in templates. Fonts used in templates, which are NOT listed below, will be substituted. If you would like to use a font, which is not installed on the backend server, please contact your LiveDocx provider.' .
    PHP_EOL . 
    PHP_EOL . '(Note this method call is cached for maximum performance. The supported formats change very infrequently, hence, they are good candidates to be cached.)' .
    PHP_EOL . 
    PHP_EOL . Helper::arrayDecorator($fonts) . 
    PHP_EOL . 
    PHP_EOL
);

print(PHP_EOL);

unset($mailMerge);
