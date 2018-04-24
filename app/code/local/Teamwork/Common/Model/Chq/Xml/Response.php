<?php
class Teamwork_Common_Model_Chq_Xml_Response
{
    const RESPONSE_BASE = 'teamwork_common/chq_xml_response_';
    public function getClassLoader($chqStaging, $awaitingDocument)
    {
        return Mage::getModel(
            self::RESPONSE_BASE . Teamwork_Common_Model_Chq_Api_Type::getClassByType($chqStaging->getData('ApiRequestType')),
            array('chqStaging' => $chqStaging, 'workingDocument' => $awaitingDocument)
        );
    }
}