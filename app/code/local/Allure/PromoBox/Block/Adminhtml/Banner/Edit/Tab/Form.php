<?php

class Allure_PromoBox_Block_Adminhtml_Banner_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('promobox_banner_form', array('legend' => Mage::helper('promobox')->__('Banner information')));

        $fieldset->addField('name', 'text', array(
            'label' => Mage::helper('promobox')->__('Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'name',
        ));

        $afterElementHtml = '<p class="nm" style="color: red"><small>' . 'For 1x2 Banner upload image size 565X361(PX)<br> For 2x2 Banner upload image size 565X733(PX)' . '</small></p>';
        $afterElementHtml .='<script>
//<![CDATA[
document.addEventListener("DOMContentLoaded", function(){ 
       document.getElementsByClassName("delete-image")[0].style.display="none";
}, false);

//]]>
</script>';

        $sizeAfterelement='<script>
//<![CDATA[

function checkIsIframeAvailble(obj) {
  
    if(obj.value=="two_by_two") {
    document.getElementById("iframe_src").parentElement.parentElement.style.display="none";
    document.getElementById("iframe_style").parentElement.parentElement.style.display="none";
    }else{
    document.getElementById("iframe_src").parentElement.parentElement.style.display="";        
    document.getElementById("iframe_style").parentElement.parentElement.style.display="";        
    }
}


checkIsIframeAvailble(document.getElementById("size"));

//]]>
</script>';
        $fieldset->addField('size', 'select', array(
            'label' => Mage::helper('promobox')->__('Size'),
            'name' => 'size',
            'values' => array(
                array(
                    'value' => "one_by_two",
                    'label' => Mage::helper('promobox')->__('1X2'),
                ),
                array(
                    'value' => "two_by_two",
                    'label' => Mage::helper('promobox')->__('2X2'),
                ),
            ),
            'after_element_html'=>$sizeAfterelement,
            'onchange'=>'checkIsIframeAvailble(this)',
        ));


        $fieldset->addField('image', 'image', array(
            'label' => Mage::helper('promobox')->__('Image'),
            'required' => true,
            'class'     => 'required-entry required-file',
            'name' => 'image',
            'after_element_html' => $afterElementHtml,
        ));



        $fieldset->addField('html_block', 'editor', array(
            'name' => 'html_block',
            'label' => Mage::helper('promobox')->__('Html Content'),
            'title' => Mage::helper('promobox')->__('Html Content'),
        ));

        $fieldset->addField('iframe_src', 'text', array(
            'name' => 'iframe_src',
            'label' => Mage::helper('promobox')->__('Video Id'),
            'after_element_html' => "<p><small>Left empty to display banner image</small></p>",
        ));

        $fieldset->addField('iframe_style', 'textarea', array(
            'name' => 'iframe_style',
            'label' => Mage::helper('promobox')->__('IFrame style'),
            'after_element_html' => "<p><small>Left empty to apply global style</small></p>",

        ));


        if (Mage::getSingleton('adminhtml/session')->getBannerData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getBannerData());
            Mage::getSingleton('adminhtml/session')->setBannerData(null);
        } elseif (Mage::registry('banner_data')) {
            $tmp = Mage::registry('banner_data')->getData();
            $tmp['image'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'promobox' . DS . $tmp['image'];
            $form->setValues($tmp);
        }

        return parent::_prepareForm();
    }

}