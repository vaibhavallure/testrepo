<?php

class Ecp_AjaxWishlist_Model_Wishlist extends Mage_Wishlist_Model_Wishlist
{
	    public function getItemCollection()
    {
        if (is_null($this->_itemCollection)) {
            /** @var $currentWebsiteOnly boolean */
            $currentWebsiteOnly = !Mage::app()->getStore()->isAdmin();
            $this->_itemCollection =  Mage::getResourceModel('wishlist/item_collection')
                ->addWishlistFilter($this)
                ->addStoreFilter($this->getSharedStoreIds($currentWebsiteOnly));
        }

        return $this->_itemCollection;
    }
	
}
