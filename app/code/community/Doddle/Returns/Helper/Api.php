<?php
class Doddle_Returns_Helper_Api extends Mage_Core_Helper_Abstract
{
    const PATH_OAUTH_TOKEN = '/v1/oauth/token';
    const PATH_ORDERS = '/v2/orders/';
    const SCOPE_ORDERS = 'orders:write';

    /** @var array */
    protected $_accessTokens = array();

    /**
     * Post an order to the Doddle Orders API, return the Doddle API order ID if successful
     *
     * @param $orderData
     * @return bool|mixed
     * @throws Mage_Core_Exception
     */
    public function postOrder($orderData)
    {
        $jsonOrderData = Mage::helper('core')->jsonEncode($orderData);

        $request = $this->buildRequest(
            self::PATH_ORDERS,
            Varien_Http_Client::POST,
            self::SCOPE_ORDERS,
            null,
            $jsonOrderData
        );

        $response = $this->sendRequest($request);

        if (isset($response['resource']['orderId'])) {
            return $response['resource']['orderId'];
        }

        return false;
    }

    /**
     * @param $path
     * @param string $method
     * @param null $accessScope
     * @param null $postData
     * @param null $rawData
     * @return Varien_Http_Client
     * @throws Mage_Core_Exception
     */
    protected function buildRequest(
        $path,
        $method = Varien_Http_Client::GET,
        $accessScope = null,
        $postData = null,
        $rawData = null
    ) {
        try {
            $http = new Varien_Http_Client();

            // Get access token if scope required
            if ($accessScope != null) {
                $accessToken = $this->getAccessToken($accessScope);
                $http->setHeaders('Authorization', 'Bearer ' . $accessToken);
            }

            // Set post data if set
            if ($postData != null) {
                $http->setParameterPost($postData);
            }

            // Set raw JSON data and header if set
            if ($rawData != null) {
                $http->setHeaders('Content-type', 'application/json');
                $http->setRawData($rawData);
            }

            $uri = $this->getApiUrl() . $path;

            $http->setUri($uri)
                ->setMethod($method);
        } catch (Exception $e) {
            Mage::throwException(
                sprintf(
                    'Failed to build HTTP request: %s %s - %s',
                    $method,
                    $uri,
                    $e->getMessage()
                )
            );
        }

        return $http;
    }

    /**
     * @param $scope
     * @return mixed
     * @throws Mage_Core_Exception
     */
    protected function getAccessToken($scope)
    {
        if (!isset($this->_accessTokens[$scope])) {
            $apiKey = $this->getApiKey();
            $apiSecret = $this->getApiSecret();
            $path = sprintf('%s?api_key=%s', self::PATH_OAUTH_TOKEN, $apiKey);

            $post = array(
                'grant_type' => 'client_credentials',
                'scope' => $scope
            );

            $request = $this->buildRequest($path, Varien_Http_Client::POST, null, $post);

            try {
                $request->setAuth(
                    $apiKey,
                    $apiSecret
                );
            } catch (Exception $e) {
                Mage::throwException(
                    sprintf(
                        'Failed to set access token request HTTP auth - %s',
                        $e->getMessage()
                    )
                );
            }

            $response = $this->sendRequest($request);

            if ($this->verifyAccessToken($response, $scope)) {
                $accessToken = $response['access_token'];
            } else {
                Mage::throwException(
                    sprintf(
                        'Failed to retrieve access token for scope - %s',
                        $scope
                    )
                );
            }

            $this->_accessTokens[$scope] = $accessToken;
        }

        return $this->_accessTokens[$scope];
    }

    /**
     * Confirm access token and valid scope is present.
     * Note this is only implemented for single scope currently.
     *
     * @param $response
     * @param bool $scope
     * @return bool
     */
    protected function verifyAccessToken($response, $scope = false)
    {
        if (!isset($response['access_token'])) {
            return false;
        }

        if ($scope && strpos($response['scope'], $scope) === false) {
            return false;
        }

        return true;
    }

    /**
     * @param $http
     * @return bool|mixed
     * @throws Mage_Core_Exception
     */
    protected function sendRequest($http)
    {
        try {
            $response = $http->request();
        } catch (Exception $e) {
            Mage::throwException(
                sprintf(
                    'Failed to send HTTP request: %s - %s',
                    $http->getUri(),
                    $e->getMessage()
                )
            );
        }

        if ($response->getStatus() == 200) {
            $decodedResponse = $this->decodeResponse($response);
        } else {
            Mage::throwException(
                sprintf(
                    'Got HTTP %s response for request: %s - %s',
                    $response->getStatus(),
                    $http->getUri(),
                    $response->getRawBody()
                )
            );
        }

        return $decodedResponse;
    }

    /**
     * @param $response
     * @return bool|mixed
     * @throws Mage_Core_Exception
     */
    protected function decodeResponse($response)
    {
        try {
            $decodedResponse = Mage::helper('core')->jsonDecode(
                $response->getRawBody(),
                Zend_Json::TYPE_ARRAY
            );
        } catch (Exception $e) {
            Mage::throwException(
                sprintf(
                    'Failed to decode HTTP request for request: %s - %s',
                    $http->getUri(),
                    $e->getMessage()
                )
            );
        }

        return $decodedResponse;
    }

    /**
     * @return string
     */
    protected function getApiKey()
    {
        return $this->getHelper()->getApiKey();
    }

    /**
     * @return string
     */
    protected function getApiSecret()
    {
        return $this->getHelper()->getApiSecret();
    }

    /**
     * @return string
     */
    protected function getApiUrl()
    {
        if ($this->getHelper()->getApiMode() == Doddle_Returns_Model_System_Config_Source_ApiMode::API_MODE_TEST) {
            return $this->getHelper()->getTestApiUrl();
        }

        return $this->getHelper()->getLiveApiUrl();
    }

    /**
     * @return Doddle_Returns_Helper_Data
     */
    protected function getHelper()
    {
        return Mage::helper('doddle_returns');
    }
}
