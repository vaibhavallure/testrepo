<?php
/**
 * @extension   Remmote_Facebookproductcatalog
 * @author      Remmote    
 * @copyright   2016 - Remmote.com
 * @descripion  Facebook Product Catalog Model
 */
class Remmote_Facebookproductcatalog_Model_Productcatalog
{

    /**
     * Exports the product catalog to media/facebook_productcatalog folder
     * Fields exported are the required fields to create standard dynamic ads in Facebook
     * @return [type]
     * @author remmote
     * @date   2016-11-29
     */
    public function exportCatalog($websiteCode = "") {
        $_mediaBasePath     = Mage::getBaseDir('media');
        $catalog_path       = $_mediaBasePath . DS . 'facebook_productcatalog';
        $currency_code      = Mage::app()->getStore()->getCurrentCurrencyCode(); 
        $websiteId          = '';

        //Get websiteId if websiteCode is defined
        if ($websiteCode) {
            $website    = Mage::getModel('core/website')->load($websiteCode);
            $websiteId  = $website->getId();

            //Update currency code
            $website_data   = Mage::getModel('core/website')->load($websiteId); 
            $currency_code  = $website_data->getDefaultStore()->getCurrentCurrencyCode();
        } else {
            //Getting default website
            $website    = Mage::helper('remmote_facebookproductcatalog')->getDefaultWebsite();
        }

        // check if facebook_productcatalog dir exist, if not create it
        if(!file_exists($catalog_path)){
            mkdir($catalog_path , 0777 , true);
            chmod($catalog_path , 0777);
        }

        //Getting website store views (languages)
        foreach ($website->getGroups() as $group) {
            $stores = $group->getStores();
            foreach ($stores as $store) {
                if($store->getIsActive()){
                    //Generate CSV files
                    $this->_generateStoreCsvFile($websiteId, $website, $store, $currency_code, $catalog_path);
                }
            }
        }

        $date_time = date("M j, Y") . '  at  ' . date("g:iA");
        Mage::getModel('core/config')->saveConfig(Remmote_Facebookproductcatalog_Model_Config::TIME_LASTEXPORT, $date_time);
    }

    /**
     * Genarate store product catalog file
     * @param  [type]     $websiteId
     * @param  [type]     $website
     * @param  [type]     $store
     * @param  [type]     $currency_code
     * @param  [type]     $catalog_path
     * @return [type]
     * @author edudeleon
     * @date   2017-06-01
     */
    private function _generateStoreCsvFile($websiteId, $website, $store, $currency_code, $catalog_path) {
        $current_date = date('Y-m-d');

        //Define product catalog filename
        $filename = 'products_'.$website->getCode().'_'.$store->getCode().'.csv';

        //Getting extra attributes
        $extra_attributes = Mage::helper('remmote_facebookproductcatalog')->getExtraAttributes($websiteId);

        //Get category collection
        $categoriesCollection = Mage::getModel('catalog/category')
        ->getCollection()
        ->addAttributeToSelect(array('entity_id', 'remmote_google_taxonomy'))
        ->addIsActiveFilter();

        $categoriesArray = array();
        foreach ($categoriesCollection  as $category) {
            $categoriesArray[$category->getEntityId()] = $category->getRemmoteGoogleTaxonomy();
        }

        //Get product collection
        $products = Mage::getModel("catalog/product")->getCollection();
        $products->setStoreId($store->getId());

        if(!empty($websiteId)){
            $products->addWebsiteFilter(array($websiteId));
        }

        //Getting configurable products information
        $configurableProductsCollection = Mage::getModel("catalog/product")->getCollection();
        $configurableProductsCollection->setStoreId($store->getId());

        if ($websiteId) {
            $configurableProductsCollection->addWebsiteFilter(array($websiteId));
        }
        $configurableProductsCollection->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED));
        $configurableProductsCollection->addAttributeToFilter('type_id', array('eq' => 'configurable'));
        $configurableProductsCollection->addAttributeToSelect('*');
        $childProducts = array();
        foreach ($configurableProductsCollection as $configurableProduct) {
            $_children          = $configurableProduct->getTypeInstance()->getUsedProducts($configurableProduct);
            foreach ($_children as $child){
                $childProducts[$child->getId()] = array(
                    'parentId'  => $configurableProduct->getId(),
                    'parentUrl' => Mage::app()->getStore($store->getId())->getBaseUrl().$configurableProduct->getUrlPath(),
                );
            }
        }

        //Including extra attributes
        $attributes_to_select = array('sku', 'type_id', 'name', 'short_description', 'description', 'facebook_product_description', 'image', 'url_path', 'status', 'price', 'price_type', 'special_price', 'special_from_date', 'special_to_date', 'final_price', 'tax_class_id', 'brand', 'manufacturer', 'color');
        if($extra_attributes) {
            $attributes_to_select = array_merge($attributes_to_select, $extra_attributes);
        }

        $products->addAttributeToSelect($attributes_to_select);
        $products->joinTable('cataloginventory/stock_item','product_id=entity_id', array('qty', 'is_in_stock'));
        $products->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED));
        
        //Filter by visibility
        if(Mage::helper('remmote_facebookproductcatalog')->exportProductsNotVisibleIndividually($websiteId)) {
            $products->addAttributeToFilter('visibility', array('in' => array(1, 4))); //Not visible individually and Catalog, Search
        } else {
            $products->addAttributeToFilter('visibility', 4); // Visible in Catalog, Search
        }

        //Check if apply 'Use for Facebook Product Catalog' filter.
        if(!Mage::helper('remmote_facebookproductcatalog')->exportAll($websiteId)){
            $products->addAttributeToFilter('facebook_product_catalog', array('eq' => 1));
        }

        // Prepare csv file with field names
        $fopen      = fopen($catalog_path. DS . $filename, 'w');
        $csvHeader  = array("id", "title", "google_product_category", "description", "link", "image_link", "condition", "availability", "price", "sale_price", "brand", "color"); 
        if($extra_attributes){
            $csvHeader = array_merge($csvHeader, $extra_attributes);
        }
        
        fputcsv($fopen, $csvHeader, ",");

        foreach ($products as $product){
            //Skip products with no price
            if(!$product->getPrice()){ //grouped products
                continue;
            }

            //Preparing price and special price
            $include_tax = Mage::helper('remmote_facebookproductcatalog')->includeTax($websiteId);
            if($include_tax == true){
                $_price         = Mage::helper('tax')->getPrice($product, $product->getPrice(), true);
                $_special_price = $product->getPrice();
                if($product->getSpecialPrice()){
                    //Check special_date_from and special_to_date
                    if(($current_date >= $product->getSpecialFromDate()  && $current_date <= $product->getSpecialToDate()) || ($current_date >= $product->getSpecialFromDate() && !$product->getSpecialToDate())){
                        $_special_price = $product->getSpecialPrice();
                    }
                }

                if($product->getTypeId() == 'bundle') {
                    if($product->getSpecialPrice()) {
                        //Check special_date_from and special_to_date
                        if(($current_date >= $product->getSpecialFromDate()  && $current_date <= $product->getSpecialToDate()) || ($current_date >= $product->getSpecialFromDate() && !$product->getSpecialToDate())){
                            $_special_price = $product->getPrice() * ($product->getSpecialPrice()/100);
                        }
                    }
                }

                $special_price  = Mage::helper('tax')->getPrice($product, $_special_price, true);
            } else {
                $_price         = $product->getPrice();
                $special_price  = $product->getPrice();

                if($product->getSpecialPrice()){
                    //Check special_date_from and special_to_date
                    if(($current_date >= $product->getSpecialFromDate()  && $current_date <= $product->getSpecialToDate()) || ($current_date >= $product->getSpecialFromDate() && !$product->getSpecialToDate())){
                        $special_price = $product->getSpecialPrice();
                    }
                }


                if($product->getTypeId() == 'bundle') {
                    if($product->getSpecialPrice()) {
                        //Check special_date_from and special_to_date
                        if(($current_date >= $product->getSpecialFromDate()  && $current_date <= $product->getSpecialToDate()) || ($current_date >= $product->getSpecialFromDate() && !$product->getSpecialToDate())){
                            $special_price = $_price * ($special_price/100);
                        }
                    }
                }
            }

            $id     =   $product->getSku();

            //Checking if export Product ID instead of Product SKU
            if(Mage::helper('remmote_facebookproductcatalog')->useProductId($websiteId)){
                $id     =   $product->getId();
            }

            $title          = $product->getName();

            if(Mage::helper('remmote_facebookproductcatalog')->useProductDescription($websiteId)) {
                $_description  = $product->getDescription();
            } else {
                $_description  = $product->getShortDescription();
            }

            //Getting product description and remmoving HTML tags
            $description    = $product->getFacebookProductDescription() ? $product->getFacebookProductDescription() : $_description;
            $description    = strip_tags(preg_replace("/(\r\n)+|\r+|\n+|\t+|&nbsp;/", ' ', $description));
            // $description    = strip_tags(preg_replace("/\s+|\n+|\r/", ' ', $description));

            //Getting product URL
            $productUrl = Mage::app()->getStore($store->getId())->getBaseUrl().$product->getUrlPath();

            //If product is not visile individually (child product), use parent product URL
            if($product->getVisibility() == Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE) {
                if(isset($childProducts[$product->getId()])) {
                    $productUrl = $childProducts[$product->getId()]['parentUrl'];
                }
            }

            $condition      = 'new';
            $availability   = $product->getIsInStock() ? 'in stock' : 'out of stock';
            $price          = number_format($_price, 2, '.', '') . " " . $currency_code;
            $sale_price     = number_format($special_price, 2, '.', '') . " " . $currency_code;

            //brand
            $brand          = $product->getBrand();
            if($brand){
                if(is_numeric($brand)){
                    $brand = $product->getAttributeText('brand');
                }
            } else {
                $brand  = $product->getManufacturer();
                 if(is_numeric($brand)){
                    $brand = $product->getAttributeText('manufacturer');
                }
            }
            if(!$brand){
                $brand = "Undefined";
            }

            //color
            $color          = $product->getColor();
            if($color){
                if(is_numeric($color)){
                    $color = $product->getAttributeText('color');
                }
            }

            //Loading product categories
            $productCategories      = $product->getCategoryIds();

            // Load image attribute
            $product->load('image');
            $image_link = Mage::app()->getStore($store->getId())->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'frontend/base/default/images/catalog/product/placeholder/image.jpg';
            if(!($product->getImage() == "" || $product->getImage() == "no_selection")){
                $image_link = Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getImage()); 
            }

            //Get product category
            $googleProductCategory = '';
            if (count($productCategories) >= 1) {

                //Check category assignation type
                $categoryAssignation = Mage::helper('remmote_facebookproductcatalog')->getCategoryAssignation($websiteId);
                if($categoryAssignation == "parent" || !$categoryAssignation){
                    $categoryId            = !empty($productCategories[0]) ? $productCategories[0] : reset($productCategories);
                } else {
                    $categoryId            = end($productCategories);
                }

                $googleProductCategory = !empty($categoriesArray[$categoryId]) ? $categoriesArray[$categoryId] : '';
            }

            $product_attributes = array($id, $title, $googleProductCategory, $description, $productUrl, $image_link, $condition, $availability, $price, $sale_price, $brand, $color);
            if($extra_attributes) {
                foreach($extra_attributes as $attribute){
                    $product_attributes[] = $product->getData($attribute);
                }
            }
            fputcsv($fopen, $product_attributes, ",");
        }
        fclose($fopen);
    }

}