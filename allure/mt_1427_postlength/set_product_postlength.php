<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);


Mage::log('================ START ====================',Zend_Log::DEBUG,'setPostlength.log',true);

$fp = fopen("found_list.txt","a");
fwrite($fp,'----------- NEW START ------------'.PHP_EOL);
fclose($fp);
$fp = fopen("not_found_list.txt","a");
fwrite($fp,'----------- NEW START ------------'.PHP_EOL);
fclose($fp);
$fp = fopen("updated_list.txt","a");
fwrite($fp,'----------- NEW START ------------'.PHP_EOL);
fclose($fp);


$attribute = Mage::getSingleton('eav/config')
    ->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'default_postlength');

if ($attribute->usesSource()) {
    $post_length_options = $attribute->getSource()->getAllOptions(false);
}

$options = array();
foreach ($post_length_options as $option){
    $options[$option['label']] = $option['value'];
}
/** for is default **/
/*
10 5MM
13 6.5MM
16 8MM
19 9.5MM
*/
/** for values **/
/*
 0 teamwork_plu
11 5MM
14 6.5MM
17 8MM
20 9.5MM
*/
$fileName="./Threaded_Studs_Online_Review_Final3.csv";
$lines = file($fileName);
$founArray = $notFoundArray = array();
$totalFound = $totalUpdated = $totalNotFound = 0;
foreach ($lines as $lineNumber => $line) {
    $fiveMM = $sixPointFiveMM = $eightMM = $ninePointFiveMM = $plu = $default_postLength = '';
    if($lineNumber > 0){
        $data = explode(',', $line);
        /*Data*/
        $plu = isset($data[0])?$data[0]:'';

        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('teamwork_plu', $plu)
            ->addAttributeToFilter('type_id', 'simple');
        $foundCount = $collection->getSize();

        Mage::log('PLU '.$plu.' Found Count'.$foundCount,Zend_Log::DEBUG,'setPostlength.log',true);
        if($foundCount > 0){
            $totalFound++;
            $fp = fopen("found_list.txt","a");
            fwrite($fp,$plu.','.$foundCount.PHP_EOL);
            fclose($fp);
        }else{
            $fp = fopen("not_found_list.txt","w+");
            fwrite($fp,$plu.PHP_EOL);
            fclose($fp);
        }
        $productId = '';
        if($foundCount == 1) {
            $product = $collection->getFirstItem();
            $productId = $product->getId();
            Mage::log('ID :'.$productId,Zend_Log::DEBUG,'setPostlength.log',true);
        }

        if (!empty($productId)) {
            $fiveMM = isset($data[12])?$data[12]:'';
            $sixPointFiveMM = isset($data[15])?$data[15]:'';
            $eightMM = isset($data[18])?$data[18]:'';
            $ninePointFiveMM = isset($data[21])?$data[21]:'';
            /*Default Postlenth*/
            if ($data[11] == 'Yes') {
                $default_postLength = '5MM';
            } elseif ($data[14] == "Yes") {
                $default_postLength = '6.5MM';
            } elseif ($data[17] == "Yes") {
                $default_postLength = '8MM';
            } elseif ($data[20] == "Yes") {
                $default_postLength = '9.5MM';
            }

            $load_product = Mage::getModel('catalog/product')->load($productId);
            Mage::log('TYPE :' . $load_product->getTypeId(), Zend_Log::DEBUG, 'setPostlength.log', true);

            $founArray[] = array($plu, $load_product->getTypeId());
            $load_product->setFiveMmSku($fiveMM);
            $load_product->setSixPointFiveMmSku($sixPointFiveMM);
            $load_product->setEightMmSku($eightMM);
            $load_product->setNinePointFiveMmSku($ninePointFiveMM);

            if ($default_postLength != '') {
                if (isset($options[$default_postLength])) {
                    $load_product->setDefaultPostlength($options[$default_postLength]);
                }
            }

            try {
                Mage::log('Saving', Zend_Log::DEBUG, 'setPostlength.log', true);
                $totalUpdated++;
                /*$load_product->save();*/
                $fp = fopen("updated_list.txt","a");
                fwrite($fp,$plu.','.$productId.PHP_EOL);
                fclose($fp);
                Mage::log('Saved', Zend_Log::DEBUG, 'setPostlength.log', true);
            } catch (Exception $ex) {
                Mage::log('Exception while saving :' . $ex->getMessage(), Zend_Log::DEBUG, 'setPostlength.log', true);
            }



        }
    }
}

Mage::log('================ DONE ====================',Zend_Log::DEBUG,'setPostlength.log',true);
/*Mage::log('Found Data'.PHP_EOL.json_decode($founArray,true),Zend_Log::DEBUG,'setPostlength.log',true);
Mage::log('Not Found Data'.PHP_EOL.json_decode($notFoundArray,true),Zend_Log::DEBUG,'setPostlength.log',true);*/
echo 'Total Found :'.$totalFound.PHP_EOL;
echo 'Total Not Found :'.$totalNotFound.PHP_EOL;
echo 'Total Updated :'.$totalUpdated.PHP_EOL;