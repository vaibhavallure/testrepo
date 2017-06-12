<?php
/**
 * Mageplace Magento to SugarCRM Bridge
 *
 * @category    Belitsoft
 * @package     Belitsoft_Sugarcrm
 * @copyright   Copyright (c) 2013 Mageplace. (http://www.mageplace.com)
 * @license     http://www.mageplace.com/disclaimer.html
 */

class Belitsoft_Survey_Block_Adminhtml_Question_Edit_Fields_Empty extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element
{
	public function __construct()
	{
		$this->setTemplate('survey/question/edit/fields/empty.phtml');
	}
}
