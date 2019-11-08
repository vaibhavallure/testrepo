<?php
class Allure_Appointments_Block_Popup extends Mage_Core_Block_Template{

    public function getActionUrl()
    {
        $appendUrl = "";
        $storep = $this->getRequest()->getParam('store');

        if($storep)
        {
            if($appendUrl)
                $appendUrl.="&";
            else
                $appendUrl="?";
            $appendUrl.= "store=".$storep;
        }
        return $this->getUrl('*/popup/save',array('_secure' => true)).$appendUrl;
    }

    public function getTimeActionUrl()
    {
        return $this->getUrl('*/popup/ajaxGetTime', array('_secure' => true));
    }

    public function getWorkingDaysActionUrl()
    {
        return $this->getUrl('*/popup/ajaxGetWorkingDays', array('_secure' => true));
    }
    public function getSupportDetailsActionUrl()
    {
        return $this->getUrl('*/popup/ajaxSupportDetails', array('_secure' => true));
    }
    public function getAvailableSlotsActionUrl()
    {
        return $this->getUrl('*/popup/getAvailableSlots', array('_secure' => true));
    }

    public function getStoreId() {
        $popupStoreId=Mage::getStoreConfig('appointments/popup_setting/store');
        return $popupStoreId;
    }

    public function getStoreShortUrl() {
        if($this->getRequest()->getParam('user')=="admin")
            return Mage::getBaseUrl()."appointments/popup/index/user/admin";

        if(!empty(Mage::getStoreConfig('appointments/popup_setting/popup_url')))
            return Mage::getBaseUrl().Mage::getStoreConfig('appointments/popup_setting/popup_url');
        else
            return Mage::getBaseUrl()."appointments/popup/";

    }

    public function getStoreCode() {
        $storeId = $this->getStoreId();

        return Mage::helper('allure_virtualstore')->getStoreCode($storeId);
    }

    public function getActiveStore()
    {
        $configData     = $this->getAppointmentStoreMapping();
        $storesAdded    = $configData['stores'];
        $storesEnabled  = $configData['enable_store'];
        $appearsName    = $configData['appears'];
        $activeStores   = array();
        if (Mage::helper('core')->isModuleEnabled('Allure_Virtualstore')){
            $virtualStoreHelper = Mage::helper("allure_virtualstore");
            $stores = $virtualStoreHelper->getVirtualStores();
        }else{
            $stores = Mage::app()->getStores();
        }

        foreach ($storesAdded as $key=>$val){
            if($key == 0 || $storesEnabled[$key]==0){
                continue;
            }
            $activeStores[$val] = ($appearsName[$key])?$appearsName[$key]:$stores[$val]->getName();
        }
        return $activeStores;
    }
    public function getSlotTimeJson()
    {
        return json_encode((Mage::getModel('appointments/slots'))::SLOTS);
    }

    /**
     * return array of store mapping
     */
    private function getAppointmentStoreMapping(){
        return Mage::helper("appointments/storemapping")->getStoreMappingConfiguration();
    }
/**
 * Subscribe Action URL
 */
   public function getSubscribeUrl()
   {
       return $this->getUrl('*/popup/subscribe', array('_secure' => true));
   }


   public function isAdmin()
   {
       if($this->getRequest()->getParam('user')=="admin")
             return true;
       else
             return false;
   }
   public function isReleaseSubmitted($flag)
   {
     echo  ($flag) ? 'disabled title="Your release form has been submitted and is unable to be changed on line.  You may edit the form when you arrive for your appointment."': '';
   }
}
