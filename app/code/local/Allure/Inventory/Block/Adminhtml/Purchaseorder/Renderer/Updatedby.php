<?php
class Allure_Inventory_Block_Adminhtml_Purchaseorder_Renderer_Updatedby extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
    
    public function render(Varien_Object $row)
    {
        $updatedInfo=Mage::getModel('inventory/orderlogs')->getCollection();
        $updatedInfo->addFieldToFilter('po_id',$row->getData('po_id'));
        $updatedInfo->getSelect()->joinLeft('admin_user', 'admin_user.user_id = main_table.user_id', array('username','firstname','lastname'));
        //$updatedInfo->getSelect();
        $updatedInfo->setOrder('date', 'DESC');
        $updatedInfo=$updatedInfo->getFirstItem();
        if(!empty($updatedInfo->getUsername())){
            return $updatedInfo->getFirstname()." ".$updatedInfo->getLastname().' ('.$updatedInfo->getDate().')';
        }
    }
}