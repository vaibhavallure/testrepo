<?php


class Allure_Virtualstore_Block_Adminhtml_System_Store_Delete_Group extends Mage_Adminhtml_Block_Template
{
    protected function _prepareLayout()
    {
        $itemId = $this->getRequest()->getParam('group_id');

        $this->setTemplate('system/store/delete_group.phtml');
        $this->setAction($this->getUrl('*/*/deleteGroupPost', array('group_id'=>$itemId)));
        $this->setChild('confirm_deletion_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('allure_virtualstore')->__('Delete Store'),
                    'onclick'   => "deleteForm.submit()",
                    'class'     => 'cancel'
                ))
        );
        $onClick = "setLocation('".$this->getUrl('*/*/editGroup', array('group_id'=>$itemId))."')";
        $this->setChild('cancel_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('allure_virtualstore')->__('Cancel'),
                    'onclick'   => $onClick,
                    'class'     => 'cancel'
                ))
        );
        $this->setChild('back_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('allure_virtualstore')->__('Back'),
                    'onclick'   => $onClick,
                    'class'     => 'cancel'
                ))
        );
        return parent::_prepareLayout();
    }
}
