<?php
define('ENCRYPTION_KEY', 'd0a7e7997b6d5fcd55f4b5c32611b87cd923e88837b63bf2941ef819dc8ca283');
class Allure_Inventory_Helper_Data extends Mage_Core_Helper_Abstract {

    const ORDER_STATUS_NEW="new";
    const ORDER_STATUS_DRAFT="draft";
    const ORDER_STATUS_REJECT="reject";
    const ORDER_STATUS_CLOSED="closed";
    const ORDER_STATUS_CANCEL="cancel";
    const ORDER_STATUS_PARTIALLY_CLOSED="partially_closed";
    const ORDER_STATUS_ACCEPT="accept";
    const ORDER_STATUS_PARTIALLY_SHIPPED="partially_shipped";
    const ORDER_STATUS_FULLY_SHIPPED="fully_shipped";
    
	public function getOrderStatusArray(){
	
		$statusArray=array();
		$statusArray['draft']='Draft';
		$statusArray['new']='New';
		$statusArray['closed']='Closed';
		$statusArray['cancel']='Cancel';
		$statusArray['partially_closed']='Partially Closed';
		$statusArray['reject']='Reject';
		$statusArray['accept']='In Production';
		$statusArray['partially_shipped']='Partially Shipped';
		$statusArray['fully_shipped']='Fully Shipped';
		return $statusArray;
	}
	public function getOrderStatus($status){
	   $array=$this->getOrderStatusArray();
	   return $array[$status];
	}
	
	public function sendOrderEmailToVendor($po_id,$orderData,$itemArray,$vendorEmail){
		Mage::log('mail sending Step 1', Zend_Log::DEBUG, 'mylogs', true);

			$this->createPOAttachment($po_id);
			$path = Mage::getBaseDir('var') . DS . 'export' . DS;
			$name   = 'purchase_order_'.$po_id.'.csv';
			$file = $path . DS . $name;
			
			$order_url=$this->getOrderEncodeUrl($vendorEmail,$po_id);
			$emailVariables = array();
			$emailVariables['items_ordered'] = count($itemArray);
			$emailVariables['order_id'] = $po_id;
			$emailVariables['order_url'] = $order_url;
			$base_url=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'email-header.png';
			$logo=str_replace("index.php/", "",$base_url);
			
			$emailVariables['logo'] = $logo;
			$emailVariables['order_amount'] = $orderData['total_amount'];
			
			
			if (!Mage::getStoreConfig('allure_vendor/general/enabled')) {
				Mage::log("Order emails are disabled ",Zend_Log::DEBUG,"mylogs",true);
				return;
			}
			$templateId =  Mage::getStoreConfig('allure_vendor/general/template');
				
			Mage::log('template Id'. $templateId, Zend_Log::DEBUG, 'mylogs', true);
			$emailTemplate  = Mage::getModel('core/email_template');
	
			$emailTemplate  = $emailTemplate->load($templateId);
				
				
			$emailTemplate->setTemplateSubject('Maria Tash Purchase Order #'.$po_id);
				
			// Get General email address (Admin->Configuration->General->Store Email Addresses)
	
			$sender= array('name'=>Mage::getStoreConfig("trans_email/ident_general/name"), 'email'=> Mage::getStoreConfig("trans_email/ident_general/email"));
	
			Mage::log('sender : '.$sender, Zend_Log::DEBUG,'cron_data',true);
			$emailTemplate->setSenderName($sender['name']);
			$emailTemplate->setSenderEmail($sender['email']);
			Mage::log('mail sending Step 3', Zend_Log::DEBUG, 'mylogs', true);
			$copyTo = Mage::getStoreConfig('allure_vendor/general/copy_to');
			if (!empty($copyTo)) {
				$copyTo =  explode(',', $copyTo);
			}
			Mage::log('Copy To:', Zend_Log::DEBUG, 'mylogs', true);
			Mage::log($copyTo, Zend_Log::DEBUG, 'mylogs', true);
			
			$sendEmail = $vendorEmail;
			Mage::log($sendEmail, Zend_Log::DEBUG, 'mylogs', true);
			$copyMethod = Mage::getStoreConfig('allure_vendor/general/copy_method');
			Mage::log('Method:', Zend_Log::DEBUG, 'mylogs', true);
			Mage::log($copyMethod, Zend_Log::DEBUG, 'mylogs', true);
			//Mage::log($copyMethod,Zend_Log::DEBUG,"ipic",true);
			if ($copyTo && $copyMethod == 'bcc') {
				//Mage::log("Bcc appeared",Zend_Log::DEBUG,"ipic",true);
	
				foreach ($copyTo as $email)
				{
					$emailTemplate->getMail()->addBcc($email);
				}
			}
		
			if ($copyTo && $copyMethod == 'copy') {
				foreach ($copyTo as $email) {
					Mage::log($email, Zend_Log::DEBUG, 'mylogs', true);
					$emailTemplate->getMail()->addCc($email);
				}
			}
	
			Mage::log("Before Email copy", Zend_Log::DEBUG,'mylogs',true);
			//Mage::log($emailVariables, Zend_Log::DEBUG,'mylogs',true);
			/*  $emailTemplate->send($sendEmail, "Natural Source Order Cron Updates", $emailVariables); */
				
			//$mailTemplate = Mage::getModel('core/email_template');
			/* @var $mailTemplate Mage_Core_Model_Email_Template */
			Mage::log("Before Email Send", Zend_Log::DEBUG,'mylogs',true);
			try {
				$emailTemplate->getMail()->createAttachment(
						file_get_contents($file),
						Zend_Mime::TYPE_OCTETSTREAM,
						Zend_Mime::DISPOSITION_ATTACHMENT,
						Zend_Mime::ENCODING_BASE64,
						$name
						);
				
				$emailTemplate->setDesignConfig(array('area' => 'frontend'))
				->sendTransactional(
						$templateId,
						$sender,
						$sendEmail,
						null,
						$emailVariables
						);
			} catch (Exception $e) {
				Mage::log("Exception Occured".$e->getMessage(), Zend_Log::DEBUG,'mylogs',true);
			}
			Mage::log("After Email Send", Zend_Log::DEBUG,'mylogs',true);
			if (!$emailTemplate->getSentSuccess()) {
				Mage::log('mail sending exception', Zend_Log::DEBUG, 'mylogs', true);
			}
			else {
				Mage::log('mail sending done', Zend_Log::DEBUG, 'mylogs', true);
			}
	} 
    public function getOrderEncodeUrl($vendorEmail,$po_id){
    	$adminUserModel = Mage::getModel('admin/user')->load($vendorEmail,'email');
    	$data = json_encode(array('username'=>$adminUserModel->getUsername(),'id'=>$po_id));
    	$encrypted_data = $this->mc_encrypt($data, ENCRYPTION_KEY);
    	$encoded_url=urlencode($encrypted_data);
    	$formKey = Mage::getSingleton('core/session')->getFormKey();
    	$admin_url=Mage::helper('adminhtml')->getUrl('adminhtml/inventory_purchase/view/id/'.$po_id.'/', array('_secure' => true,'form_key' => $formKey));
    	Mage::log('Order Url:'.$admin_url.'auth_type/allure?token='.$encoded_url, Zend_Log::DEBUG, 'mylogs', true);
    	return $admin_url.'auth_type/allure?token='.$encoded_url;
    
    }
    public function mc_encrypt($encrypt, $key){
    	$encrypt = serialize($encrypt);
    	$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM);
    	$key = pack('H*', $key);
    	$mac = hash_hmac('sha256', $encrypt, substr(bin2hex($key), -32));
    	$passcrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $encrypt.$mac, MCRYPT_MODE_CBC, $iv);
    	$encoded = base64_encode($passcrypt).'|'.base64_encode($iv);
    	return $encoded;
    }
   
    public function createPOAttachment($id){
    	$io = new Varien_Io_File();
    	$path = Mage::getBaseDir('var') . DS . 'export' . DS;
    	$name   = 'purchase_order_'.$id.'.csv';
    	$file = $path . DS . $name;
    	$io->setAllowCreateFolders(true);
    	$io->open(array('path' => $path));
    	$io->streamOpen($file, 'w+');
    	$io->streamLock(true);
    	//$this->writeData($websiteId);
    	
    	$orderItems=Mage::getModel('inventory/orderitems')->getCollection()->addFieldToFilter('po_id',$id);
    	
    	$fp = fopen($file, 'w');
    	$csvHeader = array("Item Id","Vendor Code", "Item Desciption","Qty","VMT Comment");
    	fputcsv( $fp, $csvHeader,",");
    	foreach ($orderItems as $item){
    	    if($item->getIsCustom()){
    	        $_product=Mage::getModel('inventory/customitem')->load($item->getProductId());
    	    }else {
    	        $_product = Mage::getModel ( 'catalog/product' )->load ($item->getProductId() );
    	    }
    		
    		$id = $item->getProductId();
    		$vendorCode = $_product->getVendorItemNo();
    		$name = $_product->getName();
    		$qty = $item->getRequestedQty();
    		$comment = $item->getAdminComment();
    		fputcsv($fp, array($id,$vendorCode,$name,$qty,$comment), ",");
    	}
    	fclose($fp);
    }
    public function getPurchaseOrderItems($storeId){
        $user = Mage::getSingleton('admin/session');
        $userId = $user->getUser()->getUserId();
        $data = Mage::getModel('inventory/insertitem')->getCollection()->addFieldToFilter('store_id',$storeId)->addFieldToFilter('user_id',$userId)->getData();
        //Mage::log($data->getData(),Zend_log::DEBUG,'purchase',true);
        return $data;

    }
    public function getVendorName($vendorId){
        $attribute = Mage::getSingleton('eav/config')
            ->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'primary_vendor');

        if ($attribute->usesSource()) {
            $options = $attribute->getSource()->getAllOptions(false);
        }
        $vendorName="DMEO";
        foreach ($options as $option){
            if($option['value']==$vendorId){
                $vendorName=$option['label'];
                break;
            }
        }
        return $vendorName;
    }
    public function sendEmail($po_id, $vendorEmail,$templateId,$adminEmail){
        Mage::log('mail sending Step 1', Zend_Log::DEBUG, 'mylogs', true);
        
        $this->createPOAttachment($po_id);
        $path = Mage::getBaseDir('var') . DS . 'export' . DS;
        $name   = 'purchase_order_'.$po_id.'.csv';
        $file = $path . DS . $name;
        
        $orderData=Mage::getModel("inventory/purchaseorder")->load($po_id);
        $storeId=$orderData->getStoreId();
        $orderItems=Mage::getModel('inventory/orderitems')->getCollection()->addFieldToFilter('po_id',$id);
        $order_url="";
        if(isset($vendorEitems_orderedmail))
            $order_url=$this->getOrderEncodeUrl($vendorEmail,$po_id);
        $emailVariables = array();
        $emailVariables['items_ordered'] = count($orderItems);
        $emailVariables['order_id'] = $po_id;
        $emailVariables['shipment_status'] = $this->getOrderStatus($orderData->getStatus());
        $emailVariables['order_url'] = $order_url;
        $base_url=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'email-header.png';
        $logo=str_replace("index.php/", "",$base_url);
        
        $emailVariables['logo'] = $logo;
        $emailVariables['order_amount'] = $orderData->getTotalAmount();
        
        
        if (!Mage::getStoreConfig('allure_vendor/general/enabled',$storeId)) {
            Mage::log("Order emails are disabled ",Zend_Log::DEBUG,"mylogs",true);
            return;
        }
        
        Mage::log('template Id'. $templateId, Zend_Log::DEBUG, 'mylogs', true);
        $emailTemplate  = Mage::getModel('core/email_template');
        if($templateId)
            $emailTemplate  = $emailTemplate->load($templateId);
        
        
        $emailTemplate->setTemplateSubject('Maria Tash Purchase Order #'.$po_id);
        
        // Get General email address (Admin->Configuration->General->Store Email Addresses)
        
        $sender= array('name'=>Mage::getStoreConfig("trans_email/ident_general/name"), 'email'=> Mage::getStoreConfig("trans_email/ident_general/email"));
        
        Mage::log('sender : '.$sender, Zend_Log::DEBUG,'cron_data',true);
        $emailTemplate->setSenderName($sender['name']);
        $emailTemplate->setSenderEmail($sender['email']);
        Mage::log('mail sending Step 3', Zend_Log::DEBUG, 'mylogs', true);
        $copyTo = Mage::getStoreConfig('allure_vendor/general/copy_to',$storeId);
        if (!empty($copyTo)) {
            $copyTo =  explode(',', $copyTo);
        }
        Mage::log('Copy To:', Zend_Log::DEBUG, 'mylogs', true);
        Mage::log($copyTo, Zend_Log::DEBUG, 'mylogs', true);
        
        if($vendorEmail)
            $sendEmail = $vendorEmail;
        else 
            $sendEmail = $adminEmail;
        
        Mage::log($sendEmail, Zend_Log::DEBUG, 'mylogs', true);
        $copyMethod = Mage::getStoreConfig('allure_vendor/general/copy_method');
        Mage::log('Method:', Zend_Log::DEBUG, 'mylogs', true);
        Mage::log($copyMethod, Zend_Log::DEBUG, 'mylogs', true);
        //Mage::log($copyMethod,Zend_Log::DEBUG,"ipic",true);
        if ($copyTo && $copyMethod == 'bcc') {
            //Mage::log("Bcc appeared",Zend_Log::DEBUG,"ipic",true);
            
            foreach ($copyTo as $email)
            {
                $emailTemplate->getMail()->addBcc($email);
            }
        }
        
        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                Mage::log($email, Zend_Log::DEBUG, 'mylogs', true);
                $emailTemplate->getMail()->addCc($email);
            }
        }
        if (isset($vendorEmail) && !empty($adminEmail))	{
            foreach ($adminEmail as $email) {
                Mage::log($email, Zend_Log::DEBUG, 'mylogs', true);
                $emailTemplate->getMail()->addCc($email);
            }
        }
        
        Mage::log("Before Email copy", Zend_Log::DEBUG,'mylogs',true);
   
        Mage::log("Before Email Send", Zend_Log::DEBUG,'mylogs',true);
        try {
            $emailTemplate->getMail()->createAttachment(
                file_get_contents($file),
                Zend_Mime::TYPE_OCTETSTREAM,
                Zend_Mime::DISPOSITION_ATTACHMENT,
                Zend_Mime::ENCODING_BASE64,
                $name
                );
            
            $emailTemplate->setDesignConfig(array('area' => 'frontend'))
            ->sendTransactional(
                $templateId,
                $sender,
                $sendEmail,
                null,
                $emailVariables
                );
        } catch (Exception $e) {
            Mage::log("Exception Occured".$e->getMessage(), Zend_Log::DEBUG,'mylogs',true);
        }
        Mage::log("After Email Send", Zend_Log::DEBUG,'mylogs',true);
        if (!$emailTemplate->getSentSuccess()) {
            Mage::log('mail sending exception', Zend_Log::DEBUG, 'mylogs', true);
        }
        else {
            Mage::log('mail sending done', Zend_Log::DEBUG, 'mylogs', true);
        }
    } 
}
