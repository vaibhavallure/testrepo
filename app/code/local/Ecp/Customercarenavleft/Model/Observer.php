<?php

class Ecp_Customercarenavleft_Model_Observer
{
    public function prepareForm(Varien_Event_Observer $observer){
        //get CMS model with data
        $model = Mage::registry('cms_page');
        
        $form = $observer->getEvent()->getForm();
 
        $fieldset = $form->addFieldset(
            'customer_care_nav',
            array(
                 'legend' => 'Customer Care',
                 'class' => 'fieldset-wide'
            )
        );

        $fieldset->addField('customer_care_navigation', 'select', array(
          'label'     => 'Customer Care Navigation',          
          'required'  => false,
          'name'      => 'customer_care_navigation',
          'onclick' => "",
          'onchange' => "",
          /*'value'  => '0',*/
          'values' => array('0'=>'NO','1' => 'YES'),
          'disabled' => false,
          'readonly' => false,
          'after_element_html' => '<small>Select yes if this page will be include in customer care navigation menu</small>',
          'tabindex' => 1
        ));

        $fieldset->addField('customer_care_navigation_order', 'text', array(
            'name'      => 'customer_care_navigation_order',
            'label'     => Mage::helper('cms')->__('Customer Care Navigation Order'),
            'title'     => Mage::helper('cms')->__('Customer Care Navigation Order'),
            'disabled'  => false,
            'value'     => $model->getCustomerCareNavigationOrder()
        ));
    }
    
    public function savePage(Varien_Event_Observer $observer)
    {
        $model = $observer->getEvent()->getPage();
        $request = $observer->getEvent()->getRequest();
        $option = $request->getParam('customer_care_navigation');
        $option_order = $request->getParam('customer_care_navigation_order');

        $model->setCustomerCareNavigation($option);
        $model->setCustomerCareNavigationOrder($option_order);

    }
    
    public function prepareFormBlock(Varien_Event_Observer $observer){
        
    }
}