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

    public function getStoreId() {

        $storeId = "";

        $storeCode = 'nordstrom_la';

        if (!$storeId && $storeCode) {
            $storeId = Mage::helper('allure_virtualstore')->getStoreId($storeCode);
        }

        return $storeId;
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

    /**
     * return array of store mapping
     */
    private function getAppointmentStoreMapping(){
        return Mage::helper("appointments/storemapping")->getStoreMappingConfiguration();
    }
}
