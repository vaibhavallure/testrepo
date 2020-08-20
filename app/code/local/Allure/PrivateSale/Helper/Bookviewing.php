<?php
class Allure_PrivateSale_Helper_Bookviewing extends Mage_Core_Helper_Abstract
{
    public function isEnabled(){
        return Mage::getStoreConfig("privatesale/module/book_viewing_enabled");
    }

    private function getDataHelper()
    {
        return Mage::helper('privatesale');
    }

    public function getBookAViewingButton()
    {
        $html="";
        if($this->isEnabled() && $this->validateCategory())
        {
            $button='<div class="col-12 float-left mt-2"><div class="row">
 <a class="fancybox fancybox.iframe btn-privatesale">
              <button id="bookviewing" style="width: 163px;height: 51px;text-transform: uppercase;" type="button"  class="button dark-button" >Book A Viewing</button>
              </a></div></div>';

            $modal='';
            $script="";

            $html.= $button;
            $html.=$modal;
            $html.=$script;
        }

        return $html;
    }

    private function validateCategory()
    {
        $product = Mage::registry('current_product');
        $id = $product->getId();
        $_product = Mage::getModel('catalog/product')->load($id);
        $categoryIds = array_values($_product->getCategoryIds());
        if (in_array($this->getDataHelper()->getCategory(),$categoryIds)) {
            return true;
        }

        return false;
    }


}
