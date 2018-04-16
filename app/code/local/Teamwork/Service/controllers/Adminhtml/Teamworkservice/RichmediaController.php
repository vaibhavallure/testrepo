<?php

class Teamwork_Service_Adminhtml_Teamworkservice_RichmediaController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $helper = Mage::helper('teamwork_service');
        $listChannel = $helper->getListChannels();

        Mage::register('listChannel', $listChannel);

        $this->loadLayout()->_setActiveMenu('teamwork_service');
        $this->_addLeft($this->getLayout()->createBlock('teamwork_service/adminhtml_richmedia_tabs'));
        $contentBlock = $this->getLayout()->createBlock('teamwork_service/adminhtml_richmedia_edit');
        $this->_addContent($contentBlock);
        $this->renderLayout();
    }

    public function addAction()
    {
        $channel_id = $this->getRequest()->getParam('channel');
        if ($data = $this->getRequest()->getPost())
        {
            try
            {
                $model = Mage::getModel('teamwork_service/richmedia');

                $model->setData($data);

                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Mapping was saved successfully'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                $this->_redirect('*/*/index', array(
                    'channel' => $channel_id
                ));
            }
            catch (Exception $e)
            {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/index', array(
                    'channel' => $channel_id
                ));
            }
            return;
        }
        Mage::getSingleton('adminhtml/session')->addError($this->__('Unable to find mapping to save'));
        $this->_redirect('*/*/index', array(
                    'channel' => $channel_id
        ));
    }

    public function mappingAction()
    {
        $channel_id = $this->getRequest()->getParam('channel');
        $db = Mage::getModel('teamwork_service/adapter_db');
        $query = "SELECT channel_name FROM {$db->getTable('service_channel')} WHERE channel_id = '{$channel_id}'";
        $result = $db->getResults($query);
        Mage::register('channel_name', $result);

        $this->loadLayout()->_setActiveMenu('teamwork_service');
        $contentBlock = $this->getLayout()->createBlock('teamwork_service/adminhtml_richmedia_mapping');
        $this->_addContent($contentBlock);
        $this->renderLayout();
    }

    public function saveAction()
    {
        $data = $this->getRequest()->getPost();

        $channel_id = $data['channel_id'];

        $collection = Mage::getModel('teamwork_service/richmedia')->getCollection()
        ->addFieldToFilter('channel_id', $channel_id);

        foreach ($collection AS $richmedia)
        {
            $attributeId = $richmedia->getAttributeId();

            if (isset($data[$attributeId.'_delete']))
            {
                $richmedia->delete();
            }
            else
            {
                $richmedia->setMediaIndex($data["$attributeId"])->save();
            }
        }

        $this->_redirect('*/*/index', array(
                    'channel' => $channel_id
        ));
    }
    
    protected function _isAllowed(){
        return true;
    }
}