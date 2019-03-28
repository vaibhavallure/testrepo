<?php

class Teamwork_Common_Model_Staging_Resource_Chq extends Teamwork_Common_Model_Staging_Resource_Abstract
{
    public function _construct()
    {
        $this->_init('teamwork_common/service_chq', 'entity_id');
    }
    
    public function getMaxDateByType()
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from($this->getMainTable(), array('api_type','max_last_updated_time'=>'max(last_updated_time)'))
            ->where('last_updated_time IS NOT NULL')
        ->group('api_type');
        return $read->fetchPairs($select);
    }
    
    public function getDocumentIdsByDates($datesByType)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()->from($this->getMainTable(), array('entity_id'));
        
        $orWhere = false;
        foreach ($datesByType as $type => $date)
        {
            if ($orWhere)
            {
                $select->orWhere("(api_type = '{$type}' AND last_updated_time = '{$date}')");
            }
            else
            {
                $select->where("(api_type = '{$type}' AND last_updated_time = '{$date}')");
                $orWhere = true;
            }
        }
        $str = (string)$select;
        return $read->fetchCol($select);
    }
}