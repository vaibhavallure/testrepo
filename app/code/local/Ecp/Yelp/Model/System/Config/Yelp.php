<?php

class Ecp_Yelp_Model_System_Config_Yelp {

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        $accounts = unserialize(Mage::getStoreConfig('ecp_yelp/yelp/api_yelp_new_config'));

        $response = array();
        if (isset($accounts) && !is_null($accounts) && $accounts != "") {
            foreach ($accounts as $account)
                $response[] = array('value' => $account['id'], 'label' => $account['name']);
            return $response;
        }
    }

}
