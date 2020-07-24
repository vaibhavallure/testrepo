<?php
/**
 * 
 * @author allure
 *
 */
class Allure_WaitWhile_Helper_WClient extends Mage_Core_Helper_Abstract
{
    /**
     * wait-while booking state
     */
    const BOOKING_WAITING   = "WAITING";
    const BOOKING_SERVING   = "SERVING";
    const BOOKING_COMPLETE  = "COMPLETE";
    const BOOKING_BOOKED    = "BOOKED";
    
    /**
     * wait-while url paths
     */
    const BOOKING_PATH = "v2/visits";
    const BOOKING_AVAILABILITY = "v2/visits/availability";
    
    /**
     * @return Allure_WaitWhile_Helper_Data
     */
    public function getHelper()
    {
        return Mage::helper("allure_waitwhile");
    }
    
    /**
     * 
     * @param string $path
     * @param string $method
     * @param array $args
     */
    public function processRequest($path, $method = "GET", $args = array())
    {
        /**@var Allure_WaitWhile_Helper_Data */
        $helper = $this->getHelper();
        $bookingHost = $helper->getBookingHost();
        $url = $bookingHost."".$path;
        $request = curl_init($url);
        curl_setopt($request, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($request, CURLOPT_HEADER, false);
        //curl_setopt($request, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, 0);
        
        curl_setopt($request, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "apiKey: {$helper->getBookingApiKey()}"
        ));
        
        if(count($args) > 0){
            $json_arguments = json_encode($args);
            curl_setopt($request, CURLOPT_POSTFIELDS, $json_arguments);
        }
        
        
        // execute request
        $response = curl_exec($request);
        return $response;
    }
}