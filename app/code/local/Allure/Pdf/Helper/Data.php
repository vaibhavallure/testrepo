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
            'height' => 20
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
     * get product stock status message
     */
    public function getSalesProductStockStatus($item , $order){
        $sku     = $item->getSku();
        $storeId = $order->getStoreId();
        $product = Mage::getModel('catalog/product');
        $product->setStoreId($storeId)
                ->load($product->getIdBySku($sku));
        $message = "";
        if(!empty($product)){
            $stock = Mage::getModel('cataloginventory/stock_item')
                    ->loadByProductAndStock($product,$storeId);
            if(($stock->getQty() >= 1 && $stock->getIsInStock())
                        ||($product->getStockItem()->getManageStock() == 0)){
                    $message = "";
            }else{
                if(!is_null($product->getBackorderTime()) &&
                                        $product->getBackorderTime() != ""){
                    $message = "( ".$product->getBackorderTime()." )";
                }else{
                    $message ='( Backordered )';
                }
            }
        }else{
            $orderItemId = $item->getOrderItemId();
            $orderItem = Mage::getModel("sales/order_item")->load($orderItemId);
            if($orderItem->getBackorderTime() != null) {
                $message = "( ".$orderItem->getBackorderTime()." )";
            }
        }
        return $message;
    }
    
    /**
     * 
     */
    public function addSignatureRequiredToPdf($page ,$top ,$order){
        $actionName = Mage::app()->getRequest()->getActionName();
        if($actionName == "pdfdocs"){
            $signature = ($order->getNoSignatureDelivery()) ? "Yes" : "No";
            $page->drawText(
                Mage::helper('sales')->__('Is Signature Required : ') . $signature, 400, ($top -= 30), 'UTF-8'
            );
        }
    }
}
