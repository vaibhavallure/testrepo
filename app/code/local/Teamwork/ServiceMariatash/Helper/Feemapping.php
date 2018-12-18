<?php

class Teamwork_ServiceMariatash_Helper_Feemapping extends Mage_Core_Helper_Abstract
{
    public function getServiceFeeMapping()
    {
		$db = Mage::getModel('teamwork_service/adapter_db');
        
        $query = "SELECT entity_id, name FROM {$db->getTable('service_setting_shipping')} GROUP BY name";
        $settingShipings = $db->getResults($query);
        
        $options = array();
        $options[] = array(
            'label' => '',
            'value' => ''
        );
        foreach ($settingShipings as $shipping) {
            $options[] = array(
                'label' => $shipping['name'],
                'value' => $shipping['entity_id'],
            );
        }
        return $options;
    }
	
	public function getServiceFee()
    {
		$db = Mage::getModel('teamwork_service/adapter_db');
        
        $query = "SELECT fee_id, code FROM {$db->getTable('service_fee')} WHERE global_level = 1 AND item_level = 1";
        $serviceFee = $db->getResults($query);
        
        $options = array();
        $options[] = array(
            'label' => '',
            'value' => ''
        );
        foreach ($serviceFee as $fee) {
            $options[] = array(
                'label' => $fee['code'],
                'value' => $fee['fee_id'],
            );
        }
        return $options;
    }
	
	public function getServiceFeeMappingList()
    {
		$db = Mage::getModel('teamwork_service/adapter_db');
        
        $query = "SELECT entity_id, name FROM {$db->getTable('service_setting_shipping')} GROUP BY name";
        $settingShipings = $db->getResults($query);
        
        $output = array();
        
        foreach($settingShipings as $value)
        {
            $output[$value['entity_id']] = $value['name'];
        }
        return $output;
    }
	
	public function getServiceFeeList()
    {
		$db = Mage::getModel('teamwork_service/adapter_db');
        
        $query = "SELECT fee_id, code FROM {$db->getTable('service_fee')}";
        $settingShipings = $db->getResults($query);
        
        $output = array();
        
        foreach($settingShipings as $value)
        {
            $output[$value['fee_id']] = $value['code'];
        }
        return $output;
    }
}