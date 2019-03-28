<?php


require_once(Mage::getBaseDir('lib') . '/GoogleApiPhpClient/Google_Client.php');
require_once(Mage::getBaseDir('lib') . '/GoogleApiPhpClient/contrib/Google_Oauth2Service.php');

class Allure_GoogleConnect_Model_Client
{
    const APPLICATION_NAME = 'allure-googleconnect';
    const REDIRECT_URI_ROUTE = 'googleconnect/index/connect';

    const XML_PATH_ENABLED = 'customer/allure_googleconnect/enabled';
    const XML_PATH_CLIENT_ID = 'customer/allure_googleconnect/client_id';
    const XML_PATH_CLIENT_SECRET = 'customer/allure_googleconnect/client_secret';

    protected $client = null;
    protected $oauth2 = null;
    
    public function __construct() {
        $enabled = $this->_isEnabled();
        $clientId = $this->_getClientId();
        $clientSecret = $this->_getClientSecret();

        if(!empty($enabled)) {
            $this->client = new Google_Client();
            $this->client->setAccessType('offline');
            $this->client->setApplicationName(self::APPLICATION_NAME);
            $this->client->setClientId($clientId);
            $this->client->setClientSecret($clientSecret);
            $this->client->setRedirectUri(
                Mage::getModel('core/url')->sessionUrlVar(
                        Mage::getUrl(self::REDIRECT_URI_ROUTE)
                    )
                );

            $this->oauth2 = new Google_Oauth2Service($this->client);
        }
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getOauth2()
    {
        return $this->oauth2;
    }

    protected function _isEnabled()
    {
        return $this->_getStoreConfig(self::XML_PATH_ENABLED);
    }

    protected function _getClientId()
    {
        return $this->_getStoreConfig(self::XML_PATH_CLIENT_ID);
    }

    protected function _getClientSecret()
    {
        return $this->_getStoreConfig(self::XML_PATH_CLIENT_SECRET);
    }

    protected function _getStoreConfig($xmlPath)
    {
        return Mage::getStoreConfig($xmlPath, Mage::app()->getStore()->getId());
    }

}
