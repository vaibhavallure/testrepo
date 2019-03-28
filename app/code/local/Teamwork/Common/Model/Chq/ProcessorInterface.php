<?php
interface Teamwork_Common_Model_Chq_ProcessorInterface
{
    public function getFormatedDataForRegisterApi(Varien_Object $chqStaging);
    public function getFormatedDataForStatusApi(Varien_Object $chqStaging);
    public function deserialize($string);
    public function convertDocumentIntoStaging($responseObject, $awaitingDocument);
}
