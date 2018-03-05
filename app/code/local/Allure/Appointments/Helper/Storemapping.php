<?php
/**
 * 
 * @author allure
 *
 */
class Allure_Appointments_Helper_Storemapping extends Mage_Core_Helper_Abstract
{
	const XML_APPOINTMENTS_STORE_MAPPING = "appointments/general/storemapping";
	
	protected $_store_config_data = null;
	
	/**
	 * return array of store mapping conf. data
	 */
	public function getStoreMappingConfiguration(){
	    $storeConfigArr = array();
	    $this->_store_config_data = Mage::getStoreConfig(self::XML_APPOINTMENTS_STORE_MAPPING);
	    if(!empty($this->_store_config_data)){
	        $this->_store_config_data =  unserialize($this->_store_config_data);
	        unset($this->_store_config_data['stores'][0]);
	        foreach ($this->_store_config_data as $key=>$data){
	            $storeConfigArr[$key] = $data;
	            unset($storeConfigArr[$key][0]);
	        }
	        return $storeConfigArr;//$this->_store_config_data;
	    }
	    return array();
	}
}
	 