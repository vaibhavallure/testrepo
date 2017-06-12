<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */
 
class Belitsoft_Sugarcrm_Model_Mysql4_Synchmap_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	protected function _construct()
	{
		$this->_init('sugarcrm/synchmap');

		$this->_map['fields']['customer_lastname'] = 'customer_lastname_table.value';
		$this->_map['fields']['customer_firstname'] = 'customer_firstname_table.value';
	}

	/**
	 * Adds customer info to select
	 *
	 * @return  Mage_Newsletter_Model_Mysql4_Subscriber_Collection
	 */
	public function showCustomerInfo()
	{
		$customer = Mage::getModel('customer/customer');
		/* @var $customer Mage_Customer_Model_Customer */
		$firstname  = $customer->getAttribute('firstname');
		$lastname   = $customer->getAttribute('lastname');

		$this->getSelect()
			->joinLeft(
				array('customer_lastname_table'=>$lastname->getBackend()->getTable()),
				'customer_lastname_table.entity_id = main_table.cid
				 AND customer_lastname_table.attribute_id = '.(int) $lastname->getAttributeId(),
				array('customer_lastname'=>'value')
			)->joinLeft(
				array('customer_firstname_table'=>$firstname->getBackend()->getTable()),
				'customer_firstname_table.entity_id = main_table.cid
				 AND customer_firstname_table.attribute_id = '.(int) $firstname->getAttributeId(),
				array('customer_firstname'=>'value')
			);
		
		return $this;
	}
	
	/**
	 * Add Filter by survey
	 * 
	 * @param string $bean SugarCRM bean
	 * @return Belitsoft_Sugarcrm_Model_Mysql4_Synchmap_Collection
	 */
	public function addBeanFilter($bean)
	{
		$bean = (string)$bean;
		
		$this->getSelect()
			->where(
				'main_table.bean IN (?)',
				array (
					'', 
					$bean
				)
			);
		
		return $this;
	}
	
	/**
	 * Get SugarCRM Bean's list
	 * 
	 * @return array
	 */
	public function getBeans()
	{
		$select = $this->getConnection()
			->select()
			->from(
				$this->getMainTable(),
				'bean'
			)->group(
				'bean'
			);

		$return = array();
		if($result = $this->getConnection()->fetchCol($select)) {
			foreach($result as $item) {
				$return[$item] = $item;
			}
		}
		
		return $return;
	}
	
	/**
	 * Get synchronized customers
	 * 
	 * @return array
	 */
	public function getCustomersIds()
	{
		return $this->getIds(array('Contacts', 'Leads'));
	}
		
	/**
	 * Get synchronized orders
	 * 
	 * @return array
	 */
	public function getOrderIds()
	{
		return $this->getIds(array(Mage::helper('sugarcrm')->getSugarOrderBean()), Belitsoft_Sugarcrm_Model_Synchmap::ORDER_MODEL);
	}
		
	/**
	 * Get synchronized quotes
	 * 
	 * @return array
	 */
	public function getQuoteIds()
	{
		return $this->getIds(array(Mage::helper('sugarcrm')->getSugarOrderBean()), Belitsoft_Sugarcrm_Model_Synchmap::QUOTE_MODEL);
	}
	
	public function getIds($beans=array(), $model=array())
	{
		$select = $this->getConnection()
			->select()
			->from(
				$this->getMainTable(),
				'cid'
			)
			->where(
				'bean IN (?)',
				$beans
			)
			->group(
				'cid'
			);
			
			if(!empty($model)) {
				$select->where(
					'model IN (?)',
					$model
				);
			}


		$result = (array)$this->getConnection()->fetchCol($select);
		
		return $result;
	}
}