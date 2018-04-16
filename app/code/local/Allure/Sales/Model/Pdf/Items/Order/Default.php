<?php
/**
 * 
 * @author Allure
 *
 */
class Allure_Sales_Model_Pdf_Items_Order_Default extends Allure_Sales_Model_Pdf_Items_Abstract
{
    /**
     * Draw item line
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
            'feed'  => 290,
            'align' => 'right'
        );
        
        // draw QTY
        $lines[0][] = array(
            'text'  => $item->getQtyOrdered() * 1,
            'feed'  => 435,
            'align' => 'right'
        );
        
        // draw item Prices
        $i = 0;
        $prices = $this->getItemPricesForDisplay();
        $feedPrice = 395;
        $feedSubtotal = $feedPrice + 170;
        foreach ($prices as $priceData){
            if (isset($priceData['label'])) {
                // draw Price label
                $lines[$i][] = array(
                    'text'  => $priceData['label'],
                    'feed'  => $feedPrice,
                    'align' => 'right'
                );
                // draw Subtotal label
                $lines[$i][] = array(
                    'text'  => $priceData['label'],
                    'feed'  => $feedSubtotal,
                    'align' => 'right'
                );
                $i++;
            }
            // draw Price
            $lines[$i][] = array(
                'text'  => $priceData['price'],
                'feed'  => $feedPrice - 30,
                'font'  => 'bold',
                'align' => 'right'
            );
            // draw Subtotal
            $lines[$i][] = array(
                'text'  => $priceData['subtotal'],
                'feed'  => $feedSubtotal,
                'font'  => 'bold',
                'align' => 'right'
            );
            $i++;
        }
        
        // draw Tax
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getTaxAmount()),
            'feed'  => 495,
            'font'  => 'bold',
            'align' => 'right'
        );
        
        // custom options
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
                
                //allure comment
                /* if ($option['value']) {
                    if (isset($option['print_value'])) {
                        $_printValue = $option['print_value'];
                    } else {
                        $_printValue = strip_tags($option['value']);
                    }
                    $values = explode(', ', $_printValue);
                    foreach ($values as $value) {
                        $lines[][] = array(
                            'text' => Mage::helper('core/string')->str_split($value, 30, true, true),
                            'feed' => 40
                        );
                    }
                } */
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
        if($actionName == "pdfdocs" || $actionName == "pdforders"){
            $helper    = Mage::helper("allure_pdf");
            $feed = 35;
            $lineBlockArr = $helper->getOrderItemStockStatus($item , $order ,$feed);
            $isShow = $lineBlockArr['is_show'];
            if($isShow){
                $lineBlockStockMessage = $lineBlockArr['line_block'];
                $page = $pdf->drawLineBlocks($page, array($lineBlockStockMessage), array('table_header' => true));
                $this->setPage($page);
            }
            
            $salesInstr = $helper->getSalesOrderItemSpecialInstruction($item,$feed,true);
            if($salesInstr['is_show']){
                $page = $pdf->drawLineBlocks($page, array($salesInstr['label_block']), array('table_header' => true));
                $this->setPage($page);
                $page = $pdf->drawLineBlocks($page, array($salesInstr['value_block']), array('table_header' => true));
                $this->setPage($page);
            }
            
            $lineSeparator = $helper->getLineSeparator();
            $page = $pdf->drawLineBlocks($page, array($lineSeparator), array('table_header' => true));
            $this->setPage($page);
            $isLastItem = $item->getIsLastItem();
            if($isLastItem){
                $orderInfo = $helper->getOrderGiftMessage($order);
                if($orderInfo['is_show']){
                    $page = $pdf->drawLineBlocks($page, array($orderInfo['label']), array('table_header' => true));
                    $this->setPage($page);
                    /* $page = $pdf->drawLineBlocks($page, array($orderInfo['from']), array('table_header' => true));
                    $this->setPage($page);
                    $page = $pdf->drawLineBlocks($page, array($orderInfo['to']), array('table_header' => true));
                    $this->setPage($page); */
                    $page = $pdf->drawLineBlocks($page, array($orderInfo['message']), array('table_header' => true));
                    $this->setPage($page);
                }
            }
        }
        //allure code - End
    }
}
