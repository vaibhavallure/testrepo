<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

class Simtech_Searchanise_Model_System_Config_Source_Searchanise_Attr_Summary
{
    /**
     * Retrieve option values array
     *
     * @return array
    */
    public function toOptionArray()
    {
        return array(
            array('value' => 'short_description', 'label' => Mage::helper('searchanise')->__('Short Description')),
            array('value' => 'description', 'label' => Mage::helper('searchanise')->__('Description')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'short_description' => Mage::helper('searchanise')->__('Short Description'),
            'description' => Mage::helper('searchanise')->__('Description'),
        );
    }
}
