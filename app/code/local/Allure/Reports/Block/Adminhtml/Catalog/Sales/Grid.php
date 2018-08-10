<?php

class Allure_Reports_Block_Adminhtml_Catalog_Sales_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_columnGroupBy = 'period';
    
    function getFilterData(){
        
        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('filter'));
        $requestData = $this->_filterDates($requestData, array('from', 'to'));
        $requestData['store_ids'] = $this->getRequest()->getParam('store_ids');
        $params = new Varien_Object();
        foreach ($requestData as $key => $value) {
            if (!empty($value)) {
                $params->setData($key, $value);
            }
        }
        
        return $params;
    }
    
    
    protected function _filterDates($array, $dateFields)
    {
        if (empty($dateFields)) {
            return $array;
        }
        $filterInput = new Zend_Filter_LocalizedToNormalized(array(
            'date_format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
        ));
        $filterInternal = new Zend_Filter_NormalizedToLocalized(array(
            'date_format' => Varien_Date::DATE_INTERNAL_FORMAT
        ));
        
        foreach ($dateFields as $dateField) {
            if (array_key_exists($dateField, $array) && !empty($dateField)) {
                $array[$dateField] = $filterInput->filter($array[$dateField]);
                $array[$dateField] = $filterInternal->filter($array[$dateField]);
            }
        }
        return $array;
    }
    
    
    
    public function __construct()
    {
        parent::__construct();
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(true);
        $this->setUseAjax(false);
    }
    
    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        //return 'sales/order_grid_collection';
        return 'sales/order';
    }
    
    protected function getPeriodFormat($filterData){
        $format = "%y-%m-%d";
        $periodType = $filterData['period_type'];
        if ($periodType == "year")
            $format = "%y";
        elseif ($periodType == "month"){
            $format = "%y-%m";
        }else{
            $format = "%y-%m-%d";
        }
        
        $reportType = $filterData['report_type'];
        $order_date_col= "main_table.created_at";
        if($reportType != "created_at_order"){
            $order_date_col = "updated_at";
        }
       // $groupClause = "DATE_FORMAT(".$order_date_col.", '".$format."')";
        $groupClause="customer_group_id";
        return $groupClause;
    }
    
    protected function getFilterCondition($filterData){
        $periodType = $filterData['period_type'];
        $reportType = $filterData['report_type'];
        $order_date_col= "main_table.created_at";
        $requestParams = $this->getRequest()->getParam('store_ids');
        $storeId = 1;
        if(!empty($requestParams)){
            $storeId = $requestParams;
        }
        $defaultStoreId = 1;
        Mage::app()->getStore()->setId($defaultStoreId);
    
        
       // $from = date('Y-m-d', strtotime($filterData->getData('from')));
      //  $to = date('Y-m-d', strtotime($filterData->getData('to')));
        
        //$from =Mage::getSingleton('core/date')->gmtDate('Y-m-d H:i:s',strtotime($filterData->getData('from')." 00:59:59"));
      //  $to = Mage::getSingleton('core/date')->gmtDate('Y-m-d H:i:s',strtotime($filterData->getData('to')." 23:59:59"));
         
        $from = $filterData->getData('from')." 00:00:00";
        $to = $filterData->getData('to')." 23:59:59";
        
        
        $local_tz = new DateTimeZone('UTC');
        $local = new DateTime('now', $local_tz);
       
        $user_tz = new DateTimeZone(Mage::getStoreConfig('general/locale/timezone',$defaultStoreId));
        $user = new DateTime('now', $user_tz);
        
        $usersTime = new DateTime($user->format('Y-m-d H:i:s'));
        $localsTime = new DateTime($local->format('Y-m-d H:i:s'));
        $offset = $local_tz->getOffset($local) - $user_tz->getOffset($user);
        $interval = $usersTime->diff($localsTime);
        
        if($offset > 0)
            $diffZone=$interval->h .' hours'.' '. $interval->i .' minutes';
        else
            $diffZone= '-'.$interval->h .' hours'.' '. $interval->i .' minutes';
            
                
        if(!empty($filterData->getData('from')) && !empty($filterData->getData('to')) ){
            $from = date("Y-m-d H:i:s",strtotime($diffZone,strtotime($from)));
            $to = date("Y-m-d H:i:s",strtotime($diffZone,strtotime($to)));
        }
      
        
        if($reportType != "created_at_order"){
            $order_date_col = "main_table.updated_at";
        }
        
        $whereClause = "";
        if ($periodType == "year"){
            $year1 = date("Y", strtotime($from));
            $year2 = date("Y", strtotime($to));
            $whereClause = "YEAR({$order_date_col})=".$year1.
            " OR YEAR({$order_date_col})=".$year2;
        }elseif ($periodType == "month"){
            $year1 = date("Y", strtotime($from));
            $year2 = date("Y", strtotime($to));
            $month1 = date("m", strtotime($from));
            $month2 = date("m", strtotime($to));
            $whereClause = "(YEAR($order_date_col)=".$year1.
                " AND MONTH($order_date_col)=".$month1.") OR ".
                "(MONTH($order_date_col)=".$month2.
                " AND YEAR($order_date_col)=".$year2.")";
        }else{
            $whereClause = $order_date_col." >='".$from."' and ".$order_date_col." <='".$to."'";
        }
        return $whereClause;
    }
    
    protected function getPeriodType(){
        $filterData = $this->getFilterData();
        $periodType = $filterData['period_type'];
        return $periodType;
    }
    
    protected function _prepareCollection()
    {
        $filterData = $this->getFilterData();
        
        $order_status = $filterData['order_statuses'];
        if(!empty($order_status)){
            $order_status = $order_status[0];
        }
        
        $requestParams = $this->getRequest()->getParam('store_ids');
        $storeId = 1;
        if(!empty($requestParams)){
            $storeId = $requestParams;
        }
        
        $from = $filterData->getData('from')." 00:00:00";
        $to = $filterData->getData('to')." 23:59:59";
      
        
        $groupClause = $this->getPeriodFormat($filterData);
        
        $condition = $this->getFilterCondition($filterData);

        /* $collection = Mage::getModel($this->_getCollectionClass())
            ->getCollection(); */
        $collection=Mage::getResourceModel('sales/order_item_collection');
     //   $collection = Mage::getModel('sales/order')->getCollection()
   
        
        //apply store condition
        if($storeId){
            //$collection = $collection->addFieldToFilter("store_id",array("in"=>array($storeId)));
            $collection = $collection->addFieldToFilter("main_table.store_id",array("in"=>array($storeId)));
        }
        
        if(!empty($order_status)){
            $order_status = explode(",", $order_status);
            $collection = $collection->
                addFieldToFilter("status",array("in"=>$order_status));
        }
        
        $counterpointStationIds = $filterData['counterpoint_sta_id'];
        if(!empty($counterpointStationIds)){
              $counterpointStationIds = explode(",", $counterpointStationIds[0]);
              $collection = $collection->
              addFieldToFilter("counterpoint_sta_id",array("in"=>$counterpointStationIds));
        }
        
        
        $customerGroup = $filterData['customer_group'];
        if(!empty($customerGroup)){
            $customerGroup = $customerGroup[0];
        }
        
        
        
       
        if(!empty($customerGroup)){
            $customerGroup = explode(",", $customerGroup);
            $collection->join(
                array('sub_table' => 'sales/order'),
                'sub_table.entity_id=main_table.order_id'
                );
            $collection = $collection->
            addFieldToFilter("sub_table.customer_group_id",array("in"=>$customerGroup));
            $collection->getSelect()->group('sub_table.customer_group_id');
        }
        $collection->getSelect()->group('main_table.order_id');
        
        $collection->getSelect()->group('sku');
    
        $reportType = $filterData['report_type'];
        $order_date_col= "main_table.created_at";
        if($reportType != "created_at_order"){
           $order_date_col = "updated_at";
        }
       
        
        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS);
        
        if($order_date_col=="updated_at")
            $collection->getSelect()->columns('main_table.updated_at as created_at');
        else 
            $collection->getSelect()->columns('main_table.created_at');
         
            $collection->getSelect()->columns('main_table.sku');
            if(!empty($customerGroup)){
                $collection->getSelect() ->columns('sub_table.customer_group_id');
            }
            $collection->getSelect()->columns('sum(IFNULL(main_table.qty_ordered,0)) total_qty_ordered')
            ->columns('sum(IFNULL(main_table.qty_canceled,0)) total_qty_canceled')
            ->columns('sum(IFNULL(main_table.qty_invoiced,0)) total_qty_invoiced')
            ->columns('sum(IFNULL(main_table.qty_refunded,0)) total_qty_refunded')
            ->columns('sum(IFNULL(main_table.qty_ordered * main_table.base_price ,0))  total_sales')
            ->columns('sum(IFNULL(main_table.qty_canceled * main_table.base_price ,0)) canceled_sales')
            ->columns('sum(IFNULL(main_table.qty_invoiced * main_table.base_price ,0)) invoiced_sales')
            ->columns('sum(IFNULL(main_table.qty_refunded * main_table.base_price ,0)) refunded_sales')
            ->columns('sum(IFNULL(main_table.base_discount_amount ,0)) discount_sales')
                     ->where($condition);
                  //echo $collection->getSelect();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
   
    protected $_countTotals = true;
    public function getTotals()
    {
        $totals = new Varien_Object();
        $fields = array(
            'total_qty_ordered' => 0,
            'total_qty_canceled' => 0,
            'total_qty_invoiced' => 0,
            'total_qty_refunded' => 0,
            'canceled_sales' => 0,
            'invoiced_sales' => 0,
            'refunded_sales' => 0,
            'discount_sales' => 0,
          
            
        );
        foreach ($this->getCollection() as $item) {
            foreach($fields as $field=>$value){
                $fields[$field]+=$item->getData($field);
            }
        }
        //First column in the grid
        $fields['created_at'] = 'Totals';
       // $totals->setData($fields);
        //print_r($totals->toArray());exit;
        return $totals;
    }
    
    public function getCurrentCurrencyCode()
    {
        $requestParams = $this->getRequest()->getParam('store_ids');
        $storeId = 1;
        if(!empty($requestParams)){
            //$storeId = $requestParams; store cleanup 
            return Mage::app()->getStore($storeId)->getCurrentCurrencyCode();
        }else {
            return "USD";
        }
       
    }
    public function getRate($toCurrency)
    {
        return 1;
    }
    
    protected function _prepareColumns()
    {
        
/*         $this->addColumn('period', array(
            'header'        => Mage::helper('sales')->__('Period'),
            'index'         => 'created_at',
            'width'         => 100,
            'sortable'      => false,
            'period_type'   => $this->getPeriodType(),
            'renderer'      => 'adminhtml/report_sales_grid_column_renderer_date',
            'totals_label'  => Mage::helper('sales')->__('Total'),
            'html_decorators' => array('nobr'),
        )); */
        
        if ($this->getFilterData()->getStoreIds()) {
            $this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
        }
        $currencyCode = $this->getCurrentCurrencyCode();
        $rate = $this->getRate($currencyCode);
        
        $customerGroup = $this->getFilterData()->getCustomerGroup();
        if(!empty($customerGroup)){
            $customerGroup = $customerGroup[0];
        }
        if(!empty($customerGroup)){
            $customerGroup = explode(",", $customerGroup);
        }
        
        if(!empty($customerGroup)){
        $this->addColumn('customer_group_id', array(
            'header'    => Mage::helper('sales')->__('Customer Group'),
            'index'     => 'customer_group_id',
            'width' => '50px',
            'type'      => 'options',
            'options'   => Mage::getModel('customer/group')->getCollection()->toOptionHash()
        ));
        }
        $this->addColumn('sku', array(
            'header'    => Mage::helper('sales')->__('SKU'),
            'index'     => 'sku',
            'type'      => 'text',
            'width' => '250px',
            'sortable'  => false
        ));
        
        $this->addColumn('total_qty_ordered', array(
            'header'    => Mage::helper('sales')->__('Total Qty Ordered'),
            'index'     => 'total_qty_ordered',
            'type'      => 'number',
            'total'     => 'sum',
            'sortable'  => false
        ));
        
        $this->addColumn('total_sales', array(
            'header'        => Mage::helper('sales')->__('Sales Total'),
            'index'         => 'total_sales',
            'total'         => 'sum',
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'sortable'      => false,
            'rate'          => $rate,
        ));
        
        
       
        $this->addColumn('total_qty_invoiced', array(
            'header'    => Mage::helper('sales')->__('Total Qty Invoiced'),
            'index'     => 'total_qty_invoiced',
            'type'      => 'number',
            'total'     => 'sum',
            'sortable'  => false
        ));
        
        
        $this->addColumn('invoiced_sales', array(
            'header'        => Mage::helper('sales')->__('Invoiced Total Amount'),
            'index'         => 'invoiced_sales',
            'total'         => 'sum',
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'sortable'      => false,
            'rate'          => $rate,
        ));
        
        
        $this->addColumn('total_qty_refunded', array(
            'header'    => Mage::helper('sales')->__('Total Qty Refunded'),
            'index'     => 'total_qty_refunded',
            'type'      => 'number',
            'total'     => 'sum',
            'sortable'  => false
        ));
        
        $this->addColumn('refunded_sales', array(
            'header'        => Mage::helper('sales')->__('Refunded Total Amount'),
            'index'         => 'refunded_sales',
            'total'         => 'sum',
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'sortable'      => false,
            'rate'          => $rate,
        ));
       
        $this->addColumn('total_qty_canceled', array(
            'header'    => Mage::helper('sales')->__('Total Qty Canceled'),
            'index'     => 'total_qty_canceled',
            'type'      => 'number',
            'total'     => 'sum',
            'sortable'  => false
        ));
        
        $this->addColumn('canceled_sales', array(
            'header'        => Mage::helper('sales')->__('Canceled Total Amount'),
            'index'         => 'canceled_sales',
            'total'         => 'sum',
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'sortable'      => false,
            'rate'          => $rate,
        ));
        
        
        $this->addExportType('*/*/exportCatalogreportCsv', Mage::helper('adminhtml')->__('CSV'));
        $this->addExportType('*/*/exportCatalogreportExcel', Mage::helper('adminhtml')->__('Excel XML'));
        
        return parent::_prepareColumns();
    }
}
