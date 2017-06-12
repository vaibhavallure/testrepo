<?php

/**
 * POS REST Api entry point.
 */
class Ebizmarts_BakerlooRestful_IndexController extends Mage_Core_Controller_Front_Action
{

    public $verb = null;
    public $parameters = array();

    public function indexAction()
    {
        $this->verb       = $this->getRequest()->getMethod();
        $this->parameters = $this->getRequest()->getParams();

        $h = $this->getApiHelper();

        $id = $h->debug($this->getRequest(), $this->getControllerName());
        Mage::register('brest_request_id', $id);
        //@TODO: Log ERRORS (rejected orders for example) always to DB even though logs are disabled.

        $moduleVersion = $h->getApiModuleVersion();

        $this->getResponse()
            ->setHeader('Content-Type', 'application/json', true)
            ->setHeader('Connection', 'keep-alive', true);

        //Check if module is active
        if (!$this->_isEnabled()) {
            return $this->getResponse()
                ->setHttpResponseCode(410)
                ->setBody($this->encodeResponse($this->_error("Service is not active.")));
        }

        //Check if activation request
        if ($this->_isActivationRequest()) {
            $activationData = $this->_getActivateAccountData();
            return $this->getResponse()
                ->setHttpResponseCode(200)
                ->setBody($this->encodeResponse($activationData));
        }

        //Validate Request
        try {
            $this->_isCallAllowed();
        } catch (Mage_Core_Exception $ce) {
            return $this->getResponse()
                ->setHttpResponseCode(401)
                ->setBody($this->encodeResponse($this->_error($ce->getMessage())));
        }

        //Set API Key Header
        $this->getResponse()
            ->setHeader($h->getApiKeyHeader(), $this->_getApiKey(), true)
            ->setHeader($h->getApiVersionHeader(), $moduleVersion, true)
            ->setHeader($h->getMagentoVersionHeader(), $h->getMagentoVersionCode() . ' ' . Mage::getVersion(), true);

        try {
            //Validate version provided on Accept header
            $accept = (string)$this->getRequest()->getHeader('B-Accept');
            if ($accept != "*/*" && !empty($accept)) {
                $requestVersion = explode("=", $accept);

                $requestVersion = $requestVersion[1];

                if (version_compare($requestVersion, $moduleVersion, 'gt')) {
                    $versionMessage = $h->__("Version not supported. You asked for v%s, I have v%s.", $requestVersion, $moduleVersion);

                    $this->getResponse()
                        ->setHttpResponseCode(406)
                        ->setBody($this->encodeResponse($this->_error($versionMessage)));
                    return;
                }
            }

            if ($this->getRequest()->getParam('PROFILER')) {
                $h->startprofiler();
            }

            try {
                $model = $this->getApiResource();
            } catch (Mage_Core_Model_Store_Exception $e) {
                Mage::logException($e);
                Mage::throwException("Invalid store.");
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::throwException("Resource not found.");
            }

            //GET, POST, PUT, DELETE...
            $actionName = strtolower($this->verb);

            //Allow custom actions to be used eg ?action=sendEmail
            //When using custom actions, HTTP VERB does not matter
            $customAction = $this->getRequest()->getParam('action', null);
            if (!empty($customAction)) {
                $actionName = $customAction;
            }

            //Execute action
            if ('getZip' == $actionName) {
                $this->getPagesHelper()->getZippedPages($this->getRequest(), $this->getResponse(), null, false);
                return;
            } elseif ('getDB' == $actionName) {
                $this->getPagesHelper()->getDB($this->getRequest(), $this->getResponse(), true, null, false);
                return;
            } elseif (is_object($model) && $model->getModelName() == "Ebizmarts_BakerlooRestful_Model_Api_Backup" && 'get' == $actionName) {
                $model->getBackup($this->getResponse());
                return;
            } else {
                if (!method_exists($model, $actionName)) {
                    Mage::throwException("Invalid action.");
                }

                $result = $model->$actionName();
            }

            if ($this->getRequest()->getParam('PROFILER')) {
                $h->logprofiler((int)$this->getRequest()->getHeader('B-Store-Id'), $this->getControllerName(), $actionName);
                $h->endprofiler();
            }

            $this->getResponse()
                ->setHttpResponseCode(200)
                ->setBody($this->encodeResponse($result));

            if ($actionName == "post") {
                if (is_array($result) && isset($result['error_message'])) {
                    $this->getResponse()->setHttpResponseCode(500);
                } else {
                    $this->getResponse()->setHttpResponseCode(201);
                }
            }
        } catch (Exception $ex) {
            Mage::logException($ex);

            if ($this->getRequest()->getParam('PROFILER')) {
                $h->logprofiler((int)$this->getRequest()->getHeader('B-Store-Id'), $this->getControllerName(), $actionName);
                $h->endprofiler();
            }

            $this->getResponse()
                ->setHttpResponseCode(500)
                ->setBody($this->encodeResponse($this->_error($ex->getMessage())));
        }
    }

    /**
     * @return Ebizmarts_BakerlooRestful_Helper_Data
     */
    public function getApiHelper()
    {
        return Mage::helper('bakerloo_restful');
    }
    
    /**
     * @return Ebizmarts_BakerlooRestful_Helper_Pages
     */
    public function getPagesHelper()
    {
        return Mage::helper('bakerloo_restful/pages');
    }

    /**
     * @return Ebizmarts_BakerlooRestful_Model_Api_Api
     */
    public function getApiResource()
    {
        $className = 'bakerloo_restful/api_' . $this->getControllerName();
        $model = Mage::getModel($className, $this->parameters);

        return $model;
    }

    /**
     * @return string
     */
    public function getControllerName()
    {
        return Mage::helper('bakerloo_restful/http')->getApiResourceFromRequest($this->getRequest());
    }

    /**
     * @return string
     */
    public function getActionName()
    {
        return Mage::helper('bakerloo_restful/http')->getActionFromRequest($this->getRequest());
    }

    public function encodeResponse($data)
    {
        return $this->getApiHelper()->encodeResponse($data);
    }

    private function _error($message)
    {
        return $this->getApiHelper()->jsonError($message);
    }

    /**
     * Validate request IP address checking against whitelist in config.
     *
     * @param int $storeId
     * @return boolean
     */
    private function _isCallAllowed($storeId = null)
    {
        return $this->getApiHelper()->isCallAllowed($this->getRequest(), $storeId);
    }

    /**
     * Check if API request is activation request.
     *
     * @return bool
     * @throws Zend_Controller_Request_Exception
     */
    private function _isActivationRequest()
    {

        $activationKey = $this->getRequest()->getHeader($this->getApiHelper()->getActivationKeyHeader());

        if ($activationKey && $activationKey != '') {
            return true;
        } else {
            return false;
        }
    }

    private function _getActivateAccountData()
    {
        /** @var Ebizmarts_BakerlooRestful_Helper_Data $helper */
        $helper = $this->getApiHelper();

        $activationKey = $this->getRequest()->getHeader($helper->getActivationKeyHeader());
        $decriptedKeyData = $helper->decryptActivationKey($activationKey);

        //activation key syntax is URL|apipath|DATE_CREATED
        $pieces = explode("|", $decriptedKeyData);
        if (count($pieces) == 3) {
            $url = $pieces[0];
            $date = $pieces[2];
            $magentoDomain = $helper->getMagentoDomain();
            $currDate = date("Y-m-d");
            $hourdiff = round((strtotime($currDate) - strtotime($date)) / 3600, 1);

            if (strpos($magentoDomain, $url) === 0) {
                if ($hourdiff < $helper->getActivationKeyExpirationHours()) {
                    return $this->encodeResponse(array("api_key" => $this->_getApiKey(), "shop_type" => "magento"));
                } else {
                    return $this->encodeResponse(array("error" => "Activation key expired"));
                }
            } else {
                return $this->encodeResponse(array("error" => "Incorrect domain in activation key"));
            }
        } else {
            return $this->encodeResponse(array("error" => "Incorrect activation key"));
        }
    }

    /**
     * Check if the module is active.
     * @return bool
     */
    private function _isEnabled()
    {
        return (boolean)$this->getApiHelper()->config("general/enabled");
    }

    /**
     * Get API key from config.
     * @return string|null
     */
    private function _getApiKey()
    {
        return $this->getApiHelper()->getApiKey();
    }
}
