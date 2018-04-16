<?php

class Teamwork_CEGiftcards_Block_Adminhtml_Sales_Order_Create_Fieldset_Giftcard
    extends Teamwork_CEGiftcards_Block_Catalog_Product_View_Type_Giftcard
{

    public function getIsLastFieldset()
    {
        if ($this->hasData('is_last_fieldset')) {
            return $this->getData('is_last_fieldset');
        } else {
            return !$this->getProduct()->getOptions();
        }
    }


}
