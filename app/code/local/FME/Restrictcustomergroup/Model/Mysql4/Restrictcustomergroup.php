<?php

class FME_Restrictcustomergroup_Model_Mysql4_Restrictcustomergroup
    extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the rule_id refers to the key field in your database table.
        $this->_init('restrictcustomergroup/restrictcustomergroup', 'rule_id');
    }
    /**
     * Assign page to store views
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Cms_Model_Resource_Page
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object) {
        
        $this->_saveBlock($object); // saving block ids
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = (array)$object->getStores();
        if (empty($newStores)) {
            $newStores = (array)$object->getStoreId();
        }
        $table  = $this->getTable('restrictcustomergroup/restrictcustomergroup_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);

        if ($delete)
        {
            $where = array(
                'rule_id = ?'     => (int) $object->getId(),
                'store_id IN (?)' => $delete
            );

            $this->_getWriteAdapter()->delete($table, $where);
        }

        if ($insert)
        {
            $data = array();

            foreach ($insert as $storeId)
            {
                $data[] = array(
                    'rule_id'  => (int) $object->getId(),
                    'store_id' => (int) $storeId
                );
            }

            $this->_getWriteAdapter()->insertMultiple($table, $data);
        }
        
        return parent::_afterSave($object);
    }
    /**
     * Perform operations after object load
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Cms_Model_Resource_Page
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object) {
        
        if ($object->getId())
        {
            $stores = $this->lookupStoreIds($object->getId());

            $object->setData('store_id', $stores);
            $object->setData('stores', $stores);
            //$blocks = $this->lookupBlockIds($object->getId());
            //
            //$object->setData('blocks', $blocks);
            //$object->setData('blocks_id', $blocks);
        }

        return parent::_afterLoad($object);
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupStoreIds($pageId)
    {
        $adapter = $this->_getReadAdapter();

        $select  = $adapter->select()
            ->from($this->getTable('restrictcustomergroup/restrictcustomergroup_store'), 'store_id')
            ->where('rule_id = ?',(int)$pageId);

        return $adapter->fetchCol($select);
    }
    
    public function lookupBlockIds($id) {
        
        $adapter = $this->_getReadAdapter();

        $select  = $adapter->select()
            ->from($this->getTable('restrictcustomergroup/restrictcustomergroup_blocks'), 'block_id')
            ->where('rule_id = ?',(int)$id);

        return $adapter->fetchCol($select);
    }
    
    protected function _saveBlock(Mage_Core_Model_Abstract $obj)
    {
        $oldBlockIds = array();
        $oldBlockIds = $this->lookupBlockIds($obj->getId());
        $newBlockIds = array();
        $links = $obj['links'];
        
        if (isset($links['blocks_related']))
        {
            $newBlockIds = Mage::helper('adminhtml/js')->decodeGridSerializedInput($links['blocks_related']);
            
            $table  = $this->getTable('restrictcustomergroup/restrictcustomergroup_blocks');
            $insert = array_diff($newBlockIds, $oldBlockIds);
            $delete = array_diff($oldBlockIds, $newBlockIds);
            
            if ($delete)
            {
                $where = array(
                    'rule_id = ?'     => (int) $obj->getId(),
                    'block_id IN (?)' => $delete
                );
    
                $this->_getWriteAdapter()->delete($table, $where);
            }
    
            if ($insert)
            {
                $data = array();
    
                foreach ($insert as $id)
                {
                    $data[] = array(
                        'rule_id'  => (int) $obj->getId(),
                        'block_id' => (int) $id
                    );
                }
    
                $this->_getWriteAdapter()->insertMultiple($table, $data);
            }
        }
        return $this;
    }
    
    //public function assosiatedBlockIds(FME_Restrictcustomergroup_Model_Restrictcustomergroup $obj, $id, $status = true) {
    //    
    //    $select = $this->_getReadAdapter()
    //                ->getSelect('b.block_id')
    //                ->from(array('b' => $this->getTable('fme_restrictcustomergroup_blocks')))
    //                ->join($this->getMainTable(), $this->getMainTable().'rule_id = b.rule_id')
    //                ->where('b.rule_id = (?)', $id);
    //                
    //    if ($status) {
    //        $select = $select->where($this->getMainTable().'status = (?)', '1' );
    //    }
    //    
    //    return $this->_getReadAdapter()
    //                ->fetchAll($select);
    //}
}