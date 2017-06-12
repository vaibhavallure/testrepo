<?php
/**
 * Description of Celebrities
 *
 * @category    Ecp
 * @package     Ecp_Celebrities
 */
class Ecp_Celebrities_Block_Adminhtml_Celebrities_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('celebrities_form', array('legend' => Mage::helper('ecp_celebrities')->__('Celebrity information')));

        $fieldset->addField('celebrity_name', 'text', array(
            'label' => Mage::helper('ecp_celebrities')->__('Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'celebrity_name',
        ));

        $fieldset->addField('default_image', 'image', array(
            'label' => Mage::helper('ecp_celebrities')->__('Image'),
            'required' => true,
            'name' => 'default_image',
        ));

        $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig();

        $wysiwygConfig->setDirectivesUrl(str_replace('ecpcelebrities', 'admin', $wysiwygConfig->getDirectivesUrl()));
        $plugins = $wysiwygConfig->getPlugins();
        $plugins[0]['options']['onclick']['subject'] = str_replace('ecpcelebrities', 'admin', $plugins[0]['options']['onclick']['subject']);
        $plugins[0]['options']['url'] = str_replace('ecpcelebrities', 'admin', $plugins[0]['options']['url']);
        $wysiwygConfig->setPlugins($plugins);
        $wysiwygConfig->setDirectivesUrlQuoted(str_replace('ecpcelebrities', 'admin', $wysiwygConfig->getDirectivesUrlQuoted()));
        $wysiwygConfig->setFilesBrowserWindowUrl(str_replace('ecpcelebrities', 'admin', $wysiwygConfig->getFilesBrowserWindowUrl()));
        $wysiwygConfig->setWidgetWindowUrl(str_replace('ecpcelebrities', 'admin', $wysiwygConfig->getWidgetWindowUrl()));

        $fieldset->addField('description', 'editor', array(
            'name' => 'description',
            'label' => Mage::helper('ecp_celebrities')->__('Description'),
            'title' => Mage::helper('ecp_celebrities')->__('Description'),
            'style' => 'height:26em;width:60em;',
            'wysiwyg' => true,
            'config' => $wysiwygConfig
        ));

        $fieldset->addField('url', 'text', array(
            'label' => Mage::helper('ecp_celebrities')->__('URL'),
            'name' => 'url',
        ));

        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('ecp_celebrities')->__('Status'),
            'name' => 'status',
            'values' => array(
                array(
                    'value' => 1,
                    'label' => Mage::helper('ecp_celebrities')->__('Enabled'),
                ),
                array(
                    'value' => 2,
                    'label' => Mage::helper('ecp_celebrities')->__('Disabled'),
                ),
            ),
        ));

        $fieldset->addField('ordering', 'text', array(
            'label' => Mage::helper('ecp_celebrities')->__('Order'),
            'name' => 'ordering',
        ));

        if (Mage::getSingleton('adminhtml/session')->getCelebritiesData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getCelebritiesData());
            Mage::getSingleton('adminhtml/session')->setCelebritiesData(null);
        } elseif (Mage::registry('celebrities_data')) {
            $tmp = Mage::registry('celebrities_data')->getData();
            $tmp['default_image'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'celebrities' . DS . $tmp['default_image'];
            $form->setValues($tmp);
        }
        return parent::_prepareForm();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }
}