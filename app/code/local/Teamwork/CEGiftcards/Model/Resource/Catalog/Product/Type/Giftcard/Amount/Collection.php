<?php

class Teamwork_CEGiftcards_Model_Resource_Catalog_Product_Type_Giftcard_Amount_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract//Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('teamwork_cegiftcards/catalog_product_type_giftcard_amount');
    }

    public function addProductFilter($product)
    {
        if ($product instanceof Mage_Catalog_Model_Product) {
            $productId = $product->getId();
        } else {
            $productId = $product;
        }
        $this->addFieldToFilter("product_id", $productId);
        return $this;
    }

    public function _initSelect()
    {
        parent::_initSelect();
        $this->addOrder('position',  self::SORT_ORDER_ASC);
    }


}
