<?php

class Ebizmarts_BakerlooRestful_Model_Api_Wishlist extends Ebizmarts_BakerlooRestful_Model_Api_Api
{
    protected $_model = "wishlist/wishlist";

    protected function _getIndexId()
    {
        return 'wishlist_id';
    }

    public function post()
    {
        Mage::throwException('Not implemented.');
    }

    public function put()
    {
        Mage::throwException('Not implemented.');
    }

    public function _createDataObject($id = null, $data = null)
    {
        $result = array();

        if (is_null($data)) {
            $wishlist = $this->getModel($this->_model)->load($id);
        } else {
            $wishlist = $data;
        }

        /** @var Mage_Wishlist_Model_Wishlist $wishlist */
        if ($wishlist->getId()) {
            $result["wishlist_id"] = (int)$wishlist->getId();
            $result["customer_id"] = (int)$wishlist->getCustomerId();
            $result["wishlist_items"] = array();

            $items = $wishlist->getItemCollection();
            foreach ($items as $_item) {
                $result["wishlist_items"][] = array(
                    "wishlist_item_id" => (int)$_item->getId(),
                    "product_id" => (int)$_item->getProductId(),
                    "qty" => (int)$_item->getQty()
                );
            }
        }

        return $this->returnDataObject($result);
    }

    public function addToWishlist()
    {
        $h = $this->getHelper('bakerloo_restful');

        if (!$this->getStoreId()) {
            Mage::throwException($h->__('Please provide a Store ID.'));
        }
        Mage::app()->setCurrentStore($this->getStoreId());

        //get the customer
        $customerId = $this->_getQueryParameter('customer_id');
        $customer = $this->getModel('customer/customer')->load((int)$customerId);

        if (!$customer->getId()) {
            Mage::throwException($h->__('Cannot add product to wishlist. Customer ID not specified.'));
        }

        $data = $this->getJsonPayload(true);

        //get the product
        $productId = $data['product_id'];
        $product = $this->getModel('catalog/product')->load($productId);
        if (!$product->getId()) {
            Mage::throwException($h->__('Cannot add product to wishlist. Product "%s" does not exist.', $productId));
        }

        $buyInfo = $this->getHelper('bakerloo_restful/sales')->getBuyInfo($data);

        /** @var Mage_Wishlist_Model_Wishlist $wishlist */
        $wishlist = $this->getModel('wishlist/wishlist')->loadByCustomer($customer, true);
        $result = $wishlist->addNewItem($product, $buyInfo);

        if (is_string($result)) {
            Mage::throwException($h->__($result));
        }

        $wishlist->save();
        return $this->_createDataObject(null, $wishlist);
    }
}
