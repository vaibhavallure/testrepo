<?php

class Teamwork_Service_Block_Adminhtml_Richmedia_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{   
    public function __construct()
    {
        parent::__construct();
        $this->setTitle($this->__('List channels'));
    }

    protected function _prepareLayout()
    {
        $channel_id = $this->getRequest()->getParam('channel');
        
        $listChannel = Mage::registry('listChannel');
        
        if(!empty($listChannel))
        {
            foreach($listChannel as $channel)
            {
                //set default channel
                if (empty($channel_id))
                {
                    Mage::app()->getRequest()->setParam('channel', $channel['channel_id']);
                    $channel_id = $channel['channel_name'];
                }
                
                $active = false;
                
                if($channel['channel_id'] == $channel_id)
                {
                    $active = true;
                }
                // add list tab
                $this->addTab($channel['channel_name'], array(
                'label'     => $this->__($channel['channel_name']),
                'active'    => $active,
                'url' => $this->getUrl('*/*/*', array('channel' => $channel['channel_id'])),
                ));
            }
        }
        
        return parent::_prepareLayout();   
    }
}
