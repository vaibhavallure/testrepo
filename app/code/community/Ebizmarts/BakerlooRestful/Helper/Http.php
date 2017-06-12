<?php

/**
 * HTTP Helper class.
 *
 */
class Ebizmarts_BakerlooRestful_Helper_Http
{

    const STATUS_CODE_200 = 200;
    const STATUS_CODE_201 = 201;
    const STATUS_CODE_400 = 400;
    const STATUS_CODE_401 = 401;
    const STATUS_CODE_406 = 406;
    const STATUS_CODE_410 = 410;
    const STATUS_CODE_500 = 500;

    public function getCurlAdapter()
    {
        return new Varien_Http_Adapter_Curl();
    }

    /**
     * POST request to given url.
     *
     * @param string $url
     * @param string $requestBody
     * @param array $requestHeaders
     * @return string
     */
    public function POST($url, $requestBody, $requestHeaders)
    {
        return $this->request($url, $requestBody, $requestHeaders, true);
    }

    /**
     * GET request to given url.
     *
     * @param string $url
     * @param string $requestBody
     * @param array $requestHeaders
     * @return string
     */
    public function GET($url, $requestBody, $requestHeaders)
    {
        return $this->request($url, $requestBody, $requestHeaders);
    }

    /**
     * Request to given url.
     *
     * @param string $url
     * @param string $requestBody
     * @param array $requestHeaders
     * @param bool $post
     * @param bool $verifyPeer
     * @param bool $verifyHost
     * @return string
     */
    public function request($url, $requestBody, $requestHeaders, $post = false, $verifyPeer = false, $verifyHost = false)
    {

        $curlAdapter = $this->getCurlAdapter();

        $config = array(
            'timeout'    => 90,
            'verifypeer' => $verifyPeer,
            'verifyhost' => $verifyHost,
            'header'     => false
        );
        $curlAdapter->setConfig($config);

        $UA = isset($requestHeaders['B-User-Agent']) ? $requestHeaders['B-User-Agent'] : null;
        if (!is_null($UA)) {
            $curlAdapter->addOption(CURLOPT_USERAGENT, $UA);
        }

        if ($post) {
            $curlAdapter->write(Zend_Http_Client::POST, $url, '1.1', $requestHeaders, $requestBody);
        } else {
            $curlAdapter->write(Zend_Http_Client::GET, $url, '1.1', $requestHeaders, $requestBody);
        }

        $response = $curlAdapter->read();

        $errorMessage = $curlAdapter->getError();
        if ($errorMessage) {
            $rawresponse = $errorMessage;
        } else {
            $rawresponse = $response;
        }

        $curlAdapter->close();

        //For older magentos (1.6.0.0 for example)
        $rawresponseTmp = Zend_Http_Response::extractBody($rawresponse);
        if ($rawresponseTmp != "") {
            $rawresponse = $rawresponseTmp;
        }

        return $rawresponse;
    }

    public function getJsonPayload($request, $asArray = false)
    {
        $payload = $request->getRawBody();

        $data = json_decode($payload, $asArray);

        if (is_object($data) or is_array($data)) {
            return $data;
        } else {
            Mage::throwException("Invalid post data.");
        }
    }

    /**
     * @param Mage_Core_Controller_Request_Http $request
     * @return string
     */
    public function getApiResourceFromRequest(Mage_Core_Controller_Request_Http $request)
    {
        $params = $request->getParams();
        $name = "";

        if (count($params)) {
            $keys = array_keys($params);
            $name = $keys[0];
        }

        return $name;
    }

    /**
     * @param Mage_Core_Controller_Request_Http $request
     * @return string
     */
    public function getActionFromRequest(Mage_Core_Controller_Request_Http $request)
    {
        $action = strtolower($request->getMethod());

        //Allow custom actions to be used eg ?action=sendEmail
        //When using custom actions, HTTP VERB does not matter
        $customAction = $request->getParam('action', null);
        if (!empty($customAction)) {
            $action = $customAction;
        }

        return $action;
    }
}
