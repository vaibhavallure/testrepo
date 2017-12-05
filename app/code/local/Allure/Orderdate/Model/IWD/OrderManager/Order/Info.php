<?php
class Allure_Orderdate_Model_IWD_OrderManager_Order_Info extends IWD_OrderManager_Model_Order_Info
{
    protected $params;

    public function updateOrderInfo($params)
    {
        $this->init($params);

        if (isset($params['confirm_edit']) && !empty($params['confirm_edit'])) {
            $this->addChangesToConfirm();
        } else {
            $this->editInfo();
            $this->updateOrderAmounts();
            $this->addChangesToLog();
            $this->notifyEmail();
        }
    }
    
    protected function editInfo()
    {
        $this->load($this->params['order_id']);
        
        $this->updateOrderState();
        $this->updateOrderStatus();
        $this->updateOrderStoreId();
        $this->updateOrderCreatedDate();
    }
    
    /**
     * change order date @allure
     */
    protected function updateOrderCreatedDate()
    {
        if(isset($this->params['created_at'])) {
            $createdAt       = $this->params['created_at'];
            $gmtCreatedDate  = Mage::getModel("core/date")->gmtDate("Y-m-d", $createdAt);
            $createdDateOnly = date("Y-m-d",strtotime($this->getCreatedAt()));//Mage::getModel("core/date")->gmtDate("Y-m-d", $this->getCreatedAt());
            if (!empty($gmtCreatedDate) && $createdDateOnly != $gmtCreatedDate) {
                Mage::getSingleton('iwd_ordermanager/logger')->addChangesToLog('created_at', $this->getCreatedAt(), $createdAtTime);
                $createdAtTime =  Mage::getModel("core/date")->gmtDate("Y-m-d", $createdAt)." ".date('H:i:s',strtotime($this->getCreatedAt()));
                $this->setData('created_at', $createdAtTime)->save();
            }
        }
    }
    
}