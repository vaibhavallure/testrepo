<?php

class Ecp_UploadImages_Model_Convert_Adapter_Product extends Mage_Dataflow_Model_Convert_Adapter_Abstract {

    protected $_productModel;
    protected $_stores;
    
    private static $_aws = null;

    public function load() {
        // you have to create this method, enforced by Mage_Dataflow_Model_Convert_Adapter_Interface
    }

    public function save() {
        // you have to create this method, enforced by Mage_Dataflow_Model_Convert_Adapter_Interface
    }

    private function setStores() {
        if (is_null($this->_stores)) {
            $allStores = Mage::app()->getStores();
            foreach ($allStores as $_eachStoreId => $val) {
                $_storeCode = Mage::app()->getStore($_eachStoreId)->getCode();
                $_storeName = Mage::app()->getStore($_eachStoreId)->getName();
                $_storeId = Mage::app()->getStore($_eachStoreId)->getId();
                $this->_stores[$this->getStoreCodeFileByStoreName($_storeName)] = array('id' => $_storeId, 'code' => $_storeCode, 'name' => $_storeName);
            }
        }
    }

    private function getStoreCodeFileByStoreName($storename) {
        switch ($storename) {
            case 'Vista Alkosto':
            case 'Español':
                return 'AKINT';
            case 'Vista Ktronix':
                return 'KTINT';
            case 'Vista Alkomprar':
                return 'ALINT';
        }
    }

    /**
     * Retrieve product model cache
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProductModel() {
        if (is_null($this->_productModel)) {
            $productModel = Mage::getModel('catalog/product');
            $this->_productModel = Mage::objects()->save($productModel);
        }
        return Mage::objects()->load($this->_productModel);
    }
    
    private static function log($message) {

        $logName = 'amazon_s3_product-' . date('Ymd') . '.log';
        
        Mage::log('Amazon S3 Product #'.date('Y-m-d H:i:s')." => ".$message, Zend_Log::DEBUG, $logName, true);
    }

    public function saveRow(array $importData) {
        
        define('MAGENTO', Mage::getBaseDir());
        self::log('START : saveRow');
        
        // Staring import image from Amazon S3 Bucket
        $bucket = Mage::getStoreConfig('allure_imagecdn/amazons3/bucket');
        
        $remove_source = Mage::getStoreConfig('allure_imagecdn/general/remove_source');
                
        if (!self::$_aws) {
            self::log('Connecting...');

            self::$_aws = Mage::getSingleton('uploadimages/connect_amazon_s3')->connect();
        
            self::log('Connected');
        }
        
        $sku = $importData['sku'];
        $image = $importData['image'];
        $ext = $importData['ext'];

        switch ($ext) {
            case 'png':
                $mimeType = 'image/png';
                break;
            case 'jpg':
                $mimeType = 'image/jpeg';
                break;
            case 'gif':
                $mimeType = 'image/gif';
                break;
        }

        $imgName = str_replace('|', '-', trim($sku));
        $imageFile = $imgName . '#' . $image . '.' . $ext;
        $imageFilePath = MAGENTO . '/var/import/imagesFile/' . $imageFile;

        $skuArr = explode('|', $sku);
        $skuCode = $sku;
        if (isset($skuArr[1])) {
            //MT-114 Directional Image Upload Issue Fixed
            if(isset($skuArr[2]))
            {
                if((strtoupper(trim($skuArr[2]))=='LEFT')||(strtoupper(trim($skuArr[2]))=='RIGHT')){
                    $skuCode = $skuArr[0] . '|' . $skuArr[1] .'|'.$skuArr[2].'%';
                    Mage::log('In Directional Image SKU',Zend_Log::DEBUG,'directionImageUpload.log',true);
                    Mage::log('SKU Code (From Image) : '.$skuCode,Zend_Log::DEBUG,'directionImageUpload.log',true);
                }
                else
                {
                    $skuCode = $skuArr[0] . '|' . $skuArr[1] . '%';
                }
            }
            else
                {
                    $skuCode = $skuArr[0] . '|' . $skuArr[1] . '%';
                }

        }
        self::log('SKU CODE'.$skuCode);


        self::log('Checking Image for SKU "#'.$skuCode.'" ...');
        
        if (file_exists($imageFilePath)) {

            $used = false;
            self::log('Image found for SKU "#'.$skuCode.'" ...');
        
            self::log('Loading Product Collection  by SKU "#'.$skuCode.'" ...');

            if (isset($skuArr[1])) {
                $skuProductCollection = Mage::getResourceModel('catalog/product_collection')
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter('SKU', array('like' => $skuCode));
            } else {
                $skuProductCollection = Mage::getResourceModel('catalog/product_collection')
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter('SKU', array('eq' => $sku));
            }

            foreach ($skuProductCollection as $_product) {
                $media = Mage::getModel('catalog/product_attribute_media_api');
                $product = Mage::getModel('catalog/product')->load($_product->getId());

                self::log('Processing Product SKU "#'.$_product->getSku().'"');

                $exists = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'product' . DS . substr($basename, 0, 1) . DS . substr($basename, 1, 1) . DS . str_replace(' ', '_', basename($imgName)) . '_' . $image . '.' . $ext;
                $imageOfProduct = $product->getImage();

                // Fix MT-1442
                $types = (($imageOfProduct == 'no_selection' || empty($imageOfProduct))) ? array('image', 'small_image', 'thumbnail') : array();

                // Fix MT-2208 - When uploading photos with the same name they are not overwriitten
                $delLable = 'Image #' . $image;
                $this->delOldImagesByLabel($product->getId(), $delLable);
                
                // Fix MT-2208 - the new "#1" images are not being set to appear as the default base, small, and thumbnail images as they should be.
                if (count($types)==0) {
                    if ($image==1) {
                        $types = array('image', 'small_image', 'thumbnail');
                    }
                }
                
                $newImage = array(
                    'file' => array(
                        'content' => base64_encode($imageFilePath),
                        'mime' => $mimeType,
                        'name' => basename($imgName) . '_' . $image,
                    ),
                    'label' => $product->getName() . ' Image #' . $image, // change this.
                    'position' => $image,
                    'types' => $types,
                    'exclude' => 0,
                );

                self::log('Creating Images for Product "#'.$product->getSku().'"');
                $imageRenamedFile = $media->create($product->getSku(), $newImage);

                /*-----code added by allure to clear image cache----*/
                $fileName=pathinfo($imageRenamedFile)['basename'];
                $this->clearImageCache($fileName);
                /*allure code ended -------------------------------*/


                self::log('Done Creating Images for Product "#'.$_product->getSku().'" => '.$imageRenamedFile);
                $used = true;

            }
            
            if ($remove_source) {
                $object = $bucket.'/magentoimport/'.$imageFile;
                
                self::log('Removing Object From Bucket: "'.$imageFile.'"...');
                
                if (self::$_aws->removeObject($object)) {
                    self::log('Removed Object From Bucket "'.$imageFile.'"');
                } else {
                    self::log('FAIL: Removing Object From Bucket "'.$imageFile.'"');
                }
            }

            // delete image file in Amazon S3 to avoid caching image
            if (false && $used === true) {
                
                // Delete cached image in Amazon S3
                $newImagePath = explode(DS, $imageRenamedFile); // $imageRenamedFile is "/t/e/test_s3_1.png"

                $prex = preg_quote(end($newImagePath));
                
                Mage::log('Regular Expression ' . $prex, null, $logName);
                
                $response = self::$_aws->get_object_list($bucket, array(
                    'pcre' => "/$prex/i",
                ));         
                
                Mage::log('Response Image List from S3 ', null, $logName);
                Mage::log($response, null, $logName);

                if (count($response) >0 ) {

                    foreach ($response as $file) {
                        //self::$_aws->delete_object($bucket, $file);
                        Mage::log('Deleted Cache Image on S3 ' . $file, null, $logName);
                    }
                    
                    $message = Mage::helper('dataflow')->__('Removed images cached on S3');
                    $this->addException($message);
                    
                } else {
                    $message = 'No cached images need to deleted after import';
                    Mage::log($message, null, $logName);
                }
                
                // Delete imported image in Amazone S3
                $have_file = $imgName . '#' . $image . '.' . $ext;
                Mage::log('Have file name' . $have_file, null, $logName);
                
                $response = self::$_aws->get_object_list($bucket, array(
                    'pcre' => '/magentoimport/i',
                ));
                
                Mage::log('Imported Images Response ', null, $logName);
                Mage::log($response, null, $logName);
                
                if (count($response) > 0) {
                    unset($response[0]);
                    foreach ($response as $key => $file) {
                        
                        Mage::log('Preparing delete ' . $file, null, $logName);

                        if (self::$_aws->if_object_exists($bucket, $file)) {
                            
                            Mage::log('Base filename ' . basename($file), null, $logName);
                            
                            if($have_file == basename($file))  {
                                //self::$_aws->delete_object($bucket, $file);
                                Mage::log('Deleted file:  ' . $file, null, $logName);
                            }
                            
                            $message = Mage::helper('dataflow')->__('Deleted imported images file on S3/magentoimport');
                            $this->addException($message);                                        

                            Mage::log('Deleted Imported Image on S3/magentoimport ' . $file, null, $logName);
                        }          

                    }
                }
                else {
                    $message = 'No imported images need to deleted after import';
                    Mage::log($message, null, $logName);
                }
                
                rename($imageFilePath, MAGENTO . DS . 'var/import/imagesCompleted/' . $imgName . '_' . $image . '.' . $ext);
            } else {
                //rename($imageFilePath, MAGENTO . DS . 'var/import/images/' . $imgName . '_' . $image . '.' . $ext);
            }
        } else {
            $message = 'Image ' . $imageFile . '. Cannot be found in: /var/import/images';
            Mage::throwException($message);
        }

        return true;
    }
    
    private function delOldImagesByLabel($productId, $imageLabel) {

    
        $delOldFiles = array();
        $media = Mage::getModel('catalog/product_attribute_media_api');
        $product = Mage::getModel('catalog/product')->load($productId);

        $_gallery = $product->getMediaGalleryImages();

        foreach ($_gallery as $img) {

            $imgLabel = $img->getLabel();
            if (strpos($imgLabel, $imageLabel)!==false) {
                $delOldFiles[] = $img->getFile();
            }

        }

        if (count($delOldFiles)>0) {
            foreach ($delOldFiles as $delfile) {
                $media->remove($productId, $delfile);
                /*@allure code added to remove image */
                $source  = Mage::getBaseDir('media'). DS . 'catalog' . DS . 'product';
                @unlink($source.$delfile);
            }
        }    

    }

/*code added by allure to clear image cache-------------------*/
    function clearImageCache($file) {
        self::log("-----------------------------------------------------");
        self::log('checking existing cache for image =>"'.$file.'"');
        $folder=Mage::getBaseDir('media')."/catalog/product/cache/";
        $dir = new RecursiveDirectoryIterator($folder);
        $ite = new RecursiveIteratorIterator($dir);
        $pattern="/".$file."/";
        $files = new RegexIterator($ite, $pattern, RegexIterator::GET_MATCH);
        foreach($files as $key=>$file) {
            self::log('cache found path =>"'.$key.'"');
            unlink($key);
            self::log('cache removed');
        }
        self::log("done");
        self::log("------------------------------------------------------");
    }

}
