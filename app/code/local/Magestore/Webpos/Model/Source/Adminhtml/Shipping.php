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

class Magestore_Webpos_Model_Source_Adminhtml_Shipping {

    protected $_allowShippings = array();

    public function __construct() {
        $shippings = array();
        $collection = Mage::getModel('shipping/config')->getActiveCarriers();
        if (!count($collection)){
            foreach ($collection as $code => $carrier) {
                $shippings[] = $code;
            }
        }
        $this->_allowShippings = $shippings;
        $this->_shippingHelper = Mage::helper('webpos/shipping');
    }

    public function toOptionArray() {
        $collection = Mage::getModel('shipping/config')->getActiveCarriers();

        if (!count($collection))
            return;

        $options = array(
            array('value' => '', 'label' => '--- '.$this->_shippingHelper->__('None').' ---')
        );
        foreach ($collection as $ccode => $carrier) {
            $methods = $carrier->getAllowedMethods();
            if(count($methods) > 0){
                foreach ($methods as $mcode => $method) {
                    $title = $carrier->getConfigData('title').' - '.$method;
                    if($ccode == 'webpos_shipping') {
                        $mcode = 'storepickup';
                    }
                    $options[] = array('value' => $ccode.'_'.$mcode, 'label' => $title);
                }
            }
        }
        return $options;
    }

    public function getShippingTitleByCode($code){
        $collection = Mage::getModel('shipping/config')->getActiveCarriers();
        return (count($collection) > 0 && isset($collection[$code]))?$collection[$code]->getConfigData('title').' - '.$collection[$code]->getConfigData('name'):'';
    }

    public function getAllowShippingMethods() {
        return $this->_allowShippings;
    }

    public function getShippingData(){
        $collection = Mage::getModel('shipping/config')->getActiveCarriers();
        $shippingList = array();
        if(count($collection) > 0) {
            foreach ($collection as $code => $carrier) {
                if (!in_array($code, $this->_allowShippings))
                    continue;
                $isDefault = '0';
                if($code == $this->_shippingHelper->getDefaultShippingMethod()) {
                    $isDefault = '1';
                }
                $method_code = $code;
                if($code == 'webpos_shipping') {
                    $method_code = 'storepickup';
                }
                $methodCode = $code.'_'.$method_code;
                $methodTitle = $carrier->getConfigData('title').' - '.$carrier->getConfigData('name');
                $methodPrice = ($carrier->getConfigData('price') != null) ? $carrier->getConfigData('price') : '0';
                $methodPriceType = ($carrier->getConfigData('type') != null) ? $carrier->getConfigData('type') : '';
                $methodDescription = ($carrier->getConfigData('description') != null) ?$carrier->getConfigData('description') : '0';
                $methodSpecificerrmsg = ($carrier->getConfigData('specificerrmsg') != null) ?$carrier->getConfigData('specificerrmsg') : '';

                $shippingModel =  Mage::getModel('webpos/shipping_shipping');
                $shippingModel->setCode($methodCode);
                $shippingModel->setTitle($methodTitle);
                $shippingModel->setPrice($methodPrice);
                $shippingModel->setDescription($methodDescription);
                $shippingModel->setIsDefault($isDefault);
                $shippingModel->setErrorMessage($methodSpecificerrmsg);
                $shippingModel->setPriceType($methodPriceType);
                $shippingList[] = $shippingModel->getData();
            }
        }
        return $shippingList;
    }

}
