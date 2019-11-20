<?php

require_once ('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
//echo "<pre>";



$product_list_with_category = 'product_list_with_category.csv';//$_GET['product_list'];
$categories_data = 'categories.csv';//$_GET['category_data'];


$cu=new CategoryUpdate();


// create mapped array of products with Category ID's
$productArray = $cu->getMappedArrayOfProductWithCategories($product_list_with_category);
$categories_data = "";//$cu->getMappedArrayOfCategoriesData($categories_data);
$cu->assignProductCategories($productArray,$categories_data);


class CategoryUpdate
{
var $updatedCategories=array();


    function getMappedArrayOfProductWithCategories($product_list_with_category)
    {
        try {
            $productArray = array();
            $lines = file($product_list_with_category);
            $headerWithCategoryIds = null;

            foreach ($lines as $lineNumber => $line) {
                if ($lineNumber === 0) {
                    $headerWithCategoryIds = explode(",", $line);
                } else {
                    $lineArray = explode(",", $line);
                    $sku = $lineArray[0];
                    foreach ($lineArray as $index => $lineItem) {
                        if (trim(strtolower($lineItem)) == "p") {
                            $productArray[$sku][] = $headerWithCategoryIds[$index];
                        }
                    }
                }
            }

            return $productArray;

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }


// create mapped array of redesign categories with their data
    function getMappedArrayOfCategoriesData($categories_data)
    {
        $redesignCategoryData = array();
        try {
            $lines = file($categories_data);
            $columns = null;

            foreach ($lines as $lineNumber => $line) {
                if ($lineNumber === 0) {
                    $columns = explode(",", $line);
                } else {
                    $lineArray = explode(",", $line);
                    $entity_id = trim($lineArray[0], '"');
                    foreach ($lineArray as $index => $lineItem) {
                        $redesignCategoryData[$entity_id][trim($columns[$index], '"')] = trim($lineItem, '"');
                    }
                }
            }
            return $redesignCategoryData;

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }


// assign product to categories and create and assign to categories if category doesn't exist
    function assignProductCategories($productArray, $redesignCategoryData)
    {

        $resource = Mage::getSingleton('core/resource');
        $writeAdapter = $resource->getConnection('core_write');
        $writeAdapter->beginTransaction();

        $recordIndex=0;

        foreach ($productArray as $sku => $categoryIds) {
            try {
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
                $this->log($sku.":");
                $this->log($categoryIds);

                if ($product) {

                    foreach ($categoryIds as $categoryId) {
                        $categoryId = trim($categoryId);

                            $category = Mage::getModel('catalog/category')->load($categoryId);
                            if ($category->getId()) {

                                if(!in_array($categoryId,$this->updatedCategories)) {
                                    $query1 = "DELETE FROM `catalog_category_product` WHERE `category_id`=" . $categoryId;
                                    $writeAdapter->query($query1);
                                    $this->log("unset all products for category ".$categoryId);
                                    array_push($this->updatedCategories, $category->getId());
                                }

                                $query2="INSERT INTO `catalog_category_product`(`category_id`, `product_id`) VALUES (".$categoryId.",".$product->getId().")";
                                $writeAdapter->query($query2);
                                $this->log("product assigned category:".$categoryId."  product:".$sku);
                            }
                            else
                            {
                                $this->log("category not found ".$categoryId." for product ".$sku);
                            }
                    }

                } else {
                    $this->log("product not found  ".$sku);

                }
                $this->log("row--------------------------------------------------- ------------#".$recordIndex);

                $recordIndex++;
                if (($recordIndex % 100) == 0) {
                    $writeAdapter->commit();
                    $writeAdapter->beginTransaction();

                }

            } catch (Exception $e) {
                $writeAdapter->rollback();
            }




        }

        $writeAdapter->commit();
    }
    function saveCategory($data)
    {
        try {
            $category = Mage::getModel('catalog/category')->addData($data)->save();
            array_push($this->updatedCategories, $category->getId());
            return $category;
        } catch (Exception $e) {
            $this->log($e->getMessage());
        }
    }
    function log($message)
    {
        Mage::log($message,Zend_Log::DEBUG,'adi.log',true);
    }
}
