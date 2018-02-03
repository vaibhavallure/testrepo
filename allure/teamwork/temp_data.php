<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');
$logFile = "cntr_customer_prepare.log";

$page = $_GET["page"];
$size = $_GET['size'];
if(empty($page)){
    die("Please add page");
}

if(empty($size)){
    $size = 100;
}

try{
    $collection = Mage::getModel("sales/order")->getCollection();
    $collection->addFieldToFilter( 'create_order_method', array('eq'=>1));
    $collection->setCurPage($page);
    $collection->setPageSize($size);
    $collection->setOrder('entity_id', 'asc');
    $collection->getSelect()->group('customer_id');
    
    Mage::log("count = ".$collection->getSize(),Zend_log::DEBUG,$logFile,true);
    $cnt = 0;
    $resource       = Mage::getSingleton('core/resource');
    $writeAdapter   = $resource->getConnection('core_write');
    $writeAdapter->beginTransaction();
    foreach ($collection as $order){
        $customerId = $order->getCustomerId();
        $emailTemp      = $order->getCustomerEmail();
        try{
            $customer = Mage::getModel("customer/customer")->load($customerId);
            /* $model = Mage::getModel("allure_teamwork/customer")
                ->load($customerId,"customer_id");
            if(!$model->getId()){
                $email = $customer->getEmail();
                $extraInfo = unserialize($order->getCounterpointExtraInfo());
                $custNo    = $extraInfo['cust_no'];
                $model->setCustNo($custNo);
                $model->setEmail($email);
                $model->setTempEmail($emailTemp);
                $model->setCustomerId($customerId);
                if($customer->getCustomerType()){
                    $model->setIsNonMagCust(1);
                }
                $model->save();
                Mage::log($cnt ." save customer_id:".$customerId,Zend_log::DEBUG,$logFile,true);
            } */
            
            if($customer->getCustomerType()){
                $extraInfo = unserialize($order->getCounterpointExtraInfo());
                $custNo    = $extraInfo['cust_no'];
                $model = Mage::getModel("allure_teamwork/customer")
                    ->load($custNo,"cust_no");
                if(!$model->getId()){
                    $email = $customer->getEmail();
                    $model->setCustNo($custNo);
                    $model->setEmail($email);
                    $model->setTempEmail($emailTemp);
                    $model->setCustomerId($customerId);
                    $model->setIsNonMagCust(1);
                    $model->save();
                    Mage::log($cnt ." save customer_id:".$customerId,Zend_log::DEBUG,$logFile,true);
                }else{
                    $modelDupl = Mage::getModel("allure_teamwork/duplcustomer")
                        ->load($customerId,"customer_id");
                    if(!$modelDupl->getId()){
                        $email = $customer->getEmail();
                        $modelDupl->setEmail($email);
                        $modelDupl->setTempEmail($emailTemp);
                        $modelDupl->setCustomerId($customerId);
                        $modelDupl->setIsNonMagCust(1);
                        $modelDupl->setCustNo($custNo);
                        $modelDupl->save();
                    }
                }
            }else{
                $model = Mage::getModel("allure_teamwork/customer")
                ->load($customerId,"customer_id");
                if(!$model->getId()){
                    $email = $customer->getEmail();
                    $extraInfo = unserialize($order->getCounterpointExtraInfo());
                    $custNo    = $extraInfo['cust_no'];
                    $model->setCustNo($custNo);
                    $model->setEmail($email);
                    $model->setTempEmail($emailTemp);
                    $model->setCustomerId($customerId);
                    $model->save();
                    Mage::log($cnt ." save customer_id:".$customerId,Zend_log::DEBUG,$logFile,true);
                } 
            }
            
            Mage::log("customer_id:".$customerId,Zend_log::DEBUG,$logFile,true);
            $customer = null;
            $model = null;
            if (($cnt % 100) == 0) {
                $writeAdapter->commit();
                $writeAdapter->beginTransaction();
            }
        }catch (Exception $exc){
            Mage::log("customer_id:".$customerId." Exc:".$exc->getMessage(),Zend_log::DEBUG,$logFile,true);
        }
        $cnt++;
    }
   $writeAdapter->commit();
}catch (Exception $e){
    Mage::log("Exception:".$e->getMessage(),Zend_log::DEBUG,$logFile,true);
}
Mage::log("Finish...",Zend_log::DEBUG,$logFile,true);
