<?php

class Ebizmarts_BakerlooRestful_Adminhtml_Bakerlooorders_Edit_Tab_ItemsController extends Mage_Adminhtml_Controller_Action
{

    public function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'delete':
                $acl = 'ebizmarts_pos/orders/remove';
                break;
            case 'exportCsv':
                $acl = 'ebizmarts_pos/orders/export';
                break;
            case 'save':    //save and edit request share same permissions
            case 'edit':
                $acl = 'ebizmarts_pos/orders/edit';
                break;
            case 'place':
                $acl = 'ebizmarts_pos/orders/retry';
                break;
            default:
                $acl = 'ebizmarts_pos/orders/list';
                break;
        }
        return Mage::getSingleton('admin/session')->isAllowed($acl);
    }

    public function indexAction()
    {

        $this->_title($this->__('Order Items'));

        $this->loadLayout();
        $this->_setActiveMenu('ebizmarts_pos');
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('bakerloo_restful/adminhtml_bakerlooorders_edit_tab_items_grid')->toHtml()
        );
    }
}
