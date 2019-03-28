<?php


use Braintree\Exception\NotFound;

class Allure_Inventory_Adminhtml_Inventory_PurchaseController extends Allure_Inventory_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
            ->_setActiveMenu($this->_menu_path)
            ->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Manage Stock'), Mage::helper('adminhtml')->__('Manage Stock')
            );
        return $this;
    }

    public function indexAction() {
        $this->_initAction();
        $this->_title($this->__('Inventory'))
            ->_title($this->__('Manage Stock'));

        $this->renderLayout();
    }
    public function confirmAction() {
        $this->_initAction();
        $this->_title($this->__('Inventory'))
        ->_title($this->__('Manage Stock'));
        
        $this->renderLayout();
    }


    public function saveAction() {
        $admin = Mage::getSingleton('admin/session')->getUser();
        $data = $this->getRequest()->getPost();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        foreach ($data['qty'] as $product => $key) {
            $arr = array_filter($data['qty'][$product]);
            if (! empty($arr)) {
                foreach ($arr as $stockId => $qty) {
                    $updateStock = Mage::getModel('cataloginventory/stock_item')->loadByProductAndStock($product, $stockId);
                    if (! is_null($updateStock->getItemId()) && ($updateStock->getItemId() != 0)) {
                        $previousQty = $updateStock->getQty();
                        $newQty = $updateStock->getQty() + $qty;
                        $resource = Mage::getSingleton('core/resource');
                        $writeAdapter = $resource->getConnection('core_write');
                        $table = $resource->getTableName('cataloginventory/stock_item');
                        $query = "update {$table} set  qty = '{$newQty}' where product_id = '{$product}' AND stock_id = '{$stockId}'";
                        $writeAdapter->query($query);

                        $inventory = Mage::getModel('inventory/inventory');
                        $inventory->setProductId($product);
                        $inventory->setUserId($admin->getUserId());
                        $inventory->setPreviousQty($previousQty);
                        $inventory->setAddedQty($qty);
                        $inventory->setUpdatedAt(date("Y-m-d H:i:s"));
                        $inventory->setStockId($stockId);
                        $inventory->save();
                    }
                }
            }
        }
        Mage::getSingleton('adminhtml/session')->addSuccess("stock updated");
        $this->_redirect('*/*/');
    }

    public function newAction()
    {
        $this->loadLayout();
        $this->_title($this->__('Inventory'))
            ->_title($this->__('Create  Order'));
        $this->renderLayout();
    }
    public function ordersAction()
    {
        $this->loadLayout();
        $this->_title($this->__('Inventory'))
            ->_title($this->__('View Orders'));
        $this->renderLayout();
    }
    public function viewAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function createOrderAction(){
        $admin      = Mage::getSingleton('admin/session')->getUser();
        $data       = $this->getRequest()->getPost();
        $storeId    = $data['store'];
        $itemsData  = $this->getPOItemsForStore($storeId);
        $arrItemIds = array(); 
        $helper     = Mage::helper('inventory');
        $connection = Mage::getSingleton('core/resource')
                        ->getConnection('core_write');
        if (count($itemsData)){
            try {
                if ($itemsData) {
                    // Default Vendor if vendor is not assigned to Product
                    $vendor = Mage::getStoreConfig("allure_vendor/manage_vendor/vendor");
                    $items  = array();
                    foreach ($itemsData as $_item) {
                        $itemId = $_item['item_id'];
                        $arrItemIds[] = $itemId;
                        if (!$_item['is_custom']) {
                            $product = Mage::getModel('catalog/product')->load($itemId);
                            if ($product->getPrimaryVendor()){
                                $vendor = $product->getPrimaryVendor();
                            }
                            if(!empty($_item['vendor_sku'])){
                                Mage::getResourceSingleton('catalog/product_action')
                                    ->updateAttributes(array($product->getId()),
                                         array ('vendor_item_no' => $_item['vendor_sku']), 
                                        $storeId );
                            } 
                            if(!empty($_item['cost'])){
                                Mage::getResourceSingleton('catalog/product_action')
                                ->updateAttributes(array($product->getId()),
                                    array ('cost' => $_item['cost']),
                                    $storeId );
                            }
                        }
                        $items[$vendor][$itemId] = $_item;
                    }
                }
                $websiteId  = 1;
                $stockId    = 1;
                if (Mage::getSingleton('core/session')->getMyWebsiteId()){
                    $websiteId = Mage::getSingleton('core/session')->getMyWebsiteId();
                    $website   = Mage::getModel("core/website")->load($websiteId);
                    $stockId   = $website->getStockId();
                }

                $date = new Zend_Date(Mage::getModel('core/date')->timestamp());
                $date->addDay('7');
                $date->toString('Y-m-d H:i:s');
                $message            = "";
                $orderItems         = "";
                $notOrderItems      = "";
                if (isset($items)) {
                    foreach ($items as $key => $itemArray) {
                        $vendorId     = $key;
                        $vendorName   = Mage::helper('allure_vendor')->getVanderName($vendorId);
                        $vendorEmail  = Mage::helper('allure_vendor')->getVanderEmail($vendorId);
                        if (isset($vendorName) && ! empty($vendorName)) {
                            $totalAmount = 0;
                            $po_id       = null;
                            foreach ($itemArray as $item) {
                                $totalAmount += $item['qty'] * $item['cost'];
                            }
                            // Create order
                            if ($stockId == 2)
                                $orderStatus = Allure_Inventory_Helper_Data::ORDER_STATUS_DRAFT;
                            else
                                $orderStatus = Allure_Inventory_Helper_Data::ORDER_STATUS_NEW;

                            $model     = Mage::getModel('inventory/purchaseorder');
                            $orderData = array(
                                'ref_no'        => $data['refence_no'],
                                'vendor_id'     => $vendorId,
                                'created_date'  => date("Y-m-d H:i:s"),
                                'updated_date'  => date("Y-m-d H:i:s"),
                                'vendor_name'   => $vendorName,
                                'status'        => $orderStatus,
                                'total_amount'  => $totalAmount,
                                'stock_id'      => $stockId
                            );
                            $model->setData($orderData);
                            $po_id = $model->save()->getId();
                            $model = null;

                            $modelArr = array();
                            foreach ($itemArray as $item) {
                                // Map Order items With Order
                                // Insert entry in allure_purchase_order_item
                                $model     = Mage::getModel('inventory/orderitems');
                                $dataItems = array(
                                    'po_id'         => $po_id,
                                    'ref_no'        => $data['refence_no'],
                                    'product_id'    => $item['item_id'],
                                    'requested_qty' => $item['qty'],
                                    'remaining_qty' => $item['qty'],
                                    'proposed_qty'  => 0,
                                    'status'        => 'new',
                                    'requested_delivery_date' => $date,
                                    'is_custom'     => $item['is_custom'],
                                    'admin_comment' => $item['comment'],
                                    'total_amount'  => $item['qty'] * $item['cost'],
                                    'stock_id'      => $stockId,
                                    'vendor_sku'    =>$item['vendor_sku']
                                    
                                );
                                $modelArr[] = $dataItems;
                                $model = null;
                            }

                            //insert multiple record at one time
                            $connection->insertMultiple("allure_purchase_order_item",$modelArr);
                            
                            // Purchase order logs just for extra information
                            // insert entry in allure_purchase_order_log
                            $model = Mage::getModel('inventory/orderlogs');
                            $logData = array(
                                'po_id' => $po_id,
                                'vendor_id' => $vendorId,
                                'user_id' => $admin->getUserId(),
                                'date' => date("Y-m-d H:i:s"),
                                'total_amount' => $totalAmount,
                                'stock_id' => $stockId,
                                'action'=>'create'
                                
                            );
                            $model->setData($logData);
                            $model->save()->getId();
                            $model = null;
                            if ($stockId != 2) {
                                try {
                                    $templateId=Mage::getStoreConfig('allure_vendor/general/purchase_order_create',$storeId);
                                    $adminEmail=Mage::getStoreConfig('allure_vendor/general/admin_email',$storeId);
                                    if (!empty($adminEmail)) {
                                        $adminEmail =  explode(',', $adminEmail);
                                    }
                                    //sendEmail($po_id, $vendorEmail,$templateId,$templateId)
                                    $helper->sendEmail($po_id, $vendorEmail, $templateId, $adminEmail,true);
                                } catch (Exception $e) {}
                            } // Send email to vendor directly except LOndon stroe
                            else {
                                try {
                                    //Send notification to Admin
                                    $templateId=Mage::getStoreConfig('allure_vendor/general/purchase_order_create',$storeId);
                                    $adminEmail=Mage::getStoreConfig('allure_vendor/general/admin_email',$storeId);
                                    //sendEmail($po_id, $vendorEmail,$templateId,$templateId)
                                    if (!empty($adminEmail)) {
                                        $adminEmail =  explode(',', $adminEmail);
                                    }
                                    $helper->sendEmail($po_id, '', $templateId, $adminEmail,true);
                                } catch (Exception $e) {}
                            } //ENd of else
                        }
                    }
                    $message = "Purchase order created successfully.";
                    $strItemIds = implode(",", $arrItemIds);
                    $sqlStr     = "DELETE FROM allure_inventory_purchase_tmp WHERE ITEM_ID IN (".$strItemIds.")";
                    $connection->query($sqlStr);
                    Mage::getSingleton('adminhtml/session')->addSuccess($message);
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }else {
            Mage::getSingleton('adminhtml/session')->addError("Please add items to order first");
        }
        $jsonData = json_encode(compact('success', 'message', 'data'));
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }
    public function saveOrderAction() {
        $admin = Mage::getSingleton('admin/session')->getUser();
        $data = $this->getRequest()->getPost();
        $remove = Mage::app()->getRequest()->getParam('remove');
        echo "<pre>";
        $resource = Mage::getSingleton('core/resource');
        $writeAdapter = $resource->getConnection('core_write');
      
        $po_id = $data['order_id'];
        $helper = Mage::helper('inventory');
        $deleteArray=array();
        if($remove){
            foreach ($data['order'] as $product => $key) {
                if(isset($key['include']) && $key['include']=='on'){
                    $deleteArray[]=$product;
                    unset($data['order'][$product]);
                }
            }
            if(!empty($deleteArray)){
                $productids=implode(',', $deleteArray);
                $table = $resource->getTableName('inventory/orderitems');
                $query = "delete from {$table} where product_id IN ({$productids}) AND po_id = {$po_id}";
                $writeAdapter->query($query);
            }
        }
       
        // get aditional paramters
        try {
            $ship   = Mage::app()->getRequest()->getParam('ship');
            $close  = Mage::app()->getRequest()->getParam('close');
            $canFullyShipOrder = 1;
            $diffArray=array();
            $totalPrice=0;
            foreach ($data['order'] as $product => $key) {
                $arr = array_filter($data['order'][$product]);
                if (isset($arr) && $arr) {
                    $date = "";
                    if (isset($arr['proposed_delivery_date']) && $arr['proposed_delivery_date']){
                        $date = date('F j, Y', strtotime($arr['proposed_delivery_date']));
                    }
                    Mage::log($date,Zend_log::DEBUG, 'pologs', true);
                    /* $dataItems = array(
                        'po_id'                  => $po_id,
                        'product_id'             => $product,
                        'requested_qty'          => $arr['requested_qty'],
                        'proposed_qty'           => $arr['proposed_qty'],
                        'status'                 => 'new',
                        'proposed_delivery_date' => $date,
                        'admin_comment'          => $arr['admin_comment'],
                        'vendor_comment'         => $arr['vendor_comment']
                    ); */
                    $poObj = Mage::getModel('inventory/purchaseorder')->load($po_id);
                    $storeId = $poObj->getStockId();

                    // Tring to get only one first item and updating it
                    $items = Mage::getModel('inventory/orderitems')->getCollection()
                                ->addFieldToFilter('product_id', $product)
                                ->addFieldToFilter('po_id', $po_id);
                    foreach ($items as $item) {
                        if(!$item->getIsCustom()){
                            if($arr['vendor_sku']!=$item->getVendorSku()){
                                Mage::getResourceSingleton('catalog/product_action')
                                ->updateAttributes(array($item->getProductId()), array(
                                    'vendor_item_no' => $arr['vendor_sku']
                                ), $storeId);
                                $item->setData('vendor_sku', $arr['vendor_sku']);
                            }
                        }
                        
                        $price=Mage::getModel('catalog/product')->setStoreId($storeId)->load($item->getProductId())->getCost();
                        $totalPrice=$totalPrice+($arr['requested_qty']*$price);
                        
                        if(trim($item->getAdminComment())!=trim($arr['admin_comment'])){
                            $diffArray[$item->getProductId()]['admin_comment']=$arr['admin_comment'];
                            $diffArray[$item->getProductId()]['admin_comment_old']=$item->getAdminComment();
                            $diffArray[$item->getProductId()]['is_custom']=$item->getIsCustom();
                        }
                       
                        if(trim($item->getRequestedQty()) != trim($arr['requested_qty'])){
                            $diffArray[$item->getProductId()]['qty'] = $arr['requested_qty'];
                            $diffArray[$item->getProductId()]['qty_old'] = $item->getRequestedQty();
                            $diffArray[$item->getProductId()]['is_custom'] = $item->getIsCustom();
                            
                           
                           /*  $oldTotal=$item->getTotalAmount();
                            $newTotal=$arr['requested_qty']*$price;
                            if($oldTotal >$newTotal){
                                $final=$oldTotal-$newTotal;
                                $poObj->setTotalAmount($poObj->getTotalAmount()-$final);
                            }
                            else {
                                $final=$newTotal-$oldTotal;
                                $poObj->setTotalAmount($poObj->getTotalAmount()+$final);
                            } */
                            
                            
                            $item->setData('total_amount',($arr['requested_qty']*$price));
                             
                            //$poObj->save();
                             //Incase req qty change in draft state update proposed qty and remaining qty
                             $arr['proposed_qty'] = $arr['requested_qty'];
                             $item->setData('proposed_qty', $arr['requested_qty']);
                             $item->setData('remaining_qty', $arr['requested_qty']);
                        }
                        if(trim($item->getVendorComment()) != trim($arr['vendor_comment'])){
                            $diffArray[$item->getProductId()]['vendor_comment'] = $arr['vendor_comment'];
                            $diffArray[$item->getProductId()]['vendor_comment_old'] = $item->getVendorComment();
                            $diffArray[$item->getProductId()]['is_custom'] = $item->getIsCustom();
                        }
                        if(isset($arr['proposed_delivery_date'])){
                            if(date('m/d/Y',strtotime($item->getProposedDeliveryDate())) != $arr['proposed_delivery_date']){
                                $diffArray[$item->getProductId()]['proposed_delivery_date'] = $date;
                                $diffArray[$item->getProductId()]['proposed_delivery_date_old'] = ($item->getProposedDeliveryDate())?date('F j, Y', strtotime($item->getProposedDeliveryDate())):'-';
                                $diffArray[$item->getProductId()]['is_custom'] = $item->getIsCustom();
                            }
                        }
                        if ($date)
                            $item->setData('proposed_delivery_date', $date);
                        $item->setData('admin_comment', $arr['admin_comment']);
                        $item->setData('vendor_comment', $arr['vendor_comment']);
                        $item->setData('requested_qty', $arr['requested_qty']);
                            
                        if ($close || $ship)
                            $item->setData('proposed_qty', $arr['proposed_qty']);
                        $item->save();
                    }

                    if ($date) {
                        $days = "7";
                        if (Mage::getStoreConfig('allure_vendor/backorder/backorder_time'))
                            $days = Mage::getStoreConfig('allure_vendor/backorder/backorder_time');
                        
                       /*      update Back-ordered time update to child items */
                            
                      if (!$item->getIsCustom()){
                        $_product = Mage::getModel('catalog/product')->load($product);
                        $parentSKu = '';
                        $subString = '';
                        $skuString = explode('|', $_product->getSku());
                        $parentSKu = (string)$skuString[0];
                        unset($skuString[0]);
                        $subString = implode('|',$skuString);
                        
                        $childrenProducts = Mage::getModel('catalog/product')->getCollection();
                        $childrenProducts->addAttributeToFilter( array(array('attribute'=> 'parent_item_number','like' => $parentSKu)));
                        $childProductArr = array();
                        $childProductArr[] = $product;
                        foreach ($childrenProducts as $child){
                            $child=Mage::getModel('catalog/product')->load($child->getId());
                            $childSku=explode('|', $child->getSku());
                            if(!isset($subString) && count($childSku)==1){
                                $childProductArr[] = $child->getId();
                                continue;
                            }
                            unset($childSku[0]);
                            if($subString==implode('|',$childSku))
                            {
                                $childProductArr[] = $child->getId();
                                continue;
                            }
                            
                            if($child->getTypeId()=="configurable"){
                                $currentchildrenIds = $child->getTypeInstance()->getChildrenIds($child->getId());
                                foreach ($currentchildrenIds[0] as $childrenId) {
                                    $sbuChild=Mage::getModel('catalog/product')->load($childrenId);
                                    $subChildSku=explode('|', $sbuChild->getSku());
                                    unset($subChildSku[0]);
                                    if($subString==implode('|',$subChildSku))
                                        $childProductArr[] = $childrenId;
                                   
                                }
                                
                            }
                            
                        }
                        $childProductArr=array_unique($childProductArr);
                        
                        Mage::log("Backorder time set for:",Zend_log::DEBUG,'po_backorderdate.log',true);
                        Mage::log($childProductArr,Zend_log::DEBUG,'po_backorderdate.log',true);
                        $backDate = date_create($date);
                        date_add($backDate, date_interval_create_from_date_string($days . " days"));
                        
                        $backDate = date_format($backDate, "F j, Y");

                        if (!$item->getIsCustom()) {
                            Mage::getResourceSingleton('catalog/product_action')
                                ->updateAttributes($childProductArr, array(
                                    'backorder_time' => $backDate
                                    ), $storeId);
                        }
                      }
                      
                    }
                    
                    $qtyRemaining=$arr['remaining_qty']-$arr['proposed_qty'];
                    
                    Mage::log("Qty Remain:".$qtyRemaining,Zend_log::DEBUG,"mylogs",true);
                    if ($qtyRemaining > 0 )
                        $canFullyShipOrder =0;
                    Mage::log("close:".$canFullyShipOrder,Zend_log::DEBUG,"mylogs",true);
                }
            }
            if ($close && $canFullyShipOrder)
                $status = Allure_Inventory_Helper_Data::ORDER_STATUS_FULLY_SHIPPED;
            if (($ship || $close) && $canFullyShipOrder==0)
                $status = Allure_Inventory_Helper_Data::ORDER_STATUS_PARTIALLY_SHIPPED;
            
            $currentDate = new Zend_Date(Mage::getModel('core/date')->timestamp());
            $currentDate->toString('jS F, Y');
            $order = Mage::getModel('inventory/purchaseorder')->load($po_id);
            $stockId=$order->getStockId();
            $vendorId=$order->getVendorId();
            $orderlogsmodel = Mage::getModel('inventory/orderlogs');
            $logData = array(
                'po_id' => $po_id,
                'vendor_id' => $vendorId,
                'user_id' => $admin->getUserId(),
                'date' => date("Y-m-d H:i:s"),
                'stock_id' => $stockId
            );
            $orderlogsmodel->setData($logData);
            $orderlogsmodel->save()->getId();
            
            //update order total only for Draftstate
            if($order->getTotalAmount()!=$totalPrice && $order->getStatus()==Allure_Inventory_Helper_Data::ORDER_STATUS_DRAFT)
                $order->setTotalAmount($totalPrice);
                
            if (($close || $ship) && isset($status))
                $order->setData('status', $status);

            $order->setData('updated_date', $currentDate)->save();
//            $vendorEmail = Mage::helper('allure_vendor')->getVanderEmail($order->getVendorId());

            if ($close && $canFullyShipOrder){
                //fully Shipped
                $templateId=Mage::getStoreConfig('allure_vendor/general/purchase_order_shipment',$storeId);
                $adminEmail=Mage::getStoreConfig('allure_vendor/general/admin_email',$storeId);
                if (!empty($adminEmail)) {
                    $adminEmail =  explode(',', $adminEmail);
                }
                $vendorEmail = Mage::helper('allure_vendor')->getVanderEmail($order->getVendorId());
                $helper->sendEmail($po_id,$vendorEmail,$templateId,$adminEmail,true,$diffArray);

                Mage::getSingleton('adminhtml/session')->addSuccess("Order shipped fully.");

            }
            elseif ($close){
                //Partially Ship

                $templateId=Mage::getStoreConfig('allure_vendor/general/purchase_order_shipment',$storeId);
                $adminEmail=Mage::getStoreConfig('allure_vendor/general/admin_email',$storeId);
                if (!empty($adminEmail)) {
                    $adminEmail =  explode(',', $adminEmail);
                }
                $vendorEmail = Mage::helper('allure_vendor')->getVanderEmail($order->getVendorId());
                Mage::log("vendorEmail:".$templateId,Zend_log::DEBUG,"mylogs",true);
                $helper->sendEmail($po_id,$vendorEmail,$templateId,$adminEmail,true,$diffArray);
                Mage::getSingleton('adminhtml/session')->addSuccess("Order shipped partially, as some of items remaining to ship.");
            }
            elseif ($ship){
                //Partially Ship

                $templateId=Mage::getStoreConfig('allure_vendor/general/purchase_order_shipment',$storeId);
                $adminEmail=Mage::getStoreConfig('allure_vendor/general/admin_email',$storeId);
                if (!empty($adminEmail)) {
                    $adminEmail =  explode(',', $adminEmail);
                }
                $vendorEmail = Mage::helper('allure_vendor')->getVanderEmail($order->getVendorId());
                Mage::log("vendorEmail:".$vendorEmail,Zend_log::DEBUG,"mylogs",true);
                $helper->sendEmail($po_id,$vendorEmail,$templateId,$adminEmail,true,$diffArray);
                Mage::getSingleton('adminhtml/session')->addSuccess("Order Shipped partially.");
            }
            else{
                //Order Save
                $templateId=Mage::getStoreConfig('allure_vendor/general/purchase_order_comment',$storeId);
                $adminEmail=Mage::getStoreConfig('allure_vendor/general/admin_email',$storeId);
                $vendorEmail='';
                if($order->getStatus()!=Allure_Inventory_Helper_Data::ORDER_STATUS_DRAFT){
                   
                   $vendorEmail = Mage::helper('allure_vendor')->getVanderEmail($order->getVendorId());
                   if (!empty($adminEmail)) {
                         $adminEmail =  explode(',', $adminEmail);
                   }
                   $helper->sendEmail($po_id,$vendorEmail,$templateId,$adminEmail,true,$diffArray);
                }
                Mage::getSingleton('adminhtml/session')->addSuccess("Order saved sucessfully.");
            }
        } catch (Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e);
        }
        $this->_redirect('*/*/orders');
    }
    
    public function receivelistAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    public function viewreceiveAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    public function updatereciveAction(){

        $admin = Mage::getSingleton('admin/session')->getUser();
        $data  = $this->getRequest()->getPost();
        $po_id = $data['order_id'];
        $currentOrder = Mage::getModel('inventory/purchaseorder')->load($po_id);
        $storeId = $currentOrder->getStockId();
        $helper = Mage::helper('inventory');
        $diffArray = array();
        
        $void = Mage::app()->getRequest()->getParam('void');
        $close = Mage::app()->getRequest()->getParam('close');
        try {
            foreach ($data['order'] as $product => $key) {
                $arr = array_filter($data['order'][$product]);
                $arr['proposed_qty']=$arr['proposed_qty']?$arr['proposed_qty']:0;
               
                $tempProduct = $items = Mage::getModel('inventory/orderitems')->getCollection()
                    ->addFieldToFilter('product_id', $product)
                    ->addFieldToFilter('po_id', $po_id)
                    ->getFirstItem();
                
                if (! $tempProduct['is_custom'] &&   $arr['proposed_qty'] > 0) {
                    $updateStock = Mage::getModel('cataloginventory/stock_item')
                            ->loadByProductAndStock($product, $currentOrder->getStockId());
                   $cost=Mage::getModel('catalog/product')->setStoreId($currentOrder->getStockId())->load($product)->getCost();
                    
                    if (! empty($arr) && ! $void) {
                        if (! is_null($updateStock->getItemId()) && ($updateStock->getItemId() != 0)) {

                            // Tring to get only one first item and updating it

                           /*  $items = Mage::getModel('inventory/orderitems')->getCollection()
                                ->addFieldToFilter('product_id', $product)
                                ->addFieldToFilter('po_id', $po_id);
                            
                            foreach ($items as $item) { */
                                
                            if(trim($tempProduct['admin_comment']) != trim($arr['admin_comment'])){
                                    $diffArray[$product]['admin_comment'] = $arr['admin_comment'];
                                    $diffArray[$product]['admin_comment_old'] = $tempProduct['admin_comment'];
                                    $diffArray[$product]['is_custom'] = $tempProduct['is_custom'];
                                }
                                if(trim($tempProduct['vendor_comment']) != trim($arr['vendor_comment'])){
                                    $diffArray[$product]['vendor_comment'] = $arr['vendor_comment'];
                                    $diffArray[$product]['vendor_comment_old'] = $tempProduct['vendor_comment'];
                                    $diffArray[$product]['is_custom'] = $tempProduct['is_custom'];
                                }
                                if(isset($arr['proposed_delivery_date'])){
                                    if(date('m/d/Y',strtotime($tempProduct['proposed_delivery_date'])) != $arr['proposed_delivery_date']){
                                        $diffArray[$product]['proposed_delivery_date'] = $date;
                                        $diffArray[$product]['proposed_delivery_date_old'] = ($tempProduct['proposed_delivery_date'])?date('F j, Y', strtotime($tempProduct['proposed_delivery_date'])):'-';
                                        $diffArray[$product]['is_custom'] = $tempProduct['is_custom'];
                                    }
                                }
                               /*  $remainingQty = $tempProduct['remaining_qty'] - $arr['proposed_qty'];
                                $tempProduct['remaining_qty'] = $remainingQty; */
                                $tempProduct['admin_comment'] = $arr['admin_comment'];
                                $tempProduct['vendor_comment'] = $arr['vendor_comment'];
                                $tempProduct['requested_qty'] = $arr['requested_qty'];
                                $tempProduct['proposed_qty'] = $arr['proposed_qty'];
                                
                                /* $item->setData('remaining_qty', $remainingQty);
                                $item->setData('admin_comment', $arr['admin_comment']);
                                $item->setData('vendor_comment', $arr['vendor_comment']);
                                $item->setData('requested_qty', $arr['requested_qty']);
                                $item->setData('proposed_qty', $arr['proposed_qty'])->save(); */
                                $tempProduct->save();
                            //}

                            // Receive stock
                            $previousQty = $updateStock->getQty();
                            $newQty = $updateStock->getQty() + $arr['proposed_qty'];
                            if ($close && isset($close))
                                $updateStock->setData('po_sent', 0); // Reset flag on order close
                            $updateStock->setData('qty', $newQty)->save();

                            if($arr['proposed_qty'] > 0){
                                $inventory = Mage::getModel('inventory/inventory');
                                $inventory->setProductId($product);
                                $inventory->setUserId($admin->getUserId());
                                $inventory->setPreviousQty($previousQty);
                                $inventory->setAddedQty($arr['proposed_qty']);
                                $inventory->setCost($cost);
                                $inventory->setUpdatedAt(date("jS F, Y"));
                                $inventory->setStockId($currentOrder->getStockId());
                                $inventory->setPoId($po_id);
                                $inventory->save();
                            }
                        }
                    }
                } else {

                    if(trim($tempProduct['admin_comment']) != trim($arr['admin_comment'])){
                        $diffArray[$product]['admin_comment'] = $arr['admin_comment'];
                        $diffArray[$product]['admin_comment_old'] = $tempProduct['admin_comment'];
                        $diffArray[$product]['is_custom'] = $tempProduct['is_custom'];
                    }
                    if(trim($tempProduct['vendor_comment']) != trim($arr['vendor_comment'])){
                        $diffArray[$product]['vendor_comment'] = $arr['vendor_comment'];
                        $diffArray[$product]['vendor_comment_old'] = $tempProduct['vendor_comment'];
                        $diffArray[$product]['is_custom'] = $tempProduct['is_custom'];
                    }
                    if(isset($arr['proposed_delivery_date'])){
                        if(date('m/d/Y',strtotime($tempProduct['proposed_delivery_date'])) != $arr['proposed_delivery_date']){
                            $diffArray[$product]['proposed_delivery_date'] = $date;
                            $diffArray[$product]['proposed_delivery_date_old'] = ($tempProduct['proposed_delivery_date'])?date('F j, Y', strtotime($tempProduct['proposed_delivery_date'])):'-';
                            $diffArray[$product]['is_custom'] = $tempProduct['is_custom'];
                        }
                    }
                   /*  $remainingQty = $tempProduct['remaining_qty'] - $arr['proposed_qty'];
                    $tempProduct['remaining_qty'] = $remainingQty; */
                    $tempProduct['admin_comment'] = $arr['admin_comment'];
                    $tempProduct['vendor_comment'] = $arr['vendor_comment'];
                    $tempProduct['requested_qty'] = $arr['requested_qty'];
                    $tempProduct['proposed_qty'] = $arr['proposed_qty'];
                    
                    /* $item->setData('remaining_qty', $remainingQty);
                     $item->setData('admin_comment', $arr['admin_comment']);
                     $item->setData('vendor_comment', $arr['vendor_comment']);
                     $item->setData('requested_qty', $arr['requested_qty']);
                     $item->setData('proposed_qty', $arr['proposed_qty'])->save(); */
                    $tempProduct->save();
                    }

                    // Receive stock

                    /*
                     * $inventory=Mage::getModel('inventory/inventory');
                     * $inventory->setProductId($product);
                     * $inventory->setUserId($admin->getUserId());
                     * $inventory->setPreviousQty($previousQty);
                     * $inventory->setAddedQty($arr['proposed_qty']);
                     * $inventory->setUpdatedAt(date("jS F, Y"));
                     * $inventory->setStockId($currentOrder->getStockId());
                     * $inventory->setPoId($po_id);
                     * $inventory->save();
                     */
               // } // End Of else
            } // End of foreach

           
            $order = Mage::getModel('inventory/purchaseorder')->load($po_id);
            $vendorId=$order->getVendorId();
            $stockId=$order->getStockId();
            $canFullyShipOrder=1;
            $itemCollection = Mage::getModel('inventory/orderitems')->getCollection()
            ->addFieldToFilter('po_id', $po_id);
            foreach ($itemCollection as $tempItem){
                if($tempItem->getRemainingQty() > 0){
                    $canFullyShipOrder=0;
                    break;
                }
            }
            $status = Allure_Inventory_Helper_Data::ORDER_STATUS_PARTIALLY_CLOSED;
            if ($close && $canFullyShipOrder)
                $status = Allure_Inventory_Helper_Data::ORDER_STATUS_CLOSED;
            if ($void)
                $status = Allure_Inventory_Helper_Data::ORDER_STATUS_REJECT;
            
            if ($close && $canFullyShipOrder){
                    //Fully ship and closed
                    
                    $templateId=Mage::getStoreConfig('allure_vendor/general/purchase_order_close',$storeId);
                    $adminEmail=Mage::getStoreConfig('allure_vendor/general/admin_email',$storeId);
                    if (!empty($adminEmail)) {
                        $adminEmail =  explode(',', $adminEmail);
                    }
                    $vendorEmail = Mage::helper('allure_vendor')->getVanderEmail($order->getVendorId());
                    Mage::log("vendorEmail:".$vendorEmail,Zend_log::DEBUG,"mylogs",true);
                    $helper->sendEmail($po_id,$vendorEmail,$templateId,$adminEmail,true,$diffArray);
             }else {
                 //Partially ship and closed
                 if(!$void){
                   $templateId=Mage::getStoreConfig('allure_vendor/general/purchase_order_receive',$storeId);
                   $adminEmail=Mage::getStoreConfig('allure_vendor/general/admin_email',$storeId);
                   if (!empty($adminEmail)) {
                       $adminEmail =  explode(',', $adminEmail);
                   }
                   $vendorEmail = Mage::helper('allure_vendor')->getVanderEmail($order->getVendorId());
                   Mage::log("vendorEmail:".$vendorEmail,Zend_log::DEBUG,"mylogs",true);
                   $helper->sendEmail($po_id,$vendorEmail,$templateId,$adminEmail,true,$diffArray);
               }
             }
             
             $orderlogsmodel = Mage::getModel('inventory/orderlogs');
             $logData = array(
                 'po_id' => $po_id,
                 'vendor_id' => $vendorId,
                 'user_id' => $admin->getUserId(),
                 'date' => date("Y-m-d H:i:s"),
                 'stock_id' => $stockId
             );
             $orderlogsmodel->setData($logData);
             $orderlogsmodel->save()->getId();
             
            $currentDate = new Zend_Date(Mage::getModel('core/date')->timestamp());
            $currentDate->toString('jS F, Y');
         
            if (isset($status))
                $order->setData('status', $status);
            $order->setData('updated_date', $currentDate)->save();

            Mage::getSingleton('adminhtml/session')->addSuccess("Order Received");

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/receivelist');
    }
    public function exportDownloadsCsvAction(){
        $fileName   = 'orders.csv';
        $content    = $this->getLayout()->createBlock('inventory/adminhtml_purchaseorder_grid')
            ->setSaveParametersInSession(true)
            ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }
    public function exportDownloadsExcelAction(){
        $fileName   = 'orders.xlsx';
        $content    = $this->getLayout()->createBlock('inventory/adminhtml_purchaseorder_grid')
            ->setSaveParametersInSession(true)
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }
    public function acceptAction(){
        $admin = Mage::getSingleton('admin/session')->getUser();
        $id=Mage::app()->getRequest()->getParam('id');
        $helper = Mage::helper('inventory');
        try {
            if($id && isset($id))
            {
                $currentDate = new Zend_Date(Mage::getModel('core/date')->timestamp());
                $currentDate->toString('jS F, Y');
                $status = Allure_Inventory_Helper_Data::ORDER_STATUS_ACCEPT;
                $order = Mage::getModel('inventory/purchaseorder')->load($id);
                $storeId=$order->getStoreId();
                if (isset($status))
                    $order->setData('status', $status);
                $order->setData('updated_date', $currentDate)->save();
                $vendorId=$order->getVendorId();
                $stockId=$order->getStockId();
                $orderlogsmodel = Mage::getModel('inventory/orderlogs');
                $logData = array(
                    'po_id' => $order->getPoId(),
                    'vendor_id' => $vendorId,
                    'user_id' => $admin->getUserId(),
                    'date' => date("Y-m-d H:i:s"),
                    'acton' => 'accept',
                    'stock_id' => $stockId
                );
                $orderlogsmodel->setData($logData);
                $orderlogsmodel->save()->getId();
                
                $templateId=Mage::getStoreConfig('allure_vendor/general/purchase_order_accept',$storeId);
                $adminEmail=Mage::getStoreConfig('allure_vendor/general/admin_email',$storeId);
                $vendorEmail = Mage::helper('allure_vendor')->getVanderEmail($order->getVendorId());
                Mage::log("Vendor Email:".$vendorEmail,Zend_log::DEBUG,"mylogs",true);
                if (!empty($adminEmail)) {
                    $adminEmail =  explode(',', $adminEmail);
                }
                
                $helper->sendEmail($id,$vendorEmail,$templateId,$adminEmail,false);

            }
            Mage::getSingleton('adminhtml/session')->addSuccess("Order accepted");
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/*/orders');
    }
    public function approveAction(){
        $admin = Mage::getSingleton('admin/session')->getUser();
        $id=Mage::app()->getRequest()->getParam('id');
        $helper = Mage::helper('inventory');
        try {
            if($id && isset($id))
            {
                $currentDate = new Zend_Date(Mage::getModel('core/date')->timestamp());
                $currentDate->toString('jS F, Y');
                $status = Allure_Inventory_Helper_Data::ORDER_STATUS_NEW;
                $order = Mage::getModel('inventory/purchaseorder')->load($id);
                $storeId=$order->getStoreId();
                if (isset($status))
                    $order->setData('status', $status);
                    $order->setData('updated_date', $currentDate)->save();
                    
                    $orderlogsmodel = Mage::getModel('inventory/orderlogs');
                    $logData = array(
                        'po_id' => $order->getPoId(),
                        'vendor_id' => $order->getVendorId(),
                        'user_id' => $admin->getUserId(),
                        'date' => date("Y-m-d H:i:s"),
                        'acton' => 'approve',
                        'stock_id' => $order->getStockId()
                    );
                    
                    $orderlogsmodel->setData($logData);
                    $orderlogsmodel->save()->getId();
                    
                    $templateId=Mage::getStoreConfig('allure_vendor/general/purchase_order_accept',$storeId);
                    $adminEmail=Mage::getStoreConfig('allure_vendor/general/admin_email',$storeId);
                    if (!empty($adminEmail)) {
                        $adminEmail =  explode(',', $adminEmail);
                    }
                    
                    $helper->sendEmail($id,'',$templateId,$adminEmail,false);
                    
            }
            Mage::getSingleton('adminhtml/session')->addSuccess("Order accepted");
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        
        $this->_redirect('*/*/orders');
    }
    public function addpurchaseitemAction(){
        $request = $this->getRequest()->getPost();
        $website = 1;
        $ItemsInfo = array();
        $ItemsInfo = Mage::getModel('core/session')->getMyItemsInfo();
        $Items = $ItemsInfo['items'];
        $ItemsInfo['refence_no'] = $request['refence_no'];
        $ItemsInfo['order_total'] = $request['order_total'];
        
        $model = Mage::getModel('inventory/insertitem');
        $user = Mage::getSingleton('admin/session');
        $userId = $user->getUser()->getUserId();
        if ($request['item']['include']) {
            $insertData = array(
                'item_id' => $request['item']['id'],
                'qty' => $request['item']['qty'],
                'cost' => $request['item']['cost'],
                'comment' => $request['item']['comment'],
                'user_id' => $userId,
                'store_id' => $request['item']['store'],
                'is_custom' => $request['item']['is_custom'],
                'vendor_sku' => $request['item']['vendor_sku']

            );
            $model->setData($insertData);
            $model->save();
        } else {

            try {
                $item = $this->loadItemByIdAndStore($request['item']['id'], $request['item']['store']);
                $model = Mage::getModel('inventory/insertitem')->load($item->getId());
                $model->delete();
                // echo "Data deleted successfully.";
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }

        $jsonData = json_encode(compact('success', 'message', 'data'));
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }
    public function getstoredinfoAction(){
        $request = $this->getRequest()->getPost();
        $user = Mage::getSingleton('admin/session');
        $userId = $user->getUser()->getUserId();
        $data = Mage::getModel('inventory/insertitem')->getCollection()
            ->addFieldToFilter('store_id', $request['store'])
            ->addFieldToFilter('user_id', $userId)
            ->getData();
        // Mage::log($data->getData(),Zend_log::DEBUG,'purchase',true);
        $jsonData = json_encode(compact('success', 'message', 'data'));
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);

    }
    public function loadItemByIdAndStore($id,$storeId){
        $user = Mage::getSingleton('admin/session');
        $userId = $user->getUser()->getUserId();
        $model = Mage::getModel('inventory/insertitem')->getCollection()
            ->addFieldToFilter('item_id', $id)
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('user_id', $userId);
        return $model->getFirstItem();
    }
    public function loadItemByIdAndStorePO($id,$storeId,$poId){
        $user = Mage::getSingleton('admin/session');
        $userId = $user->getUser()->getUserId();
        $model = Mage::getModel('inventory/orderitems')->getCollection()
        ->addFieldToFilter('product_id', $id)
        ->addFieldToFilter('stock_id', $storeId)
        ->addFieldToFilter('po_id', $poId);
        return $model->getFirstItem();
    }
    public function loadDraftItemByIdAndStore($id,$storeId){
        $user = Mage::getSingleton('admin/session');
        $userId = $user->getUser()->getUserId();
        $model = Mage::getModel('inventory/draftitems')->getCollection()
        ->addFieldToFilter('item_id', $id)
        ->addFieldToFilter('store_id', $storeId)
        ->addFieldToFilter('user_id', $userId);
        return $model->getFirstItem();
    }
    public function getPOItemsForStore($storeId){
        $user       = Mage::getSingleton('admin/session');
        $userId     = $user->getUser()->getUserId();
        $collection = Mage::getModel('inventory/insertitem')
            ->getCollection()
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('user_id', $userId)
            ->getData();
        return $collection;
    }
    public function massCancelAction(){
        $ids = $this->getRequest()->getParam('po_id');

        $helper = Mage::helper('inventory');
        $resource = Mage::getSingleton('core/resource');
        $writeAdapter = $resource->getConnection('core_write');
        $table = $resource->getTableName('cataloginventory/stock_item');
        
        try {
            foreach ($ids as $id) {
                if ($id && isset($id)) {
                    $currentDate = new Zend_Date(Mage::getModel('core/date')->timestamp());
                    $currentDate->toString('jS F, Y');
                    $status = Allure_Inventory_Helper_Data::ORDER_STATUS_CANCEL;
                    $order = Mage::getModel('inventory/purchaseorder')->load($id);
                    $statusExisting=$order->getStatus();
                    $storeId=$order->getStoreId();
                    
                    $orderlogsmodel = Mage::getModel('inventory/orderlogs');
                    $logData = array(
                        'po_id' => $order->getPoId(),
                        'vendor_id' => $order->getVendorId(),
                        'user_id' => $admin->getUserId(),
                        'date' => date("Y-m-d H:i:s"),
                        'acton' => 'cancel',
                        'stock_id' => $order->getStockId()
                    );
                    
                    $orderlogsmodel->setData($logData);
                    $orderlogsmodel->save()->getId();
                    
                    if ($statusExisting== Allure_Inventory_Helper_Data::ORDER_STATUS_DRAFT ||$order->getStatus()== Allure_Inventory_Helper_Data::ORDER_STATUS_NEW) {
                        $orderItems = Mage::getModel('inventory/orderitems')->getCollection($id, 'po_id');
                        foreach ($orderItems as $item) {
                            $query = "update {$table} set  po_sent =0 where product_id = '{$item->getProductId()}' AND stock_id = '{$order->getStockId()}'";
                            $writeAdapter->query($query);
                        }
                        if (isset($status))
                            $order->setData('status', $status);
                        $order->setData('updated_date', $currentDate)->save();
                        Mage::log('Ids Received', Zend_Log::DEBUG, 'mylogs', true);
                        $templateId = Mage::getStoreConfig('allure_vendor/general/purchase_order_cancel', $storeId);
                        Mage::log('Ids Received'.$templateId, Zend_Log::DEBUG, 'mylogs', true);
                        
                        $adminEmail = Mage::getStoreConfig('allure_vendor/general/admin_email', $storeId);
                        if (! empty($adminEmail)) {
                            $adminEmail = explode(',', $adminEmail);
                        }
                        $vendorEmail='';
                        if($statusExisting!=Allure_Inventory_Helper_Data::ORDER_STATUS_DRAFT)
                            $vendorEmail = Mage::helper('allure_vendor')->getVanderEmail($order->getVendorId());
                        $helper->sendEmail($id, $vendorEmail, $templateId, $adminEmail, false);

                    }

                }
            } //end Of Foreach
            Mage::getSingleton('adminhtml/session')->addSuccess("The order has been cancelled.");
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/orders');
    }
    public function massApproveAction(){
        $ids = $this->getRequest()->getParam('po_id');
        $helper = Mage::helper('inventory');

        try {
            foreach ($ids as $id) {
                if ($id && isset($id)) {
                    $currentDate = new Zend_Date(Mage::getModel('core/date')->timestamp());
                    $currentDate->toString('jS F, Y');
                    $status = Allure_Inventory_Helper_Data::ORDER_STATUS_NEW;
                    $order = Mage::getModel('inventory/purchaseorder')->load($id);
                    $storeId=$order->getStoreId();
                    if ($order->getStatus()== Allure_Inventory_Helper_Data::ORDER_STATUS_DRAFT) {
                        $order->setData('status', $status);
                        $order->setData('updated_date', $currentDate)->save();
                        $vendorEmail = Mage::helper('allure_vendor')->getVanderEmail($order->getVendorId());

                        $templateId=Mage::getStoreConfig('allure_vendor/general/purchase_order_approve',$storeId);
                        $adminEmail=Mage::getStoreConfig('allure_vendor/general/admin_email',$storeId);
                        if (!empty($adminEmail)) {
                            $adminEmail =  explode(',', $adminEmail);
                        }
                        $helper->sendEmail($id, '',$templateId,$adminEmail,false);
                        $vendorEmail = Mage::helper('allure_vendor')->getVanderEmail($order->getVendorId());
                        $templateId=Mage::getStoreConfig('allure_vendor/general/purchase_order_create',$storeId);
                        $helper->sendEmail($id, $vendorEmail,$templateId,$adminEmail,true);

                    }
                }
            }
            Mage::getSingleton('adminhtml/session')->addSuccess("The order has been approved and sent to vendor.");
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/*/orders');
    }
    public function resetAction(){
        $data = $this->getRequest()->getPost();
        $itemsData = $this->getPOItemsForStore($data['store']);
        foreach ($itemsData as $singleItem) {
            Mage::getModel('inventory/insertitem')->load($singleItem['id'])->delete();
        }
        Mage::getSingleton('adminhtml/session')->addSuccess("The reset action performed successfully");
        $jsonData = json_encode(compact('success', 'message', 'data'));
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);

    }
    public function addcustomitemAction(){

        $this->loadLayout();
        $this->renderLayout();
    }
   
    public function savecustomitemAction()
    {
        $data = $this->getRequest()->getPost();

        if (isset($data['data']) && ! empty($data['data'])) {
            foreach ($data['data'] as $key => $value) {
                try {
                    $model = Mage::getModel('inventory/customitem');
                    if($value['sku'] || $value['name'] ){
                        $model->setData(array(
                            'sku' => $value['sku'],
                            'name' => $value['name'],
                            'cost' => $value['cost']
                        ));

                        $insertId = $model->save()->getId();
                    }
                    if (isset($insertId)) {
                        $websiteId = 1;
                        $stockId = 1;
                        if (Mage::getSingleton('core/session')->getMyWebsiteId())
                            $websiteId = Mage::getSingleton('core/session')->getMyWebsiteId();
                        $website = Mage::getModel("core/website")->load($websiteId);
                        $stockId = $website->getStockId();
                        $user = Mage::getSingleton('admin/session');
                        $userId = $user->getUser()->getUserId();
                        $modelTemp = Mage::getModel('inventory/insertitem');
                        $insertData = array(
                            'item_id' => $insertId,
                            'store_id' => $stockId,
                            'qty' => $value['qty'],
                            'user_id' => $userId,
                            'comment' => $value['comment'],
                            'cost' => $value['cost'],
                            'is_custom' => 1
                        );
                        $modelTemp->setData($insertData);
                        $modelTemp->save();
                    }
                    Mage::getSingleton('adminhtml/session')->addSuccess("Items added to your order");
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            }
        }else
            Mage::getSingleton('adminhtml/session')->addError("Please insert valid item");

        $this->_redirectReferer();
    }

    public function removeitemAction(){
        $request = $this->getRequest()->getPost();
        if(isset($request) && !empty($request)){
            $item = Mage::getModel('inventory/orderitems')->getCollection()
            ->addFieldToFilter('product_id', $request['product'])
            ->addFieldToFilter('po_id', $request['order'])->getFirstItem();
            $cost=$item->getTotalAmount();
            $item->delete();
            $order=Mage::getModel('inventory/purchaseorder')->load($request['order']);
            $order->setTotalAmount($order->getTotalAmount()-$cost);
            $order->save();
            
        }
        Mage::getSingleton('adminhtml/session')->addSuccess("Item removed from PO");
        $jsonData = json_encode(compact('success', 'message', 'data'));
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }
    public function addproductAction(){
        $this->loadLayout();
        $this->renderLayout();
    }
    public function addconfirmproductAction(){
        $request = $this->getRequest()->getPost();
        $website = 1;
        $ItemsInfo = array();
        $ItemsInfo = Mage::getModel('core/session')->getMyItemsInfo();
        $Items = $ItemsInfo['items'];
        $ItemsInfo['refence_no'] = $request['refence_no'];
        $ItemsInfo['order_total'] = $request['order_total'];
        
        $model = Mage::getModel('inventory/draftitems');
        $user = Mage::getSingleton('admin/session');
        $userId = $user->getUser()->getUserId();
        if ($request['item']['include']) {
            $insertData = array(
                'item_id' => $request['item']['id'],
                'qty' => $request['item']['qty'],
                'cost' => $request['item']['cost'],
                'comment' => $request['item']['comment'],
                'user_id' => $userId,
                'store_id' => $request['item']['store'],
                'is_custom' => $request['item']['is_custom'],
                'po_id' => $request['item']['po_id'],
                'vendor_sku' => $request['item']['vendor_sku']
            );
            $model->setData($insertData);
            $model->save();
            
            $orderItem = $this->loadItemByIdAndStorePO($request['item']['id'], $request['item']['store'],$request['item']['po_id']);
            $itemmodel = Mage::getModel('inventory/orderitems')->load($orderItem->getId());
            if($itemmodel->getId()){

            }else{
                //$insertData['po_id']=$po_id;
                $insertData['requested_qty']= $request['item']['qty'];
                $insertData['remaining_qty']= $request['item']['qty'];
                $insertData['proposed_qty']= $request['item']['qty'];
                $insertData['product_id']= $request['item']['id'];
                $insertData['stock_id']= $request['item']['store'];
                $insertData['admin_comment']= $request['item']['comment'];
                $insertData['status']= 'draft';
                $itemmodel->setData($insertData);
                $itemmodel->save();
            }
            
          //  $model->delete();
            
        } else {
            
            try {
                $item = $this->loadDraftItemByIdAndStore($request['item']['id'], $request['item']['store']);
                $model = Mage::getModel('inventory/draftitems')->load($item->getId());
                $model->delete();
                // echo "Data deleted successfully.";
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
        
        $jsonData = json_encode(compact('success', 'message', 'data'));
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }
    public function getdraftselecteditemsAction(){
        $request = $this->getRequest()->getPost();
        if($request = $this->getRequest()->getParam('po_id'))
            $poid=  $request = $this->getRequest()->getParam('po_id');
        else 
            $poid=$request['po_id'];
        if($request = $this->getRequest()->getParam('store'))
            $storeId=  $request = $this->getRequest()->getParam('store');
        else
            $storeId=$request['store'];
            
        $user = Mage::getSingleton('admin/session');
        $userId = $user->getUser()->getUserId();
        $data = Mage::getModel('inventory/draftitems')->getCollection()
        ->addFieldToFilter('store_id', $storeId)
        ->addFieldToFilter('po_id', $poid)
        ->getData();
        // Mage::log($data->getData(),Zend_log::DEBUG,'purchase',true);
        $jsonData = json_encode(compact('success', 'message', 'data'));
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
        
    }
    public  function confirmdraftorderAction(){
        $admin = Mage::getSingleton('admin/session')->getUser();
        $data = $this->getRequest()->getPost();
        $po_id = $data['order_id'];
        $order=Mage::getModel('inventory/purchaseorder')->load($po_id);
        $storeId    = $data['store'];
        $stockId=$data['store'];
        $itemsData  = $this->getDraftPOItems($po_id);
        $arrItemIds = array();
        $helper     = Mage::helper('inventory');
        $connection = Mage::getSingleton('core/resource')
        ->getConnection('core_write');
        $vendor=$order->getVendorId();
        if (count($itemsData)){
            try {
                if ($itemsData) {
                    $items  = array();
                    foreach ($itemsData as $_item) {
                        $itemId = $_item['item_id'];
                        $arrItemIds[] = $itemId;
                        if (!$_item['is_custom']) {
                            $product = Mage::getModel('catalog/product')->load($itemId);
                            if(!empty($_item['vendor_sku'] && ($product->getVendorItemNo()!=$_item['vendor_sku']))){
                                Mage::getResourceSingleton('catalog/product_action')
                                ->updateAttributes(array($product->getId()),
                                    array ('vendor_item_no' => $_item['vendor_sku']),
                                    $storeId );
                            }
                            if(!empty($_item['cost'])){
                                Mage::getResourceSingleton('catalog/product_action')
                                ->updateAttributes(array($product->getId()),
                                    array ('cost' => $_item['cost']),
                                    $storeId );
                            }
                           
                        }
                        $items[$vendor][$itemId] = $_item;
                    }
                }
                
                
                $date = new Zend_Date(Mage::getModel('core/date')->timestamp());
                $date->addDay('7');
                $date->toString('Y-m-d H:i:s');
                $message            = "";
                $orderItems         = "";
                $notOrderItems      = "";
                if (isset($items)) {
                    foreach ($items as $key => $itemArray) {
                        $vendorId     = $key;
                        $vendorName   = Mage::helper('allure_vendor')->getVanderName($vendorId);
                        $vendorEmail  = Mage::helper('allure_vendor')->getVanderEmail($vendorId);
                        if (isset($vendorName) && ! empty($vendorName)) {
                            $totalAmount = 0;
                            $po_id       = $po_id;
                            foreach ($itemArray as $item) {
                                $totalAmount += $item['qty'] * $item['cost'];
                            }
                     
                           $orderStatus = Allure_Inventory_Helper_Data::ORDER_STATUS_NEW;
                                    
                          $model     = Mage::getModel('inventory/purchaseorder');
                          $orderData = array(
                                        'ref_no'        => $data['refence_no'],
                                        'vendor_id'     => $vendorId,
                                        'created_date'  => date("Y-m-d H:i:s"),
                                        'updated_date'  => date("Y-m-d H:i:s"),
                                        'vendor_name'   => $vendorName,
                                        'status'        => $orderStatus,
                                        'total_amount'  => $totalAmount,
                                        'stock_id'      => $stockId
                                    );
                          $order->setStatus($orderStatus);
                          $order->setTotalAmount($totalAmount);
                          $order->save();
                          $sqlStrDel     = "DELETE FROM allure_purchase_order_item WHERE po_id =".$order->getPoId();
                          $connection->query($sqlStrDel);
                                 
                                    
                         $modelArr = array();
                         foreach ($itemArray as $item) {
                                        // Map Order items With Order
                                        // Insert entry in allure_purchase_order_item
                                        $dataItems = array(
                                            'po_id'         => $po_id,
                                            'ref_no'        => $data['refence_no'],
                                            'product_id'    => $item['item_id'],
                                            'requested_qty' => $item['qty'],
                                            'remaining_qty' => $item['qty'],
                                            'proposed_qty'  => $item['qty'],
                                            'status'        => 'new',
                                            'requested_delivery_date' => $date,
                                            'is_custom'     => $item['is_custom'],
                                            'admin_comment' => $item['comment'],
                                            'total_amount'  => $item['qty'] * $item['cost'],
                                            'stock_id'      => $stockId,
                                            'vendor_sku'    =>$item['vendor_sku']
                                            
                                        );
                                        $modelArr[] = $dataItems;
                                       
                                    }
                                    
                                    //insert multiple record at one time
                                    $connection->insertMultiple("allure_purchase_order_item",$modelArr);
                                    
                                    // Purchase order logs just for extra information
                                    // insert entry in allure_purchase_order_log
                                    $model = Mage::getModel('inventory/orderlogs');
                                    $logData = array(
                                        'po_id' => $po_id,
                                        'vendor_id' => $vendorId,
                                        'user_id' => $admin->getUserId(),
                                        'date' => date("Y-m-d H:i:s"),
                                        'total_amount' => $totalAmount,
                                        'action' => 'confirmorder',
                                        'stock_id' => $stockId
                                    );
                                    $model->setData($logData);
                                    $model->save()->getId();
                                    $model = null;
                                    
                                        try {
                                            $templateId=Mage::getStoreConfig('allure_vendor/general/purchase_order_create',$storeId);
                                            $adminEmail=Mage::getStoreConfig('allure_vendor/general/admin_email',$storeId);
                                            if (!empty($adminEmail)) {
                                                $adminEmail =  explode(',', $adminEmail);
                                            }
                                            $vendorEmail = Mage::helper('allure_vendor')->getVanderEmail($order->getVendorId());
                                            //sendEmail($po_id, $vendorEmail,$templateId,$templateId)
                                            $helper->sendEmail($po_id, $vendorEmail, $templateId, $adminEmail,true);
                                        } catch (Exception $e) {}
                        }
                    }
                    $message = "Purchase order created successfully.";
                    $strItemIds = implode(",", $arrItemIds);
                    $sqlStr     = "DELETE FROM allure_inventory_purchase_tmp WHERE ITEM_ID IN (".$strItemIds.")";
                    $connection->query($sqlStr);
                    Mage::getSingleton('adminhtml/session')->addSuccess($message);
                    $this->_redirect('*/*/orders');
                }
            } catch (Exception $e) {
                $this->_redirectReferer();
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }else {
            $this->_redirectReferer();
            Mage::getSingleton('adminhtml/session')->addError("Please select items to order first");
        }
       
    }
    public function getDraftPOItems($poId){
        $collection = Mage::getModel('inventory/draftitems')
        ->getCollection()
        ->addFieldToFilter('po_id', $poId)
        ->getData();
        return $collection;
    }
    public function exportAction(){
        $id=Mage::app()->getRequest()->getParam('id');
        $helper = Mage::helper('inventory');
        if($id && isset($id))
        try {
           
            $csvFile = 'purchaseorder_'.$id;
            
            header('Content-Disposition: attachment; filename='.$csvFile.'.csv');
            header('Content-type: text/csv');
            header('Pragma: no-cache');
            header('Expires: 0');
          
            $file = fopen('php://output', 'w');
            
            fputcsv($file, array('ID', 'NAME','COLOR', 'SKU', 'VENDOR SKU', 'ORDERED QTY','VMT COMMENT','REMAINING QTY','EXPECTED SHIP 
                    DATE','VENDOR COMMENT'));
            $data = array();
            $order=Mage::getModel('inventory/purchaseorder')->load(id);
            $collection=Mage::getModel('inventory/orderitems')->getCollection()
            ->addFieldToFilter("po_id",$id);
            $productModel = Mage::getModel('catalog/product');
            $attr = $productModel->getResource()->getAttribute("metal_color");
            foreach ($collection as $_product){
                
                if(!$_product->getIsCustom())
                    $product = Mage::getModel ( 'catalog/product' )->setStoreId($order->getStockId())->load ($_product->getProductId());
                else
                    $product = Mage::getModel ( 'inventory/customitem' )->load ($_product->getProductId());
                    
                $productId=$product->getId();
                $name=$product->getName();
                $sku=$product->getSku();
                $vendorSku=$_product->getVendorSku();
                $orderedQty=$_product->getRequestedQty();
                $vmtComment=$_product->getAdminComment();
               // $shippedQty=$_product->getProposedQty();
                $remainingQty=$_product->getRemainingQty();
                $shipDate=$_product->getProposedDeliveryDate();
                $vendorComment=$_product->getVendorComment();
                $color_label='';
                if ($attr->usesSource()) {
                    $color_label = $attr->getSource()->getOptionText($product->getMetalColor());
                }
            
                $data[]=array($productId,$name,$color_label,$sku,$vendorSku,$orderedQty,$vmtComment,$remainingQty,$shipDate,$vendorComment);
            }
            foreach ($data as $row)
            {
                fputcsv($file, $row);
            }
            
            
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        
       // $this->_redirect('*/*/orders');
    }
    public function vendorviewAction(){
        $this->_initAction();
        $this->_title($this->__('Inventory'))
        ->_title($this->__('Purchase Order'));
        $this->renderLayout();
    }
   
    public function addVendorItemAction(){
        $request = $this->getRequest()->getPost();
        $model = Mage::getModel('inventory/vendorwork');
        if ($request['item']['include']) {
            $orderItem = $this->loadVendorItemByIdAndPO($request['item']['id'],$request['item']['po_id']);
            
            $insertData = array(
                'product_id' => $request['item']['id'],
                'shipped_qty' => $request['item']['qty'],
                'vendor_comment' => $request['item']['comment'],
                'is_custom' => $request['item']['is_custom'],
                'ship_date' => $request['item']['date'],
                'po_id' => $request['item']['po_id'],
                'vendor_sku' => $request['item']['vendor_sku']
            );
            if($orderItem->getId()){
                $model=$orderItem;
                $model->setData($insertData);
                $model->save();
            }else {
                $model->setData($insertData);
                $model->save();
            }
            
        } else {
            
            try {
                $item = $this->loadVendorItemByIdAndPO($request['item']['id'], $request['item']['po_id']);
                if($item->getId()){
                    $item->delete();
                }
                // echo "Data deleted successfully.";
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
        $jsonData = json_encode(compact('success', 'message', 'data'));
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }
    
    public function loadVendorItemByIdAndPO($id,$poId){
        $model = Mage::getModel('inventory/vendorwork')->getCollection()
        ->addFieldToFilter('product_id', $id)
        ->addFieldToFilter('po_id', $poId);
        return $model->getFirstItem();
    }
    public function getVendorSelecteditemsAction(){
        
        $request = $this->getRequest()->getPost();
        
        
        if ($request = $this->getRequest()->getParam('po_id'))
            $poid = $request = $this->getRequest()->getParam('po_id');
        else
            $poid = $request['po_id'];
        
        $data = Mage::getModel('inventory/vendorwork')->getCollection()
            ->addFieldToFilter('po_id', $poid)
            ->getData();
        
        $jsonData = json_encode(compact('success', 'message', 'data'));
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }
    public function shipOrderAction() {
        $admin = Mage::getSingleton('admin/session')->getUser();
        $data = $this->getRequest()->getPost();
        $resource = Mage::getSingleton('core/resource');
        $writeAdapter = $resource->getConnection('core_write');
        $po_id = $data['order_id'];
        $helper = Mage::helper('inventory');
        // get aditional paramters
        try {
            $ship   = Mage::app()->getRequest()->getParam('ship');
            $close  = Mage::app()->getRequest()->getParam('close');
            $canFullyShipOrder = 1;
            $diffArray=array();
            $poObj = Mage::getModel('inventory/purchaseorder')->load($po_id);
            $storeId = $poObj->getStockId();
            
            $vendorCollection = Mage::getModel('inventory/vendorwork')->getCollection()
            ->addFieldToFilter('po_id', $po_id);
            
            if(count($vendorCollection) == 0){
                Mage::getSingleton('adminhtml/session')->addError("Please select items to ship ");
                $this->_redirect('*/*/orders');
                return '';
            }
           
            foreach ($vendorCollection as $vendorItem){
                $date='';
                $item = Mage::getModel('inventory/orderitems')->getCollection()
                ->addFieldToFilter('product_id', $vendorItem->getProductId())
                ->addFieldToFilter('po_id', $po_id);
                $item=$item->getFirstItem();
                /* print_r($item->getData());
                die; */
                if(!$item->getIsCustom()){
                    if($vendorItem->getVendorSku()!=$item->getVendorSku()){
                        Mage::getResourceSingleton('catalog/product_action')
                        ->updateAttributes(array($item->getProductId()), array(
                            'vendor_item_no' => $vendorItem->getVendorSku()
                        ), $storeId);
                        $item->setData('vendor_sku', $vendorItem->getVendorSku());
                    }
                }
               
                if($vendorItem->getShippedQty()!=0){
                    $diffArray[$item->getProductId()]['qty'] = $vendorItem->getShippedQty();
                    $diffArray[$item->getProductId()]['qty_old'] = $item->getRequestedQty();
                    $diffArray[$item->getProductId()]['is_custom'] = $item->getIsCustom();
                    
                    $qtyRemaining=$item->getRemainingQty()-$vendorItem->getShippedQty();
                    $item->setData('proposed_qty', $vendorItem->getShippedQty());
                    $item->setData('remaining_qty', $qtyRemaining);
                    
                    
                }
                if(trim($item->getVendorComment()) != trim($vendorItem->getVendorComment())){
                    $diffArray[$item->getProductId()]['vendor_comment'] = $vendorItem->getVendorComment();
                    $diffArray[$item->getProductId()]['vendor_comment_old'] = $item->getVendorComment();
                    $diffArray[$item->getProductId()]['is_custom'] = $item->getIsCustom();
                    $item->setData('vendor_comment', $vendorItem->getVendorComment());
                    
                }
                if(!is_null($vendorItem->getShipDate())){
                    if(date('m/d/Y',strtotime($item->getProposedDeliveryDate())) != $vendorItem->getShipDate()){
                        $date = date('F j, Y', strtotime($vendorItem->getShipDate()));
                        $diffArray[$item->getProductId()]['proposed_delivery_date'] = $date;
                        $diffArray[$item->getProductId()]['proposed_delivery_date_old'] = ($item->getProposedDeliveryDate())?date('F j, Y', strtotime($item->getProposedDeliveryDate())):'-';
                        $diffArray[$item->getProductId()]['is_custom'] = $item->getIsCustom();
                        $item->setData('proposed_delivery_date', $vendorItem->getShipDate());
                    }
                }
                try {
                    $item->save();
                    $vendorItem->delete();
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    
                }
                
                if ($date) {
                    $days = "7";
                    if (Mage::getStoreConfig('allure_vendor/backorder/backorder_time'))
                        $days = Mage::getStoreConfig('allure_vendor/backorder/backorder_time');
                        
                        /*      update Back-ordered time update to child items */
                        if (!$item->getIsCustom()){
                            $_product = Mage::getModel('catalog/product')->load($item->getProductId());
                            $parentSKu = '';
                            $subString = '';
                            $skuString = explode('|', $_product->getSku());
                            $parentSKu = (string)$skuString[0];
                            unset($skuString[0]);
                            $subString = implode('|',$skuString);
                            
                            $childrenProducts = Mage::getModel('catalog/product')->getCollection();
                            $childrenProducts->addAttributeToFilter( array(array('attribute'=> 'parent_item_number','like' => $parentSKu)));
                            $childProductArr = array();
                            $childProductArr[] = $product;
                            foreach ($childrenProducts as $child){
                                $child=Mage::getModel('catalog/product')->load($child->getId());
                                $childSku=explode('|', $child->getSku());
                                if(!isset($subString) && count($childSku)==1){
                                    $childProductArr[] = $child->getId();
                                    continue;
                                }
                                unset($childSku[0]);
                                if($subString==implode('|',$childSku))
                                {
                                    $childProductArr[] = $child->getId();
                                    continue;
                                }
                                
                                if($child->getTypeId()=="configurable"){
                                    $currentchildrenIds = $child->getTypeInstance()->getChildrenIds($child->getId());
                                    foreach ($currentchildrenIds[0] as $childrenId) {
                                        $sbuChild=Mage::getModel('catalog/product')->load($childrenId);
                                        $subChildSku=explode('|', $sbuChild->getSku());
                                        unset($subChildSku[0]);
                                        if($subString==implode('|',$subChildSku))
                                            $childProductArr[] = $childrenId;
                                            
                                    }
                                    
                                }
                                
                            }
                            $childProductArr=array_unique($childProductArr);
                            $backDate = date_create($date);
                            date_add($backDate, date_interval_create_from_date_string($days . " days"));
                            
                            $backDate = date_format($backDate, "F j, Y");
                            
                            if (!$item->getIsCustom()) {
                                try {
                                    Mage::getResourceSingleton('catalog/product_action')
                                    ->updateAttributes($childProductArr, array(
                                        'backorder_time' => $backDate
                                    ), $storeId);
                                    
                                } catch (Exception $e) {
                                    
                                }
                            }
                        }
                        
                } //End
            }
            
            $itemCollection = Mage::getModel('inventory/orderitems')->getCollection()
            ->addFieldToFilter('po_id', $po_id);
            foreach ($itemCollection as $tempItem){
                if($tempItem->getRemainingQty() > 0){
                    $canFullyShipOrder=0;
                    break;
                }
            }
            
            if ($close && $canFullyShipOrder)
                $status = Allure_Inventory_Helper_Data::ORDER_STATUS_FULLY_SHIPPED;
            if (($ship || $close) && $canFullyShipOrder==0)
                $status = Allure_Inventory_Helper_Data::ORDER_STATUS_PARTIALLY_SHIPPED;
            $currentDate = new Zend_Date(Mage::getModel('core/date')->timestamp());
            $currentDate->toString('jS F, Y');
            $vendorId = $poObj->getVendorId();
            $orderlogsmodel = Mage::getModel('inventory/orderlogs');
            $logData = array(
                'po_id' => $po_id,
                'vendor_id' => $vendorId,
                'user_id' => $admin->getUserId(),
                'date' => date("Y-m-d H:i:s"),
                'stock_id' => $stockId
            );
            $orderlogsmodel->setData($logData);
            $orderlogsmodel->save()->getId();
            if (($close || $ship) && isset($status)){
                $poObj->setData('status', $status);
                $poObj->setData('updated_date', $currentDate)->save();
            }
            
            if ($close && $canFullyShipOrder) {
                // fully Shipped
                $templateId = Mage::getStoreConfig('allure_vendor/general/purchase_order_shipment', $storeId);
                $adminEmail = Mage::getStoreConfig('allure_vendor/general/admin_email', $storeId);
                if (! empty($adminEmail)) {
                    $adminEmail = explode(',', $adminEmail);
                }
                $vendorEmail = Mage::helper('allure_vendor')->getVanderEmail($vendorId);
                $helper->sendEmail($po_id, $vendorEmail, $templateId, $adminEmail, true, $diffArray);
                
                Mage::getSingleton('adminhtml/session')->addSuccess("Order shipped fully.");
            } elseif ($close) {
                // Partially Ship
                
                $templateId = Mage::getStoreConfig('allure_vendor/general/purchase_order_shipment', $storeId);
                $adminEmail = Mage::getStoreConfig('allure_vendor/general/admin_email', $storeId);
                if (! empty($adminEmail)) {
                    $adminEmail = explode(',', $adminEmail);
                }
                $vendorEmail = Mage::helper('allure_vendor')->getVanderEmail($vendorId);
                Mage::log("vendorEmail:" . $templateId, Zend_log::DEBUG, "mylogs", true);
                $helper->sendEmail($po_id, $vendorEmail, $templateId, $adminEmail, true, $diffArray);
                Mage::getSingleton('adminhtml/session')->addSuccess("Order shipped partially, as some of items remaining to ship.");
            } elseif ($ship) {
                // Partially Ship
                
                $templateId = Mage::getStoreConfig('allure_vendor/general/purchase_order_shipment', $storeId);
                $adminEmail = Mage::getStoreConfig('allure_vendor/general/admin_email', $storeId);
                if (! empty($adminEmail)) {
                    $adminEmail = explode(',', $adminEmail);
                }
                $vendorEmail = Mage::helper('allure_vendor')->getVanderEmail($vendorId);
                Mage::log("vendorEmail:" . $vendorEmail, Zend_log::DEBUG, "mylogs", true);
                $helper->sendEmail($po_id, $vendorEmail, $templateId, $adminEmail, true, $diffArray);
                Mage::getSingleton('adminhtml/session')->addSuccess("Order Shipped partially.");
            }
                            
        } catch (Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e);
        }
        $this->_redirect('*/*/orders');
    }
}