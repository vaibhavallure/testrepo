<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */
 
class Belitsoft_Sugarcrm_Model_Mysql4_Fieldsmap_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	protected $_grid_view = false;

	protected $_connection;
	protected $_module_fields;
	protected $_module_name;
	
	
	protected function _construct()
	{
		$this->_connection = Mage::getSingleton('sugarcrm/connection');
		$this->_module_name = $this->_connection->getModuleName();
		if(empty($this->_module_name)) {
			throw Mage::exception('Belitsoft_Sugarcrm', 'SugarCRM module name not defined');			
		}
		
		$this->_init('sugarcrm/fieldsmap');
    }
    
    public function getSugarConnection()
    {
    	return $this->_connection;
    }

	protected function _initSelect()
	{
		parent::_initSelect();
		
		$this->_setModule();

		return $this;
	}
    
	protected function _afterLoad()
	{
		parent::_afterLoad();
		
		if(!$this->_grid_view) {
			return $this;
		}
		
		$this->_module_fields = $this->_connection->getModuleFields();
		if(empty($this->_module_fields) || !is_array($this->_module_fields)) {
			return $this;
		}
		
		$customer_attributes = Mage::getModel('customer/customer')->getAttributes();
		$customer_address_attrs = Mage::getModel('customer/address')->getAttributes();
		
		foreach ($this->_items as $item) {
			$sugarcrm_field = $item->getData('sugarcrm_field');
			if(!empty($sugarcrm_field) && array_key_exists($sugarcrm_field, $this->_module_fields)) {
				$item->setData('sugarcrm_label', $this->_module_fields[$sugarcrm_field][$this->_connection->getLabelTitle()]);
				$item->setData('sugarcrm_type', $this->_module_fields[$sugarcrm_field][$this->_connection->getTypeTitle()]);
				$options = null;
				$field_options = $this->_module_fields[$sugarcrm_field][$this->_connection->getOptionsTitle()];
				if(!empty($field_options) && is_array($field_options)) {
					$options = implode(', ', $field_options);
				}
				$item->setData('sugarcrm_options', $options);
			}
			
			$fields_mapping_type = $item->getData('fields_mapping_type');
			if($fields_mapping_type == Belitsoft_Sugarcrm_Model_Connection::SYNC_EVALCODE) {
				$item->setData('mage_customer_field', null);
				$item->setData('custom_model', null);
				
				$eval_code = $item->getData('eval_code');
				if(strlen($eval_code) > 100) {
					$item->setData('eval_code', substr($eval_code, 0, 100).' ...');
				}

			} else if($fields_mapping_type == Belitsoft_Sugarcrm_Model_Connection::SYNC_CUSTOM) {
				$item->setData('mage_customer_field', null);
				$item->setData('eval_code', null);
				
				$custom_model = $item->getData('custom_model');
				$customs = Mage::getModel('sugarcrm/source_customs')->toOptionHash();
				if(array_key_exists($custom_model, $customs)) {
					if(strlen($customs[$custom_model]) > 40) {
						$item->setData('custom_model', substr($customs[$custom_model], 0, 35).' ...');
					} else {
						$item->setData('custom_model', $customs[$custom_model]);
					}
				} else {
					$item->setData('custom_model', 'Not found');
				}
			} else {
				$item->setData('eval_code', null);
				$item->setData('custom_model', null);
				
				$magento_field = $item->getData('mage_customer_field');
				if(!empty($magento_field)) {
					$attr_names = explode('|', $magento_field);
					if(count($attr_names)==2) {
						if(array_key_exists($attr_names[0], $customer_attributes) && array_key_exists($attr_names[1], $customer_address_attrs)) {
							$item->setData('mage_customer_field_label', $customer_address_attrs[$attr_names[1]]->getData('frontend_label').' ('.$customer_attributes[$attr_names[0]]->getData('frontend_label').')');
						}
					} else {
						if(array_key_exists($attr_names[0], $customer_attributes)) {
							$item->setData('mage_customer_field_label', $customer_attributes[$attr_names[0]]->getData('frontend_label'));
						}	
					}
				}
			}
		}
		
#		Mage::log($this->_items);
	}
	
	protected function _setModule()
	{
		$this->getSelect()->where('module_name=?', $this->_module_name);
		return $this;
	}
	
	public function setGridView($grid_view=true)
	{
		$this->_grid_view = $grid_view;
	}
	
	public function getItemsByName()
	{
		$items = parent::getItems();
		
		$return = array();
		foreach($items as $key=>$value) {
			$return[$value->getData('sugarcrm_field')] = $value->getData();
		}
		
		return $return;
	}
}
