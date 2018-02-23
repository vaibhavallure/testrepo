<?php
class Teamwork_Common_Model_Chq_Xml_Request_Category extends Teamwork_Common_Model_Chq_Xml_Request_Abstract
{
    protected $_channelList = array();
    
    protected function addFilters($addRecModified=true)
    {
        $filters = parent::addFilters($addRecModified);

        $this->_channelList = Mage::getModel('teamwork_common/staging_channel')->getChannels();
        if( !empty($this->_channelList) )
        {   
            $styleFilter = $filters->addChild('Filter');
            $styleFilter->addAttribute('Field', 'ECommerceChannelId');
            $styleFilter->addAttribute('Operator', 'Contains');
            $styleFilter->addAttribute('Value', implode(',', array_keys($this->_channelList)));
        }
    }
}