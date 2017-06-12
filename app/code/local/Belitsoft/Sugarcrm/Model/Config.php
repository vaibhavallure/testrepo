<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Model_Config extends Mage_Core_Model_Abstract
{
	const ORDER_SYNCH_ENABLE	= 0;
	const ORDER_SYNCH_DISABLE	= 1;
	const ORDER_SYNCH_ENABLE_WITH_CONDITION	= 2;
	
	protected function _construct()
	{
		parent::_construct();

		$this->_init('sugarcrm/config');
	}

	/**
	 * Set config item
	 *
	 * @return Belitsoft_Sugarcrm_Model_Config
	 */
	public function setConfigData($name, $value)
	{
		$this->setData('name', $name);
		$this->setData('value', $value);

		return $this;
	}

	/**
	 *  Return config var
	 *
	 *  @param	string $key Var path key
	 *  @param	int $storeId Store View Id
	 *  @return	  mixed
	 */
	public function getConfigData($key, $section=null, $storeId = null)
	{
		if (!is_null($section) && !$this->hasData($key)) {
			$value = Mage::getStoreConfig('sugarcrm/' . $section . '/' . $key, $storeId);
			$this->setData($key, $value);
		} else {
			$read = $this->_getResource()->getReadConnection();
			$select = $read->select();
			$select->from($this->_getResource()->getMainTable(), array('value'))
				->where('`name` = ?', $key);
			$value = $read->fetchOne($select);

			$this->setData($key, $value);
		}

		return $this->getData($key);
	}

	/**
	 * Default SugarCRM SOAP server.
	 *
	 * @param int $storeId
	 * @return string
	 */
	public function getSOAPServer($storeId = null)
	{
		return $this->getConfigData('sugarcrm_soap_server', 'connection', $storeId);
	}

	/**
	 * Default SugarCRM SOAP target namespace.
	 *
	 * @param int $storeId
	 * @return string
	 */
	public function getSOAPNamespace($storeId = null)
	{
		return $this->getConfigData('sugarcrm_soap_namespace', 'connection', $storeId);
	}

	/**
	 * Get SugarCRM affected beans.
	 *
	 * @param int $storeId
	 * @return array
	 */
	public function getAplicationName($storeId = null)
	{
		return $this->getConfigData('sugarcrm_soap_application_name', 'connection', $storeId);
	}

	/**
	 * Specify how the SOAP client serialise the message.
	 *
	 * @param int $storeId
	 * @return array
	 */
	public function getSOAPUse($storeId = null)
	{
		return array(
			SOAP_ENCODED => array(
				'label'	=> Mage::helper('sugarcrm')->__('Encoded'),
				'value'	=> SOAP_ENCODED
			),

			SOAP_LITERAL => array(
				'label'	=> Mage::helper('sugarcrm')->__('Literal'),
				'value'	=> SOAP_LITERAL
			),
		);
	}

	/**
	 * Types of the style of SOAP call.
	 *
	 * @param int $storeId
	 * @return array
	 */
	public function getSOAPStyle($storeId = null)
	{
		return array(
			SOAP_RPC => array(
				'label'	=> Mage::helper('sugarcrm')->__('RPC'),
				'value'	=> SOAP_RPC
			),

			SOAP_DOCUMENT => array(
				'label'	=> Mage::helper('sugarcrm')->__('Document'),
				'value'	=> SOAP_DOCUMENT
			),
		);
	}

	/**
	 * Get SugarCRM affected beans.
	 *
	 * @param int $storeId
	 * @return array
	 */
	public function getBeans($storeId = null)
	{
		return $this->getConfigData('affected_beans', 'beans', $storeId);
	}

	/**
	 * Get SugarCRM user operations.
	 *
	 * @param int $storeId
	 * @return array
	 */
	public function getUserOperations($storeId = null)
	{
		return $this->getConfigData('user_operations', 'beans', $storeId);
	}

	/**
	 * Get synch state of user orders.
	 *
	 * @return string
	 */
	public function isEnabledUserOrdersSynch($storeId = null)
	{
		return $this->getConfigData('enable_user_order_to_sugarcrm', null, $storeId);
	}

	/**
	 * Get synch state of user orders.
	 *
	 * @return string
	 */
	public function userOrdersSynchCondition($storeId = null)
	{
		return $this->getConfigData('enable_user_order_condition', null, $storeId);
	}
}