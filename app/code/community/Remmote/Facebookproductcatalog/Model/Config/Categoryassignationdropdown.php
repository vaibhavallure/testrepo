<?php
/**
 * @extension   Remmote_Facebookproductcatalog
 * @author      Remmote    
 * @copyright   2018 - Remmote.com
 * @descripion  Category assignation type dropdown
 */

class Remmote_Facebookproductcatalog_Model_Config_Categoryassignationdropdown
{
    /**
     * Frequency dropdown
     * @return [type]
     * @author edudeleon
     * @date   2016-12-26
     */
    public function toOptionArray()
    {
        return
            array(
                array(
                    'value' => 'parent',
                    'label' => 'Assign parent category (Highest tree category)'
                ),
                array(
                    'value' => 'child',
                    'label' => 'Assign child category (Lowest tree category)'
                )
            );
    }
}