<?php

class Allure_Facebook_Model_Session extends Varien_Object
{
	private $_client;
	private $_payload;
	private $_signature;

	public function __construct()
	{
		if($this->getCookie()) {
			list($encodedSignature, $payload) = explode('.', $this->getCookie(), 2);
			
    		//decode data
			$signature = base64_decode(strtr($encodedSignature, '-_', '+/'));
    		$data = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
    		
    		$this->setData($data);
    		
    		//compatibility hack
    		$this->setUid((string)$this->getUserId());
    		
    		$this->_signature = $signature;
    		$this->_payload = $payload;
		}
	}
	
	public function isConnected()
    {
		return $this->validate();
    }

    public function validate()
    {
    	if(!$this->hasData()) {
    		return false;
    	}
    	
		$expectedSignature = hash_hmac('sha256', $this->_payload, Mage::getSingleton('facebook/config')->getSecret(), true);
		return ($expectedSignature==$this->_signature);
    }
     
    public function getCookie()
    {
    	return Mage::app()->getRequest()->getCookie('fbsr_'.Mage::getSingleton('facebook/config')->getApiKey(), false);
    }
	     
	public function getClient()
	{
		if(is_null($this->_client)) {
			$this->_client = Mage::getModel('facebook/client',array(
									Mage::getSingleton('facebook/config')->getApiKey(),
									Mage::getSingleton('facebook/config')->getSecret(),
									$this
							));
		}
		return $this->_client;
	}
	
}
