<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Sugarcrm_Block_Adminhtml_Synchtable_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	/**
	 * Constructor for the category edit form
	 */
	public function __construct()
	{
		$this->_blockGroup = 'sugarcrm';
		$this->_controller = 'adminhtml_synchtable';
		$this->_mode = 'edit'; 
		
		parent::__construct();
		
		$this->_removeButton('reset');
		
/*		$this->_addButton('saveandcontinue',
			array(
				'label'		=> $this->__('Save and continue edit'), 
				'onclick'	=> 'saveAndContinueEdit()', 
				'class'		=> 'save'
			),
			-100
		);
		
		$this->_formScripts[] = "
			function saveAndContinueEdit(){
				editForm.submit($('edit_form').action+'back/edit/');
			}
		";
*/
	}
	
	public function getHeaderText()
	{
		if (Mage::registry('sugarcrm_synchmap_model')->getId()) {
			return $this->__('Edit Sync Data');
		} else {
			return $this->__('New Sync Data');
		}
	}

	public function getHeaderCssClass()
	{
		return '';
	}
}
