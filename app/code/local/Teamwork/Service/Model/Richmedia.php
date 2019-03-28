<?php

class Teamwork_Service_Model_Richmedia extends Mage_Core_Model_Abstract
{
    
    const SETTINGS_TEMPLATE_ELEMENTS  = 'template_elements';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_service/richmedia');
    }
    
    public function deleteMapping($channelId, $elementData = array())
    {
       $collection = Mage::getModel('teamwork_service/richmedia')->getCollection()
        ->addFieldToFilter('channel_id', $channelId)->load();
        
        $elements = array();
        
        if (!empty($elementData))
        {
            foreach ($elementData as $element)
            {
                $elements[] = $element['ecIndex'];
            }
        }
        
        foreach ($collection as $value)
        {
            if(!in_array($value['media_index'], $elements) || count($elements) == 0)
            {
                $value->delete();
            }
        }
    }
}