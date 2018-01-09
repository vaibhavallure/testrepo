<?php

class Allure_Customer_Model_Observer
{
    public function saveDefaultCustomerWebsite(Varien_Event_Observer $observer)
    {
    	$websiteId = Mage::getStoreConfig('customer/create_account/customer_default_website');
    	$storeId = Mage::app()
    	->getWebsite($websiteId)
    	->getDefaultGroup()
    	->getDefaultStoreId();
    	$attr = Mage::getModel('eav/entity_attribute')->load('website_id', 'attribute_code');
    	$attr->setDefaultValue($websiteId);
    	$attr->save();
    	
    	$attrStore = Mage::getModel('eav/entity_attribute')->load('store_id', 'attribute_code');
    	$attrStore->setDefaultValue($storeId);
    	$attrStore->save();
    	return ;
    }
    public function setcustomerStatusActive(){
    	Mage::log("Setting Customer active",Zend_log::DEBUG,'customer_activate',true);
    	$customerCollection = Mage::getModel('customer/customer')->getCollection();
    	$customerCollection->addFieldToFilter( 'am_is_activated', '1' );
    	if(count($customerCollection)){
    		foreach ($customerCollection as $customer){
    			$customer = Mage::getModel('customer/customer')->load($customer->getId());
    			$customer->setAmIsActivated(2);
    			$customer->save();
    		}
    	}
    	
    }
    public function setcustomerGroup(){
        $_log_file = "wholesale_order_customer.log";
        $_general_group     = 1;
        $_wholesale_group   = 2;
       
            
        try{
            $orderCollection = Mage::getModel("sales/order")
            ->getCollection()
            ->addFieldToFilter("customer_group_id",$_wholesale_group);
            $orderCollection->getSelect()->group('customer_id');
            
            Mage::log("order count:- ".$orderCollection->getSize(),Zend_log::DEBUG,$_log_file,true);
            $customerEmails=array();
            foreach ($orderCollection as $order){
                $groupId    = $order->getCustomerGroupId();
                $customerId = $order->getCustomerId();
                $customer   = Mage::getModel("customer/customer")->load($customerId);
                $customerGroupId = $customer->getGroupId();
                if($groupId != $customerGroupId){
                    try{
                        $customerEmails[]=$customer->getEmail();
                        $customer->setGroupId($_wholesale_group);
                        $customer->save();
                        Mage::log("email-:".$customer->getEmail()." of group switch to wholesale",Zend_log::DEBUG,$_log_file,true);
                    }catch (Exception $e){
                        Mage::log("Sub - ".$e->getMessage(),Zend_log::DEBUG,$_log_file,true);
                    }
                }
            }
            if(count($customerEmails) > 0){
                $emails = trim(Mage::getStoreConfig('customer/wholesale_reports/emails'));
                if (! $emails)
                    return;
                $emails = explode(',', $emails);
                     $mail = new Zend_Mail();
                     $mailbody = '<style type="text/css">';
                     $mailbody .= '.ExternalClass *{line-height:0;}';
                     $mailbody .= 'div,p,a,li,td {-webkit-text-size-adjust:none;-moz-text-size-adjust:none;text-size-adjust:none;-ms-text-size-adjust:none;}';
                     $mailbody .= '</style><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
                     $mailbody .= '<div   style="border-top: 3px solid white; text-align: center;float:left;background-color:#374254">';
                     $mailbody .= '<table width="300"  cellpadding="7" >';
                     $mailbody .= '<tbody>';
                     foreach ($customerEmails as $singleEmail) {
                     $mailbody .= '<tr>';
                     $mailbody .= '<td style="text-align: left;"><span style="color:#FFFFFF"><span style="font-size:16px">' . $singleEmail. '</span></span></td>';
                     $mailbody .= '</tr>';
                     $mailbody .= '<tr>';
                     }
          
                     $mailbody .= '</tbody>';
                     $mailbody .= '</table>';
                     $mailbody .= '</div>';
                     $mailbody .= '</tbody>';
                     $mailbody .= '</table>';
                     $mailbody .= '</div>';
                     $mailbody .= '</div>';
                     
                     foreach ($rows as $row) {
                         $mailbody .= '<tr>';
                         foreach ($row as $value) {
                             $mailbody .= '<td>' . $value . '</td>';
                         }
                         $mailbody .= '</tr>';
                     }
                     $mailbody .= '</table>';
                     // Mage::log($mailbody);
                     /* Sender Email */
                     $sender = Mage::getStoreConfig('trans_email/ident_general/email');
                     
                     $mail->setBodyHtml($mailbody)
                     ->setSubject("Wholesale customers Revert")
                     ->addTo($emails)
                     ->setFrom($sender, "Wholesale customers");
                     
                     try {
                         $mail->send();
                     } catch (Mage_Core_Exception $e) {
                         Mage::log("Main - ".$e->getMessage(),Zend_log::DEBUG,$_log_file,true);
                     } catch (Exception $e) {
                         Mage::logException($e);
                     }
            }
            
            
        }catch (Exception $e){
            Mage::log("Main - ".$e->getMessage(),Zend_log::DEBUG,$_log_file,true);
        }
        Mage::log("Finish ",Zend_log::DEBUG,$_log_file,true);
    }
   
}
