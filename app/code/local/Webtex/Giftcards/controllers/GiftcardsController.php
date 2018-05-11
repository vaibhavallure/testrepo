<?php

class Webtex_Giftcards_GiftcardsController extends Mage_Core_Controller_Front_Action
{
    protected function _initAction() {
        $this->loadLayout()
        ->_setActiveMenu($this->_menu_path);
      
        return $this;
    }
    public function indexAction()
    {
        $this->_redirect('*/*/balance');
    }

    public function balanceAction()
    {
        if (!Mage::helper('customer')->isLoggedIn()) {
            Mage::getSingleton('customer/session')->authenticate($this);
            return;
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    public function printAction()
    {
        if (($cardCode = $this->getRequest()->getParam('code'))) {
            $this->loadLayout('print');
            $this->renderLayout();
        } else {
            $this->_redirect('/');
        }
    }
    public function confirmAction()
    {
       try {
           $giftCardCode = trim((string)$this->getRequest()->getParam('code'));
          
           if(!empty($giftCardCode)){
               $card = Mage::getModel('giftcards/giftcards')->load($giftCardCode, 'card_code');
               
               if($card->getOrderId()){
                   $order = Mage::getModel('sales/order')->load($card->getOrderId());
                   $mailTemplate = Mage::getModel('core/email_template');
                   $template='giftcards/email/read_template';
                   $storeId=1;
                   
                   $post = array(
                       'amount'        => $this->_addCurrencySymbol($card->getAmount(),$order->getCurrency()),
                       'code'          => $card->getCardCode(),
                       'email-to'      => $card->getMailTo(),
                       'email-from'    => Mage::getStoreConfig('trans_email/ident_general/email'),
                       'recipient'     => $card->getMailToEmail(),
                       'store-phone'   => Mage::getStoreConfig('general/store_information/phone'),
                   );
                   
                   if(empty($mail)) {
                       $mail = $order->getCustomerEmail() ;
                   }
                   
                 //  Mage::getModel('giftcards/giftcards')->_send($post, 'giftcards/email/email_template', $mail, $storeId);
                   
                   if ($mail) {
                       $translate = Mage::getSingleton('core/translate');
                       $translate->setTranslateInline(false);
                       $postObject = new Varien_Object();
                       $postObject->setData($post);
                       $postObject->setStoreId($storeId);
                       $mailTemplate = Mage::getModel('core/email_template');
                       $mailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
                       ->sendTransactional(
                           Mage::getStoreConfig($template, $storeId),
                           'general',
                           $mail,
                           null,
                           array('data' => $postObject)
                           );
                       $translate->setTranslateInline(true);
                   } else {
                       throw new Exception('Invalid recipient email address.');
                   }
                   Mage::getSingleton('adminhtml/session')->addSuccess("Thank you email sent");
                   $this->_redirect("*/*/confirmmessage");
               }else {
                   Mage::getSingleton('adminhtml/session')->addError("Error Occured");
                   $this->_redirect("/");
               }
           }else {
               Mage::getSingleton('adminhtml/session')->addError("Error Occured");
               $this->_redirect("/");
           }
       } catch (Exception $e) {
           Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
           Mage::log("Exception Occured:".$e->getMessage(),Zend_log::DEBUG,'giftcard.log',TRUE);
       }
    }
    public function _addCurrencySymbol($amount, $currencyCode)
    {
        if(empty($currencyCode)) {
            $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        }
        
        $currencySymbol = Mage::app()->getLocale()->currency($currencyCode)->getSymbol();
        if($currencySymbol == '€') {
            $currencySymbol = '&euro;';
        } elseif($currencySymbol == '£') {
            $currencySymbol = '&pound;';
        }
        return $currencySymbol.$amount;
    }
    public function confirmmessageAction(){
       
        $this->loadLayout();
        $this->renderLayout();
    }
}
