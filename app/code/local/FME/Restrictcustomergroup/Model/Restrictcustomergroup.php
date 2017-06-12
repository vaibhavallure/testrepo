<?php

class FME_Restrictcustomergroup_Model_Restrictcustomergroup extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('restrictcustomergroup/restrictcustomergroup');
    }
    
    /**
     * Retrieve related products
     * @return array
     */
    public function getBlocksRelated($id) {
        
        $blocksTable = Mage::getSingleton('core/resource')
            ->getTableName('fme_restrictcustomergroup_blocks');
           
        $collection = Mage::getModel('restrictcustomergroup/restrictcustomergroup')->getCollection()
                ->addRulesFilter($id);
        //echo '<pre>';print_r($collection->getData());exit;
        $collection->getSelect()
                ->joinLeft(
                    array('related' => $blocksTable),
                    'main_table.rule_id = related.rule_id'
                 )
                ->order('main_table.rule_id');
        //echo (string) $collection->getSelect();exit;
        return $collection->getData();
    }
    
    protected function _afterLoad() {
        
        if ($this->getData('condition_serialized') && is_string($this->getData('condition_serialized'))) {
            
            $this->setData('condition_serialized', @unserialize($this->getData('condition_serialized')));
            
        }
       
        //if ($this->getData('stores') !== null && is_string($this->getData('stores'))) {
        //    $this->setData('stores', @explode(',', $this->getData('stores')));
        //}
        $this->humanizeData();
        
        return parent::_afterLoad();
    }

    protected function _beforeSave() {
        
		if(is_object($this->getData('condition_serialized'))) {
			$this->unsConditionSerialized();
		}
        if ($this->getData('condition_serialized') && is_array($this->getData('condition_serialized'))) { 
            $this->setData('condition_serialized', serialize($this->getData('condition_serialized')));
        }

        //if ($this->getData('store') !== null && is_array($this->getData('store')))
        //    $this->setData('store', @implode(',', $this->getData('store')));
            
        return parent::_beforeSave();
    }
    
    public function getTypeById($id)
    {
        $block = $this->load($id);
        
        return $block->getType();
    }
	
	public function humanizeData()
    {
        if (is_array($this->getData('condition_serialized')))
            $this->setData('condition_serialized', new Varien_Object($this->getData('condition_serialized')));
            
        return $this;
    }

    public function callAfterLoad()
    {
        return $this->_afterLoad();
    }
    
    public function assosiatedBlockIds($id, $status = true) {
        
        $res = Mage::getSingleton('core/resource');
        $_read = $res->getConnection('core_read');
        $_blockTable = $res->getTableName('fme_restrictcustomergroup_blocks');
        
        $select = $_read->select()
                   ->from(array('b' => $_blockTable), 'b.block_id')
                   ->join(
                        array('main_table' => $res->getTableName('fme_restrictcustomergroup')),
                        'main_table.rule_id = b.rule_id'
                    )
                   ->where('b.rule_id = (?)', $id);
                    
        if ($status) {
            $select = $select->where('main_table.status = (?)', '1' );
        } 
        //$read->query($select);
        return $_read->fetchCol($select);
    }
}