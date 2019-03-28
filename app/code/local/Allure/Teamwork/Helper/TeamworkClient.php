<?php
/**
 * @author allure
 * 
 */
class Allure_Teamwork_Helper_TeamworkClient extends Mage_Core_Helper_Data
{
    /**
     * return object of Allure_Teamwork_Helper_Data
     */
    private function getHelper(){
        return Mage::helper("allure_teamwork");
    }
    
    /**
     * make curl request call to
     * teamwork using teamwork api
     */
    public function send($_url , $request){
        $helper   = $this->getHelper();
        $logFile  = $helper::SYNC_TM_MAG_LOG_FILE;
        $response = null;
        try{
            $status = $helper->getTeamworkStatus();
            $logStatus = $helper->getLogStatus();
            $logStatus = ($logStatus)?true:false;
            if($status){
                $URL  = $helper->getTeamworkUrl();
                $_url = $URL."".$_url;
                $_accessToken = $helper->getTeamworkAccessToken();
                $sendRequest  = curl_init($_url);
                curl_setopt($sendRequest, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                curl_setopt($sendRequest, CURLOPT_HEADER, false);
                curl_setopt($sendRequest, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($sendRequest, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($sendRequest, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($sendRequest, CURLOPT_FOLLOWLOCATION, 0);
                curl_setopt($sendRequest, CURLOPT_HTTPHEADER, array(
                    "Content-Type: application/json",
                    "Access-Token: {$_accessToken}"
                ));
                $json_arguments = json_encode($request);
                curl_setopt($sendRequest, CURLOPT_POSTFIELDS, $json_arguments);
                $response = curl_exec($sendRequest);
                curl_close($sendRequest);
            }else{
                Mage::log("Teamwork not enable at this moment.",Zend_log::DEBUG,$logFile,$logStatus);
            }
        }catch (Exception $e){
            Mage::log("Exception:".$e->getMessage(),Zend_log::DEBUG,$logFile,$logStatus);
        }
        return $response;
    }
}
