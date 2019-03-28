<?php
class Teamwork_Service_Model_Api extends Mage_Api_Model_Resource_Abstract
{
    public function getdata($base64, $writeLog=true, $shouldParseXml = true, $runEcm = true)
    {
        Teamwork_Service_Helper_Log::logApiRequest(__METHOD__, $base64);

        $serviceModel = Mage::getModel('teamwork_service/service');
        $ecmHelper = Mage::helper('teamwork_service/ecm');
        
        $content = base64_decode($base64);
        $xml = simplexml_load_string($content);
        $request_id = (string)$xml["EcmHeaderId"];
        $serviceModel->setRequestId($request_id);

        $ecmHelper->prepareImageFolder();
        $ecmHelper->prepareEcmFolder($request_id);
        
        $serviceModel->registrateChunk($xml);
        $counter = $serviceModel->getCounter();

        if($writeLog)
        {
            $ecmHelper->writeLog($content,$request_id,$counter,$xml);
        }

        if( empty($counter) )
        {
            try
            {
                $ecmHelper->validate( (string)$xml->Channel );
            }
            catch(Exception $e)
            {
                $serviceModel->setEcmStatus('error');
                $response = $serviceModel->response( array($e->getMessage()), true );
				Teamwork_Service_Helper_Log::logApiResponse(__METHOD__, $content, $response);
                return $response;
            }
        }

        try
        {
            if( $shouldParseXml )
            {
                $serviceModel->parseXml($content);
            }
            
            $newCounter = $serviceModel->incrementCounter();
            if( $newCounter == $xml->NumberOfChunks )
            {
                $serviceModel->setEcmStatus('new');
                if( $runEcm )
                {
                    Mage::helper('teamwork_service')->runStaging($request_id);
                }
            }
        }
        catch(Exception $e)
        {
            Mage::log( $e->getMessage() );
        }

        $response = $serviceModel->response();

        Teamwork_Service_Helper_Log::logApiResponse(__METHOD__, $content, $response);
        return $response;
    }

    public function getorders($params = false)
    {
        Teamwork_Service_Helper_Log::logApiRequest(__METHOD__, $params);

        $service = Mage::getModel('teamwork_service/weborder');
        $response =  $service->generateXml($params);

        Teamwork_Service_Helper_Log::logApiResponse(__METHOD__, $params, $response);
        return $response;
    }

    public function getsettings($params = false)
    {
        Teamwork_Service_Helper_Log::logApiRequest(__METHOD__, $params);

        $service = Mage::getModel('teamwork_service/settings');
        $response = $service->generateXml($params);

        Teamwork_Service_Helper_Log::logApiResponse(__METHOD__, $params, $response);
        return $response;
    }

    public function getversion($params = false)
    {
        Teamwork_Service_Helper_Log::logApiRequest(__METHOD__, $params);

        $service = Mage::getModel('teamwork_service/version');
        $response = $service->generateXml();

        Teamwork_Service_Helper_Log::logApiResponse(__METHOD__, $params, $response);
        return $response;
    }

    public function setstatus($content = false)
    {
        Teamwork_Service_Helper_Log::logApiRequest(__METHOD__, $content);

        $service = Mage::getModel('teamwork_service/status_chq');
        $response = $service->parseXml(base64_decode($content));

        Teamwork_Service_Helper_Log::logApiResponse(__METHOD__, $content, $response);
        return $response;
    }

    public function setstatusoms($content = false)
    {
        Teamwork_Service_Helper_Log::logApiRequest(__METHOD__, $content);

        $service = Mage::getModel('teamwork_service/status_oms');
        $response = $service->parseXml(base64_decode($content));

        Teamwork_Service_Helper_Log::logApiResponse(__METHOD__, $content, $response);
        return $response;
    }

    public function setsettings($content = false)
    {
        Teamwork_Service_Helper_Log::logApiRequest(__METHOD__, $content);

        $service = Mage::getModel('teamwork_service/settings');
        $response = $service->parseXml(base64_decode($content));

        Teamwork_Service_Helper_Log::logApiResponse(__METHOD__, $content, $response);
        return $response;
    }

    public function setmapping($content = false)
    {
        Teamwork_Service_Helper_Log::logApiRequest(__METHOD__, $content);

        $mapping = Mage::getModel('teamwork_service/mapping');
        $mapping->setError('This method is deprecated');
        $response = $mapping->response();

        Teamwork_Service_Helper_Log::logApiResponse(__METHOD__, $content, $response);
        return $response;
    }

    public function getecmstatus($content = false)
    {
        Teamwork_Service_Helper_Log::logApiRequest(__METHOD__, $content);

        $service = Mage::getModel('teamwork_service/ecm');
        $response = $service->generateXml(base64_decode($content));

        Teamwork_Service_Helper_Log::logApiResponse(__METHOD__, $content, $response);
        return $response;
    }

    public function getmagentoproducts($params = false)
    {
        Teamwork_Service_Helper_Log::logApiRequest(__METHOD__, $params);

        $service = Mage::getModel('teamwork_service/service');
        $response = $service->getMagentoProducts($params);

        Teamwork_Service_Helper_Log::logApiResponse(__METHOD__, $params, $response);
        return $response;
    }

    public function getproductimages($params = false)
    {
        Teamwork_Service_Helper_Log::logApiRequest(__METHOD__, $params);

        $service = Mage::getModel('teamwork_service/export_images');
        $response = $service->getProductImages($params);

        Teamwork_Service_Helper_Log::logApiResponse(__METHOD__, $params, $response);
        return $response;
    }
}
