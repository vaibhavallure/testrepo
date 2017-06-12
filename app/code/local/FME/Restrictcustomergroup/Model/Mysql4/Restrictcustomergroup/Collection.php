<?php

class FME_Restrictcustomergroup_Model_Mysql4_Restrictcustomergroup_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('restrictcustomergroup/restrictcustomergroup');
        $this->_map['fields']['rule_id'] = 'main_table.rule_id';
        $this->_map['fields']['store'] = 'store_table.store_id';
    }
    /**
     * Add filter by store
     *
     * @param int|Mage_Core_Model_Store $store
     * @param bool $withAdmin
     * @return FME_Restrictcustomergroup_Model_Mysql4_Restrictcustomergroup_Collection
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if ($store instanceof Mage_Core_Model_Store)
        {
            $store = array($store->getId());
        }

        if (!is_array($store))
        {
            $store = array($store);
        }

        if ($withAdmin)
        {
            $store[] = Mage_Core_Model_App::ADMIN_STORE_ID;
        }

        $this->addFilter('store', array('in' => $store), 'public');

        return $this;
    }
    /**
     * Join store relation table if there is store filter
     */
    protected function _renderFiltersBefore()
    {
        if ($this->getFilter('store'))
        {
            $this->getSelect()->join(
                array('store_table' => $this->getTable('restrictcustomergroup/restrictcustomergroup_store')),
                'main_table.rule_id = store_table.rule_id',
                array()
            )->group('main_table.rule_id');

            /*
             * Allow analytic functions usage because of one field grouping
             */
            $this->_useAnalyticFunction = true;
        }
        
        return parent::_renderFiltersBefore();
    }
    
    public function addRulesFilter($rule) {
        
        if (is_array($rule))
        {            
            $condition = $this->getConnection()->quoteInto('main_table.rule_id IN(?)', $rule);
        }
        else
        {
            $condition = $this->getConnection()->quoteInto('main_table.rule_id= (?)', $rule);    
        }

        return $this->addFilter('rule_id', $condition, 'string');
    }
    
    public function addCustomerGroupFilter($value) {
        $this->getSelect()
            ->where('find_in_set(?, customer_groups)', (int) $value);
        return $this;    
    }
    
    public function addValidationFilter($store, $customerGroupId)
    {
        //$this->addStoreFilter($store, false); //echo (string) $this->getSelect();exit;
        $this->getSelect()->join(
                array('store_table' => $this->getTable('restrictcustomergroup/restrictcustomergroup_store')),
                'main_table.rule_id = store_table.rule_id',
                array()
        );
        
        $this->getSelect()->where('find_in_set(?, store_table.store_id) OR find_in_set(0, store_table.store_id)', (int) $store);
        $this->getSelect()->where('find_in_set(?, customer_groups)', (int) $customerGroupId);
        $this->getSelect()->where('status = (?)', 1);
        //$this->setOrder('priority', 'ASC');

        return $this;
    }
}