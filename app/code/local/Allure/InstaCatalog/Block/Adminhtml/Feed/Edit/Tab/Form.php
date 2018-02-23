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
 * Feed edit form tab
 *
 * @category    Allure
 * @package     Allure_InstaCatalog
 * @author      Team Allure <extensions@allureinc.co>
 */
class Allure_InstaCatalog_Block_Adminhtml_Feed_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return Allure_InstaCatalog_Block_Adminhtml_Feed_Edit_Tab_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('feed_');
        $form->setFieldNameSuffix('feed');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'feed_form',
            array('legend' => Mage::helper('allure_instacatalog')->__('Feed'))
        );

        /* $fieldset->addField(
            'media_id',
            'text',
            array(
                'label' => Mage::helper('allure_instacatalog')->__('Media Id'),
                'name'  => 'media_id',
                'required'  => true,
                'class' => 'required-entry',
				'disabled'=>true
           )
        ); */

        $fieldset->addField(
            'username',
            'text',
            array(
                'label' => Mage::helper('allure_instacatalog')->__('Link'),
                'name'  => 'username',
                'required'  => true,
                'class' => 'required-entry',
           )
        );

        $fieldset->addField(
            'caption',
            'textarea',
            array(
                'label' => Mage::helper('allure_instacatalog')->__('Caption'),
                'name'  => 'caption',
                'required'  => true,
                'class' => 'required-entry',

           )
        );
        $fieldset->addField(
            'status',
            'select',
            array(
                'label'  => Mage::helper('allure_instacatalog')->__('Status'),
                'name'   => 'status',
                'values' => array(
                    array(
                        'value' => 1,
                        'label' => Mage::helper('allure_instacatalog')->__('Enabled'),
                    ),
                    array(
                        'value' => 0,
                        'label' => Mage::helper('allure_instacatalog')->__('Disabled'),
                    ),
                ),
            )
        );
        
        $fieldset->addField(
        		'lookbook_mode',
        		'select',
        		array(
        			'label'  => Mage::helper('allure_instacatalog')->__('Type'),
        			'name'   => 'lookbook_mode',
        			'values' => array(
        					array(
        						'value' => 1,
        						'label' => Mage::helper('allure_instacatalog')->__('Shop by Look'),
        					),
        					array(
        						'value' => 0,
        						'label' => Mage::helper('allure_instacatalog')->__('Instagram'),
        					),
        				),
        		)
        );
        
        $fieldset->addType('instagramimage','Allure_InstaCatalog_Block_Adminhtml_Feed_Edit_Form_Element_Instagramimage');
        $fieldset->addField('image', 'instagramimage', array(
        		'label'     => Mage::helper('allure_instacatalog')->__('Image'),
        		'name'      => 'image',
        		'required'  => false,
        )); 
        
        $fieldset->addType('hotspots','Allure_InstaCatalog_Block_Adminhtml_Feed_Edit_Form_Element_Hotspots');
        $fieldset->addField('hotspots', 'hotspots', array(
        		'name'      => 'hotspots',
        ));
        
        
        /* if (Mage::app()->isSingleStoreMode()) {
            $fieldset->addField(
                'store_id',
                'hidden',
                array(
                    'name'      => 'stores[]',
                    'value'     => Mage::app()->getStore(true)->getId()
                )
            );
            Mage::registry('current_feed')->setStoreId(Mage::app()->getStore(true)->getId());
        } */
        
        $formValues = Mage::registry('current_feed')->getDefaultValues();
        if (!is_array($formValues)) {
            $formValues = array();
        }
        if (Mage::getSingleton('adminhtml/session')->getFeedData()) {
            $formValues = array_merge($formValues, Mage::getSingleton('adminhtml/session')->getFeedData());
            Mage::getSingleton('adminhtml/session')->setFeedData(null);
        } elseif (Mage::registry('current_feed')) {
            $formValues = array_merge($formValues, Mage::registry('current_feed')->getData());
        }
        $form->setValues($formValues);
        return parent::_prepareForm();
    }
}
