<?php

class Allure_BackorderRecord_Block_Adminhtml_Backorder_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {

        $form = new Varien_Data_Form();
        $this->setForm($form);


        $fieldset = $form->addFieldset('backorederrecord_form', array(
            'legend'=>Mage::helper('backorderrecord')->__('Select Date To Download back order')
        ));

        $dateTimeFormatIso = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

        $fieldset->addField('fromdate', 'datetime', array(
            'label'    => 'From',
            'title'    => 'From',
            'time'      => true,
            'name'     => 'from_date',
            'image'    => $this->getSkinUrl('images/grid-cal.gif'),
            'format'   => $dateTimeFormatIso,
            'required' => true,
        ));

        $fieldset->addField('todate', 'datetime', array(
            'label'    => 'To',
            'title'    => 'To',
            'time'      => true,
            'name'     => 'to_date',
            'image'    => $this->getSkinUrl('images/grid-cal.gif'),
            'format'   => $dateTimeFormatIso,
            'required' => true,
        ));

        $form->setValues(null);

        return parent::_prepareForm();
    }
}