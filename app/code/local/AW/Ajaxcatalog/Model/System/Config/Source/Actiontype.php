<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Ajaxcatalog
 * @version    2.0.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


/**
 * Action Type
 */
class AW_Ajaxcatalog_Model_System_Config_Source_Actiontype
{
    const TYPE_BUTTON_VALUE = 'button';
    const TYPE_SCROLL_VALUE = 'scroll';

    const TYPE_BUTTON_LABEL = 'Show more button';
    const TYPE_SCROLL_LABEL = 'Auto-appearing on scrolling';


    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public static function toArray()
    {
        $helper = Mage::helper('aw_ajaxcatalog');
        return array(
            self::TYPE_BUTTON_VALUE => $helper->__(self::TYPE_BUTTON_LABEL),
            self::TYPE_SCROLL_VALUE => $helper->__(self::TYPE_SCROLL_LABEL),
        );
    }

    /**
     * Options getter
     *
     * @return array
     */
    public static function toOptionArray()
    {
        $return = array();
        foreach (self::toArray() as $value => $label) {
            $return[] = array(
                'value' => $value, 'label' => $label
            );
        }
        return $return;
    }
}