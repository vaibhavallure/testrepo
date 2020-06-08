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
   
   const XML_ORDER_STATUS_CHANGE_ENABLED = 'allure_orders/order_status_settings/is_order_paid_status';
   const XML_ORDER_STATUS_AFTER_INVOICE = 'allure_orders/order_status_settings/order_status';
   
   const XML_ORDER_CANCEL_STATUS_ENABLED = 'allure_orders/order_status_settings/is_order_cancel_status';
   const XML_AFTER_ORDER_CANCEL_STATUS = 'allure_orders/order_status_settings/order_cancel_status';
   
   /**
    * Check signifyd plugin is active
    * @param int|string $storeId
    * @return boolean
    */
   public function isSignifydActive($storeId = null)
   {
       $flag = false;
       if( Mage::helper("core")->isModuleEnabled("Signifyd_Connect") ){
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
    * Check is order status value change after invoice
    * @param int|string $storeId
    */
   public function isOrderStatusChangeAfterPaymentCapture($storeId = null)
   {
       return Mage::getStoreConfig(self::XML_ORDER_STATUS_CHANGE_ENABLED, $storeId);
   }
   
   /**
    * get order status after payment capture
    * @param int|string $storeId
    */
   public function getOrderStatusAfterPaymentCapture($storeId = null)
   {
       return Mage::getStoreConfig(self::XML_ORDER_STATUS_AFTER_INVOICE, $storeId);
   }
   
   /**
    * is order cancellation status enabled
    * @param int|string $storeId
    */
   public function isOrderCancelStatusEnabled($storeId = null)
   {
       return Mage::getStoreConfig(self::XML_ORDER_CANCEL_STATUS_ENABLED, $storeId);
   }
   
   /**
    * Get cancel order status
    * @param int|string $storeId
    */
   public function getCancelOrderStatus($storeId = null)
   {
       return Mage::getStoreConfig(self::XML_AFTER_ORDER_CANCEL_STATUS, $storeId);
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
