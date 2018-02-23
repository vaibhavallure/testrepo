<?php

class Teamwork_CEGiftcards_Model_Catalog_Product_Type_Giftcard_Attribute_Source_Giftcardtype
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
        return array(
            array(
                'label' => 'Virtual',
                'value' => Teamwork_CEGiftcards_Model_Catalog_Product_Type_Giftcard::TYPE_VIRTUAL,
            ),
            array(
                'label' => 'Physical',
                'value' => Teamwork_CEGiftcards_Model_Catalog_Product_Type_Giftcard::TYPE_PHYSICAL,
            ),
//            array(
//                'label' => 'Combined',
//                'value' => Teamwork_CEGiftcards_Model_Catalog_Product_Type_Giftcard::TYPE_COMBINED,
//            ),
        );
    }
}
