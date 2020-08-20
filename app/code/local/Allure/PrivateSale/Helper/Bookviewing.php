<?php
class Allure_PrivateSale_Helper_Bookviewing extends Mage_Core_Helper_Abstract
{
    public function isEnabled(){
        return Mage::getStoreConfig("privatesale/bookviewing/book_viewing_enabled");
    }
    public function getTemplateId(){
        return Mage::getStoreConfig("privatesale/bookviewing/email_temp_code");
    }
    public function getEmailReceiver(){
        return Mage::getStoreConfig("privatesale/bookviewing/receiver");
    }

    private function getDataHelper()
    {
        return Mage::helper('privatesale');
    }

    public function getBookAViewingButton()
    {
        if($this->isEnabled() && $this->validateCategory())
        {
            return '
              <div class="col-12 float-left mt-2">
                <div class="row">
                <a class="fancybox fancybox.iframe btn-privatesale">
                <button id="bookviewing" style="width: 163px;height: 51px;text-transform: uppercase;" type="button"  class="button dark-button" >Book A Viewing</button>
                </a>
            </div>
            </div>';
        }

        return "";
    }
    public function getBookAViewingForm()
    {
        if($this->isEnabled() && $this->validateCategory())
        {
            return Mage::app()->getLayout()->createBlock('core/template')->setTemplate('privatesale/bookviewing.phtml')->toHtml();
        }

        return "";
    }

    private function validateCategory()
    {
        if(Mage::registry('current_product')) {
            $product = Mage::registry('current_product');
            $id = $product->getId();
            $_product = Mage::getModel('catalog/product')->load($id);
            $categoryIds = array_values($_product->getCategoryIds());
            if (in_array($this->getDataHelper()->getCategory(), $categoryIds)) {
                return true;
            }
        }
        return false;
    }
}
