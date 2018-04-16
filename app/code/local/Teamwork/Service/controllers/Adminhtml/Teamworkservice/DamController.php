<?php

class Teamwork_Service_Adminhtml_Teamworkservice_DamController extends Mage_Adminhtml_Controller_Action
{

    public function subscribeAction()
    {
        $successMsg = Mage::helper('teamwork_service')->__('Successfully subscribed to the DAM scheduler');
        $this->_setSessionMsgs(Mage::getResourceModel('teamwork_service/dam')->subscribeScheduler(), $successMsg);
        $this->_redirectReferer();
    }


    public function unsubscribeAction()
    {
        $successMsg = Mage::helper('teamwork_service')->__('Successfully unsubscribed from the DAM scheduler');
        $this->_setSessionMsgs(Mage::getResourceModel('teamwork_service/dam')->unsubscribeScheduler(), $successMsg);
        $this->_redirectReferer();
    }

    protected function _setSessionMsgs($result, $successMsg)
    {
        if ($result['res'] === false)
        {
            if ($result['error_msgs'])
            {
                foreach($result['error_msgs'] as $msg)
                {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('teamwork_service')->__($msg));
                }
            }
            else
            {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('teamwork_service')->__('Internal error'));
            }
        }
        else
        {
            Mage::getSingleton('adminhtml/session')->addSuccess($successMsg);
        }
    }

    protected function _isAllowed(){
        return true;
    }
}
