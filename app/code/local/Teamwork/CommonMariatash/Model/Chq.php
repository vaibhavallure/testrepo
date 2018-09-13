<?php
class Teamwork_CommonMariatash_Model_Chq extends Teamwork_Common_Model_Chq
{
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
            $lastUpdatedTime = null;
            if($chqHelper->isWaitStatus($status) && $this->_isDocumentWaitingOverdue($awaitingDocument))
            {
                $status = Teamwork_Common_Model_Chq_Api_Status::CHQ_API_STATUS_ERROR;
            }
            
            if( $chqHelper->isSuccessfulStatus($status) )
            {
                $lastUpdatedTime = Mage::getModel('teamwork_common/chq_xml_response')->getLastUpdatedTimeFromResponse($responseObj);
                
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
}