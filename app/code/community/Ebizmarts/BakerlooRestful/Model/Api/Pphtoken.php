<?php

/**
 * Class Ebizmarts_BakerlooRestful_Model_Api_Pphtoken
 *
 * @method string getApiMode()
 * @method void setApiMode(string $mode)
 */
class Ebizmarts_BakerlooRestful_Model_Api_Pphtoken extends Ebizmarts_BakerlooRestful_Model_Api_Api
{
    const API_MODE = 'mode';
    const TIMESTAMP = 'timestamp';
    const ACCESS_TOKEN = 'access_token';
    const REFRESH_TOKEN = 'refresh_token';
    const BACKEND_ACCOUNT = 'backend_account_id';
    const CONFIG_PATH = 'payment/bakerloo_paypalhere/';

    private $_backendReactivateUrl = "https://pos.ebizmarts.com/admin/paypal-here-reactivate";
    
    /**
     * Process GET requests.
     *
     * @return array
     * @throws Exception
     */
    public function get()
    {

        $this->checkGetPermissions();

        $mode = $this->_getQueryParameter(self::API_MODE);
        if ($mode == null || $mode == '') {
            $mode = $this->getStoreConfig(self::CONFIG_PATH . 'api_mode', 0);
        }

        $pphtoken = $this->getModel('bakerloo_restful/pphtoken')->load($mode, 'api_mode');

        if ($pphtoken->getId() and $pphtoken->getAccessToken()) {
            $nowTimestamp = (time()*1000) - 600000; //add 10 min window

            // refresh token if expired
            if ($nowTimestamp > (int)$pphtoken->getTimestamp()) {
                $data = array(
                    "refresh_token"      => $pphtoken->getRefreshToken(),
                    "backend_account_id" => $pphtoken->getAccountId(),
                    "store"              => 0,
                    "mode"               => $mode
                );
                $response = $this->getHelper('bakerloo_restful/http')->POST($this->_backendReactivateUrl, $data, array());
                $objResponse = json_decode($response, true);

                if ($objResponse['error'] && $objResponse['error'] != '') {
                    Mage::throwException($this->getHelper('bakerloo_restful')->__("Access token is old, refresh failed: {$objResponse['error']}"));
                } else {
                    // update token for mode
                    $pphtoken->setAccessToken($objResponse[self::ACCESS_TOKEN]);
                    $pphtoken->setTimestamp($objResponse[self::TIMESTAMP]);
                    $pphtoken->save();
                }
            }

            return array(
                "access_token" => $pphtoken->getAccessToken(),
                "timestamp"    => $pphtoken->getTimestamp()
            );

        } else {
            Mage::throwException($this->getHelper('bakerloo_restful')->__("Access token was not generated for {$mode} mode."));
        }
    }

    /**
     * save new token
     *
     */
    public function post()
    {
        parent::post();

        $data = $this->getJsonPayload(true);

        /** @var Ebizmarts_BakerlooRestful_Model_Pphtoken $pphtoken */
        $pphtoken = $this->getModel('bakerloo_restful/pphtoken')->load($data[self::API_MODE], 'api_mode');

        if (!$pphtoken->getId()) {
            $pphtoken->setApiMode($data[self::API_MODE]);
        }
        
        $pphtoken->setAccessToken($data[self::ACCESS_TOKEN]);
        $pphtoken->setRefreshToken($data[self::REFRESH_TOKEN]);
        $pphtoken->setAccountId($data[self::BACKEND_ACCOUNT]);
        $pphtoken->setTimestamp($data[self::TIMESTAMP]);
        $pphtoken->save();

        return $this;
    }

    /**
     * delete new token
     *
     */
    public function delete()
    {
        $this->checkDeletePermissions();

        $mode = $this->_getQueryParameter(self::API_MODE);
        $pphtoken = $this->getModel('bakerloo_restful/pphtoken')->load($mode, 'api_mode');

        if ($pphtoken->getId()) {
            $pphtoken->delete();
        }

        return $this;
    }
}
