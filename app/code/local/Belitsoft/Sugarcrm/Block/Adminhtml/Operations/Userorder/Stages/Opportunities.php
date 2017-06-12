<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Block_Adminhtml_Operations_Userorder_Stages_Opportunities extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element
{
	public function __construct()
	{
		$this->setTemplate('sugarcrm/operations/userorder/stages/opportunities.phtml');
	}

	public function getStageType()
	{
		return 'opportunities';
	}

	public function getStatusArray()
	{
		return $this->getParentBlock()->getStatusArray();
	}

	public function getStageArray()
	{
		return $this->getParentBlock()->getStageArray();
	}

	public function getStageId()
	{
		return $this->getParentBlock()->getStageId();
	}

	public function getStageTopId()
	{
		return $this->getParentBlock()->getStageTopId();
	}

	public function getStageName()
	{
		return $this->getParentBlock()->getStageName();
	}

	public function getAddButtonHtml()
	{
		return $this->getParentBlock()->getAddButtonHtml();
	}

	public function getDeleteButtonHtml()
	{
		return $this->getParentBlock()->getDeleteButtonHtml();
	}

	public function getMageStatusString()
	{
		return 'mage_status';
	}

	public function getSugarStageString()
	{
		return 'sugar_stage';
	}

	public function getStageOptionField()
	{
		$field = new Varien_Data_Form_Element_Select();
		$field->setName($this->getStageName().'[{{index}}]['.$this->getMageStatusString().']')
			->setId($this->getStageId().'_{{index}}_'.$this->getMageStatusString())
			->setStyle('width:auto!important;')
			->setForm(new Varien_Data_Form())
			->setValues($this->getStatusArray())
			->setAfterElementHtml($this->getIdHiddenField().$this->getDeleteHiddenField());

		return $this->toJSTmplHtml($field);
	}

	public function getSugarStageField()
	{
		$field = new Varien_Data_Form_Element_Select();
		$field->setName($this->getStageName().'[{{index}}]['.$this->getSugarStageString().']')
			->setId($this->getStageId().'_{{index}}_'.$this->getSugarStageString())
			->setStyle('width:auto!important;')
			->setValues($this->getStageArray())
			->setForm(new Varien_Data_Form());

		return $this->toJSTmplHtml($field);
	}

	public function getDeleteHiddenField()
	{
		$hidden = new Varien_Data_Form_Element_Hidden();
		$hidden->setName($this->getStageName().'[{{index}}][delete]')
			->addClass('delete')
			->setNoSpan(true)
			->setForm(new Varien_Data_Form());

		return $this->toJSTmplHtml($hidden);
	}

	public function getIdHiddenField()
	{
		$hidden = new Varien_Data_Form_Element_Hidden();
		$hidden->setName($this->getStageName().'[{{index}}][stage_id]')
			->setId($this->getStageId().'_{{index}}_stage_id')
			->setNoSpan(true)
			->setForm(new Varien_Data_Form());

		return $this->toJSTmplHtml($hidden);
	}

	public function toJSTmplHtml($el)
	{
		return str_replace(array("\r","\n"), '', $el->toHtml());
	}
}
