<?php

class Ecp_Citysearch_Model_System_Config_Citysearch {

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        $accounts = unserialize(Mage::getStoreConfig('ecp_citysearch/citysearch/api_citysearch_new_config'));

        $response = array();
        if(isset($accounts) && !is_null($accounts) && $accounts!=""){
	        foreach($accounts as $account)
	            $response[] = array('value'=>$account['listing_id'],'label'=>$account['name']);
	        return $response;
        }

    }
}
