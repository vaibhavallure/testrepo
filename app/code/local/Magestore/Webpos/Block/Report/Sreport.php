<?php

/*
 * Web POS by Magestore.com
 * Version 2.3.2
 * Updated by Daniel - 12/2015
 */

class Magestore_Webpos_Block_Report_Sreport extends Mage_Adminhtml_Block_Template {

    public function __construct() {
        $this->setTemplate('webpos/webpos/reports/sreport.phtml');
        parent::_construct();
    }

    public function getStoreName() {
        $storeId = Mage::app()->getStore()->getId();
        $storeName = Mage::getModel('core/store')->load($storeId)->getData('name');
        return $storeName;
    }

    public function getSalesInfo() {
        $storeId = Mage::app()->getStore()->getId();       
        $collection = Mage::getModel('sales/order')->getCollection()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('store_id', array('eq' => $storeId))
                    ->addFieldToFilter('webpos_admin_id', array('nin' => ''));
        $from = Mage::app()->getRequest()->getParam('from');
        $to = Mage::app()->getRequest()->getParam('to');
        $type = Mage::app()->getRequest()->getParam('type');
        $status = Mage::app()->getRequest()->getParam('status');
        if($from != ''){
            $from = date_create($from);
            $from = date_format($from,"Y-m-d H:i:s");
            $collection->addAttributeToFilter('created_at', array('from' => $from));
        }
        if($to != ''){
            $to = date_create($to);
            $to = date_format($to,"Y-m-d H:i:s");
            $collection->addAttributeToFilter('created_at', array('to' => $to));
        }
        if($status != ''){
            $collection->addFieldToFilter('status', array('in' => $status));
        }
        switch ($type) {
            case 'cashier':
                $collection = $this->salesByCashier($collection);
                break;
            
            case 'location':
                $collection = $this->salesByLocation($collection);
                break;

            case 'customer_group':
                $collection = $this->salesByCustomer($collection);
                break;

            case 'payment':
                $collection = $this->salesByPayment($collection);
                break;

            case 'general':
                $collection = $this->salesGeneralReport($collection);
                break;
        }
        return $collection;
    }

    public function salesByCashier($collection){
        $result = array();
        $result['type'] = 'cashier';
        if(count($collection) > 0){
            foreach ($collection as $order) {
                $id = $order->getData('webpos_admin_id');
                $user = Mage::getModel('webpos/user')->load($id);
                if($user->getId()){
                    if(isset($result['data'][$id])){
                        $result['data'][$id]['sales'] += (float) $order->getData('grand_total');
                        $result['data'][$id]['total_orders'] += (int)1;
                    }else{
                        $result['data'][$id] = array('name' => $user->getData('display_name'), 'sales' => (float)$order->getData('grand_total'),'total_orders' => (int)1);
                    }
                }else{
                    $result['data'][] = array('name' => $this->__('Unknown'), 'sales' => (float)$order->getData('totals'),'total_orders' => (int)1);
                }   
            }
        }
        return $result;
    }

    public function salesByLocation($collection){
        $result = array();
        $result['type'] = 'location';
        $inventoryActive = Mage::helper('webpos')->isInventoryWebPOS11Active();
        if(count($collection) > 0){
            foreach ($collection as $order) {
                $id = $order->getData('location_id');
                if($inventoryActive == true){
                    $model = Mage::getModel('inventoryplus/warehouse')->load($id);
                    $fieldName = "warehouse_name";
                }else{
                    $model = Mage::getModel('webpos/userlocation')->load($id);
                    $fieldName = "display_name";
                }
                if($model->getId()){
                    if(isset($result['data'][$id])){
                        $result['data'][$id]['sales'] += (float) $order->getData('grand_total');
                        $result['data'][$id]['total_orders'] += (int)1;
                    }else{
                        $result['data'][$id] = array('name' => $model->getData($fieldName), 'sales' => (float)$order->getData('grand_total'),'total_orders' => (int)1);
                    }
                }else{
                    $result['data'][] = array('name' => $this->__('Unknown'), 'sales' => (float)$order->getData('totals'));
                }   
            }
        }
        return $result;
    }

    public function salesByCustomer($collection){
        $result = array();
        $result['type'] = 'customer_group';
        if(count($collection) > 0){
            foreach ($collection as $order) {
                $id = $order->getData('customer_group_id');
                $name = Mage::getModel('customer/group')->load($id)->getCustomerGroupCode();
                if(isset($result['data'][$id])){
                    $result['data'][$id]['sales'] += (float) $order->getData('grand_total');
                    $result['data'][$id]['total_orders'] += (int)1;
                }else{
                    $result['data'][$id] = array('name' => $name, 'sales' => (float)$order->getData('grand_total'),'total_orders' => (int)1);
                }
            }
        }
        return $result;
    }    

    public function salesByPayment($collection){
        $result = array();
        $result['type'] = 'payment';
        if(count($collection) > 0){
            $payment_arr = array();
            $webpos_payment_methods = array('cashforpos', 'ccforpos', 'cp1forpos', 'cp2forpos', 'codforpos');
            $webpos_payment_methods_label = array(
                'cashforpos' => Mage::helper('webpos/payment')->getCashMethodTitle(),
                'ccforpos' => Mage::helper('webpos/payment')->getCcMethodTitle(),
                'cp1forpos' => Mage::helper('webpos/payment')->getCp1MethodTitle(),
                'cp2forpos' => Mage::helper('webpos/payment')->getCp2MethodTitle(),
                'codforpos' => Mage::helper('webpos/payment')->getCodMethodTitle()
            );
            foreach ($collection as $order) {
                $payment = $order->getPayment()->getMethod();
                if (in_array($payment, $webpos_payment_methods)  || $payment == 'authorizenet' || $payment == 'paypal_direct' || $payment == 'multipaymentforpos') {
                    if ($payment == 'paypal_direct' || $payment == 'authorizenet') {
                        switch($payment){
                            case 'paypal_direct':
                                $result['data'][$payment]['name'] = 'Paypal';
                                break;
                            case 'authorizenet':
                                $result['data'][$payment]['name'] = 'Authorize.net';
                                break;
                        }
                       
                        if (isset($result['data'][$payment]['sales'])) {
                            $result['data'][$payment]['sales'] += floatval($order->getData('base_grand_total'));
                        } else {
                            $result['data'][$payment]['sales'] = floatval($order->getData('base_grand_total'));
                        }
                        if (isset($result['data'][$payment]['total_orders'])) {
                            $result['data'][$payment]['total_orders'] ++;
                        } else {
                            $result['data'][$payment]['total_orders'] = 1;
                        }
                    } else {
                        if ($payment != 'multipaymentforpos') {
                            $result[$payment]['name'] = $webpos_payment_methods_label[$payment];
                        }
                    }
                    foreach ($webpos_payment_methods as $method_code) {
                        $db_field_name = ($method_code == 'cashforpos') ? 'webpos_base_cash' : 'webpos_base_' . $method_code;
                        $amountPaid = $order->getData($db_field_name);
                        if ($method_code == 'cashforpos') {
                            $amountChange = $order->getData('webpos_base_change');
                            $amountPaid = $amountPaid - $amountChange;
                        }
                        if (isset($amountPaid) && $amountPaid > 0) {
                            $result['data'][$method_code]['name'] = $webpos_payment_methods_label[$method_code];
                            if (in_array($method_code, $payment_arr)) {
                                $result['data'][$method_code]['sales'] += floatval($amountPaid);
                                $result['data'][$method_code]['total_orders'] += 1;
                            } else {
                                $result['data'][$method_code]['sales'] = floatval($amountPaid);
                                $result['data'][$method_code]['total_orders'] = 1;
                            }
                            array_push($payment_arr, $method_code);
                        } elseif ($payment == 'cashforpos' && $method_code == 'cashforpos') {
                            if (in_array($payment, $payment_arr)) {
                                $result['data'][$payment]['sales'] += floatval($order->getData('base_grand_total'));
                                $result['data'][$payment]['total_orders'] += 1;
                            } else {
                                $result['data'][$payment]['sales'] = floatval($order->getData('base_grand_total'));
                                $result['data'][$payment]['total_orders'] = 1;
                            }
                            array_push($payment_arr, 'cashforpos');
                        }
                    }
                } else {
                    $cashforpos = $order->getData('webpos_base_cash');
                    if (isset($cashforpos) && $cashforpos > 0) {
                        if (isset($cashforpos) && $cashforpos > 0) {
                            if (in_array('cashforpos', $payment_arr)) {
                                $result['data']['cashforpos']['sales'] += floatval($cashforpos);
                                $result['data']['cashforpos']['total_orders'] += 1;
                            } else {
                                $result['data']['cashforpos']['sales'] = floatval($cashforpos);
                                $result['data']['cashforpos']['total_orders'] = 1;
                            }
                            array_push($payment_arr, 'cashforpos');
                        }
                        $result['data']['other_payment']['name'] = Mage::helper('webpos')->__('Other Payments');
                        $result['data']['other_payment']['sales'] = floatval($order->getData('base_grand_total')) - floatval($cashforpos);
                        $result['data']['other_payment']['total_orders'] = 1;
                    } else {
                        $result['data']['other_payment']['name'] = Mage::helper('webpos')->__('Other Payments');
                        $result['data']['other_payment']['sales'] = floatval($order->getData('base_grand_total'));
                        $result['data']['other_payment']['total_orders'] = 1;
                    }
                }
                array_push($payment_arr, $payment);
            }
        }
        return $result;
    }

    public function salesGeneralReport($collection){
        $result = array();
        $result['type'] = 'general';
        $result['discount'] = $result['tax'] = 0;
        if(count($collection) > 0){
            $payment_arr = array();
            $webpos_payment_methods = array('cashforpos', 'ccforpos', 'cp1forpos', 'cp2forpos', 'codforpos');
            $webpos_payment_methods_label = array(
                'cashforpos' => Mage::helper('webpos/payment')->getCashMethodTitle(),
                'ccforpos' => Mage::helper('webpos/payment')->getCcMethodTitle(),
                'cp1forpos' => Mage::helper('webpos/payment')->getCp1MethodTitle(),
                'cp2forpos' => Mage::helper('webpos/payment')->getCp2MethodTitle(),
                'codforpos' => Mage::helper('webpos/payment')->getCodMethodTitle()
            );
            foreach ($collection as $order) {
                $result['discount'] += floatval($order->getData('base_discount_amount'));
                $result['tax'] += floatval($order->getData('base_tax_amount'));
                
                $id = $order->getData('webpos_admin_id');
                $user = Mage::getModel('webpos/user')->load($id);
                if($user->getId()){
                    if(!isset($result['data'][$id])){
                        $result['data'][$id]['name'] = $user->getData('display_name');
                    }
                }else{
                    $id = "unknown";
                    $result['data']['unknown']['name'] = $this->__('Unknown');
                }
                $payment = $order->getPayment()->getMethod();
                if (in_array($payment, $webpos_payment_methods)  || $payment == 'authorizenet' || $payment == 'paypal_direct' || $payment == 'multipaymentforpos') {
                    if ($payment == 'paypal_direct' || $payment == 'authorizenet') {
                        switch($payment){
                            case 'paypal_direct':
                                $result['data'][$id]['sales'][$payment]['name'] = 'Paypal';
                                break;
                            case 'authorizenet':
                                $result['data'][$id]['sales'][$payment]['name'] = 'Authorize.net';
                                break;
                        }
                       
                        if (isset($result['data'][$id]['sales'][$payment]['sales'])) {
                            $result['data'][$id]['sales'][$payment]['sales'] += floatval($order->getData('base_grand_total'));
                        } else {
                            $result['data'][$id]['sales'][$payment]['sales'] = floatval($order->getData('base_grand_total'));
                        }
                        if (isset($result['data'][$id]['sales'][$payment]['total_orders'])) {
                            $result['data'][$id]['sales'][$payment]['total_orders'] ++;
                        } else {
                            $result['data'][$id]['sales'][$payment]['total_orders'] = 1;
                        }
                    } else {
                        if ($payment != 'multipaymentforpos') {
                            $result['data'][$id]['sales'][$payment]['name'] = $webpos_payment_methods_label[$payment];
                        }
                    }
                    foreach ($webpos_payment_methods as $method_code) {
                        $db_field_name = ($method_code == 'cashforpos') ? 'webpos_base_cash' : 'webpos_base_' . $method_code;
                        $amountPaid = $order->getData($db_field_name);
                        if ($method_code == 'cashforpos') {
                            $amountChange = $order->getData('webpos_base_change');
                            $amountPaid = $amountPaid - $amountChange;
                        }
                        if (isset($amountPaid) && $amountPaid > 0) {
                            $result['data'][$id]['sales'][$method_code]['name'] = $webpos_payment_methods_label[$method_code];
                            if (in_array($method_code, $payment_arr)) {
                                $result['data'][$id]['sales'][$method_code]['sales'] += floatval($amountPaid);
                                $result['data'][$id]['sales'][$method_code]['total_orders'] += 1;
                            } else {
                                $result['data'][$id]['sales'][$method_code]['sales'] = floatval($amountPaid);
                                $result['data'][$id]['sales'][$method_code]['total_orders'] = 1;
                            }
                            array_push($payment_arr, $method_code);
                        } elseif ($payment == 'cashforpos' && $method_code == 'cashforpos') {
                            if (in_array($payment, $payment_arr)) {
                                $result['data'][$id]['sales'][$payment]['sales'] += floatval($order->getData('base_grand_total'));
                                $result['data'][$id]['sales'][$payment]['total_orders'] += 1;
                            } else {
                                $result['data'][$id]['sales'][$payment]['sales'] = floatval($order->getData('base_grand_total'));
                                $result['data'][$id]['sales'][$payment]['total_orders'] = 1;
                            }
                            array_push($payment_arr, 'cashforpos');
                        }
                    }
                } else {
                    $cashforpos = $order->getData('webpos_base_cash');
                    if (isset($cashforpos) && $cashforpos > 0) {
                        if (isset($cashforpos) && $cashforpos > 0) {
                            if (in_array('cashforpos', $payment_arr)) {
                                $result['data'][$id]['sales']['cashforpos']['sales'] += floatval($cashforpos);
                                $result['data'][$id]['sales']['cashforpos']['total_orders'] += 1;
                            } else {
                                $result['data'][$id]['sales']['cashforpos']['sales'] = floatval($cashforpos);
                                $result['data'][$id]['sales']['cashforpos']['total_orders'] = 1;
                            }
                            array_push($payment_arr, 'cashforpos');
                        }
                        $result['data'][$id]['sales']['other_payment']['name'] = Mage::helper('webpos')->__('Other Payments');
                        $result['data'][$id]['sales']['other_payment']['sales'] = floatval($order->getData('base_grand_total')) - floatval($cashforpos);
                        $result['data'][$id]['sales']['other_payment']['total_orders'] = 1;
                    } else {
                        $result['data'][$id]['sales']['other_payment']['name'] = Mage::helper('webpos')->__('Other Payments');
                        $result['data'][$id]['sales']['other_payment']['sales'] = floatval($order->getData('base_grand_total'));
                        $result['data'][$id]['sales']['other_payment']['total_orders'] = 1;
                    }
                }
                array_push($payment_arr, $payment);
            }
        }
        return $result;
    }

}
