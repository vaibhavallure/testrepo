<?php
class Teamwork_Common_Model_Chq
{
    protected $_chqApiScriptName = 'Api.ashx';
    protected $_chqApiStatusScriptName = 'ApiStatus.ashx';
    protected $_processor;
    
    public function __construct()
    {
        $this->_processor = Mage::getModel('teamwork_common/chq_xml');
    }
    
    public function generateEcm($apiType)
    {
        if( !count(Mage::getModel('teamwork_common/staging_chq')->getAwaitingDocuments()) )
        {
            $this->_buildWithPreDependency($apiType);
        }
        $this->_checkAwaitingDocuments();
    }
    
    protected function _buildWithPreDependency($apiType)
    {
        foreach(Mage::getModel('teamwork_common/chq_api_dependency')->getPreDependency($apiType) as $dependedType)
        {
            $this->_buildWithPreDependency( $dependedType );
        }
        
        if( Mage::helper('teamwork_common/staging_chq')->allowProcess($apiType) )
        {
            $this->_registrateDocument( $apiType );
        }
    }
    
    protected function _buildPostDependency($document)
    {
        foreach(Mage::getModel('teamwork_common/chq_api_dependency')->getPostDependency($document->getApiType()) as $dependedType)
        {
            if( Mage::helper('teamwork_common/staging_chq')->allowProcess($dependedType) && $document->getRunDependency() )
            {
                $hostDocument = new Varien_Object();
                $hostDocument->setHostDocumentId($document->getDocumentId());
                $this->_registrateDocument( $dependedType, $hostDocument );
            }
        }
    }
    
    protected function _registrateDocument($apiType, Varien_Object $mergeData=null)
    {
        $chqStaging = Mage::getModel('teamwork_common/staging_chq');
        $chqStaging->setDocumentId( Mage::helper('teamwork_common/guid')->generateGuid() );
        $chqStaging->setApiType( $apiType );
        
        $maxDate = Mage::getModel('teamwork_common/staging_chq')->getMaxDateByType($apiType);
        if($maxDate)
        {
            $chqStaging->setMaxDate($maxDate);
        }
        
        if( !empty($mergeData) )
        {
            $chqStaging->addData((array)$mergeData->getData());
        }
        
        $response = Mage::getModel('teamwork_common/chq_http')->request(
            $this->_getApiRegisterUrl(),
            $this->_processor->getFormatedDataForRegisterApi($chqStaging)
        );
        if($response)
        {
            $responseObj = $this->_processor->deserialize($response);
            if($responseObj->getData('ApiDocumentId') && $responseObj->getData('Status'))
            {
                $chqStaging->setDocumentId( $responseObj->getData('ApiDocumentId') )
                    ->setStatus( $responseObj->getData('Status') )
                ->save();
            }
        }
    }
    
    protected function _checkAwaitingDocuments()
    {
        $reInit = false;
        foreach(Mage::getModel('teamwork_common/staging_chq')->getAwaitingDocuments() as $awaitingDocument)
        {
            if($this->checkDocumentStatus($awaitingDocument))
            {
                $reInit = true;
            }
        }
        if($reInit)
        {
            $this->_checkAwaitingDocuments();
        }
    }
    
    public function checkDocumentStatus($awaitingDocument)
    {
        $response = Mage::getModel('teamwork_common/chq_http')->request(
            $this->_getApiStatusUrl(),
            $this->_processor->getFormatedDataForStatusApi($awaitingDocument)
        );
        $reInit = false;
        
        if($response)
        {
            $chqHelper = Mage::helper('teamwork_common/staging_chq');
            $responseObj = $this->_processor->deserialize($response);
        
            $status = $responseObj->getData('Status');
            $lastUpdatedTime = $responseObj->getData('ApiRequestTime');
            if($chqHelper->isWaitStatus($status) && $this->_isDocumentWaitingOverdue($awaitingDocument))
            {
                $status = Teamwork_Common_Model_Chq_Api_Status::CHQ_API_STATUS_ERROR;
            }
            
            if( $chqHelper->isSuccessfulStatus($status) )
            {
                $this->_processor->convertDocumentIntoStaging($responseObj, $awaitingDocument);
                
                if($awaitingDocument->getHostDocumentId() && $this->_isPostDependencyProcessed($awaitingDocument))
                {
                    $this->_createCallback($awaitingDocument);
                    $reInit = true;
                }
                elseif( $responseObj->getData('TotalRecords') &&
                    Teamwork_Common_Model_Chq_Api_Type::isChainedType($awaitingDocument->getApiType()) &&
                    !$awaitingDocument->getParentDocumentId() &&
                    !$awaitingDocument->getHostDocumentId()
                )
                {
                    $this->_createChainDocuments($awaitingDocument, $responseObj->getData('TotalRecords'));
                    $reInit = true;
                }
                
                if( $awaitingDocument->getApiType() == Teamwork_Common_Model_Chq_Api_Type::CHQ_API_TYPE_INVEN_PRICES_EXPORT &&
                    !$awaitingDocument->getParentDocumentId()
                ) //TODO change price hardcode!!!
                {
                    $lastUpdatedTime = $awaitingDocument->getCreatedAt();
                }
                
                $this->_buildPostDependency($awaitingDocument);
            }
            elseif( $chqHelper->isErrorStatus($status) && $awaitingDocument->getTry() < 3 ) //TODO(?) remove hardcode: 3
            {
                $this->_restartDocument($awaitingDocument);
                $reInit = true;
            }
            
            if($awaitingDocument->getHostDocumentId())
            {
                $lastUpdatedTime = null;
            }
            
            $awaitingDocument->setStatus( $status )
                ->setLastUpdatedTime( $lastUpdatedTime )
            ->save();
        }
        return $reInit;
    }
    
    protected function _createCallback($awaitingDocument)
    { 
        $hostDocument = Mage::getModel('teamwork_common/staging_chq')->loadByGuid($awaitingDocument->getHostDocumentId());
        if( !empty($hostDocument) )
        {
            $callMethod = Mage::getModel('teamwork_common/chq_api_dependency')->getPostDependencyCallback( $hostDocument->getApiType() );
            Mage::getModel('teamwork_common/chq_callback')->{$callMethod}($hostDocument);
        }
    }
    
    protected function _restartDocument($awaitingDocument)
    {
        $restartDocument = clone $awaitingDocument;
        $restartDocument->setTry( $restartDocument->getTry() + 1 );
        $restartDocument->setCreatedAt( $restartDocument->getUpdatedAt());
        $restartDocument->unsDocumentId();
        $restartDocument->unsStatus();
        $restartDocument->unsEntityId();
        $this->_registrateDocument($restartDocument->getApiType(), $restartDocument);
    }
    
    protected function _createChainDocuments($awaitingDocument, $totalRecords)
    {
        $entitiesPerChunk = (int)(Mage::helper('teamwork_common/adminsettings')->getEntitiesPerButch(
            Teamwork_Common_Model_Chq_Api_Type::getSettingForChunk($awaitingDocument->getApiType())
        ));
        
        if($totalRecords > $entitiesPerChunk)
        {
            $newDocument = new Varien_Object();
            $newDocument->setParentDocumentId($awaitingDocument->getDocumentId());
            
            $processed = $entitiesPerChunk;
            while($totalRecords>$processed)
            {
                $newDocument->setProcessed($processed);
                $this->_registrateDocument($awaitingDocument->getApiType(), $newDocument);
                $processed += $entitiesPerChunk; //TODO: registrate next only if previous registrated succesfully - 404 error
            }
        }
    }
    
    protected function _getApiRegisterUrl()
    {
        return Mage::helper('teamwork_common/adminsettings')->getServerLink() . $this->_chqApiScriptName;
    }
    
    protected function _getApiStatusUrl()
    {
        return Mage::helper('teamwork_common/adminsettings')->getServerLink() . $this->_chqApiStatusScriptName;
    }
    
    protected function _isPostDependencyProcessed($awaitingDocument)
    {
        $documents = Mage::getModel('teamwork_common/staging_chq')->getWaitingDocumentsByHostId(
            $awaitingDocument->getHostDocumentId(),
            $awaitingDocument->getDocumentId()
        );
        return !count($documents);
    }
    
    protected function _isDocumentWaitingOverdue($document)
    {
        return ( ((strtotime(Varien_Date::now()) - strtotime($document->getCreatedAt())) / 60) >
            Mage::helper('teamwork_common/adminsettings')->getWaitApiTime() ) ?
            true :
        false ; 
    }
}