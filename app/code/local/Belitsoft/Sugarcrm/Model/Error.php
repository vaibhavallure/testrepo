<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Model_Error extends Mage_Core_Model_Abstract
{
	const TYPE_CUSTOMER	= 'customer';
	const TYPE_QUOTE	= 'quote';
	const TYPE_ORDER	= 'order';
	
	const STATUS_NEEDRESYNC	= 0;
	const STATUS_RESYNCED	= 1;
	const STATUS_CANTSYNCED	= 2;
	

	protected function _construct()
	{
		parent::_construct();

		$this->_init('sugarcrm/error');
	}
	
	public function addError($type, $function, $object, $err)
	{
		if(!$id = $object->getId()) {
			return; 
		}
		
		$this->setType($type);
		$this->setOperation($function);
		$this->setEntityId($id);
		$this->setError($err->getMessage());
		if($params = serialize($this->_getErrorParams($type))) {
			$this->setParams($params);
		}
		$this->setCreationDate(date('Y-m-d H:i:s'));
		
		$this->save();
	}
	
	public function addErrorParams($add_param, $type)
	{
		$add_param = (array)$add_param;
		$param = (array)Mage::registry('sugarcrm_error_params_'.$type);
		$param = array_merge($param, $add_param);
		Mage::unregister('sugarcrm_error_params');
		Mage::register('sugarcrm_error_params', $param);
	}
	
	protected function _getErrorParams($type)
	{
		return (array)Mage::registry('sugarcrm_error_params_'.$type);
	}	
}
