<?php

/**
 * @author allure
 *
 */
class Allure_TeamworkDam_Helper_TeamworkDAMClient extends Mage_Core_Helper_Data
{

    private function getHelper(){
        return Mage::helper("teamworkdam");
    }

    public function syncImage($imageOb) {
        $TM_URL = "media/import-media-resource";
        $helper = $this->getHelper();
        $isEnabled = $helper->getTeamworkDamStatus();

        if(!$isEnabled) {
            $this->log('Cann\'t process the request - Teamwork DAM API Disabled.');
            return false;
        }

        if (!isset($imageOb)) {
            $this->log('Cann\'t process the request - No Image data given');
            return false;
        }
        //print_r($imageOb);die;

        $urlPath = $helper->getTeamworkDamUrl();
        $requestURL = $urlPath . $TM_URL;
        //var_dump($requestURL);
        $token = trim($helper->getTeamworkDamAccessToken());
        $sendRequest = curl_init($requestURL);
        curl_setopt($sendRequest, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($sendRequest, CURLOPT_HEADER, false);
        curl_setopt($sendRequest, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($sendRequest, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($sendRequest, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($sendRequest, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($sendRequest, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Access-Token: ". $token
        ));

        $requestArgs = array(
            "posParams" => array("itemPLU" => $imageOb['plu'],"attributeThumbnail" => true),
            "ecommerceParams" => array("itemPLU" => $imageOb['plu']),
            "fileName" => $imageOb['name'],
            "mediaData" => $imageOb['data']
        );

        // convert requestArgs to json
        if ($requestArgs != null) {
            $json_arguments = json_encode($requestArgs);
            //print_r($json_arguments);die;
            curl_setopt($sendRequest, CURLOPT_POSTFIELDS, $json_arguments);
        }
        $response = curl_exec($sendRequest);
        $responseArr = json_decode($response,true);
        //echo "<pre>";
        //print_r($response);die;

        if(!empty($responseArr['errorCode'])) {
            $msg = $responseArr['errorMessage'];
            $errorCode = $responseArr['errorCode'];
            $this->log('Error occurred on request - With error code = '.$errorCode .' / Message = '.$msg);
            return false;
        }else if(!empty($responseArr['posStyle'])){
            $this->log('Image update successfully for PLU = '.$imageOb['plu'] . ' with file name = '.$imageOb['name']);
            return true;
        }
        return false;
    }

    private function log($message) {
        Mage::log($message,7,"teamwork_dam_api_client.log",true);
    }
}
