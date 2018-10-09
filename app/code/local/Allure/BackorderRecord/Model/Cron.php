<?php

class Allure_BackorderRecord_Model_Cron
{

    public function getBackorderRecord()
    {
        Mage::helper("backorderrecord")->sendEmail();
    }



    public function getBackorederCollection($dates=array())
    {

        $data=$dates;
        $days=Mage::helper("backorderrecord/config")->getDays();
        $toDate = date('Y-m-d 23:59:59', strtotime( 'yesterday'));
        $fromDate = date('Y-m-d 00:00:00', strtotime('-'.($days+1).' days'));



        if(count($data))
        {



            try {
                $fromDate = new DateTime($data['from_date']);
                $fromDate = $fromDate->format('Y-m-d H:i:s');
                $toDate = new DateTime( $data['to_date']);
                $toDate = $toDate->format('Y-m-d H:i:s');

            }catch (Exception $e)
            {

                if(Mage::helper("backorderrecord/config")->getDebugStatus())
                    Mage::log('Incorrect Date Entered '.$e->getMessage(),Zend_Log::DEBUG, 'backorder_data.log', true);

                return null;

            }

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

            if(Mage::helper("backorderrecord/config")->getDebugStatus())
                Mage::log('collection cant be generated '.$e->getMessage(),Zend_Log::DEBUG, 'backorder_data.log', true);


        }
        return $backorderCollection;
    }


}