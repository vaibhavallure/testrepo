<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Model_Synchmap extends Mage_Core_Model_Abstract
{
	const QUOTE_MODEL = 'quote';
	const ORDER_MODEL = 'order';

	protected function _construct()
	{
		parent::_construct();

		$this->_init('sugarcrm/synchmap');
	}

	/**
	 * Load Customer synch by bean name
	 *
	 * @param 	int 	$customer_id
	 * @param 	string 	$bean
	 * @return	string	SugarCRM Bean ID
	 */
	public function loadCustomerSynchData($magento_id, $bean, $model = null)
	{
		$read = $this->_getResource()->getReadConnection();
		$select = $read->select();
		$select->from($this->_getResource()->getMainTable())
			->where('`cid` = ?', (int)$magento_id)
			->where("`bean` = ?", $bean);

		if($model == self::QUOTE_MODEL) {
			$select->where("`model` = ?", $model);
		}

		$data = $read->fetchRow($select);

		$this->setData($data);

		return $this;
	}
}
