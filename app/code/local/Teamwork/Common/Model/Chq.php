<?php
class Teamwork_Common_Model_Chq
{
    protected $_chqApiScriptName = 'Api.ashx';
    protected $_chqApiStatusScriptName = 'ApiStatus.ashx';
    protected $_processor;
    //TODO rename folder Xml into Format/Xml; Xml.php transfer into Format folder; add class Format
    
    public function __construct()
    {
        $this->_processor = Mage::getModel('teamwork_common/chq_' . Mage::helper('teamwork_common/format')->getFormat());
    }
    
    public function generateEcm($apiType,$forceRegistration=false)
    {
        if( !count(Mage::getModel('teamwork_common/staging_chq')->getAwaitingDocuments()) || $forceRegistration ) //TODO? AND when success and are not fully convertDocumentIntoEcm
        {
            foreach(Mage::getModel('teamwork_common/chq_api_dependency')->getDependency($apiType) as $dependedType)
            {
                $this->generateEcm( $dependedType, true );
            }
            
            if( Mage::helper('teamwork_common/staging_chq')->allowProcess($apiType) )
            {
                $this->_registrateDocument( $apiType );
            }
        }
        if(!$forceRegistration)
        {
            $this->_checkAwaitingDocuments();
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
        
        $registerData = $this->_processor->getFormatedDataForRegisterApi($chqStaging);
        $response = Mage::getModel('teamwork_common/chq_http')->request($this->_getApiRegisterUrl(), $registerData);
        if($response)
        {
            $this->_processor->deserialize($response);
            if($this->_processor->getData('ApiDocumentId') && $this->_processor->getData('Status'))
            {
                $chqStaging->setDocumentId( $this->_processor->getData('ApiDocumentId') );
                $chqStaging->setStatus( $this->_processor->getData('Status') );
                try
                {
                    $chqStaging->save();
                }
                catch(Exception $e)
                {
                    Mage::log($e->getMessage());
                    //TODO ERROR HANDLE
                }
            }
        }
    }
    
    protected function _checkAwaitingDocuments()
    {
        foreach(Mage::getModel('teamwork_common/staging_chq')->getAwaitingDocuments() as $awaitingDocument)
        {
            if( $this->checkDocumentStatus($awaitingDocument) )
            {
                $this->_checkAwaitingDocuments();
                break;
            }
        }
    }
    
    public function checkDocumentStatus($awaitingDocument)
    {
        $helper = Mage::helper('teamwork_common/staging_chq');
        $response = Mage::getModel('teamwork_common/chq_http')->request( $this->_getApiStatusUrl(), $this->_processor->getFormatedDataForStatusApi($awaitingDocument) );
        if($response)
        {
            $this->_processor->deserialize($response);
            
            $status = $this->_processor->getData('Status');
            if($helper->isWaitStatus($status) && $this->_isDocumentWaitingOverdue($awaitingDocument))
            {
                $status = Teamwork_Common_Model_Chq_Api_Status::CHQ_API_STATUS_ERROR;
            }
            
            if( $helper->isSuccessfulStatus($status) )
            {
                $awaitingDocument->setLastUpdatedTime( $this->_processor->getData('ApiRequestTime') );
                
                $this->_processor->convertDocumentIntoEcm($awaitingDocument);
                if( $this->_processor->continueCallChain() && !$awaitingDocument->getParentDocumentId() )
                {
                    $this->_createChainDocuments($awaitingDocument);
                }
            }
            elseif( $helper->isErrorStatus($status) && $awaitingDocument->getTry() < 3 ) //TODO remove hardcode: 3
            {
                $awaitingDocument->setTry( $awaitingDocument->getTry() + 1 );
                $awaitingDocument->setCreatedAt( $awaitingDocument->getUpdatedAt());
                $awaitingDocument->unsDocumentId();
                $awaitingDocument->unsStatus();
                $this->_registrateDocument($awaitingDocument->getApiType(), $awaitingDocument);
                return true;
            }
            $awaitingDocument->setStatus( $status );
            
            try
            {
                $awaitingDocument->save();
            }
            catch(Exception $e)
            {
                Mage::log($e->getMessage()); //TODO ERROR HANDLE
            }
        }
    }
    
    protected function _createChainDocuments($awaitingDocument)
    {
        $totalRecords = $this->_processor->getData('TotalRecords');
        $entitiesPerChunk = (int)(Mage::helper('teamwork_common/adminsettings')->getEntitiesPerButch());
        
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
    
    protected function _isDocumentWaitingOverdue($document)
    {
        //TODO: remove hardcode: 20
        return (((strtotime(Varien_Date::now()) - strtotime($document->getCreatedAt())) / 60) > 5) ? true : false ;
    }
}