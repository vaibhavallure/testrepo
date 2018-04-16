<?php

abstract class Teamwork_Common_Model_Staging_Resource_Abstract extends Mage_Core_Model_Mysql4_Abstract
{
    public function loadByAttributes($attributes)
    {
        $adapter = $this->_getReadAdapter();
        $where   = array();
        foreach ($attributes as $attributeCode=> $value) {
            $where[] = sprintf('%s=:%s', $attributeCode, $attributeCode);
        }
        $select = $adapter->select()
            ->from($this->getMainTable())
        ->where(implode(' AND ', $where));

        return $adapter->fetchRow($select, $attributes);
    }
    
    public function deleteEntities($where)
    {
        $this->_getWriteAdapter()->delete($this->getMainTable(), $where);
    }
}