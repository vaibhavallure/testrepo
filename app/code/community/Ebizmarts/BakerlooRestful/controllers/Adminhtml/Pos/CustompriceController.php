<?php

class Ebizmarts_BakerlooRestful_Adminhtml_Pos_CustompriceController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('ebizmarts_pos');
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('bakerloo_restful/adminhtml_pos_customprice_grid')->toHtml()
        );
    }

    public function newAction()
    {
        $this->_forward('edit');
    }


    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'delete':
            case 'new':
            case 'save':
            case 'edit':
                $acl = 'ebizmarts_pos/customprice/add_edit';
                break;
            default:
                $acl = 'ebizmarts_pos/customprice/list';
        }
        return Mage::getSingleton('admin/session')->isAllowed($acl);
    }
}
