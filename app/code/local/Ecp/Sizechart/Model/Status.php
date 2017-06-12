<?php
/**
 * Description of Sizechart
 *
 * @category    Ecp
 * @package     Ecp_Sizechart
 */
class Ecp_Sizechart_Model_Status extends Varien_Object
{
    const STATUS_ENABLED	= 1;
    const STATUS_DISABLED	= 2;

    static public function getOptionArray()
    {
        return array(
            self::STATUS_ENABLED    => Mage::helper('ecp_sizechart')->__('Enabled'),
            self::STATUS_DISABLED   => Mage::helper('ecp_sizechart')->__('Disabled')
        );
    }
}