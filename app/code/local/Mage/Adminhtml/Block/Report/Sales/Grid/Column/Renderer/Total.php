<?php
class Mage_Adminhtml_Block_Report_Sales_Grid_Column_Renderer_Total
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render (Varien_Object $row)
    {
    
        
        if($this->getRequest()->getParam('store_ids')){
            $storeId=$this->getRequest()->getParam('store_ids');
            /* Format our dates */
            if(array_key_exists('period', $row->getData())){
                
            $from = $row->getData('period')." 00:00:00";
            $to = $row->getData('period')." 23:59:59"; //
            
            $fromDate = date('Y-m-d H:i:s', strtotime($from));
            $toDate = date('Y-m-d H:i:s', strtotime($to));
            
            /* Get the collection */
            $orders = Mage::getModel('sales/order')->getCollection()
            ->addAttributeToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate))
            ->addAttributeToFilter('store_id',$storeId);
            $orders->getFirstItem();
            $symbol=Mage::app()->getLocale()->currency( $orders->getFirstItem()->getStoreCurrencyCode() )->getSymbol();
            
            $value=round(($row->getData($this->getColumn()->getIndex())),2);
             echo ($symbol).$value;
            }
            else 
              echo "";
           
            
           // echo $this->getColumn()->getIndex();
            //echo "<pre>";
           //print_r($row->getData());
        }
        return "";
        //return $this->getColumn()->getIndex();
    }
}