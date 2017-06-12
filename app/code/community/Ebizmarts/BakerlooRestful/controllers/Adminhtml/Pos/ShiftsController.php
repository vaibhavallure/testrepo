<?php

class Ebizmarts_BakerlooRestful_Adminhtml_Pos_ShiftsController extends Mage_Adminhtml_Controller_Action
{
    public function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('ebizmarts_pos/shifts');
    }

    public function indexAction()
    {
        $this->_title($this->__('Shifts'))
            ->_title($this->__('POS'));

        $this->loadLayout();
        $this->_setActiveMenu('ebizmarts_pos');
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('bakerloo_restful/adminhtml_pos_shifts_grid')->toHtml()
        );
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('bakerloo_restful/shift')->load($id);

        if (!$model->getId()) {
            $this->_getSession()->addError($this->__('This shift no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }

        $this->_title($this->__('Shifts'))
            ->_title($this->__('POS'));

//        if($model->getJsonPayload())
//            $model->setJsonPayload(json_decode($model->getJsonPayload()));

        Mage::register('pos_shift', $model);

        $this->loadLayout();
        $this->_setActiveMenu('ebizmarts_pos');

        $this->renderLayout();
    }


    public function getCurrentShift()
    {
        return Mage::registry('pos_shift');
    }
}
