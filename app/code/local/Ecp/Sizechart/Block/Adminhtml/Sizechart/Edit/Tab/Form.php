<?php
/**
 * Description of Sizechart
 *
 * @category    Ecp
 * @package     Ecp_Sizechart
 */
class Ecp_Sizechart_Block_Adminhtml_Sizechart_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('sizechart_form', array('legend'=>Mage::helper('ecp_sizechart')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('ecp_sizechart')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig();
      $adminKey= (Mage::getBaseUrl()=="https://www.mariatash.com/index.php/" || Mage::getBaseUrl()=="https://beta.mariatash.com/index.php/")? 'MariaTashGOadmin' : 'admin';
      $wysiwygConfig->setDirectivesUrl(str_replace('ecpsizechart',$adminKey,$wysiwygConfig->getDirectivesUrl()));
      $plugins = $wysiwygConfig->getPlugins();
      $plugins[0]['options']['onclick']['subject'] = str_replace('ecpsizechart','admin',$plugins[0]['options']['onclick']['subject']);
      $plugins[0]['options']['url'] = str_replace('ecpsizechart',$adminKey,$plugins[0]['options']['url']);
      $wysiwygConfig->setPlugins($plugins);
      $wysiwygConfig->setDirectivesUrlQuoted(str_replace('ecpsizechart',$adminKey,$wysiwygConfig->getDirectivesUrlQuoted()));
      $wysiwygConfig->setFilesBrowserWindowUrl(str_replace('ecpsizechart',$adminKey,$wysiwygConfig->getFilesBrowserWindowUrl()));
      $wysiwygConfig->setWidgetWindowUrl(str_replace('ecpsizechart',$adminKey,$wysiwygConfig->getWidgetWindowUrl()));
      
      $fieldset->addField('block_content', 'editor', array(
          'name'      => 'block_content',
          'label'     => Mage::helper('ecp_sizechart')->__('Content'),
          'title'     => Mage::helper('ecp_sizechart')->__('Content'),
          'style'     => 'height:26em;width:60em;',
          'wysiwyg'   => true,
          'required'  => true,
          'config' => $wysiwygConfig
      ));
      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('ecp_sizechart')->__('PDF File'),
          'required'  => false,
          'name'      => 'filename',
      ));
      if ( Mage::getSingleton('adminhtml/session')->getSizechartData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getSizechartData());
          Mage::getSingleton('adminhtml/session')->setSizechartData(null);
      } elseif ( Mage::registry('sizechart_data') ) {
          $form->setValues(Mage::registry('sizechart_data')->getData());
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