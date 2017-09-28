<?php
/**
 * @author:Allure
 * 
 */
class Allure_Counterpoint_Helper_SugarcrmClient extends Mage_Core_Helper_Abstract{
    /**
     * @return Allure_Counterpoint_Helper_Data
     */
    public function getDataHelper(){
        return Mage::helper('allure_counterpoint');
    }
    
    /**
     * @param $path .i.e it is sub url
     * @return string
     */
    public function generateUrl($path){
        $helper         = $this->getDataHelper();
        $baseUrl        = $helper->getSugarCRMApiUrl();
        return $baseUrl.$path;
    }
    
    /**
     * @return array of login parameters for login purpose to crm
     */
    public function loginParams(){
        $helper         = $this->getDataHelper();
        $username       = $helper->getSugarCRMUsername();
        $password       = $helper->getSugarCRMPassword();
        $client_id      = $helper->getSugarCRMClientId();
        $client_secret  = $helper->getSugarCRMClientSecret();
        $grant_type     = $helper->getSugarCRMGrantType();
        $platform       = $helper->getSugarCRMPlatform();
        return array(
            "username"      => $username,
            "password"      => $password,
            "client_id"     => $client_id,
            "client_secret" => $client_secret,
            "grant_type"    => $grant_type,
            "platform"      => $platform
        );
    }
    
    /**
     * @return $oauth_token after successfull login to suagarcrm
     */
    public function login(){
        $helper         = $this->getDataHelper();
        $loginPath      = $helper::LOGIN_PATH;
        $loginUrl       = $this->generateUrl($loginPath);
        $loginParams    = $this->loginParams();
        $oauth_token          = $this->loginPost($loginUrl, $loginParams);
        return $oauth_token;
    }
    
    /**
     * @param string $loginUrl
     * @param array $loginParams
     * @return string
     */
    public function loginPost($loginUrl,$loginParams){
        $loginRequest = curl_init($loginUrl);
        curl_setopt($loginRequest, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($loginRequest, CURLOPT_HEADER, false);
        curl_setopt($loginRequest, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($loginRequest, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($loginRequest, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($loginRequest, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json"
        ));
        
        // convert loginParams to json
        $jsonArguments = json_encode($loginParams);
        curl_setopt($loginRequest, CURLOPT_POSTFIELDS, $jsonArguments);
        // execute loginRequest
        $loginResponse = curl_exec($loginRequest);
        // decode oauth2 response to get token
        $login_response_obj = json_decode($loginResponse, true);
        $login_access_token = $login_response_obj['access_token'];
        return $login_access_token;
    }
    
    /**
     * @param string $requestURL
     * @param array $requestArguments
     * @param boolean $isAuth
     * @param string $requestType
     * @return array response
     */
    public function sendRequest($requestURL,$requestArguments,$isAuth,$token=null,$requestType="GET"){
        $isAuth      = true;
        $oauthToken  = $token;
        $sendRequest = curl_init($requestURL);
        curl_setopt($sendRequest, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($sendRequest, CURLOPT_HEADER, false);
        curl_setopt($sendRequest, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($sendRequest, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($sendRequest, CURLOPT_CUSTOMREQUEST, $requestType);
        curl_setopt($sendRequest, CURLOPT_FOLLOWLOCATION, 0);
        if ($isAuth) {
            if(is_null($oauthToken)){
                $oauthToken = $this->login();
            }
            curl_setopt($sendRequest, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "oauth-token: {$oauthToken}"
            ));
        } else {
            curl_setopt($sendRequest, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json"
            ));
        }
        // convert requestArguments to json
        if ($requestArguments != null) {
            $json_arguments = json_encode($requestArguments);
            curl_setopt($sendRequest, CURLOPT_POSTFIELDS, $json_arguments);
        }
        // execute sendRequest
        $response = curl_exec($sendRequest);
        return $response;
    }
    
    
}
