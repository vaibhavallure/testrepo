<?php

class Teamwork_Common_Model_Staging_Chq extends Teamwork_Common_Model_Staging_Abstract
{
    protected $_guidField = 'document_id';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_common/chq');
    }
    
    public function getAwaitingDocuments()
    {
        $helper = Mage::helper('teamwork_common/staging_chq');
        return $this->getCollection()
            ->addFieldToFilter(
                'status', array('in' => $helper->getWaitStatuses())
            )
            ->setOrder('entity_id', Varien_Data_Collection::SORT_ORDER_ASC)
        ->load();
    }
    
    public function getWaitingDocumentsByHostId($hostDocumentId, $skipDocumentId)
    {
        $helper = Mage::helper('teamwork_common/staging_chq');
        return $this->getCollection()
            ->addFieldToFilter(
                'host_document_id', array('in' => $hostDocumentId)
            )
            ->addFieldToFilter(
                'document_id', array('nin' => $skipDocumentId)
            )
            ->addFieldToFilter(
                'status', array('in' => $helper->getWaitStatuses())
            )
        ->load();
    }
    
    public function getMaxDateByType($type)
    {
        return $this->getCollection()
            ->addFieldToFilter(
                'api_type', array('eq' => $type)
            )
            ->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('MAX(last_updated_time)')
            ->group('api_type')
            ->query()
        ->fetchColumn();
    }
    
    public function сleanUpChqDocuments()
    {
        $resource = $this->_getResource();
        
        $where = array(
            'status NOT IN (?)' => Mage::helper('teamwork_common/staging_chq')->getWaitStatuses()
        );
        
        if($datesByType = $resource->getMaxDateByType())
        {
            $usefulIds = $resource->getDocumentIdsByDates($datesByType);
            if($usefulIds)
            {
                $where['entity_id NOT IN (?)'] = $usefulIds;
            }
        }
        
        $resource->deleteEntities($where);
    }
    
    protected function _beforeSave()
    {
        $currentTime = Varien_Date::now();
        if( !$this->getEntityId() && !$this->getCreatedAt() )
        {
            $this->setCreatedAt($currentTime);
        }
        $this->setUpdatedAt($currentTime);
        
        return parent::_beforeSave();
    }
}