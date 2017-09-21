<?php

class Allure_Pdf_Model_Sales_Order_Pdf_Creditmemo extends Mage_Sales_Model_Order_Pdf_Creditmemo
{
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
                Mage::helper('sales')->__('Order # ') . $order->getRealOrderId(), 35, ($top -= 30), 'UTF-8'
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
        $helper->addSignatureRequiredToPdf($page,$customTop,$order);
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
        
        /* Payment */
        $paymentInfo = Mage::helper('payment')->getInfoBlock($order->getPayment())
        ->setIsSecureMode(true)
        ->toPdf();
        $paymentInfo = htmlspecialchars_decode($paymentInfo, ENT_QUOTES);
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key=>$value){
            if (strip_tags(trim($value)) == '') {
                unset($payment[$key]);
            }
        }
        reset($payment);
        
        /* Shipping Address and Method */
        if (!$order->getIsVirtual()) {
            /* Shipping Address */
            $shippingAddress = $this->_formatAddress($order->getShippingAddress()->format('pdf'));
            $shippingMethod  = $order->getShippingDescription();
        }
        
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($page, 12);
        $page->drawText(Mage::helper('sales')->__('Sold to:'), 35, ($top - 15), 'UTF-8');
        
        if (!$order->getIsVirtual()) {
            $page->drawText(Mage::helper('sales')->__('Ship to:'), 285, ($top - 15), 'UTF-8');
        } else {
            $page->drawText(Mage::helper('sales')->__('Payment Method:'), 285, ($top - 15), 'UTF-8');
        }
        
        $addressesHeight = $this->_calcAddressHeight($billingAddress);
        if (isset($shippingAddress)) {
            $addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
        }
        
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
            
            $addressesEndY = min($addressesEndY, $this->y);
            $this->y = $addressesEndY;
            
            $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 275, $this->y-25);
            $page->drawRectangle(275, $this->y, 570, $this->y-25);
            
            $this->y -= 15;
            $this->_setFontBold($page, 12);
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $page->drawText(Mage::helper('sales')->__('Payment Method'), 35, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Shipping Method:'), 285, $this->y , 'UTF-8');
            
            $this->y -=10;
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
            
            $this->_setFontRegular($page, 10);
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            
            $paymentLeft = 35;
            $yPayments   = $this->y - 15;
        }
        else {
            $yPayments   = $addressesStartY;
            $paymentLeft = 285;
        }
        
        foreach ($payment as $value){
            if (trim($value) != '') {
                //Printing "Payment Method" lines
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
                    $page->drawText(strip_tags(trim($_value)), $paymentLeft, $yPayments, 'UTF-8');
                    $yPayments -= 15;
                }
            }
        }
        
        if ($order->getIsVirtual()) {
            // replacement of Shipments-Payments rectangle block
            $yPayments = min($addressesEndY, $yPayments);
            $page->drawLine(25,  ($top - 25), 25,  $yPayments);
            $page->drawLine(570, ($top - 25), 570, $yPayments);
            $page->drawLine(25,  $yPayments,  570, $yPayments);
            
            $this->y = $yPayments - 15;
        } else {
            $topMargin    = 15;
            $methodStartY = $this->y;
            $this->y     -= 15;
            
            foreach (Mage::helper('core/string')->str_split($shippingMethod, 45, true, true) as $_value) {
                $page->drawText(strip_tags(trim($_value)), 285, $this->y, 'UTF-8');
                $this->y -= 15;
            }
            
            $yShipments = $this->y;
            $totalShippingChargesText = "(" . Mage::helper('sales')->__('Total Shipping Charges') . " "
                . $order->formatPriceTxt($order->getShippingAmount()) . ")";
                
                $page->drawText($totalShippingChargesText, 285, $yShipments - $topMargin, 'UTF-8');
                $yShipments -= $topMargin + 10;
                
                $tracks = array();
                if ($shipment) {
                    $tracks = $shipment->getAllTracks();
                }
                if (count($tracks)) {
                    $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                    $page->setLineWidth(0.5);
                    $page->drawRectangle(285, $yShipments, 510, $yShipments - 10);
                    $page->drawLine(400, $yShipments, 400, $yShipments - 10);
                    //$page->drawLine(510, $yShipments, 510, $yShipments - 10);
                    
                    $this->_setFontRegular($page, 9);
                    $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                    //$page->drawText(Mage::helper('sales')->__('Carrier'), 290, $yShipments - 7 , 'UTF-8');
                    $page->drawText(Mage::helper('sales')->__('Title'), 290, $yShipments - 7, 'UTF-8');
                    $page->drawText(Mage::helper('sales')->__('Number'), 410, $yShipments - 7, 'UTF-8');
                    
                    $yShipments -= 20;
                    $this->_setFontRegular($page, 8);
                    foreach ($tracks as $track) {
                        
                        $CarrierCode = $track->getCarrierCode();
                        if ($CarrierCode != 'custom') {
                            $carrier = Mage::getSingleton('shipping/config')->getCarrierInstance($CarrierCode);
                            $carrierTitle = $carrier->getConfigData('title');
                        } else {
                            $carrierTitle = Mage::helper('sales')->__('Custom Value');
                        }
                        
                        //$truncatedCarrierTitle = substr($carrierTitle, 0, 35) . (strlen($carrierTitle) > 35 ? '...' : '');
                        $maxTitleLen = 45;
                        $endOfTitle = strlen($track->getTitle()) > $maxTitleLen ? '...' : '';
                        $truncatedTitle = substr($track->getTitle(), 0, $maxTitleLen) . $endOfTitle;
                        //$page->drawText($truncatedCarrierTitle, 285, $yShipments , 'UTF-8');
                        $page->drawText($truncatedTitle, 292, $yShipments , 'UTF-8');
                        $page->drawText($track->getNumber(), 410, $yShipments , 'UTF-8');
                        $yShipments -= $topMargin - 5;
                    }
                } else {
                    $yShipments -= $topMargin - 5;
                }
                
                $currentY = min($yPayments, $yShipments);
                
                // replacement of Shipments-Payments rectangle block
                $page->drawLine(25,  $methodStartY, 25,  $currentY); //left
                $page->drawLine(25,  $currentY,     570, $currentY); //bottom
                $page->drawLine(570, $currentY,     570, $methodStartY); //right
                
                $this->y = $currentY;
                $this->y -= 15;
        }
    }
}
