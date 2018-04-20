<?php

class Teamwork_Common_Helper_Dates extends Mage_Core_Helper_Abstract
{
    public function getMagentoDatetime($datetime=null)
    {
        return date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp($datetime)); // TODO FIX
    }
}