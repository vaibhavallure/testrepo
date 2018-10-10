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

        $fromDate = date('Y-m-d 00:00:00', strtotime('-'.($days).' days'));
        $toDate = date('Y-m-d 23:59:59', strtotime( 'yesterday'));




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




        if(Mage::helper("backorderrecord/config")->getDebugStatus())
            Mage::log('Before date formated by timezone formdate='.$fromDate.' todate='.$toDate,Zend_Log::DEBUG, 'backorder_data.log', true);


        $diffZone=$this->getDiffTimezone();
        $toDate = date('Y-m-d H:i:s', strtotime($diffZone,strtotime($toDate)));
        $fromDate = date('Y-m-d H:i:s', strtotime($diffZone,strtotime($fromDate)));


        if(Mage::helper("backorderrecord/config")->getDebugStatus())
            Mage::log('After date formated by timezone formdate='.$fromDate.' todate='.$toDate,Zend_Log::DEBUG, 'backorder_data.log', true);



        try {

            $backorderCollection = Mage::getModel('sales/order_item')->getCollection()
                ->addAttributeToSort('item_id', 'DESC')
                ->addAttributeToFilter('created_at', array('from' => $fromDate, 'to' => $toDate));
            $backorderCollection->getSelect()->where(new Zend_Db_Expr("(qty_backordered IS NOT NULL)"));/* OR gift_message_id IS NOT NULL*/
        }
        catch (Exception $e){

            if(Mage::helper("backorderrecord/config")->getDebugStatus())
                Mage::log('collection cant be generated '.$e->getMessage(),Zend_Log::DEBUG, 'backorder_data.log', true);


        }
        return $backorderCollection;
    }



    public function getDiffTimezone()
    {

        $local_tz = new DateTimeZone('UTC');
        $local = new DateTime('now', $local_tz);


        $user_tz = new DateTimeZone(Mage::getStoreConfig('general/locale/timezone',1));
        $user = new DateTime('now', $user_tz);

        $usersTime = new DateTime($user->format('Y-m-d H:i:s'));
        $localsTime = new DateTime($local->format('Y-m-d H:i:s'));
        $offset = $local_tz->getOffset($local) - $user_tz->getOffset($user);
        $interval = $usersTime->diff($localsTime);

        if($offset > 0)
            return  $diffZone=$interval->h .' hours'.' '. $interval->i .' minutes';
        else
            return  $diffZone= '-'.$interval->h .' hours'.' '. $interval->i .' minutes';


    }


}