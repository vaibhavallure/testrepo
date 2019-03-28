<?php
class Teamwork_Service_Model_Ecm extends Mage_Core_Model_Abstract
{
    const ECM_STATUS_LOADING = 'loading';
    const ECM_STATUS_NEW = 'new';
    const ECM_STATUS_PROCESSING = 'processing';
    const ECM_STATUS_REINDEX = 'reindex';
    const ECM_STATUS_DONE = 'done';
    const ECM_STATUS_ERROR = 'error';
    
    protected $_xml, $_db, $_response;
    protected $_statuses = array(
        self::ECM_STATUS_LOADING        => 'Wait',
        self::ECM_STATUS_NEW            => 'Wait',
        self::ECM_STATUS_PROCESSING     => 'Process',
        self::ECM_STATUS_REINDEX        => 'Process',
        self::ECM_STATUS_DONE           => 'Success',
        self::ECM_STATUS_ERROR          => 'Error',
    );

    public function _construct()
    {
        $this->_db = Mage::getModel('teamwork_service/adapter_db');
    }

    public function generateXml($xml)
    {
        $this->_response = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><EcmHeaders></EcmHeaders>');
        if(!empty($xml))
        {
            $this->_xml = simplexml_load_string($xml);

            foreach($this->_xml as $ecmHeader)
            {
                $info = $this->_db->getOne('service', array('request_id' => $ecmHeader['EcmHeaderId']));
                if(empty($info))
                {
                    $info['request_id'] = $ecmHeader['EcmHeaderId'];
                }
                $this->generateOperations($info);
            }

            if(!empty($ecmHeader['EcmHeaderId']))
            {
                Mage::helper('teamwork_service')->runStaging($ecmHeader['EcmHeaderId']);
            }
        }
        
        return base64_encode($this->_response->asXML());
    }

    protected function generateOperations($info)
    {
        if(!empty($info['response']))
        {
            $response = @unserialize($info['response']);

            $ecm = $this->_response->addChild('EcmHeader');
            $ecm->addAttribute('EcmHeaderId', $info['request_id']);
            $operations = $ecm->addChild('Operations');
            
            $warningsInEcm = false;
            if(!empty($response))
            {
                foreach($response as $key => $val)
                {
                    $operation = $operations->addChild('Operation');
                    $operation->addAttribute('type', $key);

                    $operation->addChild('Status', $val['status']);
                    $warnings = $operation->addChild('Warnings');

                    if(!empty($val['warnings']))
                    {
                        $warningsInEcm = true;
                        foreach($val['warnings'] as $w)
                        {
                            $warning = $warnings->addChild('Warning', $w);
                            $warning->addAttribute('code', 0);
                        }
                    }

                    $errors = $operation->addChild('Errors');
                    if(!empty($val['errors']))
                    {
                        foreach($val['errors'] as $e)
                        {
                            $error = $errors->addChild('Error', $e);
                            $error->addAttribute('code', 1);
                        }
                    }
                }
            }

            $ecm->addChild('Status', $this->_statuses[$info['status']]);
            $warnings = $ecm->addChild('Warnings');
            if($warningsInEcm)
            {
                $warning = $warnings->addChild('Warning', 'ecm contains warnings');
            }
            
            $ecm->addChild('Errors');
        }
        else
        {
            $ecm = $this->_response->addChild('EcmHeader');
            $ecm->addAttribute('EcmHeaderId', $info['request_id']);
            $ecm->addChild('Status', $this->_statuses['error']);
            $ecm->addChild('Warnings');
            $ecm->addChild('Errors');
        }
    }
}