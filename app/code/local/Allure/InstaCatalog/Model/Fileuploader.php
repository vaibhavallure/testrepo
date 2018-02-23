<?php

class Allure_InstaCatalog_Model_Fileuploader {

    private $allowedExtensions = array();
    private $sizeLimit = 10485760;
    private $min_image_width = 0;
    private $min_image_height = 0;
    private $max_image_width = 0;
    private $max_image_height = 0;    
    private $filemodel;

    function __construct(){
    
    $helper = Mage::helper('allure_instacatalog');
    $sizeLimit      = $helper->getMaxUploadFilesize();
    $this->min_image_width = $helper->getMinImageWidth();
    $this->min_image_height = $helper->getMinImageHeight();    
    $this->max_image_width = $helper->getMaxImageWidth();
    $this->max_image_height = $helper->getMaxImageHeight();
    $allowed_extensions = explode(',',$helper->getAllowedExtensions());
                   
        $this->allowedExtensions = array_map("strtolower", $allowed_extensions);
        if ($sizeLimit>0) $this->sizeLimit = $sizeLimit;      

        if (isset($_GET['qqfile'])) {
            $this->filemodel = Mage::getModel('allure_instacatalog/uploadedfilexhr');
        } elseif (isset($_FILES['qqfile'])) {
            $this->filemodel = Mage::getModel('allure_instacatalog/uploadedfileform');
        } else {
            $this->filemodel = false; 
        }
                                       
    }
    
    public function checkServerSettings(){        
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));        
        
        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit){
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';             
            return array('error' => 'increase post_max_size and upload_max_filesize to $size');    
        }

        if ($this->max_image_width < $this->min_image_width || $this->max_image_height < $this->min_image_height){            
            return array('error' => 'File was not uploaded. Minimal image width (height) can\'t be greater then maximal. Please, check settings.');    
        }
        return true; 
               
    }
    
    private function toBytes($str){
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;        
        }
        return $val;
    }
        
    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
 function handleUpload($uploadDirectory, $replaceOldFile = FALSE){
        if (!is_writable($uploadDirectory)){
            return array('error' => "Server error. Upload directory isn't writable.");
        }
        
        if (!$this->filemodel){
            return array('error' => 'No files were uploaded.');
        }
        
        $size = $this->filemodel->getSize();
        
        if ($size == 0) {
            return array('error' => 'File is empty');
        }
        
        if ($size > $this->sizeLimit) {
            return array('error' => 'File is too large');
        }
        
        $pathinfo = pathinfo($this->filemodel->getName());
        $filename = $pathinfo['filename'];
        //$filename = md5(uniqid());
        $filename = uniqid();
        $ext = $pathinfo['extension'];

        if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)){
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'File has an invalid extension, it should be one of '. $these . '.');
        }
        
        if(!$replaceOldFile){
            /// don't overwrite previous files that were uploaded
            while (file_exists($uploadDirectory . $filename . '.' . $ext)) {
                $filename .= rand(10, 99);
            }
        }

        if ($this->filemodel->save($uploadDirectory . $filename . '.' . $ext)){  
                    $imgPathFull = $uploadDirectory . $filename . '.' . $ext;
                	$dimensions = Mage::helper('allure_instacatalog')->getImageDimensions($imgPathFull);
                     if ($this->min_image_width!=0 && $this->min_image_height!=0) {
                        if ($dimensions['width'] < $this->min_image_width || $dimensions['height'] < $this->min_image_height)
                        {
                           unlink($imgPathFull);
                           return array('error'=> 'Uploaded file dimensions are less than those specified in the configuration.');
                        }                                                        
                     }
                                                                                
                    if ($this->max_image_width!=0 && $this->max_image_height!=0) {
                        if ($dimensions['width'] > $this->max_image_width || $dimensions['height'] > $this->max_image_heigh)
                        {
                            $resized_image = new Varien_Image($imgPathFull);
                            $resized_image->constrainOnly(TRUE);
                            $resized_image->keepAspectRatio(TRUE);
                            $resized_image->keepTransparency(TRUE);
                            $resized_image->resize($this->max_image_width,$this->max_image_height);
                            $resized_image->save($imgPathFull);
                            $dimensions = Mage::helper('allure_instacatalog')->getImageDimensions($imgPathFull);
                        }
                    }
            return array('success'=>true, 'filename'=>$filename . '.' . $ext, 'dimensions' => $dimensions);
        } else {
            return array('error'=> 'Could not save uploaded file.' .
                'The upload was cancelled, or server error encountered');
        }
        
    }        
}
