<?php

class Mage_Core_Model_System_Config_Source_SameSite
{
    private $options
        = [
            'Lax'    => 'Lax - modern default',
            'Strict' => 'Strict - most secure',
            'None'   => 'None - least problems expected (but must be secure/HTTPS)'
        ];

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $return = [];
        foreach ($this->options as $key => $value) {
            $return[] = ['value' => $key, 'label' => $this->getHelper()->__($value)];
        }

        return $return;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $return = [];
        foreach ($this->options as $key => $value) {
            $return[$key] = $this->getHelper()->__($value);
        }

        return $return;
    }

    /**
     * @return Mage_Core_Helper_Abstract
     */
    private function getHelper()
    {
        return Mage::helper('core');
    }
}
