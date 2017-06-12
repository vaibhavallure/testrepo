<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Block_Adminhtml_Synchtable_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	/**
	 * Preperation of current form
	 *
	 * @return Belitsoft_Survey_Block_Adminhtml_Category_Edit_Form
	 */
	protected function _prepareForm()
	{
		$model = Mage::registry('sugarcrm_synchmap_model');
		
		$form = new Varien_Data_Form();
		
		$isNew = $model->getId() ? false : true;
		
		$fieldset_mage = $form->addFieldset('mage_fieldset',
			array(
				'legend'	=> $this->__('Magento Synch Data'), 
				'class'		=> 'fieldset-wide'
			)
		);
		
		$fieldset_mage->addField('model',
			'select', 
			array(
				'name'		=> 'model',
				'label'		=> $this->__('Magento Model'),
				'title'		=> $this->__('Magento Model'), 
				'values'	=> $this->_getMagentoModelsArray(),
			)
		);
		
		$fieldset_mage->addField('cid',
			'text',
			array(
				'name'		=> 'cid',
				'label'		=> $this->__('Magento ID'), 
				'title'		=> $this->__('Magento ID'), 
				'required'	=> true,
			)
		);
		
		
		$fieldset_sugar = $form->addFieldset('sugar_fieldset',
			array(
				'legend'	=> $this->__('SugarCRM Synch Data'), 
				'class'		=> 'fieldset-wide'
			)
		);
		
		$fieldset_sugar->addField('bean',
			'select', 
			array(
				'name'		=> 'bean',
				'label'		=> $this->__('SugarCRM Module'),
				'title'		=> $this->__('SugarCRM Module'), 
				'values'	=> $this->_getSugarcrmModulesArray(),
			)
		);
 		
		$fieldset_sugar->addField('sid',
			'text',
			array(
				'name'		=> 'sid',
				'label'		=> $this->__('SugarCRM Module ID'), 
				'title'		=> $this->__('SugarCRM Module ID'), 
				'required'	=> true,
			)
		);
		
		if(!$isNew) {
			$form->addField('id',
				'hidden',
				array(
					'name' => 'id'
				)
			);
			
			$form->getElement('model')->setDisabled(1);
			$form->getElement('bean')->setDisabled(1);
		}
		

		$form->setValues($model->getData());
		$form->setUseContainer(true);
		$form->setAction($this->getSaveUrl());
		$form->setId('edit_form');
		$form->setMethod('post');
		
		$this->setForm($form);

		return parent::_prepareForm();
	}
	
	protected function _getMagentoModelsArray()
	{ 
		return Mage::getModel('sugarcrm/source_models')->toOptionArray();
	}
	
	protected function _getSugarcrmModulesArray()
	{ 
		return Mage::getModel('sugarcrm/source_beans')->toOptionArray();
	}
		
	public function getSaveUrl()
	{
		return $this->getUrl('*/*/save');
	}
}
