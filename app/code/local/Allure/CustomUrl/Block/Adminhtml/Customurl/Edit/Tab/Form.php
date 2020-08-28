<?php
/**
 * 
 * @author allure
 *
 */
class Allure_CustomUrl_Block_Adminhtml_Customurl_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return Allure_CustomUrl_Block_Adminhtml_Customurl_Edit_Tab_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('customurl');
        $form->setFieldNameSuffix('customurl');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'customurl_form',
            array('legend' => Mage::helper('allure_customurl')->__('Custom Url'))
        );

        $fieldset->addField(
            'url_id',
            'hidden',
            array(
                'label' => Mage::helper('allure_customurl')->__('Url Id'),
                'name'  => 'url_id',
            )
            );
        
        $fieldset->addField(
            'current_url',
            'text',
            array(
                'label' => Mage::helper('allure_customurl')->__('Current Url'),
                'name'  => 'current_url',
                'required'  => true,
                'class' => 'required-entry',
           )
        );
        
        $fieldset->addField(
            'request_path',
            'text',
            array(
                'label' => Mage::helper('allure_customurl')->__('Request Path'),
                'name'  => 'request_path',
                'required'  => true,
                'class' => 'required-entry',
            )
            );
        
        $fieldset->addField(
            'target_path',
            'text',
            array(
                'label' => Mage::helper('allure_customurl')->__('Target Path'),
                'name'  => 'target_path',
                'required'  => true,
                'class' => 'required-entry',
            )
            );
        
        $fieldset->addField('store_id', 'select', array(
            'label'     => Mage::helper("allure_customurl")->__("Store"),
            'name'    => 'store_id',
            'values'   => Mage::getSingleton('adminhtml/system_store')->getStoreOptionHash(),
        ));

        $fieldset->addField(
            'is_rewrite_url',
            'select',
            array(
                'label'  => Mage::helper('allure_customurl')->__('Is Rewrite'),
                'name'   => 'is_rewrite_url',
                'values' => array(
                    array(
                        'value' => 0,
                        'label' => Mage::helper('allure_customurl')->__('No'),
                    ),
                    array(
                        'value' => 1,
                        'label' => Mage::helper('allure_customurl')->__('Yes'),
                    ),
                ),
            )
        );
        
        $fieldset->addField(
        		'options',
        		'select',
        		array(
        			'label'  => Mage::helper('allure_customurl')->__('Options'),
        			'name'   => 'options',
        			'values' => array(
        					array(
        						'value' => '',
        						'label' => Mage::helper('allure_customurl')->__(''),
        					),
        					array(
        						'value' => 'R',
        						'label' => Mage::helper('allure_customurl')->__('Temporary Redirect(302)'),
        					),
            			    array(
            			        'value' => 'RP',
            			        'label' => Mage::helper('allure_customurl')->__('Permanent Redirect(301)'),
            			    ),
        				),
        		)
        );
        
        if (Mage::getSingleton('adminhtml/session')->getCustomUrlData()) {
            $formValues = Mage::getSingleton('adminhtml/session')->getCustomUrlData();
            $form->setValues($formValues);
            Mage::getSingleton('adminhtml/session')->setCustomUrlData(null);
        } elseif (Mage::registry('current_customurl')) {
            $formValues = Mage::registry('current_customurl')->getData();
            $form->setValues($formValues);
        }
        $form->setValues($formValues);
        return parent::_prepareForm();
    }
}
