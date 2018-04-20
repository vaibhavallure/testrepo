<?php
class Teamwork_Service_Model_Adminhtml_Collectiondb extends Varien_Data_Collection_Db{
 
    public $_entityName = '';
    
    public function getAllIds()
    {
        $data = $this->_fetchAll('SELECT * FROM '. $this->_entityName);
        
        $ids = array();
        
        foreach ($data as $item) {
            $ids[] = $item['entity_id'];
        }
        return $ids;
    }
}
