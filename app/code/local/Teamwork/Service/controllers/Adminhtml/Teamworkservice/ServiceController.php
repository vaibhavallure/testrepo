<?php

class Teamwork_Service_Adminhtml_Teamworkservice_ServiceController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout()->_setActiveMenu('teamwork_service');
        $contentBlock = $this->getLayout()->createBlock('teamwork_service/adminhtml_service');
        $this->_addContent($contentBlock);
        $this->renderLayout();
    }

    public function viewAction()
    {

        $entityName = $this->getRequest()->getParam('table');

        $arrayEntity = Mage::getConfig()->getNode('global/models/service_resource/entities')->asArray();

        if (!array_key_exists($entityName, $arrayEntity))
        {
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Table '. $entityName .' not exists in this module'));
            $this->_redirect('*/*/', array());
        }
        else
        {
            $this->loadLayout()->_setActiveMenu('teamwork_service');
            $contentBlock = $this->getLayout()->createBlock('teamwork_service/adminhtml_service_edit');
            $this->_addContent($contentBlock);
            $this->renderLayout();
        }
    }

    public function editAction()
    {
        $entity_id = (int) $this->getRequest()->getParam('entity_id');
        $entity = (string) $this->getRequest()->getParam('entity');

        $db = Mage::getModel('teamwork_service/adapter_db');

        $query = "SELECT * FROM {$db->getTable($entity)} WHERE entity_id = {$entity_id}";

        $result = $db->getResults($query);

        Mage::register('model', $result);

        $this->loadLayout()->_setActiveMenu('teamwork_service');
        $contentBlock = $this->getLayout()->createBlock('teamwork_service/adminhtml_service_edit_edit');
        $this->_addContent($contentBlock);
        $this->renderLayout();
    }

    public function deleteAction()
    {
        $entity_id = $this->getRequest()->getParam('entity_id');
        $entity = $this->getRequest()->getParam('entity');

        $db = Mage::getSingleton('core/resource')->getConnection('read');

        if (isset($entity_id) && isset($entity))
        {
            try {
                $db->delete(Mage::getSingleton('core/resource')->getTableName($entity), array('entity_id =?' => $entity_id));
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Record entity was deleted successfully'));
                $this->_redirect('*/*/view', array(
                    'table' => $entity
                ));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array(
                    'entity' => $entity,
                    'entity_id' => $entity_id
                ));
            }
            return;
        }

        Mage::getSingleton('adminhtml/session')->addError($this->__('Unable to find item to delete'));
        $this->_redirect('*/*/edit', array(
            'entity' => $entity,
            'entity_id' => $entity_id
        ));
    }

    public function saveAction()
    {
        $entity_id = $this->getRequest()->getParam('entity_id');
        $entity = $this->getRequest()->getParam('entity');

        $db = Mage::getSingleton('core/resource')->getConnection('read');

        if ($data = $this->getRequest()->getPost()) {
            try {
                $helper = Mage::helper('teamwork_service');

                unset($data['form_key']);
                $db->update(Mage::getSingleton('core/resource')->getTableName($entity), $data, array('entity_id =?' => $entity_id));

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Record entity was saved successfully'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                $this->_redirect('*/*/view', array(
                    'table' => $entity
                ));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/view', array(
                    'table' => $entity
                ));
            }
            return;
        }
        Mage::getSingleton('adminhtml/session')->addError($this->__('Unable to find item to save'));
        $this->_redirect('*/*/view', array(
            'table' => $entity
        ));
    }

    public function massDeleteAction()
    {
        $entity_name = $this->getRequest()->getParam('table');

        $entity = $this->getRequest()->getParam($entity_name, null);

        $db = Mage::getSingleton('core/resource')->getConnection('read');

        if (is_array($entity) && sizeof($entity) > 0) {
            try {
                foreach ($entity as $id) {
                    $db->delete(Mage::getSingleton('core/resource')->getTableName($entity_name), array(
                    'entity_id = ?'   => $id
                    ));
                }
                $this->_getSession()->addSuccess($this->__('Total of %d entity have been deleted', sizeof($entity)));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        } else {
            $this->_getSession()->addError($this->__('Please select entity'));
        }

        $this->_redirect('*/*/view', array(
            'table' => $entity_name
        ));
    }
    
    protected function _isAllowed(){
        return true;
    }
}