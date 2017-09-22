<?php
class Allure_Pdf_Model_Sales_Order_Pdf_Items_Creditmemo_Default extends Mage_Sales_Model_Order_Pdf_Items_Creditmemo_Default
{
    /**
     * Draw process
     */
    public function draw()
    {
        $order  = $this->getOrder();
        $item   = $this->getItem();
        $pdf    = $this->getPdf();
        $page   = $this->getPage();
        $lines  = array();

        // draw Product name
        $lines[0] = array(array(
            'text' => Mage::helper('core/string')->str_split($item->getName(), 35, true, true),
            'feed' => 35,
        ));

        // draw SKU
        $lines[0][] = array(
            'text'  => Mage::helper('core/string')->str_split($this->getSku($item), 17),
            'feed'  => 255,
            'align' => 'right'
        );

        // draw Total (ex)
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getRowTotal()),
            'feed'  => 330,
            'font'  => 'bold',
            'align' => 'right',
        );

        // draw Discount
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt(-$item->getDiscountAmount()),
            'feed'  => 380,
            'font'  => 'bold',
            'align' => 'right'
        );

        // draw QTY
        $lines[0][] = array(
            'text'  => $item->getQty() * 1,
            'feed'  => 445,
            'font'  => 'bold',
            'align' => 'right',
        );

        // draw Tax
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getTaxAmount()),
            'feed'  => 495,
            'font'  => 'bold',
            'align' => 'right'
        );

        // draw Total (inc)
        $subtotal = $item->getRowTotal() + $item->getTaxAmount() + $item->getHiddenTaxAmount()
            - $item->getDiscountAmount();
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($subtotal),
            'feed'  => 565,
            'font'  => 'bold',
            'align' => 'right'
        );

        // draw options
        $options = $this->getItemOptions();
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $optionStr = $option['label'];
                if ($option['value']) {
                    if (isset($option['print_value'])) {
                        $_printValue = $option['print_value'];
                    } else {
                        $_printValue = strip_tags($option['value']);
                    }
                }
                $optionStr = $optionStr ." : ".$_printValue;
                
                $lines[][] = array(
                    'text' => Mage::helper('core/string')->str_split(strip_tags($optionStr), 40, true, true),
                    'font' => 'italic',
                    'feed' => 35
                );

                // draw options value
                /* $_printValue = isset($option['print_value']) ? $option['print_value'] : strip_tags($option['value']);
                $lines[][] = array(
                    'text' => Mage::helper('core/string')->str_split($_printValue, 30, true, true),
                    'feed' => 40
                ); */
            }
        }

        $lineBlock = array(
            'lines'  => $lines,
            'height' => 20
        );

        $page = $pdf->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $this->setPage($page);
        
        //allure code - Start
        $actionName = Mage::app()->getRequest()->getActionName();
        if($actionName == "pdfdocs"){
            $helper    = Mage::helper("allure_pdf");
            $lineBlockArr = $helper->getItemStockStaus($item , $order,35);
            $isShow = $lineBlockArr['is_show'];
            if($isShow){
                $lineBlockStockMessage = $lineBlockArr['line_block'];
                $page = $pdf->drawLineBlocks($page, array($lineBlockStockMessage), array('table_header' => true));
                $this->setPage($page);
            }
            
            $lineSeparator = $helper->getLineSeparator();
            $page = $pdf->drawLineBlocks($page, array($lineSeparator), array('table_header' => true));
            $this->setPage($page);
        }
        //allure code - End
    }
}
