<?php
/**
 * OnePica_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0), a
 * copy of which is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   OnePica
 * @package    OnePica_AvaTax
 * @author     OnePica Codemaster <codemaster@onepica.com>
 * @copyright  Copyright (c) 2009 One Pica, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

/**
 * Class OnePica_AvaTax_Block_Adminhtml_Landedcost_Parameter_Edit_Form
 */
class OnePica_AvaTax_Block_Adminhtml_Landedcost_Parameter_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     * @throws \Varien_Exception
     * @throws \Exception
     */
    protected function _prepareForm()
    {
        /** @var OnePica_AvaTax_Helper_LandedCost $helper */
        $helper = Mage::helper('avatax/landedCost');

        $form = new Varien_Data_Form(
            array(
                'id'     => 'edit_form',
                'action' => $this->getUrl(
                    '*/*/parameterSave', array('id' => $this->getRequest()->getParam('id'),)
                ),
                'method' => 'post',
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);
        $fieldset = $form->addFieldset('parameter_form', array('legend' => $this->__('Parameter information')));

        /** @var \OnePica_AvaTax_Block_Adminhtml_Landedcost_Parameter_Edit_Form_Renderer_Parameter $renderer */
        $renderer = Mage::app()->getLayout()
                        ->createBlock('avatax/adminhtml_landedcost_parameter_edit_form_renderer_parameter');

        $fieldset->addField(
            'avalara_parameter_type', 'text', array(
                'name'     => 'avalara_parameter_type',
                'label'    => $this->__('Parameter'),
                'class'    => 'required-entry',
                'note'     => $this->__('For example "%s"', $helper->getMassType()),
                'required' => true
            )
        )->setRenderer($renderer);

        $fieldset->addField(
            'description', 'text', array(
                'name'     => 'description',
                'label'    => $this->__('Description'),
                'class'    => 'required-entry',
                'required' => true,
            )
        );

        $fieldset->addField(
            'avalara_uom', 'text', array(
                'name'  => 'avalara_uom',
                'label' => $this->__('UOM')
            )
        );

        /** @var \Mage_Directory_Model_Resource_Country_Collection $countryList */
        $countryCollection = Mage::getModel('directory/country')->getResourceCollection()->loadByStore();

        $countryList = $countryCollection->toOptionArray(' ');

        $fieldset->addField(
            'country_list', 'multiselect', array(
                'name'     => 'country_list[]',
                'label'    => $this->__('Country List'),
                'title'    => $this->__('Country List'),
                'required' => true,
                'values'   => $countryList,
            )
        );

        $parameterData = $this->_getAdminhtmlSession()->getParameterData();
        // Set values from session for the same id only
        if ($parameterData && is_array($parameterData) && isset($parameterData['parameter_id'])
            && $this->getRequest()->getParam('id') === $parameterData['parameter_id']) {

            $form->setValues($this->_getAdminhtmlSession()->getParameterData());
        } elseif (Mage::registry('parameter_data')) {
            $form->setValues(Mage::registry('parameter_data')->getData());
        }

        return parent::_prepareForm();
    }

    /**
     * @return \Mage_Adminhtml_Model_Session
     */
    protected function _getAdminhtmlSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }
}
