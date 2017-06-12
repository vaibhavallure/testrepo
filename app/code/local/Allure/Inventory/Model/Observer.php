<?php
define('ENCRYPTION_KEY', 'd0a7e7997b6d5fcd55f4b5c32611b87cd923e88837b63bf2941ef819dc8ca283');
class Allure_Inventory_Model_Observer
{

    public function setVendorSession(Varien_Event_Observer $observer)
    {
    	$params=Mage::app()->getRequest()->getParams();
    	
    	
    	if (!array_key_exists("auth_type",$params))
    		return ;
    	Mage::log($params,zend_log::DEBUG,"mylogs",TRUE);
    	$token = $params['token']; //$_REQUEST['token'];
   
    	if(!empty($token) && !empty($params['auth_type']))
    	{
    		if($params['auth_type']!="allure")
    			return;
    		//echo $token;
    		$session = $this->_getSession();
    		$jsonData=$this->mc_decrypt($token, ENCRYPTION_KEY);
    		Mage::log("recived data:".$jsonData,zend_log::DEBUG,"mylogs",TRUE);
    		$data = json_decode($jsonData);
    		Mage::getSingleton('core/session', array('name' => 'adminhtml'));
    		 
    		// supply username
    		$user = Mage::getModel('admin/user')->loadByUsername($data->username);
    		 
    		if (Mage::getSingleton('adminhtml/url')->useSecretKey()) {
    			Mage::getSingleton('adminhtml/url')->renewSecretUrls();
    		}
    		$session = Mage::getSingleton('admin/session');
    		$session->setIsFirstVisit(true);
    		$session->setUser($user);
    		$session->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());
    		Mage::dispatchEvent('admin_session_user_login_success',array('user'=>$user));
    		if (!$session->isLoggedIn()) {
    			$session->setIsFirstVisit(true);
    			$session->setUser($user);
    			$session->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());
    			Mage::dispatchEvent('admin_session_user_login_success',array('user'=>$user));
    		}
    	}
    	
    }
    function mc_decrypt($decrypt, $key){
    	$decrypt = explode('|', $decrypt.'|');
    	$decoded = base64_decode($decrypt[0]);
    	$iv = base64_decode($decrypt[1]);
    	if(strlen($iv)!==mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)){ return false; }
    	$key = pack('H*', $key);
    	$decrypted = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $decoded, MCRYPT_MODE_CBC, $iv));
    	$mac = substr($decrypted, -64);
    	$decrypted = substr($decrypted, 0, -64);
    	$calcmac = hash_hmac('sha256', $decrypted, substr(bin2hex($key), -32));
    	if($calcmac!==$mac){ return false; }
    	$decrypted = unserialize($decrypted);
    	return $decrypted;
    }
    
    protected function _getSession()
    {
    	$switchSessionName = 'adminhtml';
    	$currentSessionId = Mage::getSingleton('core/session')->getSessionId();
    	$currentSessionName = Mage::getSingleton('core/session')->getSessionName();
    	if ($currentSessionId && $currentSessionName && isset($_COOKIE[$currentSessionName])) {
    		$switchSessionId = $_COOKIE[$switchSessionName];
    		$this->_switchSession($switchSessionName, $switchSessionId);
    		if(Mage::getModel('admin/session')->isLoggedIn()){
    			return Mage::getSingleton('admin/session');
    		}
    	}
    }
    
    
    public function _switchSession($namespace, $id = null) {
    	session_write_close();
    	$GLOBALS['_SESSION'] = null;
    	$session = Mage::getSingleton('core/session');
    	if ($id) {
    		$session->setSessionId($id);
    	}
    	$session->start($namespace);
    }
}