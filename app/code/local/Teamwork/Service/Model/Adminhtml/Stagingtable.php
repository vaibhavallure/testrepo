<?php
class Teamwork_Service_Model_Adminhtml_Stagingtable extends Varien_Data_Collection{
 
    private $_isLoaded = false;
    private $_direction = "DESC";
    private $_entitiesList;

    public function load($printQuery = false, $logQuery = false)
    {
        $this->_entitiesList = $this->getEntitiesList();
        
        if ($this->_isLoaded) {
            return $this;
        }
        
        $this->clear();
        
        if ($this->_direction == "ASC")
        {
            $this->_entitiesList = array_reverse($this->_entitiesList);
        }
        
        $this->_totalRecords = count($this->_entitiesList);
        
        $this->_entitiesList = array_slice($this->_entitiesList, $this->_curPage * $this->_pageSize - $this->_pageSize, $this->_pageSize);
            
        foreach($this->_entitiesList as $row)
        {
            $rowObj = new Varien_Object();
            $rowObj->setData($row);
            $this->addItem($rowObj);
        }
        
        return $this;
    }
 
    public function addFieldToFilter($field, $condition = null)
    {
        $this->clear();
        
        $mixed_search = array("%", "'", "\\");
        
        foreach ($condition as $key => $value)
        {
            $value = str_replace($mixed_search, "", $value);
        }
        
        $this->_entitiesList = $this->getEntitiesList();
        
        foreach($this->_entitiesList as $row)
        {
            $rowObj = new Varien_Object();
            $pos = strpos($row[$field], $value);
            
            if ($pos !== false)
            {
                $rowObj->setData($row);
                $this->addItem($rowObj);
            }
        }
        
        $this->_totalRecords = count($this->_items);
        
        if ($this->_pageSize < count($this->_items))
        {
            $this->_items = array_slice($this->_items, $this->_curPage*$this->_pageSize-$this->_pageSize, $this->_pageSize);
        }
        
        $this->_isLoaded = true;
   
        return $this;
    }
    
    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        if ($direction == "ASC")
        {
            $this->_items = array_reverse($this->_items);
        }
        
        $this->_direction = $direction;
        
        return $this;
    }
    
    private function getEntitiesList()
    {
        return Mage::getConfig()->getNode('global/models/service_resource/entities')->asArray();
    }
}
