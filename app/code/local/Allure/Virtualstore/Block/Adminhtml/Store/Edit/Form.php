<?php
/**
 * Created by PhpStorm.
 * User: swapnil
 * Date: 8/14/18
 * Time: 6:18 PM
 */
class Allure_Virtualstore_Block_Adminhtml_Store_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
                "id" => "edit_form",
                "action" => $this->getUrl("*/*/save",
                    array("store_id" => $this->getRequest()->getParam("store_id"))
                ),
                "method" => "post",
                "enctype" =>"multipart/form-data"
            )
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}