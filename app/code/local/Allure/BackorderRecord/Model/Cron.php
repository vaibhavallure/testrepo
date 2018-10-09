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
        $toDate = date('Y-m-d 23:59:59', strtotime( 'yesterday'));
        $fromDate = date('Y-m-d 00:00:00', strtotime('-'.($days+1).' days'));



        if(count($dates))
        {
            $fromDate = new DateTime( $dates['from_date']);
            $fromDate = $fromDate->format('Y-m-d H:i:s');

            $toDate = new DateTime( $dates['to_date']);
            $toDate = $toDate->format('Y-m-d H:i:s');

        }


//        echo "formdate={$fromDate}<br>";
//        echo "todate={$toDate}<br>";


        try {

            $backorderCollection = Mage::getModel('sales/order_item')->getCollection()
                ->addAttributeToSort('item_id', 'DESC')
                ->addAttributeToFilter('created_at', array('from' => $fromDate, 'to' => $toDate));
            $backorderCollection->getSelect()->where(new Zend_Db_Expr("(qty_backordered IS NOT NULL OR gift_message_id IS NOT NULL)"));
        }
        catch (Exception $e){

            if($this->config()->getDebugStatus())
                Mage::log('collection cant be generated '.$e->getMessage(),Zend_Log::DEBUG, 'backorder_data', true);


        }
        return $backorderCollection;
    }


}