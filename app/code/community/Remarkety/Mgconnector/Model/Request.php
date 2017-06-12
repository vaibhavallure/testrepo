<?php

/**
 * Request model
 *
 * @category   Remarkety
 * @package    Remarkety_Mgconnector
 * @author     Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class Remarkety_Mgconnector_Model_Request
{
    const REMARKETY_URI = 'https://app.remarkety.com/public/install/notify';
    const REMARKETY_METHOD = 'POST';
    const REMARKETY_TIMEOUT = 10;
    const REMARKETY_VERSION = 0.9;
    const REMARKETY_PLATFORM = 'MAGENTO';
    const REMARKETY_OEM = 'remarkety';

    protected function _getRequestConfig()
    {
        return array(
            'adapter' => 'Zend_Http_Client_Adapter_Curl',
            'curloptions' => array(
//                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HEADER => true,
                CURLOPT_CONNECTTIMEOUT => self::REMARKETY_TIMEOUT,
//	            CURLOPT_SSL_CIPHER_LIST => "RC4-SHA"
//                CURLOPT_SSL_VERIFYPEER => false,
            ),
        );
    }

    protected function _getPayloadBase()
    {
        $domain = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        $domain = substr($domain, 7, -1);

        $version = Mage::getVersion();
        $version .= ' ' . (Mage::helper('core')->isModuleEnabled('Enterprise_Enterprise') ? 'EE' : 'CE');

        $arr = array(
            'domain' => $domain,
            'platform' => Remarkety_Mgconnector_Model_Request::REMARKETY_PLATFORM,
            'version' => $version,
            'oem'       => Remarkety_Mgconnector_Model_Request::REMARKETY_OEM,
        );
        return $arr;
    }

    public function makeRequest($payload)
    {
        try {
            $payload = array_merge($payload, $this->_getPayloadBase());
            $client = new Zend_Http_Client(
                self::REMARKETY_URI,
                $this->_getRequestConfig()
            );
            $client->setParameterPost($payload);
            $response = $client->request(self::REMARKETY_METHOD);

            Mage::log(var_export($payload, true), null, 'remarkety-ext.log');
            Mage::log($response->getStatus(), null, 'remarkety-ext.log');
            Mage::log($response->getBody(), null, 'remarkety-ext.log');

            $body = (array)json_decode($response->getBody());

            Mage::getSingleton('core/session')->setRemarketyLastResponseStatus($response->getStatus() === 200 ? 1 : 0);
            Mage::getSingleton('core/session')->setRemarketyLastResponseMessage(serialize($body));

            switch ($response->getStatus()) {
                case '200':
                    return $body;
                case '400':
                    throw new Exception('Request failed. ' . $body['message']);
                default:
                    throw new Exception('Request to remarkety servers failed ('.$response->getStatus().')');
            }
        } catch(Exception $e) {
            Mage::log($e->getMessage(), null, 'remarkety-ext.log');
            throw new Mage_Core_Exception($e->getMessage());
        }
    }
}