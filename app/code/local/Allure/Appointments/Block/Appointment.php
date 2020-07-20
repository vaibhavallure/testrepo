<?php
class Allure_Appointments_Block_Appointment extends Mage_Core_Block_Template{

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
        return $this->getUrl('*/book/save',array('_secure' => true)).$appendUrl;
    }

    public function getTimeActionUrl()
    {
        return $this->getUrl('*/book/ajaxGetTime', array('_secure' => true));
    }

    public function getWorkingDaysActionUrl()
    {
        return $this->getUrl('*/book/ajaxGetWorkingDays', array('_secure' => true));
    }
    public function getSupportDetailsActionUrl()
    {
        return $this->getUrl('*/book/ajaxSupportDetails', array('_secure' => true));
    }
    public function getAvailableSlotsActionUrl()
    {
        return $this->getUrl('*/book/getAvailableSlots', array('_secure' => true));
    }

    public function getStoreId() {

        $storep = $this->getRequest()->getParam('store');
        $appointment_id = Mage::registry('appointment_booking_id');

        if($storep)
            return $storep;
        elseif ($appointment_id)
            return $this->helper()->isRealAppointmentId($appointment_id,"store_id");
        elseif (Mage::app()->getRequest()->getParam("id"))
            return $this->helper()->isRealAppointmentId(Mage::app()->getRequest()->getParam("id"),"store_id");
        elseif( $this->getRequest()->getParam('code'))
            return Mage::helper('allure_virtualstore')->getStoreId($this->getRequest()->getParam('code'));


        return "";
    }

    public function getStoreShortUrl($store_id=null) {
            $storeCode=$this->getStoreCode($store_id);

        if($this->getRequest()->getParam('user')=="admin")
            return Mage::getBaseUrl()."appointments/book/store/code/".$storeCode."/user/admin";
        else
            return Mage::getBaseUrl()."appointments/book/store/code/".$storeCode;
    }

    public function getStoreCode($store_id=null) {
        if(!$store_id)
            $store_id = $this->getStoreId();

        return Mage::helper('allure_virtualstore')->getStoreCode($store_id);
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
            /*code to remove popup store from list*/
            if($this->helper()->isPopupStore($val))
            {
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
        return $this->getUrl('*/book/subscribe', array('_secure' => true));
    }


    public function isAdmin()
    {
        if($this->getRequest()->getParam('user')=="admin")
            return true;
        else
            return false;
    }
    public function isMobile()
    {
        if(Zend_Http_UserAgent_Mobile::match( Mage::helper('core/http')->getHttpUserAgent(), $_SERVER ))
            return true;
        else
            return false;
    }
    public function isReleaseSubmitted($flag)
    {
        echo  ($flag) ? 'disabled title="Your release form has been submitted and is unable to be changed on line.  You may edit the form when you arrive for your appointment."': '';
    }
    public function getStoreName($store_id=null)
    {
        if(!$store_id)
            $store_id=$this->getStoreId();

        return $this->helper()->getStoreData($store_id,"appears");
    }
    public function getStoreAddress($store_id=null)
    {
        if(!$store_id)
            $store_id=$this->getStoreId();
        return $this->helper()->getStoreData($store_id,"store_address");
    }
    public function getStoreContact($store_id=null)
    {
        if(!$store_id)
            $store_id=$this->getStoreId();
        return $this->helper()->getStoreData($store_id,"store_phone");
    }
    public function getStoreHours($store_id=null)
    {
        if(!$store_id)
            $store_id=$this->getStoreId();
        return $this->helper()->getStoreData($store_id,"store_hours_operation");
    }
    public function helper()
    {
        return Mage::helper("appointments/data");
    }
    public function isModify()
    {
        $controller=Mage::app()->getRequest()->getControllerName();
        $action=Mage::app()->getRequest()->getActionName();
        $id=$this->getPara("id");

        if($controller=="book" && $action=="index" && !empty($id))
            return true;
        else
            return false;
    }
    public function getPara($key)
    {
        return Mage::app()->getRequest()->getParam($key);
    }
    public function getPriceBlock()
    {
        $blockId= $this->helper()->getStoreData($this->getStoreId(),"piercing_pricing_block");
       return $this->getLayout()->createBlock('cms/block')->setBlockId($blockId)->toHtml();
    }
    public function getInformationBlock()
    {
        $static_block_code="important-information-".$this->getStoreCode();
        if($content= $this->getLayout()->createBlock('cms/block')->setBlockId($static_block_code)->toHtml())
            return $content;
        else
            return $this->getLayout()->createBlock('cms/block')->setBlockId("important-information")->toHtml();
    }
    public function storeFound()
    {
          if($this->getStoreId())
              return true;
          else
              return false;
    }
    public function getBannerBlock(){
        $blockId= !empty($this->getStoreCode())?$this->getStoreCode()."_appointment_banner_block":"appointment_banner_block";
        return $this->getLayout()->createBlock('cms/block')->setBlockId($blockId)->toHtml();
    }

    public function getDefaultCountryShortCodeHtml()
    {
        $store_id=$this->getStoreId();
        echo '<input type="hidden" name="default_country_phone" id="default_country_phone" value="'.$this->helper()->getStoreData($store_id,"default_country_phone").'">';

    }

    public function getAlertMessage(){
        $store_id=$this->getStoreId();
        return $this->helper()->getStoreData($store_id,"alertMessage");
    }
    /* MT-1439 :Disable Piercing Option */
    public function getIsDisabledPiercingOption(){
        $store_id = $this->getStoreId();
        return $this->helper()->getStoreData($store_id,"disable_piercing");
    }

    /*MT-1484*/
    public function getNoOfGuestBlock($selected=0)
    {
        $no_of_guest=$this->getGuestsNo($selected);

        $options='';

        for ($i=1;$i<=$no_of_guest;$i++)
        {
            $isSelected="";
            if($selected==$i)
            {
                $isSelected="selected";
            }
          $options.='<option value="'.$i.'" '.$isSelected.'>'.$i.'</option>';
        }
        $dropdown='<select class="input-box" id="count" name="piercing_qty" onchange="changeQty(this)">'.$options.'</select>';

        return $dropdown;
    }

    public function getGuestsNo($selected=0)
    {
             $no_of_guest=4;
             $no_of_guest_config=(int)Mage::getStoreConfig('appointments/no_of_guest/no');

             if(is_int($no_of_guest_config)  &&  $no_of_guest_config>0) {
                 $no_of_guest = Mage::getStoreConfig('appointments/no_of_guest/no');
             }

             if($no_of_guest<$selected) {
                 $no_of_guest = $selected;
             }

             return $no_of_guest;
    }

}
