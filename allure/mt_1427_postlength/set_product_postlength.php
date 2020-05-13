<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);
Mage::log('================ START ====================',Zend_Log::DEBUG,'setPostlength.log',true);

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
$fileName="./Threaded_Studs_Online_Review_Final.csv";
$lines = file($fileName);
$founArray = $notFoundArray = array();
$totalFound = $totalUpdated = $totalNotFound = 0;
foreach ($lines as $lineNumber => $line) {
    $fiveMM = $sixPointFiveMM = $eightMM = $ninePointFiveMM = $plu = $default_postLength = '';
    if($lineNumber > 0){
        $data = explode(',', $line);
        /*Data*/
        $plu = isset($data[0])?$data[0]:'';
        Mage::log('------------------------------------------',Zend_Log::DEBUG,'setPostlength.log',true);
        Mage::log('PLU :'.$plu,Zend_Log::DEBUG,'setPostlength.log',true);
        $fiveMM = isset($data[11])?$data[11]:'';
        $sixPointFiveMM = isset($data[14])?$data[14]:'';
        $eightMM = isset($data[17])?$data[17]:'';
        $ninePointFiveMM = isset($data[20])?$data[20]:'';
        /*Default Postlenth*/
        if ($data[10] == 'Yes') {
            $default_postLength = '5MM';
        } elseif ($data[13] == "Yes") {
            $default_postLength = '6.5MM';
        } elseif ($data[16] == "Yes") {
            $default_postLength = '8MM';
        } elseif ($data[19] == "Yes") {
            $default_postLength = '9.5MM';
        }


        if ($plu != '') {
            $load_product = Mage::getModel('catalog/product')->loadByAttribute('teamwork_plu', $plu);
            if ($load_product) {
                $totalFound++;
                Mage::log('TYPE :'.$load_product->getTypeId(),Zend_Log::DEBUG,'setPostlength.log',true);
                if ($load_product->getTypeId() == 'simple') {
                    
                    $founArray[]=array($plu,$load_product->getTypeId());
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
                        Mage::log('Saving',Zend_Log::DEBUG,'setPostlength.log',true);
                        $totalUpdated++;
                        /*$load_product->save();*/
                        Mage::log('Saved',Zend_Log::DEBUG,'setPostlength.log',true);
                    } catch (Exception $ex) {
                        Mage::log('Exception while saving :'.$ex->getMessage(),Zend_Log::DEBUG,'setPostlength.log',true);
                    }
                }

            }
            else{
                $totalNotFound++;
                $notFoundArray[] = $plu;
            }

        }
    }
}

Mage::log('================ DONE ====================',Zend_Log::DEBUG,'setPostlength.log',true);
Mage::log('Found Data'.PHP_EOL.json_decode($founArray,true),Zend_Log::DEBUG,'setPostlength.log',true);
Mage::log('Not Found Data'.PHP_EOL.json_decode($notFoundArray,true),Zend_Log::DEBUG,'setPostlength.log',true);
echo 'Total Found :'.$totalFound.PHP_EOL;
echo 'Total Not Found :'.$totalNotFound.PHP_EOL;
echo 'Total Updated :'.$totalUpdated.PHP_EOL;