<?php

class Ecp_Footlinks_Model_Type extends Varien_Object
{
    const BLOCK_TYPE	= 1;
    const URL_TYPE	= 2;

    static public function getOptionArray()
    {
        return array(
            self::BLOCK_TYPE    => Mage::helper('footlinks')->__('Block'),
            self::URL_TYPE   => Mage::helper('footlinks')->__('Url')
        );
    }
}