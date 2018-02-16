<?php

class Teamwork_Service_Model_Resource_Confattrmapprop extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_mainTableFields =  array(
                    'chq_entity_id' => 'main.entity_id',
                    'chq_attribute_set_id' => 'main.attribute_set_id',
                    'chq_code' => 'main.code',
                    'chq_description' => 'main.description',
                    'chq_alias' => 'main.alias',
                    'chq_internal_id' => 'main.internal_id',
                    'values_mapping' => 'main.values_mapping',
                    'is_active' => 'main.is_active',
                );
    protected $_attributeTableFields = array(
                    'magento_attribute_code' => 'atr.attribute_code', 
                    'magento_frontend_label' => 'atr.frontend_label',
                    'magento_attribute_id'   => 'atr.attribute_id',
                );
    
    public function _construct()
    {
        $this->_init('teamwork_service/service_attribute_set', 'entity_id');
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param Mage_Core_Model_Abstract $object
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        if ($field == $this->getIdFieldName())
        {
            $field = $this->_getAliasFromTableField('main.' . $field);
        }
        return $this->getRawSelect()->where($this->_getTableFieldFromAlias($field) . '=?', $value);
    }

    public function getRawSelect()
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('main'=>$this->getMainTable()), $this->_mainTableFields)
            ->joinLeft(array('atr' => $this->getTable('eav/attribute')), 'atr.attribute_id=main.internal_id', $this->_attributeTableFields);
        return $select;         
    }

    protected function _getTableFieldFromAlias($alias)
    {
        $list = $this->getFieldsAliases();
        if (!isset($alias, $list))
        {
            Mage::throwException('Wrong field name');
        }
        if (isset($alias, $this->_mainTableFields)) return $this->_mainTableFields[$alias];
        if (isset($alias, $this->_attributeTableFields)) return $this->_attributeTableFields[$alias];
        Mage::throwException('Wrong field name'); 
    }

    protected function _getAliasFromTableField($field, $throwEx = true)
    {
        foreach ($this->getFieldsAliases() as $alias => $tableField)
        {
            if ($field == $tableField) return $alias;
        }

        if ($throwEx) Mage::throwException('Wrong field name');

        return false;
    }
    
    /**
     * Delete the object - it is not allowed to delete this records
     *
     * @param Varien_Object $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    public function delete(Mage_Core_Model_Abstract $object)
    {
        return $this;
    }
    
    /**
     * Prepare data for passed table
     *
     * @param Varien_Object $object
     * @param string $table
     * @return array
     */
    protected function _prepareDataForTable(Varien_Object $object, $table)
    {
        $data = array();
        $fields = $this->_getWriteAdapter()->describeTable($table);
        foreach (array_keys($fields) as $field) {
            $alias = $this->_getAliasFromTableField('main.' . $field, false);
            if ($alias !== false && $object->hasData($alias)) {
                $fieldValue = $object->getData($alias);
                if (null !== $fieldValue) {
                    $fieldValue   = $this->_prepareTableValueForSave($fieldValue, $fields[$field]['DATA_TYPE']);
                    $data[$field] = $this->_getWriteAdapter()->prepareColumnValue($fields[$field], $fieldValue);
                } else if (!empty($fields[$field]['NULLABLE'])) {
                    $data[$field] = null;
                }
            }
        }
        return $data;
    }
    
    public function getFieldsAliases()
    {
        return array_merge($this->_mainTableFields, $this->_attributeTableFields);
    }

}
