<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */
 
class Belitsoft_Sugarcrm_Model_Operations extends Mage_Core_Model_Abstract
{
	const OPERATION_DISABLE = 0;
	const OPERATION_ENABLE = 1;
	const OPERATION_ENABLE_WITH_CONDITION = 2;
	
	
	protected function _construct()
	{
		parent::_construct();

		$this->_init('sugarcrm/operations');
	}

	/**
	 * Set operation item
	 *
	 * @return Belitsoft_Sugarcrm_Model_Operations
	 */
	public function setOperationItem($bean, $operation, $enable, $condition='')
	{
		$this->setData('bean', $bean);
		$this->setData('operation', $operation);
		$this->setData('enable', $enable);
		$this->setData('condition', $condition);		
		
		return $this;
	}
    
	/**
	 * Get SugarBeans array
	 *
	 * @return array
	 */
	public function getBeansArray()
	{
		$_beans = Mage::getSingleton('sugarcrm/config')->getBeans();
		$result = array();
		foreach ($_beans as $bean => $info) {
			$result[$bean] = $info['label'];
		}
		
		return $result;
	}
	
	/**
	 * Get user operations
	 *
	 * @return array
	 */
	public function getOperationsArray()
	{
		$_opers = Mage::getSingleton('sugarcrm/config')->getUserOperations();
		$result = array();
		foreach ($_opers as $oper => $info) {
			$result[$oper] = $info['label'];
		}
		
		return $result;
	}
	
	
	/**
	 * Get enabled operations
	 *
     * @param	string	$operation_type	Operation name
	 * @return	array
	 */
	public function getEnabledOperations($operation_type=null)
	{
		Mage::unregister('sugarcrm_bean_operation_conditions');
	
		$condition = array();
		$bean_operations = array();
		$collection = Mage::getResourceModel('sugarcrm/operations_collection')->load();
		foreach($collection as $attribute) {
			$data = $attribute->getData();
			if(!empty($operation_type) && is_string($operation_type)) {
				if($data['operation'] == $operation_type) {
					$bean_operations[$data['bean']] = $data['enable'];
				}
			} else {
				$bean_operations[$data['bean']][$data['operation']] = $data['enable'];
			}
					
			$condition[$data['bean']][$data['operation']] = $data['condition'];
		}
		
		Mage::register('sugarcrm_bean_operation_conditions', $condition);
				
		return $bean_operations;
	}
	
	public function getConditions($bean=null, $operation=null)
	{
		if(is_array(Mage::registry('sugarcrm_bean_operation_conditions'))) {
			$condition = Mage::registry('sugarcrm_bean_operation_conditions');
			
			if(is_null($bean) && is_null($operation)) {
				return $condition;
			
			} else if($bean && $operation) {
				if(!empty($condition[$bean]) && is_array($condition[$bean]) && array_key_exists($operation, $condition[$bean])) {
					return $condition[$bean][$operation];
				}

				return '';

			} else if($bean) {
				if(array_key_exists($bean, $condition)) {
					return $condition[$bean];
				}

				return array();

			} else {
				return null;
			}
		
		} else {
			$this->getEnabledOperations();
			
			return $this->getConditions($bean, $operation);
		}
	}

	public function truncate()
	{
		$this->_getResource()->truncate();
	}
}