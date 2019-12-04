<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);

$helper = Mage::helper('catalog/category');

$categories = $helper->getStoreCategories();

ob_start();

function  addLogStream($message) {
    echo $message."<br/>\n";
    flush();
    ob_end_flush();
}

function parseCategory($category, $parents = array()) {
    addLogStream("Processing Category:: {$category->getName()} [{$category->getId()}]");
    
    if ($category->getChildrenCount()) {
        $parents[] = $category->getId();
        $childrens = $category->getChildren();
        foreach ($childrens as $child) {
            parseCategory($child, $parents);
        }
    }
    
    if (count($parents)) {
        $category = Mage::getModel('catalog/category')->load($category->getId());
        $products = Mage::getResourceModel('catalog/product_collection')
        ->setStoreId(Mage::app()->getStore()->getId())
        ->addCategoryFilter($category);
        
        foreach ($products as $product) {
            
            addLogStream("Processing Product:: {$product->getSku()} [{$product->getId()}]");
            
            $associatedCategories = $product->getCategoryIds();
            
            $needUpdate = false;
            
            foreach ($parents as $parent) {
                if (!in_array($parent, $associatedCategories)) {
                    $needUpdate = true;
                    break;
                }
            }
            
            if ($needUpdate) {
                //$product = Mage::getModel('catalog/product')->load($product->getId());
                addLogStream("Old Set:: ".json_encode($associatedCategories));
                
                $associatedCategories = array_unique(array_merge($associatedCategories, $parents));
                
                addLogStream("New Set:: ".json_encode($associatedCategories));
            
                $product->setCategoryIds($associatedCategories);
                $product->save();
            }
            
            $product = null;
        }
        
        $category = null;
    }
}

foreach ($categories as $category) {
    parseCategory($category);
}

die;