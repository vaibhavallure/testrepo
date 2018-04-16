<?php

class Teamwork_Service_Model_Confattrmapprop extends Mage_Core_Model_Abstract
{
    const VALUESMAPPING_VALUE = 'value';
    const VALUESMAPPING_ALIAS = 'alias_value';
    const VALUESMAPPING_ALIAS2 = 'alias2_value';
    const VALUESMAPPING_ALIAS_ALIAS2 = 'alias_alias2_value';
    const VALUESMAPPING_ALIAS2_ALIAS = 'alias2_alias_value';
    
    protected $_eventPrefix = 'confattrmapprop';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_service/confattrmapprop');
    }

    public function getId()
    {
        return $this->_getData('chq_entity_id');
    }
    
    /**
     * Delete object from database
     *
     * @return Mage_Core_Model_Abstract
     */
    public function delete()
    {
        return $this;
    }
    
    static public function getValuesMappingOptions()
    {
        $helper = Mage::helper('teamwork_service');
        return array (
            self::VALUESMAPPING_VALUE => $helper->__('Value'),
            self::VALUESMAPPING_ALIAS => $helper->__('Alias -> Value'), 
            self::VALUESMAPPING_ALIAS2 => $helper->__('Alias2 -> Value'),
            self::VALUESMAPPING_ALIAS_ALIAS2 => $helper->__('Alias -> Alias2 -> Value'),
            self::VALUESMAPPING_ALIAS2_ALIAS => $helper->__('Alias2 -> Alias -> Value'),
        );
    }

    static public function getMappedAttributeValue($mapping, $value, $alias, $alias2)
    {
        if ($mapping !== self::VALUESMAPPING_VALUE
            && $mapping !== self::VALUESMAPPING_ALIAS
            && $mapping !== self::VALUESMAPPING_ALIAS2
            && $mapping !== self::VALUESMAPPING_ALIAS_ALIAS2
            && $mapping !== self::VALUESMAPPING_ALIAS2_ALIAS)
        {
            $mapping = self::VALUESMAPPING_VALUE;
        }

        $label = '';

        if (($mapping == self::VALUESMAPPING_ALIAS_ALIAS2
                || $mapping == self::VALUESMAPPING_ALIAS)
                && !empty($alias))
        {
            $label = $alias;
        }

        if (empty($label)
            && ($mapping == self::VALUESMAPPING_ALIAS_ALIAS2
                || $mapping == self::VALUESMAPPING_ALIAS2_ALIAS
                || $mapping == self::VALUESMAPPING_ALIAS2)
            && !empty($alias2))
        {
            $label = $alias2;
        }


        if (empty($label)
            && $mapping == self::VALUESMAPPING_ALIAS2_ALIAS
            && !empty($alias))
        {
            $label = $alias;
        }

        if (empty($label)) $label = $value;
        
        return $label;
    }
    
}
