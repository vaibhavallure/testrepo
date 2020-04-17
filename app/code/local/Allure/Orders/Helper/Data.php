<?php
/**
 * 
 * @author allure
 *
 */
class Allure_Orders_Helper_Data extends Mage_Core_Helper_Abstract
{
   const XML_ORDERS_SETTINGS_ENABLED = 'allure_orders/settings/enabled';
   const XML_ORDER_CONFIRMATION_EMAIL_AFTER_INVOICE = 'allure_orders/settings/order_email_after_invoice';
   const XML_ORDER_CANCELLATION_EMAIL = 'allure_orders/settings/cancel_order_email';
   
   /**
    * Check signifyd plugin is active
    * @param int|string $storeId
    * @return boolean
    */
   private function isSignifydActive($storeId = null)
   {
       $flag = false;
       if( Mage::helper("core")->isModuleEnabled("Signifyd_Connect") ){
           Mage::log("Signifyd_Connect---",Zend_Log::DEBUG,'abc.log',true);
           $isEnabled = Mage::getStoreConfig("signifyd_connect/settings/enabled", $storeId);
           if($isEnabled)
           {
               $flag = true;
           }
       }
       return $flag;
   }
   
   /**
    * Check module is active
    * @param int|string $storeId
    * @return mixed|string|NULL
    */
   public function isEnabled($storeId = null) 
   {
      return Mage::getStoreConfig(self::XML_ORDERS_SETTINGS_ENABLED, $storeId);
   }
   
   /**
    * Check order confirmation email
    * @param int|string $storeId
    * @return mixed|string|NULL
    */
   public function isAllowOrderConfirmationEmailAfterInvoice($storeId = null)
   {
       return Mage::getStoreConfig(self::XML_ORDER_CONFIRMATION_EMAIL_AFTER_INVOICE, $storeId);
   }
   
   /**
    * Check order cancellation email
    * @param int|string $storeId
    * @return mixed|string|NULL
    */
   public function isAllowOrderCancellationEmail($storeId = null)
   {
       return Mage::getStoreConfig(self::XML_ORDER_CANCELLATION_EMAIL, $storeId);
   }
   
   /**
    * Check order confrimation mail & signifyd setting 
    * for order confirmation email
    * @param int|string $storeId
    * @return boolean
    */
   public function canSendConfirmationEmail($storeId = null)
   {
       $enable = $this->isEnabled($storeId);
       $isAllowOrderEmailAfterInvoice = $this->isAllowOrderConfirmationEmailAfterInvoice($storeId);
       $isSignifydActive = $this->isSignifydActive($storeId);
       if($enable && $isAllowOrderEmailAfterInvoice && $isSignifydActive)
       {
           return true;
       }
       return false;
   }
   
   /**
    * Check order cancellation mail & signifyd setting
    * for order cancellation email
    * @param int|string $storeId
    * @return boolean
    */
   public function canSendOrderCancellationEmail($storeId = null)
   {
       $enable = $this->isEnabled($storeId);
       $isAllowOrderCancelEmail = $this->isAllowOrderCancellationEmail($storeId);
       $isSignifydActive = $this->isSignifydActive($storeId);
       if($enable && $isAllowOrderCancelEmail && $isSignifydActive)
       {
           return true;
       }
       return false;
   }

   public function isSuccessPageChange($storeId = null)
   {
       return $this->canSendConfirmationEmail($storeId);
   }
}
