<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Block_Adminhtml_Fieldsmap_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	protected $_fieldmap_id;
	protected $_module_name;
	
	public function __construct()
	{
		$this->_module_name = Mage::getSingleton('sugarcrm/connection')->getModuleName();
		$this->_fieldmap_id = Mage::registry('current_item_sugarcrm_field')->getId();
		
		parent::__construct();
		
		$this->_blockGroup = 'sugarcrm';
		$this->_controller = 'adminhtml_fieldsmap';
		$this->_mode = 'edit';
		
		$this->_removeButton('reset');
		$this->_updateButton('save', 'label', $this->__('Save Mapping'));
		$this->_updateButton('save', 'id', 'save_button');
		$this->_updateButton('delete', 'label', $this->__('Delete Mapping'));
		if(is_null($this->_fieldmap_id)) {
			$this->_removeButton('delete');
		}

		$this->_formInitScripts[] = '
			var fieldsMappingType = function() {
				return {
					disableField: function() {
						if($("fields_mapping_type'.Belitsoft_Sugarcrm_Model_Connection::SYNC_EVALCODE.'").checked) {
							$("mage_customer_field").disabled = true;
							$("custom_model").disabled = true;
							$("eval_code").disabled = false;
						} else if($("fields_mapping_type'.Belitsoft_Sugarcrm_Model_Connection::SYNC_CUSTOM.'").checked) {
							$("mage_customer_field").disabled = true;
							$("custom_model").disabled = false;
							$("eval_code").disabled = true;
						} else {
							$("mage_customer_field").disabled = false;
							$("custom_model").disabled = true;
							$("eval_code").disabled = true;
						}
					},
				}
			}();

			Event.observe(window, \'load\', function(){
				Event.observe($("fields_mapping_type'.Belitsoft_Sugarcrm_Model_Connection::SYNC_MAGEFIELD.'"), \'click\', fieldsMappingType.disableField);
				Event.observe($("fields_mapping_type'.Belitsoft_Sugarcrm_Model_Connection::SYNC_CUSTOM.'"), \'click\', fieldsMappingType.disableField);
				Event.observe($("fields_mapping_type'.Belitsoft_Sugarcrm_Model_Connection::SYNC_EVALCODE.'"), \'click\', fieldsMappingType.disableField);
			});
		';
	}

	public function getHeaderText()
	{
		if(is_null($this->_fieldmap_id)) {
			return $this->__('New Item Field');
		} else {
			return $this->__('Edit Item Field');
		}
	}

	public function getBackUrl()
	{
		return $this->getUrl('*/*/index', array('module'=>$this->_module_name));
	}
	
	public function getDeleteUrl()
	{
		return $this->getUrl('*/*/delete', array('module'=>$this->_module_name, 'id'=>$this->_fieldmap_id));
	}
	
	public function getHeaderCssClass()
	{
		return 'icon-head head-customer-groups';
	}
}
