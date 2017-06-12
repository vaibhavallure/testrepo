<?php

require_once '../app/Mage.php';

Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

define('MAGENTO', Mage::getBaseDir());

require_once Mage::getBaseDir('lib') . DS . 'AWSSDKforPHP/sdk.class.php';

$s3 = new AmazonS3();

$s3->disable_ssl_verification();

$bucket = Mage::getStoreConfig('imagecdn/amazons3/bucket');

$prex = "/" . preg_quote('test_s3_1_6.png') . "/i";

$response = $s3->get_object_list($bucket, array(
    // 'pcre' => $prex,
    // 'pcre' => '/pdf/i'
    // 'prefix' => 'TEST01',
    // 'pcre' => '/test01/i',
    'pcre' => '/magentoimport/i',
));

if (count($response) >0 ) {
    
    unset($response[0]);
    var_dump($response);

}