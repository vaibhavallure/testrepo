<?php

class Ebizmarts_BakerlooLocation_Block_Adminhtml_Pos_Store_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {

        $helper = Mage::helper('bakerloo_restful');

        $model = Mage::registry('poslocation');

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('bakerloolocation_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => $helper->__('Location details')));

        $fieldset->addField(
            'title',
            'text',
            array(
            'name'  => 'location[title]',
            'label' => $helper->__('Name'),
            'id'    => 'title',
            'required' => true,
            )
        );

        $fieldset->addField(
            'street',
            'text',
            array(
            'name'  => 'location[street]',
            'label' => $helper->__('Address'),
            'id'    => 'street',
            'required' => true,
            )
        );
        $fieldset->addField(
            'postcode',
            'text',
            array(
            'name'  => 'location[postcode]',
            'label' => $helper->__('Postcode'),
            'id'    => 'postcode',
            'required' => true,
            )
        );
        $fieldset->addField(
            'telephone',
            'text',
            array(
            'name'  => 'location[telephone]',
            'label' => $helper->__('Telephone'),
            'id'    => 'telephone',
            'required' => true,
            )
        );
        $fieldset->addField(
            'city',
            'text',
            array(
            'name'  => 'location[city]',
            'label' => $helper->__('City'),
            'id'    => 'city',
            'required' => true,
            )
        );

        $_countries = array();
        $countries = Mage::getResourceModel('directory/country_collection')->load()->toOptionArray(false);
        foreach ($countries as $country) {
            $_countries [$country['value']] = $country['label'];
        }
        $fieldset->addField(
            'country_id',
            'select',
            array(
            'name'  => 'location[country_id]',
            'label' => $helper->__('Country'),
            'id'    => 'country_id',
            'required' => true,
            'class'    => 'countries',
            'options'  => $_countries
            )
        );
        $fieldset->addField(
            'region',
            'text',
            array(
            'name'  => 'location[region]',
            'label' => $helper->__('Region'),
            'id'    => 'region',
            'required' => true,
            )
        );
        $fieldset->addField(
            'region_id',
            'select',
            array(
            'name'  => 'location[region_id]',
            'label' => $helper->__('Region'),
            'id'    => 'region_id',
            'required' => false,
            //            'style' => 'display:none'
            )
        );

        if (Mage::helper('directory')->isRegionRequired($model->getCountryId())) {
            $form->getElement('region_id')
//                ->setRenderer(Mage::getModel('adminhtml/customer_renderer_region'))
                ->setRequired(true);

            $form->getElement('region')
                ->setRequired(false);
//                ->setNoDisplay(true);
        } else {
            $form->getElement('region_id')->setNoDisplay(true);
        }

        $yesno = array(
            0 => $helper->__('No'),
            1 => $helper->__('Yes')
        );
        $fieldset->addField(
            'active',
            'select',
            array(
            'name'  => 'location[active]',
            'label' => $helper->__('Enabled'),
            'id'    => 'active',
            'required' => true,
            'options'  => $yesno
            )
        );

        $fieldset->addField(
            'fax',
            'text',
            array(
            'name'  => 'location[fax]',
            'label' => $helper->__('Fax'),
            'id'    => 'fax',
            'required' => false,
            )
        );

        if ($model->getId()) {
            $fieldset->addField(
                'id',
                'hidden',
                array(
                'name' => 'location[id]',
                )
            );
        } else {
            $model->setActive(1);
        }


        $fieldset2 = $form->addFieldset('addtitional_fieldset', array('legend' => $helper->__('Additional details')));
        $fieldset2->addField(
            'mon_hours',
            'text',
            array(
            'name'  => 'location[mon_hours]',
            'label' => $helper->__('Monday hours'),
            'id'    => 'mon_hours',
            'required' => false,
            'note'     => $helper->__('For example: 10:00 - 22:00')
            )
        );
        $fieldset2->addField(
            'tues_hours',
            'text',
            array(
            'name'  => 'location[tues_hours]',
            'label' => $helper->__('Tuesday hours'),
            'id'    => 'tues_hours',
            'required' => false,
            'note'     => $helper->__('For example: 10:00 - 22:00')
            )
        );
        $fieldset2->addField(
            'wed_hours',
            'text',
            array(
            'name'  => 'location[wed_hours]',
            'label' => $helper->__('Wednesday hours'),
            'id'    => 'wed_hours',
            'required' => false,
            'note'     => $helper->__('For example: 10:00 - 22:00')
            )
        );
        $fieldset2->addField(
            'thurs_hours',
            'text',
            array(
            'name'  => 'location[thurs_hours]',
            'label' => $helper->__('Thursday hours'),
            'id'    => 'thurs_hours',
            'required' => false,
            'note'     => $helper->__('For example: 10:00 - 22:00')
            )
        );
        $fieldset2->addField(
            'fri_hours',
            'text',
            array(
            'name'  => 'location[fri_hours]',
            'label' => $helper->__('Friday hours'),
            'id'    => 'fri_hours',
            'required' => false,
            'note'     => $helper->__('For example: 10:00 - 22:00')
            )
        );
        $fieldset2->addField(
            'sat_hours',
            'text',
            array(
            'name'  => 'location[sat_hours]',
            'label' => $helper->__('Saturday hours'),
            'id'    => 'sat_hours',
            'required' => false,
            'note'     => $helper->__('For example: 10:00 - 22:00')
            )
        );
        $fieldset2->addField(
            'sun_hours',
            'text',
            array(
            'name'  => 'location[sun_hours]',
            'label' => $helper->__('Sunday hours'),
            'id'    => 'sun_hours',
            'required' => false,
            'note'     => $helper->__('For example: 10:00 - 22:00')
            )
        );
        $fieldset2->addField(
            'latitude',
            'text',
            array(
            'name'  => 'location[latitude]',
            'label' => $helper->__('Latitude'),
            'id'    => 'latitude',
            'required' => false,
            )
        );
        $fieldset2->addField(
            'longitude',
            'text',
            array(
            'name'  => 'location[longitude]',
            'label' => $helper->__('Longitude'),
            'id'    => 'longitude',
            'required' => false,
            )
        );
        $fieldset2->addField(
            'website_url',
            'text',
            array(
            'name'  => 'location[website_url]',
            'label' => $helper->__('Website URL'),
            'id'    => 'website_url',
            'required' => false,
            )
        );


        $fieldset3 = $form->addFieldset('notes_fieldset', array('legend' => $helper->__('Notes')));
        $fieldset3->addField(
            'notes',
            'textarea',
            array(
            'name'  => 'location[notes]',
            'label' => $helper->__('Notes'),
            'id'    => 'notes',
            'required' => false,
            )
        );

        $data = $model->getData();

        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function _initFormValues()
    {
        return parent::_initFormValues(); // TODO: Change the autogenerated stub
    }
}
