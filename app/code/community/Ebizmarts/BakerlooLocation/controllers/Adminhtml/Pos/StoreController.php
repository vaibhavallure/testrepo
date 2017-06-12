<?php

class Ebizmarts_BakerlooLocation_Adminhtml_Pos_StoreController extends Mage_Adminhtml_Controller_Action
{

    protected function _initStore($id)
    {
        $store = Mage::getModel('bakerloo_location/store');

        if ($id) {
            $store->load($id);
        }

        Mage::register('current_store', $store);

        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('Store'))
            ->_title($this->__('Locations'))
            ->_title($this->__('POS'));

        $this->loadLayout();

        $this->_setActiveMenu('ebizmarts_pos');
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();

        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('bakerloo_location/adminhtml_pos_store_grid')->toHtml()
        );
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id    = $this->getRequest()->getParam('id');
        $model = Mage::getModel('bakerloo_location/store');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->_getSession()->addError($this->__('This item no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $this->_title($model->getId() ? $this->__("Editing location #%s", $model->getId()) : $this->__('New location'));
        $this->_title($this->__('Locations'))
            ->_title($this->__('POS'));

        // Restore previously entered form data from session
        $data = $this->_getSession()->getUserData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('poslocation', $model);

        $this->loadLayout();
        $this->_setActiveMenu('ebizmarts_pos');

        $this->renderLayout();
    }

    public function saveAction()
    {
        if ($this->getRequest()->isPost()) {
            try {
                $postData = $this->getRequest()->getPost('location');

                $id = isset($postData['id']) ? (int)$postData['id'] : null;

                $location = Mage::getModel('bakerloo_location/store')->load($id);

                try {
                    $location->addData($postData)
                            ->save();

                    $this->_getSession()->addSuccess(Mage::helper('bakerloo_location')->__('The location has been saved.'));
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }

                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->setUserData($postData);

                $this->_redirect('*/*/edit/');

                return;
            }
        }

        $this->_redirect('adminhtml/pos_store/');
    }

    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'new':
            case 'edit':
                $acl = 'ebizmarts_pos/location/edit';
                break;
            case 'list':
                $acl = 'ebizmarts_pos/location/list';
                break;
            default:
                $acl = 'ebizmarts_pos/location/list';
        }
        return Mage::getSingleton('admin/session')->isAllowed($acl);
    }
}
