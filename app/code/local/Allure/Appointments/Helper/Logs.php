<?php
class Allure_Appointments_Helper_Logs extends Mage_Core_Helper_Abstract
{
    const ADMIN_USER = "admin";
    
    private function getUser(){
        $adminSession = Mage::getSingleton('admin/session'); 
        return $adminSession->getUser();
    }
    
    /**
     * return request object
     * that contains info about
     * params and action name
     */
    private function getRequest(){
        return Mage::app()->getRequest();
    }
    
    /**
     * add log data into table
     */
    public function saveLogs($userType = "admin"){
	    try{
	        $params = $this->getRequest()->getParams();
	        $data   = json_encode($params);
	        $user   = $this->getUser();
	        $userId = $user->getUserId();
	        $email  = $user->getEmail();
	        $action = $this->getActionName();
	        $log_model = Mage::getModel("appointments/logs")
	           ->setUserId($userId)
	           ->setEmail($email)
	           ->setUserType($userType)
	           ->setAction($action)
	           ->setInputData($data)
	           ->save();
	    }catch (Exception $e){
	        //nothing maintain here
	    }
	}
	
	/**
	 * return action name with controller name of appointments module
	 */
	public function getActionName(){
	    $request           = $this->getRequest();
	    $moduleName        = strtolower($request->getModuleName());
	    $controllerName    = strtolower($request->getControllerName()); 
	    $actionName        = strtolower($request->getActionName());
	    $fullPath              = $moduleName."_".$controllerName."_".$actionName;
	    return $fullPath;
	}
	
}
	 