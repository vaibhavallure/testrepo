<?php
/**
 * Allure_InstaCatalog
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category    Allure
 * @package     Allure_InstaCatalog
 * @copyright   Copyright© 2016, Allure Inc
 * @license     http://opensource.org/licenses/mit-license.php MIT License
 * @author      Team Allure <extensions@allureinc.co>
 */
/**
 * store selection tab
 *
 * @category    Allure
 * @package     Allure_InstaCatalog
 * @author      Team Allure <extensions@allureinc.co>
 */
class Allure_InstaCatalog_Block_Adminhtml_Feed_Edit_Tab_Stores extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return Allure_InstaCatalog_Block_Adminhtml_Feed_Edit_Tab_Stores
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setFieldNameSuffix('feed');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'feed_stores_form',
            array('legend' => Mage::helper('allure_instacatalog')->__('Store views'))
        );
        $field = $fieldset->addField(
            'store_id',
            'multiselect',
            array(
                'name'     => 'stores[]',
                'label'    => Mage::helper('allure_instacatalog')->__('Store Views'),
                'title'    => Mage::helper('allure_instacatalog')->__('Store Views'),
                'required' => true,
                'values'   => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            )
        );
        $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
        $field->setRenderer($renderer);
        $form->addValues(Mage::registry('current_feed')->getData());
        return parent::_prepareForm();
    }
}
