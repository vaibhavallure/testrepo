<?php

class Allure_BackorderRecord_Model_Cron
{

    public function getBackorderRecord()
    {
        Mage::helper("backorderrecord")->sendEmail();
    }



    public function getBackorederCollection($dates=array())
    {


        $days=Mage::helper("backorderrecord/config")->getDays();
        $toDate = date('Y-m-d H:i:s');
        $fromDate = date('Y-m-d H:i:s', strtotime('-'.$days.' days'));

        if(count($dates))
        {
            $fromDate== date('Y-m-d H:i:s', strtotime($dates['from_date']));
            $toDate=date('Y-m-d H:i:s', strtotime($dates['to_date']));
        }



        $backorderCollection = Mage::getModel('sales/order_item')->getCollection()
            ->addAttributeToSort('item_id', 'DESC')
            ->addAttributeToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate));
        $backorderCollection->getSelect()->where(new Zend_Db_Expr("(qty_backordered IS NOT NULL OR gift_message_id IS NOT NULL)"));

        return $backorderCollection;
    }


}