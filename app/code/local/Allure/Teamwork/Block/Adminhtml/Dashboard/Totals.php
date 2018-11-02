<?php

class Allure_Teamwork_Block_Adminhtml_Dashboard_Totals extends Mage_Adminhtml_Block_Dashboard_Totals
{
    
    /**
     *get user role to show teamwork orders
     */
    private function getRolesToShowTeamworkOrder(){
        return array(1);//"Super Administrator"
    }
    
    protected function _prepareLayout()
    {
        if (!Mage::helper('core')->isModuleEnabled('Mage_Reports')) {
            return $this;
        }
        $isFilter = $this->getRequest()->getParam('store') || $this->getRequest()->getParam('website') || $this->getRequest()->getParam('group');
        $period = $this->getRequest()->getParam('period', '24h');

        /* @var $collection Mage_Reports_Model_Mysql4_Order_Collection */
        $collection = Mage::getResourceModel('reports/order_collection')
            ->addCreateAtPeriodFilter($period)
            ->calculateTotals($isFilter);

        if ($this->getRequest()->getParam('store')) {
            $collection->addFieldToFilter('store_id', $this->getRequest()->getParam('store'));
        } else if ($this->getRequest()->getParam('website')){
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
            $collection->addFieldToFilter('store_id', array('in' => $storeIds));
        } else if ($this->getRequest()->getParam('group')){
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
            $collection->addFieldToFilter('store_id', array('in' => $storeIds));
        } elseif (!$collection->isLive()) {
            $collection->addFieldToFilter('store_id',
                array('eq' => Mage::app()->getStore(Mage_Core_Model_Store::ADMIN_CODE)->getId())
            );
        }

        $roles = $this->getRolesToShowTeamworkOrder();
        $user = Mage::getSingleton('admin/session')->getUser();
        if($user != null){
            $userRole = $user->getRole()->getData();
            $roleName = $userRole["role_id"];
            if($roleName != 1){
                $collection->addFieldToFilter('main_table.create_order_method', array('nin' => array(2)));
            }
        }
        
        $collection->load();

        $totals = $collection->getFirstItem();

        $this->addTotal($this->__('Revenue'), $totals->getRevenue());
        $this->addTotal($this->__('Tax'), $totals->getTax());
        $this->addTotal($this->__('Shipping'), $totals->getShipping());
        $this->addTotal($this->__('Quantity'), $totals->getQuantity()*1, true);
    }
}
