<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);

$helper = Mage::helper('catalog/category');

$jwCategoryId="3";
$childCategoryid=540;
$update=false;

ob_start();

function  addLogStream($message) {
    echo $message."<br/>\n";
    flush();
    ob_end_flush();
}

function parseCategory($category_id, $jwCategoryId,$update) {

    $category = Mage::getModel('catalog/category')->load($category_id);
    addLogStream("Processing Category:: {$category->getName()} [{$category->getId()}]");

    $products = Mage::getResourceModel('catalog/product_collection')
        ->setStoreId(Mage::app()->getStore()->getId())
        ->addCategoryFilter($category);

    foreach ($products as $product) {

        addLogStream("Processing Product:: {$product->getSku()} [{$product->getId()}]");

        $associatedCategories = $product->getCategoryIds();

        $needUpdate = false;

        if (!in_array($jwCategoryId, $associatedCategories)) {
            $needUpdate = true;
        }

        if ($needUpdate) {
            $product = Mage::getModel('catalog/product')->load($product->getId());
            addLogStream("Old Set:: ".json_encode($associatedCategories));

            $associatedCategories = array_unique(array_merge($associatedCategories, array($jwCategoryId)));

            addLogStream("New Set:: ".json_encode($associatedCategories));

            if($update) {
                $product->setCategoryIds($associatedCategories);
                $product->save();
            }
        }

        $product = null;
    }

    $category = null;
}


parseCategory($childCategoryid,$jwCategoryId,$update);


die;