<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Helper_Community extends Mage_Core_Helper_Abstract
{
	const CUSTOM_XML_PATH_NAME = 'custom';
	
	public function getCustomXmlPath()
	{
		return Mage::getConfig()->getModuleDir('etc', $this->_getModuleName()) . DS . self::CUSTOM_XML_PATH_NAME;
	}
	
	public function getEnabledBeans()
	{
		static $beans;
		
		if(is_null($beans)) {
			$beans = Mage::getModel('sugarcrm/config')->getBeans();
			if(empty($beans)) {
				$beans = array();
			}
		}
		
		return $beans;
	}
	
	public function getEnabledBeansArray()
	{
		static $beans;
		
		if(is_null($beans)) {
			$beans = array_keys($this->getEnabledBeans());
		}
		
		return $beans;
	}
	
	public function isBeanEnabled($bean)
	{
		if(in_array(Belitsoft_Sugarcrm_Model_Connection::ACCOUNTS, Mage::helper('sugarcrm')->getEnabledBeansArray())) {
			return true;
		} else {
			return false;
		}
	}

	public function getSugarOrderBean()
	{
		static $user_order_to_sugarcrm = null;

		if(is_null($user_order_to_sugarcrm)) {
			$user_order_to_sugarcrm = strval(Mage::getModel('sugarcrm/config')->getConfigData('user_order_to_sugarcrm'));
			if(!$user_order_to_sugarcrm) {
				$user_order_to_sugarcrm = Belitsoft_Sugarcrm_Model_Connection::OPPORTUNITIES;
			}
		}

		return $user_order_to_sugarcrm;
	}

	public function getStages($bean=null)
	{
		$stages = Mage::registry('sugarcrm_stage');
		if(!$stages) {
			if(!$bean) {
				$bean = $this->getSugarOrderBean();
			}
			$stages = Mage::getResourceModel('sugarcrm/stages_collection')->addFilter('bean', $bean)->addOrder('main_table.stage_id', 'ASC')->getItems();

			Mage::register('sugarcrm_stage', $stages);
		}

		return (array)$stages;
	}

	public function getEnabledStages()
	{
		static $stages_keys = null;

		if(is_null($stages_keys)) {
			$stages_keys = array();
			$stages = $this->getStages();

			foreach($stages as $stage) {
				$stages_keys[$stage->getMageStatus()] = $stage->getSugarStage();
			}
		}

		return $stages_keys;
	}

	public function isCartSynchEnabled()
	{
		return array_key_exists(Belitsoft_Sugarcrm_Model_Stages::SAVE_CART_STAGE, $this->getEnabledStages());
	}

	public function isCheckoutSynchEnabled()
	{
		return array_key_exists(Belitsoft_Sugarcrm_Model_Stages::CHECKOUT_STAGE, $this->getEnabledStages());
	}
	
	public function getCustomerAccount($customer_id=null)
	{
		$config_account_id = Mage::getModel('sugarcrm/config')->getConfigData('sugarcrm_account_id');
		
		if(!$config_account_id && $customer_id
			&& ($synchmap = Mage::getModel('sugarcrm/synchmap')->loadCustomerSynchData($customer_id, Belitsoft_Sugarcrm_Model_Connection::ACCOUNTS))
			&& ($sid = $synchmap->getSid()))
		{
			return $sid;
		} else {
			return $config_account_id;
		}
	} 
	
	public function isBridgeDisabled()
	{
		static $isDisabled;
		
		if(is_null($isDisabled)) {
			$isDisabled = Mage::getModel('sugarcrm/config')->getConfigData('disable_bridge') ? true : false;
		}
		
		return $isDisabled;
	}
	
	public function showErrors()
	{
		static $show;
		
		if(is_null($show)) {
			if(Mage::app()->getStore()->isAdmin()) {
				$show = Mage::getModel('sugarcrm/config')->getConfigData('show_errors_on_backend') ? true : false;
			} else {
				$show = Mage::getModel('sugarcrm/config')->getConfigData('show_errors_on_frontend') ? true : false;
			}
		}
		
		return $show;
	}
}