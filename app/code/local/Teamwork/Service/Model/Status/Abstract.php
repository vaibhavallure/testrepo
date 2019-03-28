<?php
class Teamwork_Service_Model_Status_Abstract extends Mage_Core_Model_Abstract
{
    protected $_db, $_xml, $_status, $_requestId;

    /**
     * Object for parsing XML
     *
     * @var Teamwork_Service_Helper_Parse
     */
    protected $_parser;

    protected $_errorLevels = array(
        'success' => 'Success',
        'error'   => 'Error',
        'warning' => 'Warning'
    );

    protected function _construct()
    {
        //header('Content-Type: text/xml');
        $this->_db     = Mage::getModel('teamwork_service/adapter_db');
        $this->_parser = Mage::helper('teamwork_service/parse');
        $this->_helper = Mage::helper('teamwork_service');
    }

    protected function response($errors = null)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><Response xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://microsoft.com/wsdl/types/"></Response>';
        $response = new SimpleXMLElement($xml);

        $response->addAttribute('RequestId', $this->_requestId);
        $status = !empty($errors) ? $this->_errorLevels['warning'] : $this->_errorLevels['success'];
        $response->addChild('Status', $status);

        $errorsNode = $response->addChild('Errors');
        foreach ($errors as $error)
        {
            $errorsNode->addChild('Error', $error);
        }

        return base64_encode($response->asXML());
    }

    /**
     * Extracts all errors from given response
     *
     * @param  array $response response obtained from cUrl requests
     *
     * @return array
     */
    protected function _getErrorsFromResponse($response)
    {
        $result = array();
        if(!empty($response))
        {
            foreach($response as $orderStatusErrors)
            {
                if ($orderStatusErrors = json_decode($orderStatusErrors))
                {
                    foreach ($orderStatusErrors as $error)
                    {
                        $result[] = $error;
                    }
                }
            }
        }

        return $result;
    }


}