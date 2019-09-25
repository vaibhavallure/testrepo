<?php
/**
 * 
 * @author Allure
 *
 */
class Allure_Sales_Model_Pdf_Items_Gift_Default extends Allure_Sales_Model_Pdf_Items_Abstract
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
        $sku=$this->getSku($item);
        $sku=explode("|", $sku);
        $lines[0][] = array(
            'text'  => Mage::helper('core/string')->str_split($sku[0], 25),
            'feed'  => 290,
            'align' => 'right'
        );
        
        // draw QTY
        $lines[0][] = array(
            'text'  => $item->getQtyOrdered() * 1,
            'feed'  => 435,
            'align' => 'right'
        );
        
        // custom options
        $options = $this->getItemOptions();
        if ($options) {
            
            $mainPptionStr='';
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
                $optionStr = $optionStr .":  ".strtolower($_printValue);
                
                if (empty($mainPptionStr))
                    $mainPptionStr = $optionStr;
                else
                    $mainPptionStr = $mainPptionStr . "         " . $optionStr;
            }
            $lines[][] = array(
                'text' => $mainPptionStr,
                'feed' => 50
            );
        }
        
        $lineBlock = array(
            'lines'  => $lines,
            'height' => 15
        );
               
        $page = $pdf->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $this->setPage($page);
        
        //allure code - Start
        $actionName = Mage::app()->getRequest()->getActionName();
        if($actionName == "pdfOrdersGiftReceipt"){
            $helper    = Mage::helper("allure_pdf");
            $feed = 35;
            $lineBlockArr = $helper->getOrderItemStockStatus($item , $order ,$feed);
            $isShow = $lineBlockArr['is_show'];
            if($isShow){
                $lineBlockStockMessage = $lineBlockArr['line_block'];
                $page = $pdf->drawLineBlocks($page, array($lineBlockStockMessage), array('table_header' => true));
                $this->setPage($page);
            }
                      
            $lineSeparator = $helper->getLineSeparator();
            $page = $pdf->drawLineBlocks($page, array($lineSeparator), array('table_header' => true));
            $this->setPage($page);
            $orderInfo = $helper->getItemGiftMessage($item);
            if($orderInfo['is_show']){
                $page = $pdf->drawLineBlocks($page, array($orderInfo['label']), array('table_header' => true));
                $this->setPage($page);
                $page = $pdf->drawLineBlocks($page, array($orderInfo['from']), array('table_header' => true));
                $this->setPage($page);
                $page = $pdf->drawLineBlocks($page, array($orderInfo['to']), array('table_header' => true));
                $this->setPage($page);
                $page = $pdf->drawLineBlocks($page, array($orderInfo['message']), array('table_header' => true));
                $this->setPage($page);
            }
        }
        //allure code - End
    }
}
