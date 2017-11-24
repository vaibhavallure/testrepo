<?php 
require_once('../../app/Mage.php');
umask(0);
Mage::app();
$app = Mage::app();
Mage::app()->setCurrentStore(0);

echo "<pre>";

$fixedItems = array("ZSTH","XWB15BKD","XWB10D","XWB10BKD","XTHMQD","XTHD4","XTHD2","XTHBF6","XTHBF2D","XTHBF25D","XTHBF","XTHBAD","XSNPA10RB","XSNPA10DRB","XSNPA10D","XSNEG5RB","XSNEG5D","XSNEG5BKD","XSNEG10RB","XSNEG10D","XSNEG10BKD","XSN65BKD","XSN5RB","XSN5D","XSN5BKD","XSN10RB","XSN10D","XSN10BKD","XG5OP","XDS5DOP","XDS5D","XDGTR65RB","XDGTR65OP","XDGTR65D","XDGTR65BKD","XDG65D","XDG65BKD","XDG5D","ESN10RB","CWB10D_T","CWB10D_R","CWB10D_E","CWB10D_C","CWB10D_B","CWB10D","CWB10BKD_T","CWB10BKD_R","CWB10BKD_E","CWB10BKD_C","CWB10BKD_B","CWB10BKD","CTHBF_T","CTHBF_R","CTHBF_E","CTHBF_C","CTHBF_B","CTHBF","CSNPA10DRB_T","CSNPA10DRB_R","CSNPA10DRB_E","CSNPA10DRB_C","CSNPA10DRB_B","CSNPA10DRB","CSNPA10D_T","CSNPA10D_R","CSNPA10D_E","CSNPA10D_C","CSNPA10D_B","CSNPA10D","CSNEG5RB_T","CSNEG5RB_R","CSNEG5RB_E","CSNEG5RB_C","CSNEG5RB_B","CSNEG5RB","CSNEG5D_T","CSNEG5D_R","CSNEG5D_E","CSNEG5D_C","CSNEG5D_B","CSNEG5D","CSNEG10D_T","CSNEG10D_R","CSNEG10D_E","CSNEG10D_C","CSNEG10D_B","CSNEG10D","CSNEG10BKD_T","CSNEG10BKD_R","CSNEG10BKD_E","CSNEG10BKD_C","CSNEG10BKD_B","CSNEG10BKD","CSN5RB_T","CSN5RB_R","CSN5RB_E","CSN5RB_C","CSN5RB_B","CSN5RB","CSN5BKD_T","CSN5BKD_R","CSN5BKD_E","CSN5BKD_C","CSN5BKD_B","CSN5BKD","CSN10RB_T","CSN10RB_R","CSN10RB_E","CSN10RB_B","CSN10RB","CSN10D_T","CSN10D_R","CSN10D_E","CSN10D_C","CSN10D_B","CSN10D","CSN10BKD_T","CSN10BKD_R","CSN10BKD_E","CSN10BKD_C","CSN10BKD_B","CSN10BKD","CG5OP","CDS5DOP_R","CDS5DOP_C","CDS5DOP_B","CDS5DOP","CDGTR65RB_T","CDGTR65RB_R","CDGTR65RB_E","CDGTR65RB_C","CDGTR65RB_B","CDGTR65RB","CDGTR65OP_E","CDGTR65OP_C","CDGTR65OP_B","CDGTR65OP","CDGTR65D_T","CDGTR65D_R","CDGTR65D_E","CDGTR65D_C","CDGTR65D_B","CDGTR65D","CDGTR65BKD_R","CDGTR65BKD_C","CDGTR65BKD_B","CDGTR65BKD","CDG65D_T","CDG65D_R","CDG65D_C","CDG65D_B","CDG65D","CDG65BKD_E","CDG65BKD_C","CDG65BKD_B","CDG65BKD","CDG5D_B","CDG5D","CSN5D_T","CSN5D_R","CSN5D_E","CSN5D_C","CSN5D_B","CSN5D","XWB15D");

$reversedItems = array("XTHD4","XTHD2","XTHBF6","XTHBF2D","XTHBF25D","XTHBF","XTHBF","XTHBAD");

$skuByProductId = array();

$customPostLengthOptions = array();

$postLenths = array();

$skuByProductIdFile = Mage::getBaseDir('var').'/export/postLength_SkuByProductId3D.json';
$customPostLengthOptionsFile = Mage::getBaseDir('var').'/export/postLength_CustomPostLengthOptions3D.json';
$postLengthsFile = Mage::getBaseDir('var').'/export/postLengths3D.json';

$firstTime = true;

if (!file_exists($skuByProductIdFile)) {
	$firstTime = true;
}

if ($firstTime) {
    
    foreach ($fixedItems as $fixedSku) {
        Mage::log('Parsing Fixed SKU:: '.$fixedSku, Zend_Log::DEBUG, 'post_length_migrations_parsing.log', true);
        var_dump("Fixed SKU: ".$fixedSku);
        $productCollection = Mage::getModel('catalog/product')->getCollection()
        ->addAttributeToFilter('type_id', 'simple')
        ->addAttributeToFilter('sku', array ('like'=> $fixedSku.'|%'))
        ->load();
        
        foreach ($productCollection  as $product) {
            
            $oldItem = $product->getSku();
            
            Mage::log('Old SKU :: '.$oldItem, Zend_Log::DEBUG, 'post_length_migrations_parsing.log', true);
            var_dump("Old SKU: ".$oldItem);
            
            $oldItemSku = explode('|', $oldItem);
            
            if (count($oldItemSku) ==  4) {
                
                $parentItem = $oldItemSku[0];
                
                if (in_array($fixedSku, $reversedItems)) {
                    
                    $post_length = $oldItemSku[3];
                    
                    $newItem = implode('|', array($parentItem, $oldItemSku[1], $oldItemSku[2]));
                    
                } else {
                    
                    $post_length = $oldItemSku[2];
                    
                    $newItem = implode('|', array($parentItem, $oldItemSku[1], $oldItemSku[3]));
                    
                }
                
                if (empty($post_length)) {
                    Mage::log('Post Length :: NONE', Zend_Log::DEBUG, 'post_length_migrations_parsing.log', true);
                    var_dump("Post Length: NONE");
                    continue;
                }
                
                Mage::log('New SKU :: '.$newItem, Zend_Log::DEBUG, 'post_length_migrations_parsing.log', true);
                Mage::log('Parent SKU :: '.$parentItem, Zend_Log::DEBUG, 'post_length_migrations_parsing.log', true);
                var_dump("New SKU: ".$newItem);
                var_dump("Parent SKU: ".$parentItem);
                
                Mage::log('Post Length :: '.$post_length, Zend_Log::DEBUG, 'post_length_migrations_parsing.log', true);
                var_dump("Post Length: ".$post_length);
                
                $oldItemId = Mage::getModel('catalog/product')->getIdBySku($oldItem);
                $newItemId = Mage::getModel('catalog/product')->getIdBySku($newItem);
                $parentItemId = Mage::getModel('catalog/product')->getIdBySku($parentItem);
                
                Mage::log('Original Id :: '.$oldItemId, Zend_Log::DEBUG, 'post_length_migrations_parsing.log', true);
                Mage::log('New Id :: '.$newItemId, Zend_Log::DEBUG, 'post_length_migrations_parsing.log', true);
                Mage::log('Parent Id :: '.$parentItemId, Zend_Log::DEBUG, 'post_length_migrations_parsing.log', true);
                
                switch (strtolower($post_length)) {
                    case '5mm':
                    case '5 mm':
                        $post_length = '5mm = 3/16"';
                        break;
                    case '6.5mm':
                    case '6.5 mm':
                        $post_length = '6.5mm = 1/4"';
                        break;
                    case '8mm':
                    case '8 mm':
                        $post_length = '8mm = 5/16"';
                        break;
                    case '9.5mm':
                    case '9.5 mm':
                        $post_length = '9.5mm = 3/8"';
                        break;
                    case '11mm':
                    case '11 mm':
                        $post_length = '11mm = 7/16"';
                        break;
                }
                
                $skuByProductId[$oldItemId] = $oldItem;
                $skuByProductId[$newItemId] = $newItem;
                $skuByProductId[$parentItemId] = $parentItem;
                
                if (!empty($newItemId) && (!isset($customPostLengthOptions[$newItemId]) || !isset($customPostLengthOptions[$newItemId][$post_length]))) {
                    
                    if (!isset($customPostLengthOptions[$parentItemId])) {
                        $customPostLengthOptions[$parentItemId] = array();
                    }
                    
                    Mage::log('NEW ITEM FOUND !!', Zend_Log::DEBUG, 'post_length_migrations_parsing.log', true);
                    
                    $postLengths[$newItem][] = $post_length;
                    
                    $sort_order = count($customPostLengthOptions[$newItemId]) + 1;
                    
                    $customPostLengthOptions[$newItemId][$post_length] = array(
                            'title'         => $post_length,
                            'price'         => 0,
                            'price_type'    => 'fixed',
                            'sku'           => null,
                            'is_delete'     => 0,
                            'sort_order'    => $sort_order
                    );
                    
                    $customPostLengthOptionsLog[$newItem][$post_length] = $post_length;
                }
                
                if (!empty($newItemId) && (!isset($customPostLengthOptions[$parentItemId]) || !isset($customPostLengthOptions[$parentItemId][$post_length]))) {
                    
                    if (!isset($customPostLengthOptions[$parentItemId])) {
                        $customPostLengthOptions[$parentItemId] = array();
                    }
                    
                    Mage::log('NEW ITEM FOUND !!', Zend_Log::DEBUG, 'post_length_migrations_parsing.log', true);
                    
                    $postLengths[$parentItem][] = $post_length;
                    
                    $sort_order = count($customPostLengthOptions[$parentItemId]) + 1;
                    
                    $customPostLengthOptions[$parentItemId][$post_length] = array(
                            'title'         => $post_length,
                            'price'         => 0,
                            'price_type'    => 'fixed',
                            'sku'           => null,
                            'is_delete'     => 0,
                            'sort_order'    => $sort_order
                    );
                    
                    $customPostLengthOptionsLog[$parentItem][$post_length] = $post_length;
                }
            }
            
            unset($product);
        }
        
        unset($productCollection);
    }
    
    
    file_put_contents($skuByProductIdFile, json_encode($skuByProductId));
    file_put_contents($customPostLengthOptionsFile, json_encode($customPostLengthOptions));
    file_put_contents($postLengthsFile, json_encode($postLengths));
} else {
    $skuByProductId = json_decode(file_get_contents($skuByProductIdFile), true);
    $customPostLengthOptions = json_decode(file_get_contents($customPostLengthOptionsFile), true);
    $postLengths = json_decode(file_get_contents($postLengthsFile), true);
}

//var_dump($skuByProductId);
//var_dump($customPostLengthOptions);
//var_dump($postLengths);

//die;

$post_length_custom_options_file = Mage::getBaseDir('var').'/export/postLength_customOptions3D.csv';
$post_length_custom_options = fopen($post_length_custom_options_file, 'w');

$skippedSkus = array();

foreach ($customPostLengthOptions as $product_id => $option_values) {
    
    $sku = $skuByProductId[$product_id];
    
    if (empty($sku) || in_array($sku, $skippedSkus)) {
        Mage::log('Skipping Custom Options for SKU:: '.$sku, Zend_Log::DEBUG, 'post_length_migrations_processing.log', true);
        var_dump('Skipping Custom Options for SKU:: '.$sku);
        continue;
    }
    
    Mage::log('Adding Custom Options for SKU:: '.$sku, Zend_Log::DEBUG, 'post_length_migrations_processing.log', true);
    var_dump('Adding Custom Options for SKU:: '.$sku);
    
    if (empty($sku)) {
        continue;
    }
    
    $option = array (
            'title'			  => 'Post Length',
            'type'			   => 'drop_down',
            'is_require'        => 1,
            'sort_order'        => 0,
            'is_delete'       => 0,
            'values'		      => $option_values
    );
    
    $post_length = implode('|', $postLengths[$sku]);
    
    Mage::log('Post Length:: '.$post_length, Zend_Log::DEBUG, 'post_length_migrations_processing.log', true);
    var_dump('Post Length:: '.$post_length);
    
    try {
        
        $_product = Mage::getModel("catalog/product")->load($product_id);
        
        //Mage::getSingleton('catalog/product_option')->unsetOptions();
        $_product->getOptionInstance()->unsetOptions();
        
        if ($_product->getOptions() != ''){
            foreach ($_product->getOptions() as $_option) {
                $_option->delete();
            }
            
            $_product->setHasOptions(0)->save();
        }
        
        $_product->setProductOptions(array($option));
        $_product->setCanSaveCustomOptions(true);
        
        $_product->save();
        
        unset($_product);
        
        fputcsv($post_length_custom_options, array(
                $sku,
                $post_length,
                'OK'
        ));
    } catch (Exception $e) {
        Mage::log('Failed Adding Custom Options for SKU:: '.$sku, Zend_Log::DEBUG, 'post_length_migrations_processing.log', true);
        var_dump('Failed Adding Custom Options for SKU:: '.$sku);
        
        fputcsv($post_length_custom_options, array(
                $sku,
                $post_length,
                'FAIL:'.$e->getMessage()
        ));
    }
}

fclose($post_length_custom_options);