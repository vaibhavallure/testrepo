<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Block_Adminhtml_Fieldsmap_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected $_itemField = array();
	
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();

		$this->_itemField = $this->getItemField();
		
		$isNew = $this->_itemField->getId() ? false : true;
		
		$fieldset = $form->addFieldset('base_fieldset',
			array(
				'legend'    => $this->__('SugarCRM Fields Mapping')
			)
		);

		$fieldset->addField('sugarcrm_field',
			'select',
			array(
				'name'		=> 'sugarcrm_field',
				'label'		=> $this->__('SugarCRM Field'),
				'title'		=> $this->__('SugarCRM Field'),
				'required'	=> true,
				'options'	=> $this->_getFieldsArray(),
			)
		);
		
		$this->_checkFieldsMappingValue();
		
		$fieldset->addField('fields_mapping_type',
			'radios',
			array(
				'name'		=> 'fields_mapping_type',
				'label'		=> $this->__('Select fields mapping type'),
				'title'		=> $this->__('Select fields mapping type'),
				'values'	=> $this->_getFieldsMappingTypes(),
			)
		);
		
		$fieldset->addField('mage_customer_field',
			'select',
			array(
				'name'		=> 'mage_customer_field',
				'label'		=> $this->__('Magento Customer Field'),
				'title'		=> $this->__('Magento Customer Field'),
				'values'	=> $this->_getCustomerFields(),
				'disabled'	=> 1,
			)
		);
		
		$fieldset->addField('custom_model',
			'select',
			array(
				'name'		=> 'custom_model',
				'label'		=> $this->__('Custom Model'),
				'title'		=> $this->__('Custom Model'),
				'values'	=> $this->_getCustomModels(),
				'disabled'	=> 1,
			)
		);
		
		$fieldset->addField('eval_code',
			'textarea',
				array(
				'name'		=> 'eval_code',
				'label'		=> $this->__('PHP code'),
				'title'		=> $this->__('PHP code'),
				'style'		=> 'width: 500px;',
				'note'      => $this->__("Using of 'PHP code' field"),
				'disabled'	=> 1,
			)
		);
		
		
		switch($this->_checkFieldsMappingValue()) {
			case Belitsoft_Sugarcrm_Model_Connection::SYNC_EVALCODE:
				$form->getElement('eval_code')->setDisabled(0);
			break;
			
			case Belitsoft_Sugarcrm_Model_Connection::SYNC_CUSTOM:
				$form->getElement('custom_model')->setDisabled(0);
			break;
			
			default:
				$form->getElement('mage_customer_field')->setDisabled(0);
		}
		
		if(!$isNew) {
			$form->addField('id',
				'hidden',
				array(
					'name' => 'id'
				)
			);

			$form->getElement('sugarcrm_field')->setDisabled(1);
		}
		
		$form->addField('module_name',
			'hidden',
			array(
				'name' => 'module_name',
			)
		);
		
		$form->addValues($this->_itemField->getData());
		$form->setUseContainer(true);
		$form->setId('edit_form');
		$form->setMethod('post');
		$form->setAction($this->getSaveUrl());
		$this->setForm($form);
	}
	
	protected function _getFieldsArray()
	{
		$fieldsmap_items = Mage::getResourceModel('sugarcrm/fieldsmap_collection')->getItems();
		$reserved_sugar_fields = array();
		foreach($fieldsmap_items as $key=>$value) {
			$reserved_sugar_fields[$key] = $value->getData('sugarcrm_field');
		}
		
		$connection = Mage::getSingleton('sugarcrm/connection');
		$fields = $connection->getModuleFields();
		
		$label_title = $connection->getLabelTitle();
		$return = array();
		$id = $this->_itemField->getId();
		foreach($fields as $value=>$field) {
			if(($id || !in_array($value, $reserved_sugar_fields) && ($value != 'id'))) {
				$return[$value] = $field[$label_title].' ('.$value.')';
			}
		}
		
		natcasesort($return);
		
		return $return;
	}
	
	protected function _getCustomerFields()
	{
		$attributes = Mage::getModel('customer/customer')->getAttributes();
		$address_attrs = Mage::getModel('customer/address')->getAttributes();
		$return = array();
		$return[''] = $this->__('-- Please Select --');
		foreach($attributes as $attr_name=>$attribute) {
			$label = $attribute->getData('frontend_label');
			if(empty($label)) {
				continue;
			}
			
			$entry['value'] = $attr_name;
			$entry['label'] = $label;
			
			if($attr_name == 'default_billing' || $attr_name == 'default_shipping') {
				$entry['value'] = array();
				foreach($address_attrs as $ad_attr_name=>$ad_attr) {
					$ad_label = $ad_attr->getData('frontend_label');
					if(empty($ad_label)) {
						continue;
					}
					$addr_entry = array();
					$addr_entry['value'] = $attr_name.'|'.$ad_attr_name;
					$addr_entry['label'] = $ad_label;
					$entry['value'][] = $addr_entry;
				}
			}
			
			$return[] = $entry;
		}
		
		return $return;
	}
	
	protected function _getFieldsMappingTypes()
	{
		return Mage::getModel('sugarcrm/source_mappings')->toOptionArray();
	}
	
	protected function _getCustomModels()
	{
		$return = array();
		$return[] = array('value' => '', 'label' => $this->__('-- Please Select --'));
		$custom = Mage::getModel('sugarcrm/source_customs')->toOptionArray();
		$return = array_merge($return, $custom);
		
		return $return;
	}
	

	protected function _checkFieldsMappingValue()
	{
		$value = $this->_itemField->getFieldsMappingType();
		if(!$value || ($value != 'evalcode' && $value != 'magefield' && $value != 'custom')) {
			$value = 'magefield';
			$this->_itemField->setFieldsMappingType($value);
		}

		return $value;
	}
	
	public function getItemField()
	{
		return Mage::registry('current_item_sugarcrm_field');
	}

	public function getSaveUrl()
	{
		return $this->getUrl('*/*/save', array('module'=>Mage::getSingleton('sugarcrm/connection')->getModuleName(), 'id'=>$this->getItemField()->getId()));
	}
}
