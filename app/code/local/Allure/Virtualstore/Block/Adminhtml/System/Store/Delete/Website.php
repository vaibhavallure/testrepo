<?php

class Allure_Virtualstore_Block_Adminhtml_System_Store_Delete_Website extends Mage_Adminhtml_Block_Template
{

    protected function _prepareLayout()
    {
        $itemId = $this->getRequest()->getParam('website_id');

        $this->setTemplate('system/store/delete_website.phtml');
        $this->setAction($this->getUrl('*/*/deleteWebsitePost', array('website_id'=>$itemId)));
        $this->setChild('confirm_deletion_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('allure_virtualstore')->__('Delete Website'),
                    'onclick'   => "deleteForm.submit()",
                    'class'     => 'cancel'
                ))
        );
        $onClick = "setLocation('".$this->getUrl('*/*/editWebsite', array('website_id'=>$itemId))."')";
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
