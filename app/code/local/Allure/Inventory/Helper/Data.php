<?php
if (!defined('ENCRYPTION_KEY')) define('ENCRYPTION_KEY', 'd0a7e7997b6d5fcd55f4b5c32611b87cd923e88837b63bf2941ef819dc8ca283');
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
    
    const XML_PARENT_CATEGORY_ID = "inventory/category/parent_category_id";
    const XML_CHILD_CATEGGORY_ID = "inventory/category/child_category_id";
    
    public function getParentCategoryId(){
        return Mage::getStoreConfig(self::XML_PARENT_CATEGORY_ID);
    }
    
    public function getChildCategoryId(){
        return Mage::getStoreConfig(self::XML_CHILD_CATEGGORY_ID);
    }
    
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
    	$orderItems->getSelect()->order('main_table.product_id DESC');
    	
    	$fp = fopen($file, 'w');
    	$csvHeader = array(
    	    "Item Id",
            "Vendor Code",
            "Item Desciption",
            "Requested Qty",
          /*   "Proposed Qty", */
            "VMT Comment",
            "Vendor Comment",
            "Proposed Delivery Date"
           
            );
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
    	//	$pqty = $item->getProposedQty();
    		$comment = $item->getAdminComment();
    		$Vcomment = $item->getVendorComment();
//    		$status = $item->getStatus();
    		//$reqdelivery = $item->getRequestedDeliveryDate();
    		$propdelivery = $item->getProposedDeliveryDate();
    		//$total = $item->getTotalAmount();
    		
    		fputcsv($fp, array($id,$vendorCode,$name,$qty,$comment,$Vcomment,$propdelivery), ",");
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
        if(Mage::getStoreConfig("allure_vendor/manage_vendor/vendor"))
            $vendorName=Mage::helper('allure_vendor')->getVanderName(Mage::getStoreConfig("allure_vendor/manage_vendor/vendor"));
        else 
            $vendorName="CRYSTAL RISE";
        foreach ($options as $option){
            if($option['value']==$vendorId){
                $vendorName=$option['label'];
                break;
            }
        }
        return $vendorName;
    }
    public function sendEmail($po_id, $vendorEmail,$templateId,$adminEmail,$attachment=FALSE,$diffArray=array()){
        if($attachment)
            $this->createPOAttachment($po_id);
        $path = Mage::getBaseDir('var') . DS . 'export' . DS;
        $name   = 'purchase_order_'.$po_id.'.csv';
        $file = $path . DS . $name;
        //$vendorEmail =  explode(',', $vendorEmail);
        $emailVariables = array();
        $detailsTable="";
        if(!empty($diffArray)){
        $detailsTable = "
				<table cellspacing='0' cellpadding='0' border='0' width='650'><tr>
				<th style='background-color:#EAEAEA; border:1px solid; padding:4px;'>Product Name</th>
				<th style='background-color:#EAEAEA; border:1px solid; padding:4px;'>Old Admin Comment</th>
				<th style='background-color:#EAEAEA; border:1px solid; padding:4px;'>New Admin Comment</th>
				<th style='background-color:#EAEAEA; border:1px solid; padding:4px;'>Old Vendor Comment</th>
				<th style='background-color:#EAEAEA; border:1px solid; padding:4px;'>New Vendor Comment</th>
				<th style='background-color:#EAEAEA; border:1px solid; padding:4px;'>Old Delivery Date</th>
				<th style='background-color:#EAEAEA; border:1px solid; padding:4px;'>New Delivery Date</th>
                <th style='background-color:#EAEAEA; border:1px solid; padding:4px;'>Old Qty</th>
				<th style='background-color:#EAEAEA; border:1px solid; padding:4px;'>New Qty</th></tr>";
                foreach ($diffArray as $key=>$val)
                {
                    if($val['is_custom']){
                        $_product=Mage::getModel('inventory/customitem')->load($key);
                    }else {
                        $_product = Mage::getModel ( 'catalog/product' )->load ($key);
                    }
                    
                    $prodName = $_product->getName();
                    $oldAdminComment=($val['admin_comment_old'])?$val['admin_comment_old']:'-';
                    $newAdminComment=($val['admin_comment'])?$val['admin_comment']:'-';
                    $oldVendorComment=($val['vendor_comment_old'])?$val['vendor_comment_old']:'-';
                    $newVendorComment=($val['vendor_comment'])?$val['vendor_comment']:'-';
                    $oldDeliveryDate=($val['proposed_delivery_date_old'])?$val['proposed_delivery_date_old']:'-';
                    $newDeliveryDate=($val['proposed_delivery_date'])?$val['proposed_delivery_date']:'-';
                    $oldQty=($val['qty_old'])?$val['qty_old']:'-';
                    $qty=($val['qty'])?$val['qty']:'-';
                    
                   $rowColor = '#7ecc84';
                               
                   $detailsTable .= "<tr style='background-color:$rowColor'>
        				<td style='border:1px solid; padding:4px;'>".$prodName."</td>
        				<td style='border:1px solid; padding:4px;'>".$oldAdminComment."</td>
        				<td style='border:1px solid; padding:4px;'>".$newAdminComment."</td>
        				<td style='border:1px solid; padding:4px;'>".$oldVendorComment."</td>
        			    <td style='border:1px solid; padding:4px;'>".$newVendorComment."</td>
                        <td style='border:1px solid; padding:4px;'>".$oldDeliveryDate."</td>
        				<td style='border:1px solid; padding:4px;'>".$newDeliveryDate."</td>
                        <td style='border:1px solid; padding:4px;'>".$oldQty."</td>
                        <td style='border:1px solid; padding:4px;'>".$qty."</td></tr>";
                 } //End of Foreach
        }
       
        $emailVariables['detail_table'] = $detailsTable;
        $orderData=Mage::getModel("inventory/purchaseorder")->load($po_id);
        $storeId=$orderData->getStoreId();
        $orderItems=Mage::getModel('inventory/orderitems')->getCollection()->addFieldToFilter('po_id',$po_id);
        $order_url="";
  
       
        $emailVariables['items_ordered'] = count($orderItems);
        $emailVariables['order_id'] = $po_id;
        $emailVariables['order_status'] = $this->getOrderStatus($orderData->getStatus());
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
        $emailTemplate  = Mage::getModel('core/email_template');
        if($templateId)
            $emailTemplate  = $emailTemplate->load($templateId);
        $emailTemplate->setTemplateSubject('Maria Tash Purchase Order #'.$po_id);
        
        $sender= array('name'=>Mage::getStoreConfig("trans_email/ident_general/name"), 'email'=> Mage::getStoreConfig("trans_email/ident_general/email"));
        $emailTemplate->setSenderName($sender['name']);
        $emailTemplate->setSenderEmail($sender['email']);
        $copyTo = Mage::getStoreConfig('allure_vendor/general/copy_to',$storeId);
        if (!empty($copyTo)) {
            $copyTo =  explode(',', $copyTo);
        }
        if($vendorEmail)
            $sendEmail = $vendorEmail;
        else 
            $sendEmail = $adminEmail;
        
        
        $copyMethod = Mage::getStoreConfig('allure_vendor/general/copy_method');
       
        if ($copyTo && $copyMethod == 'bcc') {
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
        
        try {
            if ($attachment){
            $emailTemplate->getMail()->createAttachment(
                file_get_contents($file),
                Zend_Mime::TYPE_OCTETSTREAM,
                Zend_Mime::DISPOSITION_ATTACHMENT,
                Zend_Mime::ENCODING_BASE64,
                $name
                );
            }
            $emailTemplate->setDesignConfig(array('area' => 'frontend'))
            ->sendTransactional(
                $templateId,
                $sender,
                $sendEmail,
                null,
                $emailVariables
                );
        } catch (Exception $e) {
            Mage::log("Exception Occured".$e->getMessage(), Zend_Log::DEBUG,'PO_order.log',true);
        }
        if (!$emailTemplate->getSentSuccess()) {
            Mage::log('Mail Exception:', Zend_Log::DEBUG, 'PO_order.log', true);
        }
        else {
            Mage::log('Email Sucess:'.$po_id, Zend_Log::DEBUG, 'PO_order.log', true);
        }
    } 
}
