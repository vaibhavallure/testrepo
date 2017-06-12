<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Webpos
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Class Magestore_Webpos_Model_Source_Adminhtml_Payment
 */
class Magestore_Webpos_Model_Source_Adminhtml_Payment {
    /**
     * @var array
     */
    protected $_allowPayments = array();

    /**
     * Magestore_Webpos_Model_Source_Adminhtml_Payment constructor.
     */
    public function __construct() {
        $payments = array(
            'cashforpos',
            'codforpos',
            'ccforpos',
            'cp1forpos',
            'cp2forpos',
            'paypal_direct',
            'authorizenet_directpost',
            'authorizenet',
            'banktransfer',
            'checkmo',
            'cashondelivery',
            'free'
        );

        $this->_allowPayments = $payments;
        $this->_paymentHelper = Mage::helper('webpos/payment');

    }

    /**
     * @return mixed
     */
    public function getActiveMethods(){
        $website = Mage::app()->getRequest()->getParam('website');
        $store   = Mage::app()->getRequest()->getParam('store');
        if (!empty($store))
        {
            $store_id = Mage::getModel('core/store')->load($store)->getId();
        }
        elseif (!empty($website))
        {
            $website_id = Mage::getModel('core/website')->load($website)->getId();
            $store_id = Mage::app()->getWebsite($website_id)->getDefaultStore()->getId();
        }
        else
        {
            $store_id = 0;
        }
        $collection = Mage::getModel('payment/config')->getActiveMethods($store_id);
        return $collection;
    }

    /**
     * @return array|void
     */
    public function toOptionArray() {
        $collection = $this->getActiveMethods();

        if (!count($collection))
            return;

        $options = array(
            array('value' => '', 'label' => '--- '.$this->_paymentHelper->__('None').' ---')
        );
        foreach ($collection as $item) {
            if(!in_array($item->getId(), $this->_allowPayments)) continue;
            $title = $item->getTitle() ? $item->getTitle() : $item->getId();
            $options[] = array('value' => $item->getId(), 'label' => $title);
        }

        return $options;
    }

    /**
     * @return array
     */
    public function getAllowPaymentMethods() {
        $payments = array();
        $collection = $this->getActiveMethods();
        if (count($collection) > 0){
            foreach ($collection as $item) {
                if(in_array($item->getId(), $this->_allowPayments)){
                    $payments[] = $item->getId();
                }
            }
        }
        return $payments;
    }

    /**
     * @return array
     */
    public function getPaymentData(){
        $collection = $this->getActiveMethods();
        $paymentList = array();
        if(count($collection) > 0) {
            foreach ($collection as $item) {
                if (!in_array($item->getId(), $this->_allowPayments))
                    continue;
                if (!$this->_paymentHelper->isAllowOnWebPOS($item->getId()))
                    continue;
                $isDefault = Magestore_Webpos_Api_PaymentInterface::NO;
                if($item->getId() == $this->_paymentHelper->getDefaultPaymentMethod()) {
                    $isDefault = Magestore_Webpos_Api_PaymentInterface::YES;
                }
                $isReferenceNumber = Magestore_Webpos_Api_PaymentInterface::NO;
                if ($item->getConfigData('use_reference_number')){
                    $isReferenceNumber = Magestore_Webpos_Api_PaymentInterface::YES;
                }
                $isPayLater = 0;
                if ($item->getConfigData('pay_later')){
                    $isPayLater = Magestore_Webpos_Api_PaymentInterface::YES;
                }

                $paymentModel =  Mage::getModel('webpos/payment_payment');
                $paymentModel->setCode($item->getId());
                $paymentModel->setIconClass('icon-iconPOS-payment-'.$item->getId());
                $paymentModel->setTitle($item->getTitle());
                $paymentModel->setInformation('');
                $paymentModel->setType(Magestore_Webpos_Api_PaymentInterface::NO);
                $paymentModel->setIsDefault($isDefault);
                $paymentModel->setIsReferenceNumber($isReferenceNumber);
                $paymentModel->setIsPayLater($isPayLater);
                $paymentList[] = $paymentModel->getData();
            }
        }
        return $paymentList;
    }
}
