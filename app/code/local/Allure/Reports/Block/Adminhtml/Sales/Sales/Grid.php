<?php

class Allure_Reports_Block_Adminhtml_Sales_Sales_Grid extends Mage_Adminhtml_Block_Widget_Grid
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
        $this->setPagerVisibility(false);
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
        $order_date_col= "created_at";
        if($reportType != "created_at_order"){
            $order_date_col = "updated_at";
        }
        $groupClause = "DATE_FORMAT(".$order_date_col.", '".$format."')";
        return $groupClause;
    }
    
    protected function getFilterCondition($filterData){
        $periodType = $filterData['period_type'];
        $reportType = $filterData['report_type'];
        $order_date_col= "created_at";
        $from = $filterData->getData('from')." 00:00:00";
        $to = $filterData->getData('to')." 23:59:59";
        if($reportType != "created_at_order"){
            $order_date_col = "updated_at";
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
        $storeId = 0;
        if(!empty($requestParams)){
            $storeId = $requestParams;
        }
        
        $from = $filterData->getData('from')." 00:00:00";
        $to = $filterData->getData('to')." 23:59:59";
        //$from = str_replace("/", "-", $from);
        //$to = str_replace("/", "-", $to);
        // print_r($from);
        // $from = date("Y-m-d H:i:s", strtotime($from));
        // $to = date("Y-m-d H:i:s", strtotime($to));
        //allure
        $groupClause = $this->getPeriodFormat($filterData);
        
        $condition = $this->getFilterCondition($filterData);

        $collection = Mage::getModel($this->_getCollectionClass())
            ->getCollection();
        
        //apply store condition
        if($storeId){
            $collection = $collection->
                addFieldToFilter("store_id",array("in"=>array($storeId)));
        }
        
        if(!empty($order_status)){
            $order_status = explode(",", $order_status);
            $collection = $collection->
                addFieldToFilter("status",array("in"=>$order_status));
        }
        
        $reportType = $filterData['report_type'];
        $order_date_col= "created_at";
        if($reportType != "created_at_order"){
           $order_date_col = "updated_at";
        }
       
        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS);
        
        if($order_date_col=="updated_at")
            $collection->getSelect()->columns('updated_at as created_at');
        else 
            $collection->getSelect()->columns('created_at');
        
        $collection->getSelect()
            ->columns('count(entity_id) count')
            ->columns('sum(total_qty_ordered) total_qty_ordered')
            ->columns('sum(base_grand_total * store_to_base_rate) total_paid')
            ->columns('IFNULL(sum(base_total_invoiced * store_to_base_rate),0) total_invoiced')
            ->columns('IFNULL(sum(base_total_refunded * store_to_base_rate),0) total_refunded')
            ->columns('IFNULL(sum(base_tax_amount * store_to_base_rate),0) tax_amount')
            ->columns('IFNULL(sum(base_shipping_amount * store_to_base_rate),0) base_shipping_amount')
            ->columns('IFNULL(sum(base_discount_amount * store_to_base_rate),0)*(-1) base_discount_amount')
            ->columns('IFNULL(sum(base_total_canceled * store_to_base_rate),0) base_total_canceled')
            //->where("created_at >='".$from."' and created_at <='".$to."'")
            ->where($condition)
            ->group($groupClause);
         
     
        
         // echo $collection->getSelect()->__tostring();
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected $_countTotals = true;
    public function getTotals()
    {
        $totals = new Varien_Object();
        $fields = array(
            'count' => 0,
            'total_qty_ordered' => 0,
            'total_paid' => 0,
            'total_invoiced' => 0,
            'total_refunded' => 0,
            'tax_amount' => 0,
            'base_shipping_amount' => 0,
            'base_discount_amount' => 0,
            'base_total_canceled' => 0,
            
        );
        foreach ($this->getCollection() as $item) {
            foreach($fields as $field=>$value){
                $fields[$field]+=$item->getData($field);
            }
        }
        //First column in the grid
        $fields['created_at'] = 'Totals';
        $totals->setData($fields);
        //print_r($totals->toArray());exit;
        return $totals;
    }
    
    public function getCurrentCurrencyCode()
    {
        return "USD";
    }
    public function getRate($toCurrency)
    {
        return Mage::app()->getStore()->getBaseCurrency()->getRate($toCurrency);
    }
    
    protected function _prepareColumns()
    {
        
        $this->addColumn('created_at', array(
            'header'=> Mage::helper('sales')->__('Period'),
            'type'  => 'text',
            'period_type'   => $this->getPeriodType(),
            'renderer'      => 'adminhtml/report_sales_grid_column_renderer_date',
            'index' => 'created_at',
            'totals_label'  => Mage::helper('sales')->__('Total'),
        ));
        
        $this->addColumn('count', array(
            'header'=> Mage::helper('sales')->__('Orders'),
            'type'  => 'number',
            'index' => 'count',
            'total'     => 'sum',
        ));
        
        $this->addColumn('total_qty_ordered', array(
            'header'=> Mage::helper('sales')->__('Sales Items'),
            'type'  => 'number',
            'index' => 'total_qty_ordered',
            'total'     => 'sum',
        ));
        
        $currencyCode = $this->getCurrentCurrencyCode();
        $rate = $this->getRate($currencyCode);
        $this->addColumn('total_paid', array(
            'header'=> Mage::helper('sales')->__('Sales Total'),
            'index' => 'total_paid',
            'total'     => 'sum',
            'type'  => 'currency',
            'currency_code' => $currencyCode,
            'rate'          => $rate,
        ));
        
        $this->addColumn('total_invoiced', array(
            'header'=> Mage::helper('sales')->__('Invoiced'),
            'index' => 'total_invoiced',
            'total'     => 'sum',
            'type'  => 'currency',
            'currency_code' => $currencyCode,
            'rate'          => $rate,
        ));
        
        $this->addColumn('total_refunded', array(
            'header'=> Mage::helper('sales')->__('Refunded'),
            'index' => 'total_refunded',
            'total'     => 'sum',
            'type'  => 'currency',
            'currency_code' => $currencyCode,
            'rate'          => $rate,
        ));
        
        $this->addColumn('tax_amount', array(
            'header'=> Mage::helper('sales')->__('Sales Tax'),
            'index' => 'tax_amount',
            'total'     => 'sum',
            'type'  => 'currency',
            'currency_code' => $currencyCode,
            'rate'          => $rate,
        ));
        
        
        $this->addColumn('base_shipping_amount', array(
            'header'=> Mage::helper('sales')->__('Sales Shipping'),
            'index' => 'base_shipping_amount',
            'total'     => 'sum',
            'type'  => 'currency',
            'currency_code' => $currencyCode,
            'rate'          => $rate,
        ));
        
        $this->addColumn('base_discount_amount', array(
            'header'=> Mage::helper('sales')->__('Sales Discount'),
            'index' => 'base_discount_amount',
            'type'  => 'currency',
            'currency_code' => $currencyCode,
            'rate'          => $rate,
        ));
        
        $this->addColumn('base_total_canceled', array(
            'header'=> Mage::helper('sales')->__('Canceled'),
            'index' => 'base_total_canceled',
            'total'     => 'sum',
            'type'  => 'currency',
            'currency_code' => $currencyCode,
            'rate'          => $rate,
        ));
        
        
        $this->addExportType('*/*/exportSalesreportCsv', Mage::helper('adminhtml')->__('CSV'));
        $this->addExportType('*/*/exportSalesreportExcel', Mage::helper('adminhtml')->__('Excel XML'));
        
        return parent::_prepareColumns();
    }
}
