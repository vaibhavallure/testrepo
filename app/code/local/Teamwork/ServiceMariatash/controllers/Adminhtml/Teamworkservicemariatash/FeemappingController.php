<?php

class Teamwork_ServiceMariatash_Adminhtml_Teamworkservicemariatash_FeemappingController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout()->_setActiveMenu('teamwork_servicemariatash');
        $contentBlock = $this->getLayout()->createBlock('teamwork_servicemariatash/adminhtml_feemapping');
        $this->_addContent($contentBlock);
        $this->renderLayout();
    }
	
	public function newAction()
    {
		$this->loadLayout()->_setActiveMenu('teamwork_servicemariatash');
        $contentBlock = $this->getLayout()->createBlock('teamwork_servicemariatash/adminhtml_feemapping_edit');
        $this->_addContent($contentBlock);
        $this->renderLayout();
	}
	
	public function saveAction()
    {
        $id = (int) $this->getRequest()->getParam('id');
		if ($data = $this->getRequest()->getPost())
        {
            try
            {
                $model = Mage::getModel('teamwork_servicemariatash/feemapping');
				
                if($id > 0)
                {
                    $model->setData($data)->setEntityId($id);
                }
                else
                {
                    $model->setData($data);
                }
                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Mapping was saved successfully'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                $this->_redirect('*/*/index');
            }
            catch (Exception $e)
            {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/index');
            }
            return;
        }

        Mage::getSingleton('adminhtml/session')->addError($this->__('Unable to find mapping to save'));
        $this->_redirect('*/*/index');
	}
	
	public function editAction()
    {

        $id = (int) $this->getRequest()->getParam('id');
        $model = Mage::getModel('teamwork_servicemariatash/feemapping');

        if($data = Mage::getSingleton('adminhtml/session')->getFormData()){
            $model->setData($data)->setId($id);
        } else {
            $model->load($id);
        }

        Mage::register('model', $model);

        $this->loadLayout()->_setActiveMenu('teamwork_service');
        $contentBlock = $this->getLayout()->createBlock('teamwork_servicemariatash/adminhtml_feemapping_edit');
        $this->_addContent($contentBlock);
        $this->renderLayout();
    }
	
	public function massDeleteAction()
    {
        $mapping = $this->getRequest()->getParam('feemapping', null);

        if (is_array($mapping) && sizeof($mapping) > 0) {
            try {
                foreach ($mapping as $id)
                {
                    Mage::getModel('teamwork_servicemariatash/feemapping')->setId($id)->delete();
                }
                $this->_getSession()->addSuccess($this->__('Total of %d mapping have been deleted', sizeof($mapping)));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        } else {
            $this->_getSession()->addError($this->__('Please select mapping'));
        }
        $this->_redirect('*/*');
    }
	
	public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                Mage::getModel('teamwork_servicemariatash/feemapping')->setId($id)->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Mapping was deleted successfully'));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $id));
            }
        }
        $this->_redirect('*/*/');
    }
}