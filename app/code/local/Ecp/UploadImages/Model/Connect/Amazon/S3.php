<?php

class Ecp_UploadImages_Model_Connect_Amazon_S3 
{
	private static $connection;

	public function connect() 
	{
		if (!self::$connection) {
			$aws_key = Mage::getStoreConfig('allure_imagecdn/amazons3/access_key_id');
	        $aws_secret_key = Mage::getStoreConfig('allure_imagecdn/amazons3/access_key_secret');

	        self::$connection = new Zend_Service_Amazon_S3($aws_key, $aws_secret_key);
	    }

	    return self::$connection;
	}
}