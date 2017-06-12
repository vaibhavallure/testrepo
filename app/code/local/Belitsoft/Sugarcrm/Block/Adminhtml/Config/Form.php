<?php
/**
 * Adminhtml Belitsoft Sugarcrm config form block
 *
 * @category   Belitsoft
 * @package    Belitsoft_Sugarcrm
 * @author     Belitsoft <bits@belitsoft.com>
 */

class Belitsoft_Sugarcrm_Block_Adminhtml_Config_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();

		$configData = $this->getConfigData();

		$fieldset = $form->addFieldset('base_fieldset', array(
			'legend'	=> $this->__('Connection Settings')
		));
		
		if ( !($soapServer = $configData['server']) ) {
			$soapServer = $this->_getDefaultServer();
		}
		$fieldset->addField('sugarcrm_soap_server', 'text', array(
			'label'		=> $this->__('SugarCRM SOAP Location'),
			'title'		=> $this->__('SugarCRM SOAP Location'),
			'name'		=> 'sugarcrm_soap_server',
			'required'	=> true,
			'value'		=> $soapServer,
		));
/*				
		$fieldset->addField('sugarcrm_soap_wsdl', 'checkbox', array(
			'label'		=> $this->__('Use WSDL?'),
			'title'		=> $this->__('Use WSDL?'),
			'name'		=> 'sugarcrm_soap_wsdl',
			'checked'	=> (!empty($configData['wsdl']) ? true : false),
			'value'		=> 1,
		));
		
		$fieldset->addField('sugarcrm_soap_namespace', 'text', array(
			'label'		=> $this->__('Target Namespace'),
			'title'		=> $this->__('Target Namespace'),
			'name'		=> 'sugarcrm_soap_namespace',
			'value'		=> $configData['namespace'],
		));

		if ( !($soapUse = $configData['use']) ) {
			$useKeys = array_keys($this->_getUseArray());
			$soapUse = isset($useKeys[0]) ? $useKeys[0] : null;
		}
		$fieldset->addField('sugarcrm_soap_use', 'radios', array(
			'label'		=> $this->__('Use'),
			'title'		=> $this->__('Use'),
			'name'		=> 'sugarcrm_soap_use',
			'value'		=> $soapUse,
		))->setValues($this->_getUseArray());
		

		$fieldset->addField('sugarcrm_soap_style', 'radios', array(
			'label'		=> $this->__('Style'),
			'title'		=> $this->__('Style'),
			'name'		=> 'sugarcrm_soap_style',
			'value'		=> ($configData['style'] ? $configData['style'] : SOAP_RPC),
		))->setValues($this->_getStyleArray());
*/		
		$fieldset->addField('sugarcrm_soap_username', 'text', array(
			'label'		=> $this->__('SugarCRM username'),
			'title'		=> $this->__('SugarCRM username'),
			'name'		=> 'sugarcrm_soap_username',
			'required'	=> true,
			'value'		=> $configData['username'],
		));
		
		$fieldset->addField('sugarcrm_soap_password', 'password', array(
			'label'		=> $this->__('SugarCRM password'),
			'title'		=> $this->__('SugarCRM password'),
			'name'		=> 'sugarcrm_soap_password',
			'value'		=> '',
		));

		$form->addField('start_test', 'hidden', array(
			'name'		=> 'start_test',
			'value'		=> 0,
		));
		
		$fieldset_addit = $form->addFieldset('addit_fieldset', array(
			'legend'	=> $this->__('Additional Settings')
		));
		$fieldset_addit->addField('disable_bridge', 'select', array(
			'label'		=> $this->__('Disable Bridge'),
			'title'		=> $this->__('Disable Bridge'),
			'name'		=> 'disable_bridge',
			'value'		=> $configData['disable_bridge'] ? 1 : 0,
		))->setValues(Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray());
		
		$fieldset_addit->addField('show_errors_on_frontend', 'select', array(
			'label'		=> $this->__('Show errors on the site frontend'),
			'title'		=> $this->__('Show errors on the site frontend'),
			'name'		=> 'show_errors_on_frontend',
			'value'		=> $configData['show_errors_on_frontend'] ? 1 : 0,
		))->setValues(Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray());
		
		$fieldset_addit->addField('show_errors_on_backend', 'select', array(
			'label'		=> $this->__('Show errors on the site backend'),
			'title'		=> $this->__('Show errors on the site backend'),
			'name'		=> 'show_errors_on_backend',
			'value'		=> $configData['show_errors_on_backend'] ? 1 : 0,
		))->setValues(Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray());
		
		
		$form->setUseContainer(true);
		$form->setId('edit_form');
		$form->setMethod('post');
		$form->setAction($this->getSaveUrl());

		$this->setForm($form);
	}
	
	protected function _getUseArray()
	{
		$_use_types = Mage::getSingleton('sugarcrm/config')->getSOAPUse();
		$result = array();
		foreach ($_use_types as $type => $info) {
			$result[$type] = array('label'=>$info['label'], 'value'=>$type);
		}
		
		return $result;
	}
	
	protected function _getStyleArray()
	{
		$_style_types = Mage::getSingleton('sugarcrm/config')->getSOAPStyle();
		$result = array();
		foreach ($_style_types as $type => $info) {
			$result[$type] = array('label'=>$info['label'], 'value'=>$type);
		}
		
		return $result;
	}
	
	protected function _getDefaultServer()
	{
		$_server = Mage::getSingleton('sugarcrm/config')->getSOAPServer();
		
		return $_server;
	}
	
	public function getConfigData()
	{
		return Mage::registry('sugarcrm_config_data');
	}

	public function getSaveUrl()
	{
		return $this->getUrl('*/*/save');
	}
}
