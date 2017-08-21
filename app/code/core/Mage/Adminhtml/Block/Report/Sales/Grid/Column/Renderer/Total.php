<?php
class Mage_Adminhtml_Block_Report_Sales_Grid_Column_Renderer_Total
extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render (Varien_Object $row)
    {
    
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('filter'));
       // $requestData = $this->_filterDates($requestData, array('from', 'to'));
       // $requestData['store_ids'] = $this->getRequest()->getParam('store_ids');
        //$requestData['warehouse_id'] = $requestData['warehouse_id'];
        /* $params = new Varien_Object();
        foreach ($requestData as $key => $value) {
            if (!empty($value)) {
                $params->setData($key, $value);
            }
        } */
        
        //return $params;
        if($this->getRequest()->getParam('store_ids')){
            $storeId=$this->getRequest()->getParam('store_ids');
            /* Format our dates */
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
            $value=round(($row->getData($this->getColumn()->getIndex())/$orders->getFirstItem()->getBaseToGlobalRate()),2);
            echo $symbol.$value;
            //echo "<pre>";
            //print_r($orders->getFirstItem());
        }
        return "";
        //return $this->getColumn()->getIndex();
    }
}