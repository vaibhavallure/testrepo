<?php
/**
 * Created by PhpStorm.
 * User: Indrajeet
 * Date: 11/7/19
 * Time: 6:08 PM
 */
require_once('../../app/Mage.php');
umask(0);
Mage::app();
echo "<pre>";


$model = Mage::getModel('ecp_reporttoemail/observer');
$model->integrationEmail("2018-11-06",null,"manual",null);
die;

try {
    $productCollectionSF = Mage::getModel('catalog/product')->getCollection()
        ->addFieldToFilter('sku',array("in" => array("EBAR7M|ROSE GOLD","EBAR7M|WHITE GOLD","EBAR7M|YELLOW GOLD","ZFLP8OP|BLACK GOLD|14 GA THREAD|BACK","ZFLP8OP|BLACK GOLD|14 GA THREAD|SIDE","ZFLP8OP|BLACK GOLD|16 GA PUSHIN|BACK","ZFLP8OP|BLACK GOLD|16 GA PUSHIN|SIDE","ZFLP8OP|BLACK GOLD|18 GA PUSHIN|BACK","ZFLP8OP|BLACK GOLD|18 GA PUSHIN|SIDE","ZFLP8OP|BLACK GOLD|18G-16GA THREAD|BACK","ZFLP8OP|BLACK GOLD|18G-16GA THREAD|SIDE","ZFLP8OP|BLACK GOLD|19 GA TASH|BACK","ZFLP8OP|BLACK GOLD|19 GA TASH|SIDE","ZFLP8OP|ROSE GOLD|14 GA THREAD|BACK","ZFLP8OP|ROSE GOLD|14 GA THREAD|SIDE","ZFLP8OP|ROSE GOLD|16 GA PUSHIN|BACK","ZFLP8OP|ROSE GOLD|16 GA PUSHIN|SIDE","ZFLP8OP|ROSE GOLD|18 GA PUSHIN|BACK","ZFLP8OP|ROSE GOLD|18 GA PUSHIN|SIDE","ZFLP8OP|ROSE GOLD|18G-16GA THREAD|BACK","ZFLP8OP|ROSE GOLD|18G-16GA THREAD|SIDE","ZFLP8OP|ROSE GOLD|19 GA TASH|BACK","ZFLP8OP|ROSE GOLD|19 GA TASH|SIDE","ZFLP8OP|WHITE GOLD|14 GA THREAD|BACK","ZFLP8OP|WHITE GOLD|14 GA THREAD|SIDE","ZFLP8OP|WHITE GOLD|16 GA PUSHIN|BACK","ZFLP8OP|WHITE GOLD|16 GA PUSHIN|SIDE","ZFLP8OP|WHITE GOLD|18 GA PUSHIN|BACK","ZFLP8OP|WHITE GOLD|18 GA PUSHIN|SIDE","ZFLP8OP|WHITE GOLD|18G-16GA THREAD|BACK","ZFLP8OP|WHITE GOLD|18G-16GA THREAD|SIDE","ZFLP8OP|WHITE GOLD|19 GA TASH|BACK","ZFLP8OP|WHITE GOLD|19 GA TASH|SIDE","ZFLP8OP|YELLOW GOLD|14 GA THREAD|BACK","ZFLP8OP|YELLOW GOLD|14 GA THREAD|SIDE","ZFLP8OP|YELLOW GOLD|16 GA PUSHIN|BACK","ZFLP8OP|YELLOW GOLD|16 GA PUSHIN|SIDE","ZFLP8OP|YELLOW GOLD|18 GA PUSHIN|BACK","ZFLP8OP|YELLOW GOLD|18 GA PUSHIN|SIDE","ZFLP8OP|YELLOW GOLD|18G-16GA THREAD|BACK","ZFLP8OP|YELLOW GOLD|18G-16GA THREAD|SIDE","ZFLP8OP|YELLOW GOLD|19 GA TASH|BACK","ZFLP8OP|YELLOW GOLD|19 GA TASH|SIDE")));
        //->addFieldToFilter('sku',array("in" => array("EBAR7M|ROSE GOLD")));
    $skuAttributeMappings = array();
    foreach ($productCollectionSF as $productOb) {
        $product = Mage::getModel('catalog/product')->load($productOb->getId());
        if($product->getSalesforceProductId()){
            $skuAttributeMappings[$product->getSku()] = array(
                'salesforce_product_id' => $product->getSalesforceProductId(),
                'salesforce_standard_pricebk' => $product->getSalesforceStandardPricebk(),
                'salesforce_wholesale_pricebk' => $product->getSalesforceWholesalePricebk()
            );
        }
    }

    //print_r($skuAttributeMappings);die;

    foreach($productCollectionSF as $productOb) {
        $product = Mage::getModel('catalog/product')->load($productOb->getId());
        $sku = $product->getSku();
        $sfProductId = $product->getSalesforceProductId();

        if(!$sfProductId) {
            $mainStoreId = 1;
            $fields = array(
                'salesforce_product_id' => $skuAttributeMappings[$sku]['salesforce_product_id'],
                'salesforce_standard_pricebk' => $skuAttributeMappings[$sku]['salesforce_standard_pricebk'],
                'salesforce_wholesale_pricebk' => $skuAttributeMappings[$sku]['salesforce_wholesale_pricebk']
            );
            Mage::getResourceSingleton('catalog/product_action')
                ->updateAttributes(array($product->getId()), $fields, $mainStoreId);
        }

    }
}catch (Exception $e) {
    echo $e->getMessage();
}


