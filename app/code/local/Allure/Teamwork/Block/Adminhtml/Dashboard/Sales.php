<?php


class Allure_Teamwork_Block_Adminhtml_Dashboard_Sales extends Mage_Adminhtml_Block_Dashboard_Sales
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

        $collection = Mage::getResourceModel('reports/order_collection')
            ->calculateSales($isFilter);

        if ($this->getRequest()->getParam('store')) {
            $collection->addFieldToFilter('store_id', $this->getRequest()->getParam('store'));
        } else if ($this->getRequest()->getParam('website')){
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
            $collection->addFieldToFilter('store_id', array('in' => $storeIds));
        } else if ($this->getRequest()->getParam('group')){
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
            $collection->addFieldToFilter('store_id', array('in' => $storeIds));
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
        $sales = $collection->getFirstItem();

        $this->addTotal($this->__('Lifetime Sales'), $sales->getLifetime());
        $this->addTotal($this->__('Average Orders'), $sales->getAverage());
    }
}
