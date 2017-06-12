<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Block_Adminhtml_Operations_Form extends Mage_Adminhtml_Block_Widget_Form
{
	const USER_ORDER_TO_SUGARCRM = 'user_order_to_sugarcrm';
	const STAGES_DETAILS = 'stages_details';
	
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();

		$form->addField('css_before',
			'note',
			array(
				'text' =>
					'<style>.hidden{display:none;}</style>'
					. "
					<script>
					function setRadioOnclick(areaId, radioName) {
						$$('[name=\"'+radioName+'\"]').each(function(el){
							el.setAttribute('onclick', \"toggleConditionArea('\"+areaId+\"', \"+el.value+\")\");
							el.onclick = Function(\"toggleConditionArea('\"+areaId+\"', \"+el.value+\")\");
						});
					}
					
					function toggleConditionArea(areaId, conditionType) {
						if(conditionType == 2) {
							$(areaId).removeClassName('hidden');
						} else {
							$(areaId).addClassName('hidden');
						}
						
					}
					</script>
					",
			)
		);

		$operData = $this->getOperationsData();

		$fieldsetRenderer = Mage::getBlockSingleton('sugarcrm/adminhtml_system_config_form_fieldset');
        $fieldsetRenderer->setForm($this); 
		
		$sugar_operations = $this->_getOperationsArray();
		$sugar_objects = $this->_getBeansArray();
		$expanded = true;
		foreach($sugar_objects as $bean_value=>$bean_name) {
			$fieldset_bean_data = array();
			$fieldset_bean_data['legend'] = $this->__($bean_name);
			if($expanded) {
				$fieldset_bean_data['expanded'] = $expanded;
				$expanded = false;
			}
			$fieldset_bean = $form->addFieldset('sugarcrm_operation_'.$bean_value . '_fieldset', $fieldset_bean_data)->setRenderer($fieldsetRenderer);

			foreach($sugar_operations as $oper_value=>$oper_name) {
				$oper = empty($operData[$bean_value][$oper_value]) ? Belitsoft_Sugarcrm_Model_Operations::OPERATION_DISABLE : intval($operData[$bean_value][$oper_value]);

				$sugarcrm_oper_element = $fieldset_bean->addField('sugarcrm_oper_'.$bean_value.'_'.$oper_value,
					'radios',
					array(
						'label'		=> $this->__($oper_name),
						'title'		=> $this->__($oper_name),
						'name'		=> 'sugarcrm_oper_'.$bean_value.'_'.$oper_value,
						'value'		=> $oper,
						'values'	=> $this->_getOptionsArray(),
					)
				);

				$condition = Mage::getModel('sugarcrm/operations')->getConditions($bean_value, $oper_value);
 				$sugarcrm_oper_element->setAfterElementHtml($this->_getConditionHtml($sugarcrm_oper_element, $bean_value, $oper_value, $condition));
			}
		}


		$fieldset_order = $form->addFieldset('sugarcrm_operation_order_fieldset', array(
			'legend'	=> $this->__('User orders'),
		));
		$fieldset_order->setRenderer($fieldsetRenderer);

		$enable_user_order_to_sugarcrm = $fieldset_order->addField('enable_user_order_to_sugarcrm',
			'radios',
			array(
				'label'		=> $this->__('Enabled'),
				'title'		=> $this->__('Enabled'),
				'name'		=> 'enable_user_order_to_sugarcrm',
				'value'		=> $this->_getEnabledUserOrders(),
				'values'	=> $this->_getYesNoArray()
			)
		);
		$condition = $this->_getUserOrdersCondition();
		$condition_html = $this->_getConditionHtml($enable_user_order_to_sugarcrm, 'order', '', $condition);
		$enable_user_order_to_sugarcrm->setAfterElementHtml($condition_html);
		

		/* $fieldset_order->addField('enable_user_order_condition',
			'textarea',
			array(
				'label'		=> $this->__('Condition'),
				'title'		=> $this->__('Condition'),
				'name'		=> 'enable_user_order_condition',
				'value'		=> $this->_getUserOrdersCondition(),
				'values'	=> $this->_getYesNoArray(),
				'note'		=> $this->__('Insert condition here')
			)
		); */

		$fieldset_order->addField(self::USER_ORDER_TO_SUGARCRM,
			'select',
			array(
				'label'		=> $this->__('User orders to SugarCRM'),
				'title'		=> $this->__('User orders to SugarCRM'),
				'name'		=> self::USER_ORDER_TO_SUGARCRM,
				'value'		=> $this->_getSugarUserOrderBean(),
				'values'	=> $this->_getOrderOptionsArray()
			)
		);
		
		$accountId = $this->_getSugarAccountId();
		$accounts = $this->_getAccounts();
		if(!empty($accounts) && is_array($accounts)) {
			$fieldset_order->addField('sugarcrm_account_id',
				'select',
				array(
					'label'		=> $this->__('SugarCRM Account'),
					'title'		=> $this->__('SugarCRM Account'),
					'name'		=> 'sugarcrm_account_id',
					'value'		=> $accountId,
					'options'	=> $accounts,
				)
			);
		} else {
			$fieldset_order->addField('sugarcrm_account_id',
				'text',
				array(
					'label'		=> $this->__('SugarCRM Account'),
					'title'		=> $this->__('SugarCRM Account'),
					'name'		=> 'sugarcrm_account_id',
					'value'		=> $accountId ? $accountId : '',
					'note'		=> Mage::helper('sugarcrm')->isBeanEnabled(Belitsoft_Sugarcrm_Model_Connection::ACCOUNTS) ? 
									$this->__('Leave blank if you want to use customer account id for created order') :
									$this->__('Insert SugarCRM account id')
				)
			);
		}

		$opportunitiesSalesStageBlock = $this->getLayout()
			->createBlock(
				'sugarcrm/adminhtml_operations_userorder_stages',
				'',
				array(
					'stage_type' => $this->_getSugarUserOrderBean(),
				)
			);

		$fieldset_order->addField('stages_box',
			'note',
			array(
				'label'		=> $this->__('Stage Mapping'),
				'text'		=> '<div id="'.self::STAGES_DETAILS.'">' . $opportunitiesSalesStageBlock->toHtml() . '</div>',
			)
		);


		$form->setUseContainer(true);
		$form->setId('edit_form');
		$form->setMethod('post');
		$form->setAction($this->getSaveUrl());

		$this->setForm($form);

		$js = "
			var stageType = function() {
				return {
					updateStages: function() {
						var elements = [$('".self::USER_ORDER_TO_SUGARCRM."')].flatten();
						$('{$this->getParentBlock()->getSaveButtonId()}').disabled = true;
						new Ajax.Updater('".self::STAGES_DETAILS."', '{$this->getUrl('*/*/loadStage')}',
							{
								parameters: Form.serializeElements(elements),
								evalScripts: true,
								onComplete: function(){
									$('{$this->getParentBlock()->getSaveButtonId()}').disabled = false;
								}
							}
						);
					}
				}
			}();

			Event.observe(window, 'load', function() {
				if ($('".self::USER_ORDER_TO_SUGARCRM."')) {
					Event.observe($('".self::USER_ORDER_TO_SUGARCRM."'), 'change', stageType.updateStages);
				}
			});

			function saveStageEdit(action_str) {
				if(!action_str) {
					action_str = '';
				}
				try {
					if(!mpSugarcrmOperations.save()) {
						return false;
					}
				} catch(e) {}

				editForm.submit(action_str);
			}
		";

		$this->getParentBlock()->addFormScripts($js);
	}

	public function getOperationsData()
	{
		return Mage::registry('sugarcrm_operations_data');
	}

	public function getStagesDetailsAreaId()
	{
		return self::STAGES_DETAILS;
	}

	public function getStageTypeFieldName()
	{
		return self::USER_ORDER_TO_SUGARCRM;
	}

	public function getSaveUrl()
	{
		return $this->getUrl('*/*/save');
	}

	protected function _getAccounts()
	{
		$connection = Mage::getSingleton('sugarcrm/connection');
		try {
			$accounts = $connection->getAccounts();
		} catch(SoapFault $e) {
			return null;		
		} catch(Exception $e) {
			Mage::logException($e);
			return null;		
		}

		if(!empty($accounts)) {
			if(Mage::helper('sugarcrm')->isBeanEnabled(Belitsoft_Sugarcrm_Model_Connection::ACCOUNTS)) {
				array_unshift($accounts, $this->__("Use customer account id for created order"));
			} else {
				array_unshift($accounts, '-- Select account --');
			}
		}

		return $accounts;
	}

	protected function _getBeansArray()
	{
		$result = Mage::getSingleton('sugarcrm/operations')->getBeansArray();

		return $result;
	}

	protected function _getOperationsArray()
	{
		$result = Mage::getSingleton('sugarcrm/operations')->getOperationsArray();

		return $result;
	}

	protected function _getOptionsArray()
	{
		return Mage::getModel('sugarcrm/source_operationdisableenable')->toOptionArray();
	}

	protected function _getYesNoArray()
	{
		return Mage::getModel('sugarcrm/source_orderyesno')->toOptionArray();
	}

	protected function _getOrderOptionsArray()
	{
		return Mage::getModel('sugarcrm/source_orderbeans')->toOptionArray();
	}

	protected function _getSugarUserOrderBean()
	{
		return Mage::helper('sugarcrm')->getSugarOrderBean();
	}

	protected function _getEnabledUserOrders()
	{
		return strval(Mage::getModel('sugarcrm/config')->isEnabledUserOrdersSynch());
	}

	protected function _getUserOrdersCondition()
	{
		return strval(Mage::getModel('sugarcrm/config')->userOrdersSynchCondition());
	}

	protected function _getSugarAccountId()
	{
		return strval(Mage::getModel('sugarcrm/config')->getConfigData('sugarcrm_account_id'));
	}
	
	/**
	 * Custom additional elemnt html
	 *
	 * @param Varien_Data_Form_Element_Abstract $element
	 * @return string
	 */
	protected function _getConditionHtml($element, $bean_value, $oper_value, $value)
	{
		$data = array(
			'name'	=> $element->getId().'_condition',
			'value'	=> $value,
		);
		$textarea =  new Varien_Data_Form_Element_Textarea($data);
		$textarea->setId($element->getId().'_condition');
		$textarea->setForm($element->getForm());
		
		$notedata = array(
			'name'	=> $element->getId().'_condition_note',
			'text'	=> $this->__('Insert condition here')
		);
		$note =  new Varien_Data_Form_Element_Note($notedata);
		$note->setId($element->getId().'_condition_note');
		$note->setForm($element->getForm());
		
		$classSuffix = '';
		if($element->getValue() != Belitsoft_Sugarcrm_Model_Operations::OPERATION_ENABLE_WITH_CONDITION) {
			$classSuffix = ' hidden';
		}
		
		return '<div id="'.$element->getId().'_condition_area" class="'.$classSuffix.'" style="margin-top: 10px">'
			.$textarea->toHtml()
			.'<p class="note">'.$note->toHtml().'</p>'
			.'</div>'
			. '<script>setRadioOnclick("'.$element->getId().'_condition_area", "'.$element->getName().'");</script>'
			;
    }
}
