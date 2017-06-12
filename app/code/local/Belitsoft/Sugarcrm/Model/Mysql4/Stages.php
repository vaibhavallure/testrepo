<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Model_Mysql4_Stages extends Mage_Core_Model_Mysql4_Abstract
{
	protected function _construct()
	{
		$this->_init('sugarcrm/stages', 'stage_id');
	}

	public function saveStages($bean, $stages)
	{
		$condition = $this->_getWriteAdapter()->quoteInto('bean = ?', $bean);
		$this->_getWriteAdapter()->delete($this->getMainTable(), $condition);

		foreach($stages as $stage_data) {
			if(empty($stage_data['delete']) && !empty($stage_data['mage_status']) && !empty($stage_data['sugar_stage'])) {
				unset($stage_data['delete']);
				$stage_data['bean'] = $bean;
				$this->_getWriteAdapter()->insert(
					$this->getMainTable(),
					$stage_data
				);
			}
		}
	}
}
