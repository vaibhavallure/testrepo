<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

 class Belitsoft_Sugarcrm_Block_Adminhtml_Config extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		$this->_blockGroup = 'sugarcrm';
		$this->_controller = 'adminhtml';
		$this->_mode = 'config';
        
		parent::__construct();
        
		$this->_removeButton('back');
		$this->_removeButton('reset');
		$this->_updateButton('save', 'label', $this->__('Save settings'));
		$this->_updateButton('save', 'id', 'save_button');
		
		$test_button_set = array(
			'label'		=> $this->__('Test connection'),
			'id'		=> 'test_button',
			'onclick'	=> 'document.getElementById(\'start_test\').value=1;editForm.submit();'
		);
		$this->_addButton('test', $test_button_set);
	}

	public function getHeaderText()
	{
		return $this->__('Settings');
	}

    public function getHeaderCssClass()
    {
		return 'icon-head head-backups-control';
    }
}