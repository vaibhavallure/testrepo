<?php
class Allure_InstaCatalog_Block_Product extends Mage_Core_Block_Template
{
    protected  $_child_products = null;
    protected $_collection = null;
    
    protected function prepareData(){
        $_product = Mage::registry('current_product');
        $productId = $_product->getId();
        $sku_main = $_product->getSku();
        $old_sku = $sku_main;
        $skForwordingArray = array();
        $childProductArr = array();
        $childProductArr[] = $productId;
        $sku_first_char = strtoupper($old_sku[0]);
        
        // MT-362 SKu forwording logic
        if ($sku_first_char == 'X') {
            $E_sku = 'E' . substr($old_sku, 1);
            $E_ProductId = Mage::getModel("catalog/product")->getIdBySku($E_sku);
            if (isset($E_ProductId) && ! empty($E_ProductId)) {
                $skForwordingArray[] = $E_ProductId;
                $childProductArr[] = $E_ProductId;
            }
        }
        
        if ($sku_first_char == 'C' || $sku_first_char == 'S' || $sku_first_char == 'N') {
            $E_sku = 'E' . substr($old_sku, 1);
            $E_ProductId = Mage::getModel("catalog/product")->getIdBySku($E_sku);
            if (isset($E_ProductId) && ! empty($E_ProductId)) {
                $skForwordingArray[] = $E_ProductId;
                $childProductArr[] = $E_ProductId;
            }
            $X_sku = 'X' . substr($old_sku, 1);
            $X_ProductId = Mage::getModel("catalog/product")->getIdBySku($X_sku);
            if (isset($X_ProductId) && ! empty($X_ProductId)) {
                $skForwordingArray[] = $X_ProductId;
                $childProductArr[] = $X_ProductId;
            }
        }
        
        // MT-362 SKu forwording logic
        
        if (preg_match("/_/", $sku_main))
            // $sku_main = substr($sku_main, 0,strpos($sku_main, "_"));
            $childProductArr[] = $productId;
            
            if ($_product->getTypeId() == "configurable") {
                $currentchildrenIds = $_product->getTypeInstance()->getChildrenIds($productId);
                foreach ($currentchildrenIds[0] as $childrenId) {
                    $childProductArr[] = $childrenId;
                }
            }
            
            $parentItemNumber = $_product->getParentItemNumber();
            $childrenProducts = Mage::getModel('catalog/product')->getCollection();
            if (isset($parentItemNumber)) {
                $childrenProducts->addAttributeToFilter(array(
                    array(
                        'attribute' => 'parent_item_number',
                        'like' => '%' . $parentItemNumber . '%'
                    )
                ));
            } else {
                $childrenProducts->addAttributeToFilter(array(
                    array(
                        'attribute' => 'sku',
                        'like' => '' . $sku_main . '%'
                    )
                ));
            }
            $childrenProducts->addAttributeToFilter(array(
                array(
                    'attribute' => 'type_id',
                    'eq' => 'configurable'
                )
            ));
            
            $collection = Mage::getResourceModel('allure_instacatalog/feed_collection');
            $str = '';
            $cnt = 0;
            if ($childrenProducts->getSize() > 0) { // if(!empty($childrenProducts)){
                foreach ($childrenProducts as $product) {
                    $cnt = $cnt + 1;
                    $str .= ' FIND_IN_SET((' . $product->getId() . '),`product_ids`) OR ';
                }
            }
            // Adding as per MT-362 ESKU forwarding
            
            if (! empty($skForwordingArray)) {
                foreach ($skForwordingArray as $id) {
                    $str .= ' FIND_IN_SET((' . $id . '),`product_ids`) OR ';
                }
            }
            $str .= 'FIND_IN_SET((' . $productId . '),`product_ids`)';
            $collection->getSelect()->where('status = 1 AND (' . $str . ')');
            
            $this->_collection = $collection;
            $this->_child_products = $childProductArr;
    }
    
    public function getInstagramPostCollection(){
        $this->prepareData();
        return $this->_collection;
    }
    
    public function getChildProductArray(){
        return $this->_child_products;
    }
    
    public function getInstagramHotspotProducts(){
        $skuArr = array();
        foreach ($this->_collection as $feeds) {
            $_options = json_decode($feeds->getHotspots());
            foreach ($_options as $opt) {
                $skuArr[$opt->text] = $opt->text;
            }
        }
        $productCollection = Mage::getModel("catalog/product")->getCollection()
        ->addAttributeToSelect('*')
        ->addAttributeToFilter('sku', array(
            'in' => $skuArr
        ));
        
        $productArr = array();
        foreach ($productCollection as $pro) {
            $productArr[$pro->getSku()] = array(
                "name" => $pro->getName(),
                "id" => $pro->getId(),
                "url" => $pro->getProductUrl(),
                "parentItemNumber" => $pro->getParentItemNumber()
            );
        }
        return $productArr;
    }
}