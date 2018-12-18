<?php

class Teamwork_Transfer_Block_Rewrite_Adminhtml_Sales_Order_Grid extends IWD_OrderManager_Block_Adminhtml_Sales_Order_Grid //Mage_Adminhtml_Block_Sales_Order_Grid /**/
{
    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();

        $this->getMassactionBlock()->addItem('resend_to_chq', array(
            'label' => Mage::helper('teamwork_transfer')->__('Resend to CloudHQ'),
            'url'   => $this->getUrl('adminhtml/teamworktransfer_adminhtml_sales_order/resendToChq')
        ));

        return $this;
    }
}
