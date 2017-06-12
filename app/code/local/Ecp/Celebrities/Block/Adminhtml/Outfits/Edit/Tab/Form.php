<?php
/**
 * Description of Celebrities
 *
 * @category    Ecp
 * @package     Ecp_Celebrities
 */
class Ecp_Celebrities_Block_Adminhtml_Outfits_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('celebrities_outfits_form', array('legend'=>Mage::helper('ecp_celebrities')->__('Item information')));

        $fieldset->addField('celebrity_id', 'text', array(            
            'required'  => true,
            'name'      => 'celebrity_id',
            'style'     => "display:none",
            'disabled'  => false
        ));

        $fieldset->addField('outfit_image', 'image', array(
            'label'     => Mage::helper('ecp_celebrities')->__('Outfit Image'),
            'required'  => true,
            'name'      => 'outfit_image',
        ));
		
        $fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('ecp_celebrities')->__('Status'),
            'name'      => 'status',
            'values'    => array(
                                array(
                                    'value'     => 1,
                                    'label'     => Mage::helper('ecp_celebrities')->__('Enabled'),
                                ),
                                array(
                                    'value'     => 2,
                                    'label'     => Mage::helper('ecp_celebrities')->__('Disabled'),
                                ),
                            ),
        ));     
     
      
      if ( Mage::getSingleton('adminhtml/session')->getCelebritiesOutfitData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getCelebritiesOutfitData());
          Mage::getSingleton('adminhtml/session')->setCelebritiesOutfitData(null);
      } elseif ( Mage::registry('celebrities_outfit_data') ){
          $cId = Mage::registry('celebrities_outfit_data')->getData('celebrity_id');
          if(empty($cId))
            Mage::registry('celebrities_outfit_data')->setData('celebrity_id',$this->getRequest()->getParam('currentCelebrityId'));
          //////////////////////////////////////////////////////////////////
          $tmp = Mage::registry('celebrities_outfit_data')->getData();
          $tmp['outfit_image'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'celebrities' . DS .$tmp['outfit_image'];
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