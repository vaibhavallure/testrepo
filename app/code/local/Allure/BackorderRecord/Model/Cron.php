<?php

class Allure_BackorderRecord_Model_Cron
{

    public function getBackorderRecord()
    {
        Mage::helper("backorderrecord")->sendBackOrderReport();
    }



    public function getBackorederCollection($dates=array(),$store=1)
    {

        $data=$dates;
        $days=Mage::helper("backorderrecord/config")->getDays();

        $fromDate = date('Y-m-d 00:00:00', strtotime('-'.($days).' days'));
        $toDate = date('Y-m-d 23:59:59', strtotime( 'yesterday'));

        $ordertype="back";
        if(count($data))
        {
           
            try {
                $fromDate = new DateTime($data['from_date']);
                $fromDate = $fromDate->format('Y-m-d H:i:s');
                $toDate = new DateTime( $data['to_date']);
                $toDate = $toDate->format('Y-m-d H:i:s');

                $ordertype=$data['order_type'];


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
            $orderModel = Mage::getModel('sales/order');
            $orderResource = $orderModel->getResource();
            $orderTable = $orderResource->getTable('sales/order');

            //$sku = '';
            $skus = array();
            $filterWithStatus = FALSE;
            $filterWithSku = FALSE;
            $filterWithGroup = FALSE;


            //filter with SKU
            if ($data['item_sku'] && $data['show_metal_color']) {
                /*$sku = strtoupper($data['item_sku']).'|'.strtoupper($data['metal_color']).'%';*/
                foreach ($data['metal_color'] as $color) {
                     $skus[] = "main_table.sku LIKE '".strtoupper($data['item_sku'])."|".strtoupper($color)."%'";
                }                
            }else if ($data['item_sku']) {
                //$sku = strtoupper($data['item_sku']).'%';
                $skus[]= "main_table.sku LIKE '".strtoupper($data['item_sku'])."%'";
            }else if ($data['show_metal_color']) {
                //$sku = '%'.strtoupper($data['metal_color']).'%';
                foreach ($data['metal_color'] as $color) {
                   $skus[]= "main_table.sku LIKE '%".strtoupper($color)."%'";
                }                
            }
            if ($data['show_metal_color'] || $data['item_sku']) {
                $filterWithSku = TRUE;
                $filterSku = implode (' OR ', $skus );
            }

            //filter with ORDER STATUS
           if ($data['show_order_statuses'] && count($data['order_statuses']) >= 1) {
               $filterWithStatus = TRUE;
               $filterStatus = "'" . implode ( "', '", $data['order_statuses'] ) . "'";
           }

            //filter with GROUP
            if ($data['show_group'] && count($data['customer_group']) >= 1) {
                $filterWithGroup = TRUE;
                $filterGroup = implode ( ',', $data['customer_group'] );

                Mage::log("filter_group".$filterGroup ,Zend_Log::DEBUG, 'ajay.log', true);

                if($filterGroup=="0") {
                    $filterGroup = "0,111";
                }

            }

            $backorderCollection = Mage::getModel('sales/order_item')->getCollection()
                ->addAttributeToSort('item_id', 'DESC');

          //  $backorderCollection->addAttributeToFilter('main_table.created_at', array('from' => $fromDate, 'to' => $toDate));

            if ($filterWithSku) {
               //$backorderCollection->addAttributeToFilter('main_table.sku',array('like' =>$sku));
                if(!empty($filterSku))
                $backorderCollection->getSelect()->where($filterSku);
            }

            if($ordertype=="back")
                $backorderCollection->getSelect()->where(new Zend_Db_Expr("(main_table.qty_backordered IS NOT NULL)"));/* OR gift_message_id IS NOT NULL*/
            else
                $backorderCollection->getSelect()->where(new Zend_Db_Expr("(main_table.product_type='simple')"));/* OR gift_message_id IS NOT NULL*/


            $addToquery = $backorderCollection->getSelect()->joinLeft(array('sales_flat_order' => $orderTable), 'main_table.order_id = sales_flat_order.entity_id',array('sales_flat_order.status','sales_flat_order.customer_group_id'));

            if ($filterWithStatus) {
                if(!empty($filterStatus))
                $addToquery->where("sales_flat_order.status in($filterStatus)");
            }

            if ($filterWithGroup) {
                if(!empty($filterGroup))
                    $addToquery->where("sales_flat_order.customer_group_id in($filterGroup)");
                
            }

            $addToquery->where("sales_flat_order.created_at BETWEEN '".$fromDate."' AND '".$toDate."' AND sales_flat_order.old_store_id=".$store);

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