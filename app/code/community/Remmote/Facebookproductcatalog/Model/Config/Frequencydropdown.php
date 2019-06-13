<?php
class Remmote_Facebookproductcatalog_Model_Config_Frequencydropdown
{

    /**
     * Frequency dropdown
     * @return [type]
     * @author edudeleon
     * @date   2016-12-26
     */
	public function toOptionArray()
    {
        $frequency = array(
            array(
                'value' => '1',
                'label' => 'Daily'
            ),
            array(
                'value' => '7',
                'label' => 'Weekly'
            )
        );

        return $frequency;
    }
}