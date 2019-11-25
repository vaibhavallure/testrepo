<?php

class Allure_Pdf_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * get line separator for more readable format
     */
    public function getLineSeparator(){
        $lines[][] = array(
            'text'  => "--------------------------------------------------------------------------------------------------------------------------------------------------------------",
            'font' => 'italic',
            'feed' => 35
        );
        
        $lineBlock = array(
            'lines'  => $lines,
            'height' => 12
        );
        return $lineBlock;
    }
    
    /**
     * get sales items stock status
     */
    public function getItemStockStaus($item , $order,$feed = 35){
        $message   = $this->getSalesProductStockStatus($item , $order);
        $flag      = false;
        if(!empty($message)){
            $flag = true;
        }
        $lines[][] = array(
            'text'  => $message,
            'font' => 'italic',
            'feed' => $feed
        );
        
        $lineBlock = array(
            'lines'  => $lines,
            'height' => 20
        );
        
        return array("is_show"=>$flag , "line_block" => $lineBlock);
    }
    
    /**
     * 
     */
    public function getOrderItemStockStatus($item , $order,$feed = 35){
        $message   = $this->getOrderSalesProductStockStatus($item , $order);
        $flag      = true;
        if(!empty($message)){
            $flag = true;
        }
        $lines[][] = array(
            'text'  => $message,
            'feed' => 50
        );
        
        $lineBlock = array(
            'lines'  => $lines,
            'height' => 15
        );
        
        return array("is_show"=>$flag , "line_block" => $lineBlock);
    }
    
    public function getOrderSalesProductStockStatus($item , $order){
        $storeId = $order->getStoreId();
        $message = "";
        if($storeId == 1){
            $amstockHelper = Mage::helper('amstockstatus');
            $message = $amstockHelper->getOrderSalesProductStockStatus($item);
        }
        return $message;
    }
    
    
    /**
     * get product stock status message
     */
    public function getSalesProductStockStatus($item , $order){
        $sku     = $item->getSku();
        $storeId = $order->getStoreId();
        $product = Mage::getModel('catalog/product');
        $product->setStoreId($storeId)
                ->load($product->getIdBySku($sku));
        $message = "";
        
        if($storeId == 1){
            $backTimeMsg = $item->getBackorderTime();
            if (!empty($backTimeMsg)) {
                $message = $backTimeMsg;
            } else {
                $message = "";
            }
        }
        
        return $message;
    }
    
    /**
     * get order required signature status
     */
    public function addSignatureRequiredToPdf($page ,$top ,$order){
        $actionName = Mage::app()->getRequest()->getActionName();
        if($actionName == "pdfdocs" || $actionName="pdforders"){
            $signature = ($order->getNoSignatureDelivery()) ? "Yes" : "No";
            $page->drawText(
                Mage::helper('sales')->__('Is Signature Required : ') . $signature, 400, ($top -= 30), 'UTF-8'
            );
        }
    }
    
    public function getSalesOrderItemSpecialInstruction($item,$feed = 35,$flag1=false){
        try{
            $flag = false;
            $orderItemId = $item->getOrderItemId();
            $actionName = Mage::app()->getRequest()->getActionName();
            $orderItem = $item->getOrderItem();//Mage::getModel("sales/order_item")->load($orderItemId);
            if($flag1){
                $orderItem = $item;
            }
            
            $giftHelper = Mage::helper('giftmessage/message');
            if($giftHelper->getIsMessagesAvailable('order_item', $orderItem) && $orderItem->getGiftMessageId() && $giftHelper->getEscapedGiftMessage($orderItem)!=''){
                $message = $giftHelper->getEscapedGiftMessage($orderItem);
                $flag=true;
            }else{
                $message="";
            }
                $lines[][] = array(
                    'text'  => Mage::helper('core/string')->str_split("Special Message: ".$message, 80, true, true),
                    'feed' => 50,
                    'height' => 20
                    
                );
                $lineBlock = array(
                    'lines'  => $lines,
                    'height' => 20
                );
                
        
                
                return array("is_show"=>$flag,'value_block'=>$lineBlock);
           
        }catch (Exception $e){}
    }
    
    
    public function getSalesOrderItemPurchasedFrom($item,$feed = 35,$flag1=false){
        try{
            $flag = false;
            $orderItemId = $item->getOrderItemId();
            $actionName = Mage::app()->getRequest()->getActionName();
            $orderItem = $item->getOrderItem();//Mage::getModel("sales/order_item")->load($orderItemId);
            if($flag1){
                $orderItem = $item;
            }
            $message="";
            if($orderItem->getPurchasedFrom()){
                $message=$orderItem->getPurchasedFrom();
                $flag=TRUE;
            }
           
            $lines[][] = array(
                'text'  => Mage::helper('core/string')->str_split("Purchased From:  ".$message, 80, true, true),
                'feed' => 50,
                'height' => 12
            );
            $lineBlock = array(
                'lines'  => $lines,
                'height' => 12
            );
           
            
            return array("is_show"=>$flag,'value_block'=>$lineBlock);
            
            
        }catch (Exception $e){}
    }
    /**
     * get order gift message
     */
    public function getOrderGiftMessage($order){
        try{
            Mage::log($order->getId(),Zend_log::DEBUG,'abc',true);
            $giftHelper = Mage::helper('giftmessage/message');
            if($giftHelper->getIsMessagesAvailable('order', $order) && $order->getGiftMessageId()){
                $_giftMessage = $giftHelper->getGiftMessageForEntity($order);
                $from = "From : ".$this->htmlEscape($_giftMessage->getSender());
                $to   = "To : ".$this->htmlEscape($_giftMessage->getRecipient());
                $message = $giftHelper->getEscapedGiftMessage($order);
            }else {
                $from = "From : ";
                $to   = "To : ";
                $message = " ";
            }
                $lines[][] = array(
                    'text'  => "Gift Message for this order",
                    'font' => 'bold',
                    'feed' => 35,
                    'height' => 12
                );
                $lineBlock = array(
                    'lines'  => $lines,
                    'height' => 20
                );
                
                $linesFrom[][] = array(
                    'text'  => $from,
                    'font' => 'italic',
                    'feed' => 35
                );
                $lineBlockFrom = array(
                    'lines'  => $linesFrom,
                    'height' => 20
                );
                
                $linesTo[][] = array(
                    'text'  => $to,
                    'font' => 'italic',
                    'feed' => 35
                );
                $lineBlockTo = array(
                    'lines'  => $linesTo,
                    'height' => 20
                );
                
                $linesMsg[][] = array(
                    'text'  => Mage::helper('core/string')->str_split($message, 80, true, true),
                    'font' => 'italic',
                    'feed' => 35,
                    'height' => 12
                );
                $lineBlockMsg = array(
                    'lines'  => $linesMsg,
                    'height' => 20
                );
                
                
                return array(
                    "is_show"=>true,"from"=>$lineBlockFrom,
                    "to"=>$lineBlockTo,"message"=>$lineBlockMsg,
                    "label"=>$lineBlock, "break"=>$breaks
                );
         
            return array("is_show"=>false);
        }catch (Exception $e){}
    }
    
    
    /**
     * aws02
     * calculate height of address with new added extra field
     */
    public function calHeightExtraData($y,$data){
        foreach ($data as $value){
            if ($value !== '') {
                $text = array();
                foreach (Mage::helper('core/string')->str_split($value, 55, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $y += 15;
                }
            }
        }
        return $y;
    }
    
    
    /**
     * get Item gift message
     */
    public function getItemGiftMessage($item, $flag = false){
        try{
            $giftHelper = Mage::helper('giftmessage/message');
            if($giftHelper->getIsMessagesAvailable('order_item', $item) && $item->getGiftMessageId()){
                $_giftMessage = $giftHelper->getGiftMessageForEntity($item);
                $from = "From : ".$this->htmlEscape($_giftMessage->getSender());
                $to   = "To : ".$this->htmlEscape($_giftMessage->getRecipient());
                $message = $giftHelper->getEscapedGiftMessage($item);
            }else {
                $from = "From : ";
                $to   = "To : ";
                $message = " ";
            }
            $lines = array();
            $lines[][] = array(
                'text'  => "Gift Message",
                'font' => 'bold',
                'feed' => 35,
                'height' => 12
            );
            $lineBlock = array(
                'lines'  => $lines,
                'height' => 20
            );
            
            $linesFrom = array();
            $linesFrom[][] = array(
                'text'  => $from,
                'font' => 'italic',
                'feed' => 35
            );
            $lineBlockFrom = array(
                'lines'  => $linesFrom,
                'height' => 20
            );
            
            $linesTo = array();
            $linesTo[][] = array(
                'text'  => $to,
                'font' => 'italic',
                'feed' => 35
            );
            $lineBlockTo = array(
                'lines'  => $linesTo,
                'height' => 20
            );
            
            $linesMsg = array();
            $linesMsg[][] = array(
                'text'  => Mage::helper('core/string')->str_split($message, 80, true, true),
                'font' => 'italic',
                'feed' => 35,
                'height' => 12
            );
            $lineBlockMsg = array(
                'lines'  => $linesMsg,
                'height' => 20
            );
            
            return array(
                "is_show"=>true,"from"=>$lineBlockFrom,
                "to"=>$lineBlockTo,"message"=>$lineBlockMsg,
                "label"=>$lineBlock
            );
        }catch (Exception $e){
            
        }
    }
    
    
}
