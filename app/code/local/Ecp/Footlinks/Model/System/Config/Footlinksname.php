<?php

class Ecp_Footlinks_Model_System_Config_Footlinksname {

    public function toOptionArray() {
        $footlinks = Mage::getModel('ecp_footlinks/footlinks')->getCollection();
        $response = array();
        foreach($footlinks as $footlink)
            $response[] = array('value'=>$footlink->getId(),'label'=>$footlink->getTitle());
        return $response;

    }
}
