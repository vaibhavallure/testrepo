<?php
class Teamwork_Service_Block_Adminhtml_Chqmapping_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{   
    protected function _construct()
    {
        $this->_blockGroup = 'teamwork_service';
        $this->_controller = 'adminhtml_chqmapping';
    }

    public function getHeaderText()
    {
        $helper = Mage::helper('teamwork_service');
        $model = Mage::registry('model');
        
        $type = $this->getRequest()->getParam('type');
        $channel_id = $this->getRequest()->getParam('channel_id');
        
        $channelName = $helper->getChannelsList();

        if ($model->getEntityId()) {
            return $helper->__("Edit mapping CHQ field  '%s' product and '%s' channel", $type, $channelName[$channel_id]);
        } else {
            return $helper->__("Create new mapping CHQ field  '%s' product and '%s' channel", $type, $channelName[$channel_id]);
        }
    }
}