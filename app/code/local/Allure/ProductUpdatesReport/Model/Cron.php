<?php

class Allure_ProductUpdatesReport_Model_Cron
{

    public function getProductUpdatesReport()
    {
        Mage::helper("productupdatereport")->sendProductUpdateEmail();
    }

    public function getProductUpdatesCollection()
    {        
        $days = (int)Mage::helper("productupdatereport/config")->getReportDays();

        if (!$days || $days <= 0) {
          $days = 7;
        }
        
        $fromDate = date('Y-m-d 00:00:00', strtotime('-'.($days).' days'));
        $toDate = date('Y-m-d 23:59:59', strtotime( 'yesterday'));

        Mage::helper("productupdatereport")->product_updates_log('Before date formated by timezone formdate='.$fromDate.' todate='.$toDate,'products_updates.log');

        $diffZone=$this->getDiffTimezone();
        $toDate = date('Y-m-d H:i:s', strtotime($diffZone,strtotime($toDate)));
        $fromDate = date('Y-m-d H:i:s', strtotime($diffZone,strtotime($fromDate)));

        Mage::helper("productupdatereport")->product_updates_log('After date formated by timezone formdate='.$fromDate.' todate='.$toDate,'products_updates.log');
 
        try {
            $rowData = array();

            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $table = $resource->getTableName('catalog_product_flat_1');

            $query = "SELECT sku,name as product_name,SUBSTRING_INDEX(SUBSTRING(sku , position('|' in sku)+1 ), '|', 1)as metal,price,created_at,updated_at FROM ". $table ." WHERE status = 1 AND (updated_at BETWEEN '".$fromDate."' AND '".$toDate."')";

            $rowData = $readConnection->fetchAll($query);
            $csvHeaders = Mage::helper("productupdatereport")->getTableHeaders();
            array_unshift($rowData, $csvHeaders);
            /*$collection = Mage::getModel('catalog/product')->getCollection()
                        ->addAttributeToSelect('*')
                         ->addAttributeToFilter(
                            array(
                                array('attribute'=> 'updated_at',array('from' => $fromDate, 'to' => $toDate)),
                            ))
                        ->addAttributeToFilter(array(array("attribute"=>"status","eq"=>1))); 
            */
           return $rowData;
           
        } catch (Exception $e) {
            Mage::helper("productupdatereport")->product_updates_log($e->getMessage(),'products_updates.log');
        }

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