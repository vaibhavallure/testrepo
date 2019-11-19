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
$categories_data = $cu->getMappedArrayOfCategoriesData($categories_data);
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
        $fp = fopen('result.txt', 'a');
        $result = "";

        try {
            foreach ($productArray as $sku => $categoryIds) {
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);

                if ($product->getId()) {
                    $categories = array();
                    $product->setCategoryIds($categories);
                    $product->save();

                    foreach ($categoryIds as $categoryId) {
                        $categoryId = trim($categoryId);


                        if (!in_array($categoryId,$this->updatedCategories)) {
                            $category = Mage::getModel('catalog/category')->load($categoryId);
                            if ($category->getId()) {
                                // update and assign category
                                $category = $this->saveCategory($redesignCategoryData[$categoryId], $fp, "UPDATE");
                                fwrite($fp, "Category assigned with Id {$category->getId()} and Name {$category->getName()} to Product {$sku}" . PHP_EOL);
                                $categoryId = $category->getId();
                            } else {
                                // add category
                                $category = $this->saveCategory($redesignCategoryData[$categoryId], $fp, "ADD");
                                $categoryId = $category->getId();
                                fwrite($fp, "Category assigned with Id {$category->getId()} and Name {$category->getName()} to Product {$sku}" . PHP_EOL);
                            }
                        }else{
                            fwrite($fp, "Category Up to Date assigned with Id {$categoryId}  to Product {$sku}" . PHP_EOL);
                        }

                        Mage::getSingleton('catalog/category_api')->assignProduct($categoryId, $product->getId());

                    }
                } else {
                    fwrite($fp, "Product not found for {$sku}" . PHP_EOL);
                }
            }
            fclose($fp);
            echo $result;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    function saveCategory($data, $fp, $status)
    {
        try {
            $category = Mage::getModel('catalog/category')->addData($data)->save();
            array_push($this->updatedCategories, $category->getId());
            fwrite($fp, "{$status} Category with Id {$category->getId()} and Name {$category->getName()}" . PHP_EOL);
            return $category;
        } catch (Exception $e) {
            fwrite($fp, "{$e} Category with Id {$category->getId()} and Name {$category->getName()}" . PHP_EOL);
        }
    }
}
