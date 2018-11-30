<?php

class Allure_Virtualstore_Model_Resource_Website_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('allure_virtualstore/website');
    }
    
    /**
     * Convert items array to array for select options
     *
     * @return Array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('website_id', 'name');
    }
    
    /**
     * Join group and store info from appropriate tables.
     * Defines new _idFiledName as 'website_group_store' bc for
     * one website can be more then one row in collection.
     * Sets extra combined ordering by group's name, defined
     * sort ordering and store's name.
     *
     * @return Mage_Core_Model_Resource_Website_Collection
     */
    public function joinGroupAndStore()
    {
        if (!$this->getFlag('groups_and_stores_joined')) {
            $this->_idFieldName = 'website_group_store';
            $this->getSelect()->joinLeft(
                array('group_table' => $this->getTable('core/store_group')),
                'main_table.website_id = group_table.website_id',
                array('group_id' => 'group_id', 'group_title' => 'name')
                )->joinLeft(
                    array('store_table' => $this->getTable('core/store')),
                    'group_table.group_id = store_table.group_id',
                    array('store_id' => 'store_id', 'store_title' => 'name')
                    );
                $this->addOrder('group_table.name', Varien_Db_Select::SQL_ASC)       // store name
                ->addOrder('CASE WHEN store_table.store_id = 0 THEN 0 ELSE 1 END', Varien_Db_Select::SQL_ASC) // view is admin
                ->addOrder('store_table.sort_order', Varien_Db_Select::SQL_ASC) // view sort order
                ->addOrder('store_table.name', Varien_Db_Select::SQL_ASC)       // view name
                ;
                $this->setFlag('groups_and_stores_joined', true);
        }
        return $this;
    }

}
