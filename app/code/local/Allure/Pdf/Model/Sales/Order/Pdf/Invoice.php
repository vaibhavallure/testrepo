<?php
class Allure_Pdf_Model_Sales_Order_Pdf_Invoice extends Mage_Sales_Model_Order_Pdf_Invoice
{
    protected $_isCompress = false;
    /**
     * Insert order to pdf page
     *
     * @param Zend_Pdf_Page $page
     * @param Mage_Sales_Model_Order $obj
     * @param bool $putOrderId
     */
    protected function insertOrder(&$page, $obj, $putOrderId = true,$invoiceId = null)
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
            $this->_setFontRegular($page, 12);
            $page->drawText(
                Mage::helper('sales')->__('Order # ') . $order->getRealOrderId(), 35, ($top -= 30), 'UTF-8'
                );
            $this->_setFontRegular($page, 10);
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
        $helper->addSignatureRequiredToPdf($page,$customTop,$order);
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
        
        
        if($order->getCreateOrderMethod() == 2){
            $paymentObj = $order->getPayment();
            $paymentData = unserialize($paymentObj->getAdditionalData());
            $paymentId = $paymentData[$invoiceId]["payment_id"];
            if(!empty($paymentId)){
                if($paymentId != $paymentObj->getId()){
                    $paymentObj = Mage::getModel("sales/order_payment")
                        ->load($paymentId);
                }
            }
        }else{
            $paymentObj = $order->getPayment();
        }
        
        
        /* Payment */
        $paymentInfo = Mage::helper('payment')->getInfoBlock($paymentObj)
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
        
        //aws02 - address height calculate function with extra data start
        $customerGroupId = $order->getCustomerGroupId();
        $groupname = Mage::getModel('customer/group')->load($customerGroupId)->getCustomerGroupCode();
        $groupname = "Customer Group : ".$groupname;
        $customerEmail = "Email : ".$order->getCustomerEmail();
        $extraData = array($customerEmail,$groupname);
        $addressesHeight =  max($addressesHeight,$helper->calHeightExtraData($addressesHeight,$extraData));
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
    
    //compress pdf size
    public function getCompressPdf($invoices = array(), $isCompress = false){
        $this->_isCompress = $isCompress;
        return $this->getPdf($invoices);
    }
    
    
    /**
     * Return PDF document
     *
     * @param  array $invoices
     * @return Zend_Pdf
     */
    public function getPdf($invoices = array())
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');
        
        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
        
        foreach ($invoices as $invoice) {
            if ($invoice->getStoreId()) {
                Mage::app()->getLocale()->emulate($invoice->getStoreId());
                Mage::app()->setCurrentStore($invoice->getStoreId());
            }
            $page  = $this->newPage();
            $order = $invoice->getOrder();
            /* Add image */
            $this->insertLogo($page, $invoice->getStore());
            /* Add address */
            $this->insertAddress($page, $invoice->getStore());
            /* Add head */
            $this->insertOrder(
                $page,
                $order,
                Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID, $order->getStoreId()),
                $invoice->getId()
                );
            /* Add document text and number */
            $this->insertDocumentNumber(
                $page,
                Mage::helper('sales')->__('Invoice # ') . $invoice->getIncrementId()
                );
            /* Add table */
            $this->_drawHeader($page);
            /* Add body */
            $cnt = 0;
            foreach ($invoice->getAllItems() as $item){
                $cnt ++;
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                /* Draw item */
                if(count($invoice->getAllItems()) == $cnt){
                    $item = $item->setIsLastItem(1);
                    $this->_drawItem($item, $page, $order);
                }else{
                    $this->_drawItem($item, $page, $order);
                }
                $page = end($pdf->pages);
            }
            
            /* Add totals */
            $this->insertTotals($page, $invoice);
            if ($invoice->getStoreId()) {
                Mage::app()->getLocale()->revert();
            }
        }
        $this->_afterGetPdf();
        return $pdf;
    }
    
    /**
     * Set font as regular
     *
     * @param  Zend_Pdf_Page $object
     * @param  int $size
     * @return Zend_Pdf_Resource_Font
     */
    protected function _setFontRegular($object, $size = 7)
    {
        if($this->_isCompress){
            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        }else{
            $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_Re-4.4.1.ttf');
        }
        
        $object->setFont($font, $size);
        return $font;
    }
    
    /**
     * Set font as bold
     *
     * @param  Zend_Pdf_Page $object
     * @param  int $size
     * @return Zend_Pdf_Resource_Font
     */
    protected function _setFontBold($object, $size = 7)
    {
        if($this->_isCompress){
            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        }else{
            $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf');
        }
        $object->setFont($font, $size);
        return $font;
    }
    
}
