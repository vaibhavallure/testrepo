<?php

abstract class Allure_Sales_Model_Pdf_Gift_Abstract extends Allure_Sales_Model_Pdf_Abstract
{
    /**
     * Initialize renderer process
     *
     * @param string $type
     */
    protected function _initRenderer($type)
    {
        $node = Mage::getConfig()->getNode('global/pdf/invoice');
        foreach ($node->children() as $renderer) {
            $tempRenderer = (string)$renderer;
            if($renderer->getName() == "default"){
                $tempRenderer = "allure_sales/pdf_items_gift_default";
            }elseif($renderer->getName() == "grouped"){
                $tempRenderer = "allure_sales/pdf_items_gift_grouped";
            }elseif ($renderer->getName() == "bundle"){
                $tempRenderer = "bundle/sales_order_pdf_items_invoice";
            }elseif ($renderer->getName() == "downloadable"){
                $tempRenderer = "downloadable/sales_order_pdf_items_invoice";
            }
            
            $this->_renderers[$renderer->getName()] = array(
                'model'     => $tempRenderer ,
                'renderer'  => null
            );
        }
    }
    
    /**
     * Insert order to pdf page
     *
     * @param Zend_Pdf_Page $page
     * @param Mage_Sales_Model_Order $obj
     * @param bool $putOrderId
     */
    protected function insertOrder(&$page, $obj, $putOrderId = true)
    {
        if ($obj instanceof Mage_Sales_Model_Order) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof Mage_Sales_Model_Order_Shipment) {
            $shipment = $obj;
            $order = $shipment->getOrder();
        }
        
        $this->y = $this->y ? $this->y : 815;
        $top = $this->y;
        
        $customTop = $top; //allure code
        
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.45));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.45));
        $page->drawRectangle(25, $top, 570, $top - 55);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $this->setDocHeaderCoordinates(array(25, $top, 570, $top - 55));
        $this->_setFontRegular($page, 10);
        
        if ($putOrderId) {
            $page->drawText(
                Mage::helper('sales')->__('Order # ') . $order->getRealOrderId(), 35, ($top -= 15), 'UTF-8'
                );
        }
        $page->drawText(
            Mage::helper('sales')->__('Order Date: ') . Mage::helper('core')->formatDate(
                $order->getCreatedAtStoreDate(), 'medium', false
                ),
            35,
            ($top -= 15),
            'UTF-8'
            );
        
        //allure code - Start
        $helper = Mage::helper("allure_pdf");
        $this->_setFontRegular($page, 12);
        $helper->addSignatureRequiredToPdf($page,$customTop += 5,$order);
        $this->_setFontRegular($page, 10);
        //allure code - End
        
        $top -= 10;
        $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $top, 275, ($top - 25));
        $page->drawRectangle(275, $top, 570, ($top - 25));
        
        /* Calculate blocks info */
        
        /* Billing Address */
        $billingAddress = $this->_formatAddress($order->getBillingAddress()->format('pdf'));
        
        /* Shipping Address and Method */
        if (!$order->getIsVirtual()) {
            /* Shipping Address */
            $shippingAddress = $this->_formatAddress($order->getShippingAddress()->format('pdf'));
        }
        
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($page, 12);
        $page->drawText(Mage::helper('sales')->__('Sold to:'), 35, ($top - 15), 'UTF-8');
        
        if (!$order->getIsVirtual()) {
            $page->drawText(Mage::helper('sales')->__('Ship to:'), 285, ($top - 15), 'UTF-8');
        } 
        
        $addressesHeight = $this->_calcAddressHeight($billingAddress);
        if (isset($shippingAddress)) {
            $addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
        }
        
        //aws02 - address height calculate function with extra data start
        $customerGroupId = $order->getCustomerGroupId();
        $groupname = Mage::getModel('customer/group')->load($customerGroupId)->getCustomerGroupCode();
        $groupname = "Customer Group : ".$groupname;
        $customerEmail = "Email : ".$order->getCustomerEmail();
        $extraData = array($customerEmail,$groupname);
        $addressesHeight =  max($addressesHeight,$this->calHeightExtraData($addressesHeight,$extraData));
        //end
        
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(25, ($top - 25), 570, $top - 33 - $addressesHeight);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 10);
        $this->y = $top - 40;
        $addressesStartY = $this->y;
        
        foreach ($billingAddress as $value){
            if ($value !== '') {
                $text = array();
                foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $page->drawText(strip_tags(ltrim($part)), 35, $this->y, 'UTF-8');
                    $this->y -= 15;
                }
            }
        }
        
        //aws02 - email & customer group Start
        $page->drawText(strip_tags(ltrim("{$customerEmail}")), 35, $this->y, 'UTF-8');
        $this->y -= 15;
        $page->drawText(strip_tags(ltrim("{$groupname}")), 35, $this->y, 'UTF-8');
        $this->y -= 15;
        //End
        
        $addressesEndY = $this->y;
        
        if (!$order->getIsVirtual()) {
            $this->y = $addressesStartY;
            foreach ($shippingAddress as $value){
                if ($value!=='') {
                    $text = array();
                    foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
                        $text[] = $_value;
                    }
                    foreach ($text as $part) {
                        $page->drawText(strip_tags(ltrim($part)), 285, $this->y, 'UTF-8');
                        $this->y -= 15;
                    }
                }
            }
            
            //aws02 - email & customer group Start
            $page->drawText(strip_tags(ltrim("{$customerEmail}")), 285, $this->y, 'UTF-8');
            $this->y -= 15;
            $page->drawText(strip_tags(ltrim("{$groupname}")), 285, $this->y, 'UTF-8');
            $this->y -= 15;
            //End
            
            $addressesEndY = min($addressesEndY, $this->y);
            $this->y = $addressesEndY;
            
        }
    }
}
