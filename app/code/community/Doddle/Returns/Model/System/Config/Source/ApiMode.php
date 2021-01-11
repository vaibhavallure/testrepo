<?php
class Doddle_Returns_Model_System_Config_Source_ApiMode
{
    const API_MODE_LIVE = 'live';
    const API_MODE_TEST = 'test';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::API_MODE_LIVE, 'label' => Mage::helper('doddle_returns')->__(self::API_MODE_LIVE)),
            array('value' => self::API_MODE_TEST, 'label' => Mage::helper('doddle_returns')->__(self::API_MODE_TEST))
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
            self::API_MODE_LIVE => Mage::helper('doddle_returns')->__(self::API_MODE_LIVE),
            self::API_MODE_TEST => Mage::helper('doddle_returns')->__(self::API_MODE_TEST),
        );
    }
}
