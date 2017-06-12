<?php

/**
 * Adminhtml configuration upgrade form block
 *
 * @category   Remarkety
 * @package    Remarkety_Mgconnector
 * @author     Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class Remarkety_Mgconnector_Block_Adminhtml_Install_Upgrade_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/install/complete'),
            'method' => 'post',
        ));
        $form->setFieldContainerIdPrefix('data');
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset(
            'general',
            array(
                'legend' => $this->__('Upgrade Remarkety extension')
            )
        );

        $fieldset->addField('mode', 'hidden', array(
            'name' => 'data[mode]',
            'value' => 'upgrade',
        ));

        $instruction = $fieldset->addField('instruction', 'note', array(
            'text'     => '',
            'label' => false,
            'after_element_html' =>
                '<p style="font-weight:bold;">' .  $this->__('Thank you for installing the Remarkety Magento plugin.
                You are one click away from finishing setting up Remarkety on your store and sending effective, targeted emails!')
                . '<br><br>'
                . $this->__('It seems that you have already installed Remarkety on this website before. This
                version of the plugin will create a new API key, and automatically inform
                Remarkety. If this is a mistake, please <a href="%s">click here</a>.</p>', $this->getUrl('*/install/install', array('mode' => 'install_create')))
        ));
        $instruction->getRenderer()->setTemplate('mgconnector/element.phtml');

        $fieldset->addField('button', 'note', array(
            'label' => false,
            'name'  => 'button',
            'after_element_html' => '<button type="button" class="save" onclick="editForm.submit();"><span><span>'
                . $this->__('Complete Installation') . '</span></span></button>'
        ));

        return parent::_prepareForm();
    }
}