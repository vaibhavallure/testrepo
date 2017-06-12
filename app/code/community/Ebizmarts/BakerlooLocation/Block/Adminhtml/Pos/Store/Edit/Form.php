<?php

class Ebizmarts_BakerlooLocation_Block_Adminhtml_Pos_Store_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getUrl('*/*/save'), 'method' => 'post'));
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    protected function _toHtml()
    {
        $js = '<script type="text/javascript">
                //<![CDATA[
                    new RegionUpdater(\'bakerloolocation_country_id\', \'bakerloolocation_region\', \'bakerloolocation_region_id\', ' . Mage::helper('directory')->getRegionJson() . ', undefined, \'zip\');
                //]]>
                </script>';

        return parent::_toHtml() . $js;
    }
}
