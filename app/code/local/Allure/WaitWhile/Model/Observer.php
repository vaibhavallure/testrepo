<?php 

/**
 * 
 * @author allure
 *
 */
class Allure_WaitWhile_Model_Observer
{
    const WAIT_WHILE_LOG_FILE = "waitwhile_booking.log";
    
    protected $is_debug = false;
    
    /**
     * Add wait-while booking logs data about appointment
     * @param string $message
     */
    private function addLog($message)
    {
        if($this->is_debug)
            Mage::log($message,7,self::WAIT_WHILE_LOG_FILE,true);
    }
    
    private function getBookingServiceIds($appointmentId, $storeId)
    {
        $appointmentCustomers = Mage::getModel("appointments/customers")->getCollection();
        $appointmentCustomers->addFieldToSelect("*");
        $appointmentCustomers->addFieldToFilter("appointment_id", $appointmentId);
        
        $piercingCodeArray = array();
        foreach ($appointmentCustomers as $customer)
        {
            if($customer->getPiercing()){
                $piercingCodeArray[] = "piercing";
            }
            if($customer->getCheckup()){
                $piercingCodeArray[] = "checkup";
            }
            
        }
        
        $waitWhileBookingServices = Mage::getModel('allure_waitwhile/services')->getCollection();
        $waitWhileBookingServices->addFieldToSelect("*");
        $waitWhileBookingServices->addFieldToFilter("code",array("in" => $piercingCodeArray) );
        $waitWhileBookingServices->addFieldToFilter("store_id", $storeId);
                
        $waitWhileBookingServicesArray = array();
        foreach ($waitWhileBookingServices as $bookingService)
        {
            $waitWhileBookingServicesArray[$bookingService->getWaitwhileServiceId()] = 1;
        }
        return $waitWhileBookingServicesArray;
    }
    
    /**
     * @param $observer
     */
    public function process($observer)
    {
        try{
            /**@var $helper Allure_WaitWhile_Helper_WClient*/
            $helper = Mage::helper("allure_waitwhile/wClient");
            $isDebugLog = $helper->getHelper()->isDebugLog();
            $this->is_debug = $isDebugLog;
            
            if(!$helper->getHelper()->isBookingEnabled()){
                $this->addLog("Wait-While Booking configuration Disabled.");
                return ;
            }
            $this->addLog("Start Process.");
            $appointment = $observer->getEvent()->getAppointment();
            if(!$appointment){
                $this->addLog("Appointement data not found.");
                $this->addLog("End Process.");
                return ;
            }
            $appointmentId = $appointment->getId();
            /**@var $appointment Allure_Appointments_Model_Appointments */
            $appointment = Mage::getModel('appointments/appointments')->load($appointmentId);
            
            $storeId = $appointment->getStoreId();
            
            $waitWhileBookingServices = $this->getBookingServiceIds($appointmentId, $storeId);
            
            if(!count($waitWhileBookingServices)){
                $this->addLog("Wait-While booking service id required.");
                return ;
            } 
            
            $waitWhileBooking = Mage::getModel('allure_waitwhile/booking')->load($appointmentId,"appointment_id");
            
            $apptStartTime  = strtotime($appointment->getAppointmentStart());
            $apptEndTime    = strtotime($appointment->getAppointmentEnd());
            $duration = ($apptEndTime - $apptStartTime + 60); //duration in seconds
            $startTime = str_replace("UTC", "T", date("Y-m-dTH:i",$apptStartTime));
            $endTime = str_replace("UTC", "T", date("Y-m-dTH:i",$apptEndTime+60));
            $apptStatus = $appointment->getAppStatus();
            $bookingState = $helper::BOOKING_BOOKED;
            
            $waitWhileLocalization = Mage::getModel('allure_waitwhile/localization')->getCollection();
            $waitWhileLocalization->addFieldToSelect("*");
            $waitWhileLocalization->addFieldToFilter("store_id", $storeId);
            $waitWhileLocalizationObj = $waitWhileLocalization->getFirstItem();
            $waitWhileLocaleId = "";
            if($waitWhileLocalizationObj->getId()){
                $waitWhileLocaleId = $waitWhileLocalizationObj->getWaitwhileLocaleId();
            }
            
            if(!$waitWhileLocaleId){
                $this->addLog("Wait-While localization id required.");
                return ;
            }
            
            //get waitwhile resources
            $waiteWhileResourcesArray = array();
            $waitWhileResources = Mage::getModel('allure_waitwhile/resources')->getCollection();
            $waitWhileResources->addFieldToSelect("*");
            $waitWhileResources->addFieldToFilter("store_id", $storeId);
            foreach ($waitWhileResources as $waitWhileResorce){
                $waiteWhileResourcesArray[$waitWhileResorce->getWaitwhileResourceId()] = 1;
            }
            
            $args = array(
                "date"=> $startTime,
                "name"=> $appointment->getFirstname()." ".$appointment->getLastname(),
                "duration" => $duration,
                "partySize" => $appointment->getPiercingQty(),
                "locationId" => $waitWhileLocaleId,
                "email" => $appointment->getEmail(),
                "notes" => $appointment->getSpecialNotes(),
                "externalCustomerId" => $appointmentId,
                //"externalId" => $appointmentId,
                "services" => $waitWhileBookingServices,
                "phone" => $appointment->getPhone(),
                "state" => $bookingState
            );
            
            if(count($waiteWhileResourcesArray) > 0){
                $args["resources"] = $waiteWhileResourcesArray;
            }
            
            $bookingPath = $helper::BOOKING_PATH;
            
            if(!$waitWhileBooking->getAppointmentId()){
                if($apptStatus != $appointment::STATUS_ASSIGNED){
                    $this->addLog("Appointement status is not ASSIGNED.");
                    $this->addLog("End Process.");
                    return ;
                }
                
                //booking availability
                $bookingAvailability = $helper::BOOKING_AVAILABILITY;
                $bookingAvailability .= "?locationId={$waitWhileLocaleId}&fromDate={$startTime}&toDate={$endTime}";
                $availResponse = $helper->processRequest($bookingAvailability,"GET");
                $availResponse = json_decode($availResponse,true);
                if(!isset($availResponse['error'])){
                    $isAvailabilty = false;
                    foreach ($availResponse as $availability){
                        $this->addLog("numAvailableSpots = {$availability["numAvailableSpots"]}");
                        if($availability["numAvailableSpots"] != 0){
                            $isAvailabilty = true;
                        }else{
                            $isAvailabilty = false;
                            break;
                        }
                    }
                    $this->addLog("Booking Availability = {$isAvailabilty}");
                    if(!$isAvailabilty){
                        $bookingState = $helper::BOOKING_WAITING;
                        $args["state"] = $bookingState;
                    }
                }else{
                    $this->addLog($availResponse);
                }
                
                $response = $helper->processRequest($bookingPath,"POST",$args);
                $response = json_decode($response,true);
                if(isset($response["id"])){
                    $waitWhileBooking->setAppointmentId($appointmentId)
                    ->setWaitwhileBookingId($response["id"])
                    ->save();
                    $this->addLog("Appointment ID: {$appointmentId} booked into wait-while app id : {$response["id"]}");
                }else{
                    $this->addLog("Appointment ID: {$appointmentId}. Error while booking in wait-while app.");
                    $this->addLog($response);
                }
            }else{
                $waitWhileBookingId = $waitWhileBooking->getWaitwhileBookingId();
                if($apptStatus == $appointment::STATUS_ASSIGNED){
                    $bookingState = $helper::BOOKING_BOOKED;
                    $args["state"] = $bookingState;
                }elseif ($apptStatus == $appointment::STATUS_COMPLETED){
                    $bookingState = $helper::BOOKING_COMPLETE;
                    $args["state"] = $bookingState;
                }elseif ($apptStatus == $appointment::STATUS_CANCELLED){
                    $bookingState = $helper::BOOKING_COMPLETE;
                    $args["state"] = $bookingState;
                    $tag = "CANCELLED";
                    $args["addTag"]  = $tag;
                }
                
                if($waitWhileBookingId){
                    $response = $helper->processRequest("{$bookingPath}/{$waitWhileBookingId}","POST",$args);
                    $response = json_decode($response,true);
                    if(isset($response["id"])){
                        $this->addLog("Appointment ID: {$appointmentId} updated into wait-while app id : {$response["id"]}");
                    }else{
                        $this->addLog("Appointment ID: {$appointmentId}. Error while booking in wait-while app.");
                        $this->addLog($response);
                    }
                }
                
            }
            
        }catch (Exception $e){
            $this->addLog("Exception in ".get_class($this). ".Message: {$e->getMessage()}");
        }
        $this->addLog("End Process.");
    }
}