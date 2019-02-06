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
       // $groupClause = "DATE_FORMAT(".$order_date_col.", '".$format."')";
        $groupClause="customer_group_id";
        return $groupClause;
    }

    protected function getFilterCondition($filterData){
        $periodType = $filterData['period_type'];
        $reportType = $filterData['report_type'];
        $order_date_col= "created_at";
        $requestParams = $this->getRequest()->getParam('store_ids');
        //var_dump($requestParams);

        $storeId = 1;
        $defaultStoreId = 1;
        if(!empty($requestParams)){
            $storeId = $requestParams;
            $defaultStoreId=$requestParams;
        }

        //Mage::app()->getStore()->setId($defaultStoreId);


       // $from = date('Y-m-d', strtotime($filterData->getData('from')));
      //  $to = date('Y-m-d', strtotime($filterData->getData('to')));

        //$from =Mage::getSingleton('core/date')->gmtDate('Y-m-d H:i:s',strtotime($filterData->getData('from')." 00:59:59"));
      //  $to = Mage::getSingleton('core/date')->gmtDate('Y-m-d H:i:s',strtotime($filterData->getData('to')." 23:59:59"));

        $from = $filterData->getData('from')." 00:00:00";
        $to = $filterData->getData('to')." 23:59:59";


        $local_tz = new DateTimeZone('UTC');
        $local = new DateTime('now', $local_tz);

        //$user_tz = new DateTimeZone(Mage::getStoreConfig('general/locale/timezone',$defaultStoreId));

        $store=Mage::getModel("allure_virtualstore/store")->load($storeId);
        //var_dump($store->getTimezone());
        if(empty($store->getTimezone()))
            $timezone="America/New_York";
        else
            $timezone=$store->getTimezone();
        $user_tz = new DateTimeZone($timezone);
        $user = new DateTime('now', $user_tz);

        $usersTime = new DateTime($user->format('Y-m-d H:i:s'));
        $localsTime = new DateTime($local->format('Y-m-d H:i:s'));
        $offset = $local_tz->getOffset($local) - $user_tz->getOffset($user);
        $interval = $usersTime->diff($localsTime);

        if($offset > 0)
            $diffZone=$interval->h .' hours'.' '. $interval->i .' minutes';
        else
            $diffZone= '-'.$interval->h .' hours'.' '. $interval->i .' minutes';

        //var_dump($to);
        if(!empty($filterData->getData('from')) && !empty($filterData->getData('to')) ){
            $from = date("Y-m-d H:i:s",strtotime($diffZone,strtotime($from)));
            $to = date("Y-m-d H:i:s",strtotime($diffZone,strtotime($to)));
        }
       // var_dump($to);

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
        //var_dump($whereClause);
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
        $collection = Mage::getModel('sales/order')->getCollection();
        
        $subquery = new Zend_Db_Expr("(SELECT * FROM sales_flat_order_payment GROUP BY parent_id )");
        $collection->getSelect()->join(array("payment" => $subquery),
            "main_table.entity_id = payment.parent_id");
        
        /* ->join(
            array('payment' => 'sales/order_payment'),
            'main_table.entity_id=payment.parent_id',
            array('payment_method' => 'payment.method','cc_type' => 'payment.cc_type')
            ); */

        //apply store condition
        if(!empty($requestParams)){
            //$collection = $collection->addFieldToFilter("store_id",array("in"=>array($storeId)));
            $collection = $collection->addFieldToFilter("old_store_id",array("in"=>array($storeId)));
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

        if(!empty($customerGroup) || $customerGroup=="0"){

            $customerGroup = explode(",", $customerGroup);
            $collection = $collection->
            addFieldToFilter("customer_group_id",array("in"=>$customerGroup));
            $collection->getSelect()->group('customer_group_id');
        }



        $payment_methods = $filterData['payment_methods'];
        if(!empty($payment_methods)){
            $payment_methods = $payment_methods[0];
        }

        if(!empty($payment_methods)){
            $payment_methods = explode(",", $payment_methods);
            $collection = $collection->
            addFieldToFilter("payment.method",array("in"=>$payment_methods));
        }

        $card_types = $filterData['card_type'];
        if(!empty($card_types)){
            $card_types = $card_types[0];
        }

        if(!empty($card_types)){


          /*  var_dump($card_types);

            die;
            $card_types = explode(",", $card_types);
             $str='';
            $te = array();
            foreach ($card_types as $card){
                // $str = "'like " . '%"cc_type";s:2:"VI"%';
                $te[] ="'". '%"cc_type";s:2:'.'"'.$card.'"%'."'";
                $te[] ="'$card'";
            }
            $str = implode(",", $te);*/


            $temp = explode(",",$card_types);
            $card_types = "'" . implode ( "', '", $temp ) . "'";


            $collection->getSelect()->where("payment.cc_type IN (".$card_types.")");



            //$collection = $collection->addFieldToFilter("payment.cc_type",array("in"=>$card_types));
        }

        
		//apply filter for order collection related to create order method
        $createOrderMethodsStr = $filterData['create_order_method'][0];
        if($createOrderMethodsStr != null && $createOrderMethodsStr != ""){
            $createOrderMethods = explode(",", $createOrderMethodsStr);
    		if(count($createOrderMethods) > 0){
    		  $collection->addFieldToFilter("create_order_method",array("in" => $createOrderMethods));
    		}
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





        if(empty($requestParams)):

            $collection->getSelect()
            ->columns('main_table.customer_group_id')
            ->columns('count(IFNULL(.main_table.entity_id,0)) orders_count')
            ->columns('sum(IFNULL(main_table.total_qty_ordered,0)) total_qty_ordered')
            ->columns('sum((IFNULL(main_table.base_grand_total,0) * IF(main_table.base_to_global_rate != 0, main_table.base_to_global_rate, 1))-IFNULL(main_table.base_total_canceled,0)-IFNULL(main_table.teamwork_gift_amount,0)-IFNULL(main_table.teamwork_deposit_amount,0)) total_income_amount')
            ->columns('sum(
                       (IFNULL(main_table.base_total_invoiced,0)-IFNULL(main_table.base_tax_invoiced,0)-IFNULL(base_shipping_invoiced,0)
                      -(IFNULL(main_table.base_total_refunded,0)-IFNULL(main_table.base_tax_refunded,0)-IFNULL(main_table.base_shipping_refunded,0))
                      )
                        -IFNULL(main_table.teamwork_gift_amount,0)-IFNULL(main_table.teamwork_deposit_amount,0)
                        ) total_revenue_amount')

                      ->columns('sum(
                        (IFNULL(main_table.base_total_paid,0)-IFNULL(base_total_refunded,0))
                       -(IFNULL(main_table.base_tax_invoiced,0)-(IFNULL(base_tax_refunded,0))
                       -(IFNULL(main_table.base_shipping_invoiced,0)-IFNULL(base_shipping_invoiced,0))
                       -IFNULL(main_table.base_total_invoiced_cost,0))
                        -IFNULL(main_table.teamwork_gift_amount,0)-IFNULL(main_table.teamwork_deposit_amount,0)
                        ) total_profit_amount
                     ')
                     ->columns('sum((IFNULL(main_table.base_total_invoiced,0)* IF(main_table.base_to_global_rate != 0, main_table.base_to_global_rate, 1))-IFNULL(main_table.teamwork_gift_amount,0)-IFNULL(main_table.teamwork_deposit_amount,0)) total_invoiced_amount')
                     ->columns('sum((IFNULL(main_table.base_total_invoiced,0) * IF(main_table.base_to_global_rate != 0, main_table.base_to_global_rate, 1))-IFNULL(main_table.teamwork_gift_amount,0)-IFNULL(main_table.teamwork_deposit_amount,0))
                                -sum(IFNULL(main_table.base_total_refunded,0))
                                 total_net_sale')
                     ->columns('sum(IFNULL(main_table.base_total_canceled,0) * IF(main_table.base_to_global_rate != 0, main_table.base_to_global_rate, 1)) total_canceled_amount')
                     ->columns('sum(IFNULL(main_table.base_total_paid,0) * IF(main_table.base_to_global_rate != 0, main_table.base_to_global_rate, 1)) total_paid_amount')
                     ->columns('sum(IFNULL(main_table.base_total_refunded,0) * IF(main_table.base_to_global_rate != 0, main_table.base_to_global_rate, 1)) total_refunded_amount')
                     ->columns('sum((IFNULL(main_table.base_tax_amount,0)-IFNULL(main_table.base_tax_canceled,0)) * IF(main_table.base_to_global_rate != 0, main_table.base_to_global_rate, 1)) total_tax_amount')
                     ->columns('sum((IFNULL(main_table.base_tax_invoiced,0)-IFNULL(main_table.base_tax_refunded,0))* IF(main_table.base_to_global_rate != 0, main_table.base_to_global_rate, 1)) total_tax_amount_actual')
                     ->columns('sum((IFNULL(main_table.base_shipping_amount,0)-IFNULL(main_table.base_shipping_canceled,0))* IF(main_table.base_to_global_rate != 0, main_table.base_to_global_rate, 1)) total_shipping_amount')
                     ->columns('sum((IFNULL(main_table.base_shipping_invoiced,0)-IFNULL(main_table.base_shipping_refunded,0))* IF(main_table.base_to_global_rate != 0, main_table.base_to_global_rate, 1)) total_shipping_amount_actual')
                     ->columns('ABS(sum(((IFNULL(main_table.base_discount_amount,0))-IFNULL(main_table.base_discount_canceled,0)))* IF(main_table.base_to_global_rate != 0, main_table.base_to_global_rate, 1)) total_discount_amount')
                     ->columns('sum((IFNULL(main_table.base_discount_invoiced,0)-IFNULL(main_table.base_discount_refunded,0))* IF(main_table.base_to_global_rate != 0, main_table.base_to_global_rate, 1)) total_discount_amount_actual')
                     ->where($condition);

        else:


            $collection->getSelect()
                ->columns('main_table.customer_group_id')
                ->columns('count(IFNULL(.main_table.entity_id,0)) orders_count')
                ->columns('sum(IFNULL(main_table.total_qty_ordered,0)) total_qty_ordered')
                ->columns('sum(IFNULL(main_table.base_grand_total,0)-IFNULL(main_table.base_total_canceled,0)-IFNULL(main_table.teamwork_gift_amount,0)-IFNULL(main_table.teamwork_deposit_amount,0)) total_income_amount')
                ->columns('sum(
                       (IFNULL(main_table.base_total_invoiced,0)-IFNULL(main_table.base_tax_invoiced,0)-IFNULL(base_shipping_invoiced,0)
                      -(IFNULL(main_table.base_total_refunded,0)-IFNULL(main_table.base_tax_refunded,0)-IFNULL(main_table.base_shipping_refunded,0))
                      )
                        -IFNULL(main_table.teamwork_gift_amount,0)-IFNULL(main_table.teamwork_deposit_amount,0)
                        ) total_revenue_amount')

                ->columns('sum(
                        (IFNULL(main_table.base_total_paid,0)-IFNULL(base_total_refunded,0))
                       -(IFNULL(main_table.base_tax_invoiced,0)-(IFNULL(base_tax_refunded,0))
                       -(IFNULL(main_table.base_shipping_invoiced,0)-IFNULL(base_shipping_invoiced,0))
                       -IFNULL(main_table.base_total_invoiced_cost,0))
                        -IFNULL(main_table.teamwork_gift_amount,0)-IFNULL(main_table.teamwork_deposit_amount,0)
                        ) total_profit_amount
                     ')
                ->columns('sum(IFNULL(main_table.base_total_invoiced,0)-IFNULL(main_table.teamwork_gift_amount,0)-IFNULL(main_table.teamwork_deposit_amount,0)) total_invoiced_amount')
                ->columns('sum(IFNULL(main_table.base_total_invoiced,0)-IFNULL(main_table.teamwork_gift_amount,0)-IFNULL(main_table.teamwork_deposit_amount,0))
                                -sum(IFNULL(main_table.base_total_refunded,0))
                                 total_net_sale')
                ->columns('sum(IFNULL(main_table.base_total_canceled,0)) total_canceled_amount')
                ->columns('sum(IFNULL(main_table.base_total_paid,0)) total_paid_amount')
                ->columns('sum(IFNULL(main_table.base_total_refunded,0)) total_refunded_amount')
                ->columns('sum(IFNULL(main_table.base_tax_amount,0)-IFNULL(main_table.base_tax_canceled,0)) total_tax_amount')
                ->columns('sum(IFNULL(main_table.base_tax_invoiced,0)-IFNULL(main_table.base_tax_refunded,0)) total_tax_amount_actual')
                ->columns('sum(IFNULL(main_table.base_shipping_amount,0)-IFNULL(main_table.base_shipping_canceled,0)) total_shipping_amount')
                ->columns('sum(IFNULL(main_table.base_shipping_invoiced,0)-IFNULL(main_table.base_shipping_refunded,0)) total_shipping_amount_actual')
                ->columns('ABS(sum((IFNULL(main_table.base_discount_amount,0))-IFNULL(main_table.base_discount_canceled,0))) total_discount_amount')
                ->columns('sum(IFNULL(main_table.base_discount_invoiced,0)-IFNULL(main_table.base_discount_refunded,0)) total_discount_amount_actual')
                ->where($condition);


        endif;

                   // echo $collection->getSelect()->__toString();





        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected $_countTotals = true;
    public function getTotals()
    {
        $totals = new Varien_Object();
        $fields = array(
            'orders_count' => 0,
            'total_qty_ordered' => 0,
            'total_income_amount' => 0,
            'total_invoiced_amount' => 0,
            'total_refunded_amount' => 0,
            'total_tax_amount' => 0,
            'total_shipping_amount' => 0,
            'total_discount_amount' => 0,
            'total_canceled_amount' => 0,

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
            $store=Mage::getModel("allure_virtualstore/store")->load($requestParams);
            return $store->getCurrency();
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

        $customerGroup = $this->getFilterData()->getCustomerGroup();


        if(!empty($customerGroup)){
            $customerGroup = $customerGroup[0];
        }
        if(!empty($customerGroup)){
            $customerGroup = explode(",", $customerGroup);
        }
        if(!empty($customerGroup) || $customerGroup=="0"){
        $this->addColumn('customer_group_id', array(
            'header'    => Mage::helper('sales')->__('Customer Group'),
            'index'     => 'customer_group_id',
            'width' => '50px',
            'type'      => 'options',
            'options'   => Mage::getModel('customer/group')->getCollection()->toOptionHash()
        ));
        }

        $this->addColumn('orders_count', array(
            'header'    => Mage::helper('sales')->__('Orders'),
            'index'     => 'orders_count',
            'type'      => 'number',
            'total'     => 'sum',
            'sortable'  => false
        ));

        $this->addColumn('total_qty_ordered', array(
            'header'    => Mage::helper('sales')->__('Sales Items'),
            'index'     => 'total_qty_ordered',
            'type'      => 'number',
            'total'     => 'sum',
            'sortable'  => false
        ));



        if ($this->getFilterData()->getStoreIds()) {
            $this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
        }
        $currencyCode = $this->getCurrentCurrencyCode();
        $rate = $this->getRate($currencyCode);

        $this->addColumn('total_income_amount', array(
            'header'        => Mage::helper('sales')->__('Sales Total'),
            'index'         => 'total_income_amount',
            'total'         => 'sum',
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'sortable'      => false,
            'rate'          => $rate,
        ));


    /*     $this->addColumn('total_profit_amount', array(
            'header'            => Mage::helper('sales')->__('Profit'),
            'type'              => 'currency',
            'currency_code'     => $currencyCode,
            'index'             => 'total_profit_amount',
            'total'             => 'sum',
            'sortable'          => false,
            'visibility_filter' => array('show_actual_columns'),
            'rate'              => $rate,
        )); */

        $this->addColumn('total_invoiced_amount', array(
            'header'        => Mage::helper('sales')->__('Invoiced'),
            'index'         => 'total_invoiced_amount',
            'total'         => 'sum',
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'sortable'      => false,
            'rate'          => $rate,
        ));
        $this->addColumn('total_net_sale', array(
            'header'        => Mage::helper('sales')->__('Net Sales'),
            'index'         => 'total_net_sale',
            'total'         => 'sum',
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'sortable'      => false,
            'rate'          => $rate,
        ));

      /*   $this->addColumn('total_paid_amount', array(
            'header'            => Mage::helper('sales')->__('Paid'),
            'type'              => 'currency',
            'currency_code'     => $currencyCode,
            'index'             => 'total_paid_amount',
            'total'             => 'sum',
            'sortable'          => false,
            'visibility_filter' => array('show_actual_columns'),
            'rate'              => $rate,
        )); */

        $this->addColumn('total_refunded_amount', array(
            'header'        => Mage::helper('sales')->__('Refunded'),
            'index'         => 'total_refunded_amount',
            'total'         => 'sum',
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'sortable'      => false,
            'rate'          => $rate,
        ));

        $this->addColumn('total_tax_amount', array(
            'header'        => Mage::helper('sales')->__('Sales Tax'),
            'index'         => 'total_tax_amount',
            'total'         => 'sum',
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'sortable'      => false,
            'rate'          => $rate,
        ));

        /* $this->addColumn('total_tax_amount_actual', array(
            'header'            => Mage::helper('sales')->__('Tax'),
            'type'              => 'currency',
            'currency_code'     => $currencyCode,
            'index'             => 'total_tax_amount_actual',
            'total'             => 'sum',
            'sortable'          => false,
            'visibility_filter' => array('show_actual_columns'),
            'rate'              => $rate,
        ));
         */

        $this->addColumn('total_shipping_amount', array(
            'header'        => Mage::helper('sales')->__('Sales Shipping'),
            'index'         => 'total_shipping_amount',
            'total'         => 'sum',
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'sortable'      => false,
            'rate'          => $rate,
        ));

       /*  $this->addColumn('total_shipping_amount_actual', array(
            'header'            => Mage::helper('sales')->__('Shipping'),
            'type'              => 'currency',
            'currency_code'     => $currencyCode,
            'index'             => 'total_shipping_amount_actual',
            'total'             => 'sum',
            'sortable'          => false,
            'visibility_filter' => array('show_actual_columns'),
            'rate'              => $rate,
        )); */

        $this->addColumn('total_discount_amount', array(
            'header'        => Mage::helper('sales')->__('Sales Discount'),
            'index'         => 'total_discount_amount',
            'total'         => 'sum',
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'rate'          => $rate,
            'sortable'      => false,
        ));

       /*  $this->addColumn('total_discount_amount_actual', array(
            'header'            => Mage::helper('sales')->__('Discount'),
            'type'              => 'currency',
            'currency_code'     => $currencyCode,
            'index'             => 'total_discount_amount_actual',
            'total'             => 'sum',
            'sortable'          => false,
            'visibility_filter' => array('show_actual_columns'),
            'rate'              => $rate,
        )); */

        $this->addColumn('total_canceled_amount', array(
            'header'        => Mage::helper('sales')->__('Canceled'),
            'index'         => 'total_canceled_amount',
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'rate'          => $rate,
            'total'         => 'sum',
            'sortable'      => false,
        ));



        $this->addExportType('*/*/exportSalesreportCsv', Mage::helper('adminhtml')->__('CSV'));
        $this->addExportType('*/*/exportSalesreportExcel', Mage::helper('adminhtml')->__('Excel XML'));

        return parent::_prepareColumns();
    }
}
