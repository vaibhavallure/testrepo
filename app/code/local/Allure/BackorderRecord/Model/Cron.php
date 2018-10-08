<?php

class Allure_BackorderRecord_Model_Cron
{

    public function getBackorderRecord()
    {
        Mage::helper("backorderrecord")->sendEmail();
    }



    public function getBackorederCollection()
    {
        $days=Mage::helper("backorderrecord/config")->getDays();
        $toDate = date('Y-m-d H:i:s');
        $fromDate = date('Y-m-d H:i:s', strtotime('-'.$days.' days'));

        $backorderCollection = Mage::getModel('sales/order_item')->getCollection()
            ->addAttributeToSort('item_id', 'DESC')
            ->addAttributeToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate));
        $backorderCollection->getSelect()->where(new Zend_Db_Expr("(qty_backordered IS NOT NULL OR gift_message_id IS NOT NULL)"));

        return $backorderCollection;
    }


}