<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Block_Adminhtml_Operations_Userorder_Stages extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element
{
	public function __construct()
	{
		$this->setTemplate('sugarcrm/operations/userorder/stages.phtml');
	}

	protected function _prepareLayout()
	{
		$this->setChild('add_button',
			$this->getLayout()
				->createBlock('adminhtml/widget_button')
				->setData(
					array(
						'label'		=> $this->__('Add'),
						'class'		=> 'add',
						'id'		=> 'add_new_stage',
						'on_click'	=> 'mpSugarcrmStages.add()'
					)
				)
		);

		$this->setChild('delete_button',
			$this->getLayout()
				->createBlock('adminhtml/widget_button')
				->setData(
					array(
						'label'		=> $this->__('Remove'),
						'class'		=> 'delete delete-product-option',
						'on_click'	=> 'mpSugarcrmStages.remove(event)'
					)
				)
		);

		$stage_layout = $this->getStageType();
		if(!$stage_layout) {
			$stage_layout = 'empty';
		}

		$stage_block = $this->getLayout()->createBlock('sugarcrm/adminhtml_operations_userorder_stages_'.strtolower($stage_layout));
		if(!$stage_block) {
			$this->getLayout()->createBlock('sugarcrm/adminhtml_operations_userorder_stages_empty');
		}

		$stage_block->setStages(Mage::registry('sugarcrm_stage'));

		$this->setChild('stages', $stage_block);

		parent::_prepareLayout();
	}

	public function getStageId()
	{
		return 'sugarcrm_stage';
	}

	public function getStageTopId()
	{
		return 'sugarcrm_stages_top';
	}

	public function getStageName()
	{
		return 'stages';
	}

	public function getAddButtonHtml()
	{
		return $this->getChildHtml('add_button');
	}

	public function getDeleteButtonHtml()
	{
		return $this->getChildHtml('delete_button');
	}

	public function getStages()
	{
		return $this->getChildHtml('stages');
	}

	public function getStageArray()
	{
		$stages = Mage::getSingleton('sugarcrm/connection')->getStages($this->getStageType());

		return $stages;
	}

	public function getStatusArray()
	{
		$statuses = Mage::getSingleton('sales/order_config')->getStatuses();
		$statuses = array_merge(
			array(
				Belitsoft_Sugarcrm_Model_Stages::SAVE_CART_STAGE	=> Mage::helper('sugarcrm')->__('Waiting for checkout'),
				Belitsoft_Sugarcrm_Model_Stages::CHECKOUT_STAGE		=> Mage::helper('sugarcrm')->__('Checkout was started')
			),
			$statuses
		);

		return $statuses;
	}

	protected function _toJson($data)
	{
		return Mage::helper('core')->jsonEncode($data);
	}
}
