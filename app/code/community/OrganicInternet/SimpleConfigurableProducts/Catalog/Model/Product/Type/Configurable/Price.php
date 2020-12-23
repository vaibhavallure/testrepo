<?php

#The methods in there have become a bit convoluted, so it could benefit from a tidy,
#...though the logic is not that simple any more.

class OrganicInternet_SimpleConfigurableProducts_Catalog_Model_Product_Type_Configurable_Price
    extends Mage_Catalog_Model_Product_Type_Configurable_Price
{
    #We don't want to show a separate 'minimal' price for configurable products.
    public function getMinimalPrice($product)
    {
        return $this->getPrice($product);
    }

    public function getMaxPossibleFinalPrice($product) {
        #Indexer calculates max_price, so if this value's been loaded, use it
        $price = $product->getMaxPrice();
        if ($price !== null) {
            return $price;
        }

        $childProduct = $this->getChildProductWithHighestPrice($product, "finalPrice");
        #If there aren't any salable child products we return the highest price
        #of all child products, including any ones not currently salable.

        if (!$childProduct) {
            $childProduct = $this->getChildProductWithHighestPrice($product, "finalPrice", false);
        }

        if ($childProduct) {
            return $childProduct->getFinalPrice();
        }
        return false;
    }

    #If there aren't any salable child products we return the lowest price
    #of all child products, including any ones not currently salable.
    public function getFinalPrice($qty=null, $product)
    {
/*
        #calculatedFinalPrice seems not to be set in this version (1.4.0.1)
        if (is_null($qty) && !is_null($product->getCalculatedFinalPrice())) {
            #Doesn't usually get this far as Product.php checks first.
            #Mage::log("returning calculatedFinalPrice for product: " . $product->getId());
            return $product->getCalculatedFinalPrice();
        }
*/

        //Start edit
        $selectedAttributes = array();
        if ($product->getCustomOption('attributes')) {
            $selectedAttributes = unserialize($product->getCustomOption('attributes')->getValue());
        }
        //End edit
        if (sizeof($selectedAttributes)) return $this->getSimpleProductPrice($qty, $product);

        $childProduct = $this->getChildProductWithLowestPrice($product, "finalPrice");
        if (!$childProduct) {
            $childProduct = $this->getChildProductWithLowestPrice($product, "finalPrice", false);
        }

        if ($childProduct) {
            $fp = $childProduct->getFinalPrice();
        } else {
            return false;
        }
        if (isset(Mage::app()->getRequest()->getParams()['order']) && Mage::app()->getRequest()->getParams()['order']['account']['group_id'] == 2) {
            $fp = $this->getBasePrice($product,$qty);
        }
        
        $product->setFinalPrice($fp);
        return $fp;
    }

    public function getSimpleProductPrice($qty=null, $product)
    {
        $cfgId = $product->getId();
        $product->getTypeInstance(true)
            ->setStoreFilter($product->getStore(), $product);
        $attributes = $product->getTypeInstance(true)
            ->getConfigurableAttributes($product);
        $selectedAttributes = array();
        if ($product->getCustomOption('attributes')) {
            $selectedAttributes = unserialize($product->getCustomOption('attributes')->getValue());
        }
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $dbMeta = Mage::getSingleton('core/resource');
        $sql = <<<SQL
SELECT main_table.entity_id FROM {$dbMeta->getTableName('catalog/product')} `main_table` INNER JOIN
{$dbMeta->getTableName('catalog/product_super_link')} `sl` ON sl.parent_id = {$cfgId}
SQL;
        foreach($selectedAttributes as $attributeId => $optionId) {
            $alias = "a{$attributeId}";
            $sql .= ' INNER JOIN ' . $dbMeta->getTableName('catalog/product') . "_int" . " $alias ON $alias.entity_id = main_table.entity_id AND $alias.attribute_id = $attributeId AND $alias.value = $optionId AND $alias.entity_id = sl.product_id";
        }
        $id = $db->fetchOne($sql);
        return Mage::getModel("catalog/product")->load($id)->getFinalPrice($qty);
    }
    
    public function getPrice($product)
    {
        #Just return indexed_price, if it's been fetched already
        #(which it will have been for collections, but not on product page)
        $price = $product->getIndexedPrice();
        if ($price !== null) {
            return $price;
        }

        $childProduct = $this->getChildProductWithLowestPrice($product, "finalPrice");
        #If there aren't any salable child products we return the lowest price
        #of all child products, including any ones not currently salable.
        if (!$childProduct) {
            $childProduct = $this->getChildProductWithLowestPrice($product, "finalPrice", false);
        }

        if ($childProduct) {
            return $childProduct->getPrice();
        }

        return false;
    }

    public function getChildProducts($product, $checkSalable=true)
    {
        static $childrenCache = array();
        $cacheKey = $product->getId() . ':' . $checkSalable;

        if (isset($childrenCache[$cacheKey])) {
            return $childrenCache[$cacheKey];
        }

        $childProducts = $product->getTypeInstance(true)->getUsedProductCollection($product);
        $childProducts->addAttributeToSelect(array('price', 'special_price', 'status', 'special_from_date', 'special_to_date'));

        if ($checkSalable) {
            $salableChildProducts = array();
            foreach($childProducts as $childProduct) {
                if($childProduct->isSalable()) {
                    $salableChildProducts[] = $childProduct;
                }
            }
            $childProducts = $salableChildProducts;
        }

        $childrenCache[$cacheKey] = $childProducts;
        return $childProducts;
    }

/*
    public function getLowestChildPrice($product, $priceType, $checkSalable=true)
    {
        $childProduct = $this->getChildProductWithLowestPrice($product, $priceType, $checkSalable);
        if ($childProduct) {
            if ($priceType == "finalPrice") {
                $childPrice = $childProduct->getFinalPrice();
            } else {
                $childPrice = $childProduct->getPrice();
            }
        } else {
            $childPrice = false;
        }
        return $childPrice;
    }
*/
    #Could no doubt add highest/lowest as param to save 2 near-identical functions
    public function getChildProductWithHighestPrice($product, $priceType, $checkSalable=true)
    {
        $childProducts = $this->getChildProducts($product, $checkSalable);
        if (count($childProducts) == 0) { #If config product has no children
            return false;
        }
        $maxPrice = 0;
        $maxProd = false;
        foreach($childProducts as $childProduct) {
            if ($priceType == "finalPrice") {
                $thisPrice = $childProduct->getFinalPrice();
            } else {
                $thisPrice = $childProduct->getPrice();
            }
            if($thisPrice > $maxPrice) {
                $maxPrice = $thisPrice;
                $maxProd = $childProduct;
            }
        }
        return $maxProd;
    }

    public function getChildProductWithLowestPrice($product, $priceType, $checkSalable=true)
    {
        $childProducts = $this->getChildProducts($product, $checkSalable);
        if (count($childProducts) == 0) { #If config product has no children
            return false;
        }
        $minPrice = PHP_INT_MAX;
        $minProd = false;
        foreach($childProducts as $childProduct) {
            if ($priceType == "finalPrice") {
                $thisPrice = $childProduct->getFinalPrice();
            } else {
                $thisPrice = $childProduct->getPrice();
            }
            if($thisPrice < $minPrice) {
                $minPrice = $thisPrice;
                $minProd = $childProduct;
            }
        }
        return $minProd;
    }

    //Force tier pricing to be empty for configurable products:
    public function getTierPrice($qty=null, $product)
    {
        return array();
    }
}
