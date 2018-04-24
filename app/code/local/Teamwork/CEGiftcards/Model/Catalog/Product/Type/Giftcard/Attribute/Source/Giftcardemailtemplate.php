<?php

class Teamwork_CEGiftcards_Model_Catalog_Product_Type_Giftcard_Attribute_Source_Giftcardemailtemplate
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
        $result = array();
        $template = Mage::getModel('adminhtml/system_config_source_email_template');
        $template->setPath(Teamwork_CEGiftcards_Helper_Data::XML_PATH_EMAIL_TEMPLATE);
        $helper = Mage::helper('teamwork_cegiftcards');
        foreach ($template->toOptionArray() as $one) {
            $result[$one['value']] = $helper->escapeHtml($one['label']);
        }
        return $result;

    }
}
