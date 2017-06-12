<?php

class NowInStore_CatalogBuilder_ProductsController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $page = $_GET['page'];
        if (empty($page)) {
            $page = 1;
        }
        $product_collection = Mage::getModel('catalog/product')
            ->getCollection()
//                            ->addAttributeToFilter('is_active', 1)
            ->addExpressionAttributeToSelect('lower_name', 'LOWER({{name}})', array('name'))
            ->addFieldToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
            ->setPageSize(50)
            ->setCurPage($page)
            ->addAttributeToSelect(array('id', 'name', 'sku', 'price', 'group_price', 'image', 'description'));

        $keywords = $_GET['keywords'];
        if (!empty ($keywords)) {
            $product_collection = $product_collection->addAttributeToFilter('lower_name', array('like' => '%' . strtolower($keywords) . '%'));
        }

        $category_id = $_GET['category_id'];
        if (!empty ($category_id)) {
            $product_collection = $product_collection
                ->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id=entity_id', null, 'left')
                ->addAttributeToFilter('category_id', array('in' => $category_id));
        }
        $products = array();
        $currency = Mage::app()->getStore()->getCurrentCurrencyCode();
        $group_collection = Mage::getModel('customer/group')->getCollection();
        $wholesaleGroup = null;
        foreach ($group_collection as $group) {
            if ($group->getCode() === 'Wholesale') {
                $wholesaleGroup = $group;
            }
        }
        foreach ($product_collection as $product) {
            if ($product->isConfigurable()) {
                $productAttributeOptions = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);
            }
            $attributeOptions = array();
            foreach ($productAttributeOptions as $productAttribute) {
                foreach ($productAttribute['values'] as $attribute) {
                    $label = $productAttribute['label'];
                    $valueIndex = $attribute['value_index'];
                    $attributeOptions[$label][$valueIndex] = $attribute['store_label'];
                }
            }
            $price = floatval($product->getPrice());
            $wholesalePrice = 0;
            $product->setCustomerGroupId($wholesaleGroup->getId());
            $groupPrices = $product->getGroupPrice();
            if (is_null($groupPrices)) {
                $attribute = $product->getResource()->getAttribute('group_price');
                if ($attribute) {
                    $attribute->getBackend()->afterLoad($product);
                    $groupPrices = $product->getData('group_price');
                }
            }
            if (!is_null($groupPrices) || is_array($groupPrices)) {
                $wholesalePrice = $groupPrices;
            }
            array_push($products, array(
                "id" => $product->getId(),
                "title" => $product->getName(),
                "sku" => $product->getSku(),
                "price" => $price,
                "wholesale_price" => floatval($wholesalePrice),
                "main_image" => $product->getImageUrl(),
                "description" => $product->getDescription(),
                "thumbnail_image" => (string)Mage::helper('catalog/image')->init($product, 'image')->resize(75),
                "iso_currency_code" => $currency,
                "g" => $wholesaleGroup->getId(),
                "url" => $product->getProductUrl(),
                "variations" => $attributeOptions
            ));
        }
        $jsonData = json_encode($products);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }

    public function countAction()
    {
        $product_collection = Mage::getModel('catalog/product')
            ->getCollection()
//                            ->addAttributeToFilter('is_active', 1)
            ->addExpressionAttributeToSelect('lower_name', 'LOWER({{name}})', array('name'))
            ->addFieldToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
            ->addAttributeToSelect(array('id', 'name', 'sku', 'price', 'image'));

        $query = $_GET['query'];
        if (!empty ($query)) {
            $product_collection = $product_collection->addAttributeToFilter('lower_name', array('like' => '%' . strtolower($query) . '%'));
        }

        $category_id = $_GET['category_id'];
        if (!empty ($category_id)) {
            $product_collection = $product_collection
                ->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id=entity_id', null, 'left')
                ->addAttributeToFilter('category_id', array('in' => $category_id));
        }
        $jsonData = json_encode(array("count" => $product_collection->count()));
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }
}
