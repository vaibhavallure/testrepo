<?php

class Ecp_Footlinks_Model_Observer
{
    public function getContent(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
                
        $requestUrl = $event->getAction()->getRequest()->getParam('footerLinkMenu');
        $layout = $observer->getEvent()->getLayout();
        
        if(isset($requestUrl)){
            $layout->getUpdate()->addHandle("footlinks_index_content");
        }
        
    }      
}