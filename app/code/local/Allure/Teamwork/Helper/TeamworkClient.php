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
    private function getHelper()
    {
        return Mage::helper("allure_teamwork");
    }

    /**
     * make curl request call to
     * teamwork using teamwork api
     */
    public function send($_url, $request) {
        $helper = $this->getHelper();
        $logFile = $helper::SYNC_TM_MAG_LOG_FILE;
        $response = null;
        try {
            $status = $helper->getTeamworkStatus();
            $logStatus = $helper->getLogStatus();
            $logStatus = ($logStatus) ? true : false;
            if ($status) {
                $URL = $helper->getTeamworkUrl();
                $_url = $URL . "" . $_url;
                $_accessToken = $helper->getTeamworkAccessToken();
                $sendRequest = curl_init($_url);
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
            } else {
                Mage::log("Teamwork not enable at this moment.", Zend_log::DEBUG, $logFile, $logStatus);
            }
        } catch (Exception $e) {
            Mage::log("Exception:" . $e->getMessage(), Zend_log::DEBUG, $logFile, $logStatus);
        }
        return $response;
    }

    public function syncTmOrders($start,$end) {
        $TM_URL = "/services/remainingOrders";
        //$TOKEN = "OUtNUUhIV1V2UjgxR0RwejV0Tmk0VllneEljNTRZWHdLNHkwTERwZXlsaz0=";

        if (!isset($start) && !isset($end)) {
            die("provide date....");
        }

        $orderModel = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('created_at', array('from' => $start, 'to' => $end))
            ->addFieldToFilter('create_order_method', array('eq' => 2));

        $receipts = "";
        foreach ($orderModel->getData() as $order) {
            $receipts .= str_replace('TW-', '', $order['increment_id']) . ",";
        }
        //echo substr($receipts,0,strlen($receipts)-1);
        //die;

        $helper = Mage::helper("allure_teamwork");
        $urlPath = $helper->getTeamworkSyncDataUrl();
        $requestURL = $urlPath . $TM_URL;//."?start=".$start."&end=".$end;
        //var_dump($requestURL);
        $token = trim($helper->getTeamworkSyncDataToken());
        $sendRequest = curl_init($requestURL);
        curl_setopt($sendRequest, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($sendRequest, CURLOPT_HEADER, false);
        curl_setopt($sendRequest, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($sendRequest, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($sendRequest, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($sendRequest, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($sendRequest, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer " . $token
        ));

        $requestArgs = array(
            "start_time" => $start,
            "end_time" => $end,
            "receiptNos" => substr($receipts, 0, strlen($receipts) - 1)
        );
        // convert requestArgs to json
        if ($requestArgs != null) {
            $json_arguments = json_encode($requestArgs);
            curl_setopt($sendRequest, CURLOPT_POSTFIELDS, $json_arguments);
        }
        $response = curl_exec($sendRequest);
        //$response1 = json_decode($response,true);
        //echo "<pre>";
        $response1 = unserialize($response);
        //var_dump(count($response1));
        //print_r($response1);die;
        //$response = json_encode($response1);
        if (!$response1["status"]) {
            Mage::log($response1, Zend_Log::DEBUG, "teamwork_sync_data.log", true);
        } else {
            Mage::getModel("allure_teamwork/tmobserver")->addDataIntoSystem($response);
        }
        return count($response1['data']);
    }
}
