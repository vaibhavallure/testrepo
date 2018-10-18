<?php

/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Dataflow
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Convert IO adapter
 *
 * @category   Mage
 * @package    Mage_Dataflow
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Ecp_UploadImages_Model_Convert_Adapter_Io extends Mage_Dataflow_Model_Convert_Adapter_Abstract {

    /**
     * @return Varien_Io_Abstract
     */
    public function getResource($forWrite = false) {
        if (!$this->_resource) {
            $type = $this->getVar('type', 'file');
            $className = 'Varien_Io_' . ucwords($type);
            $this->_resource = new $className();

            $isError = false;

            $ioConfig = $this->getVars();
            switch ($this->getVar('type', 'file')) {
                case 'file':
                    if (preg_match('#^' . preg_quote(DS, '#') . '#', $this->getVar('path')) ||
                        preg_match('#^[a-z]:' . preg_quote(DS, '#') . '#i', $this->getVar('path'))
                    ) {

                        $path = $this->_resource->getCleanPath($this->getVar('path'));
                    } else {
                        $baseDir = Mage::getBaseDir();
                        $path = $this->_resource->getCleanPath($baseDir . DS . trim($this->getVar('path'), DS));
                    }

                    $this->_resource->checkAndCreateFolder($path);

                    $realPath = realpath($path);

                    if (!$isError && $realPath === false) {
                        $message = Mage::helper('dataflow')->__('The destination folder "%s" does not exist or there is no access to create it.', $ioConfig['path']);
                        Mage::throwException($message);
                    } elseif (!$isError && !is_dir($realPath)) {
                        $message = Mage::helper('dataflow')->__('Destination folder "%s" is not a directory.', $realPath);
                        Mage::throwException($message);
                    } elseif (!$isError) {
                        if ($forWrite && !is_writeable($realPath)) {
                            $message = Mage::helper('dataflow')->__('Destination folder "%s" is not writable.', $realPath);
                            Mage::throwException($message);
                        } else {
                            $ioConfig['path'] = rtrim($realPath, DS);
                        }
                    }
                    break;
                default:
                    $ioConfig['path'] = rtrim($this->getVar('path'), '/');
                    break;
            }

            if ($isError) {
                return false;
            }
            try {
                $this->_resource->open($ioConfig);
            } catch (Exception $e) {
                $message = Mage::helper('dataflow')->__('An error occurred while opening file: "%s".', $e->getMessage());
                Mage::throwException($message);
            }
        }
        return $this->_resource;
    }

    /**
     * Load data
     *
     * @return Mage_Dataflow_Model_Convert_Adapter_Io
     */
    public function load() {

        if (!$this->getResource()) {
            return $this;
        }

        self::log('START : load');

        $batchModel = Mage::getSingleton('dataflow/batch');
        $destFile = $batchModel->getIoAdapter()->getFile(true);
        $result = $this->getResource()->read($this->getVar('filename'), $destFile);

        $filename = $this->getResource()->pwd() . '/' . $this->getVar('filename');
        if (false === $result) {
            //$this->alertAdmin('updateFileDoNotExists');
            $message = Mage::helper('dataflow')->__('Could not load file: %s.', $filename);

            self::log($message);
            Mage::throwException($message);
        } else {
            $message = Mage::helper('dataflow')->__('Loaded successfully: "%s".', $filename);
            self::log($message);
            $this->addException($message);
        }

        $this->setData($result);
        return $this;
    }

    /**
     * Save result to destionation file from temporary
     *
     * @return Mage_Dataflow_Model_Convert_Adapter_Io
     */
    public function save() {
        if (!$this->getResource(true)) {
            return $this;
        }

        $batchModel = Mage::getSingleton('dataflow/batch');

        $dataFile = $batchModel->getIoAdapter()->getFile(true);

        $filename = $this->getVar('filename');

        $result = $this->getResource()->write($filename, $dataFile, 0777);

        if (false === $result) {
            $message = Mage::helper('dataflow')->__('Could not save file: %s.', $filename);
            Mage::throwException($message);
        } else {
            $message = Mage::helper('dataflow')->__('Saved successfully: "%s" [%d byte(s)].', $filename, $batchModel->getIoAdapter()->getFileSize());
            if ($this->getVar('link')) {
                $message .= Mage::helper('dataflow')->__('<a href="%s" target="_blank">Link</a>', $this->getVar('link'));
            }
            $this->addException($message);
        }
        return $this;
    }

    private static function log($message) {

        $logName = 'amazon_s3-' . date('Ymd') . '.log';

        Mage::log('Amazon S3 #'.date('Y-m-d H:i:s')." => ".$message, Zend_Log::DEBUG, $logName, true);
    }

    private function getDirectoryList($directory, $ext = false) {
        $result = array();

        $folderPath = Mage::getBaseDir() . DS . $directory;

        $handler = opendir($folderPath);
        while ($file = readdir($handler)) {
            if ($file != "." && $file != "..") {
                $info = pathinfo($file);
                if (!$ext)
                    $result[] = $file;
                elseif (strtolower(trim($info['extension'])) == strtolower($ext))
                    $result[] = $file;
            }
        }
        closedir($handler);
        return $result;
    }

    private function sendMail($info) {
        mail(Mage::getStoreConfig('trans_email/ident_custom1/email'), $info['subject'], $info['body'], "From: cron@colcomercio.com");
        return true;
    }

    public function move() {
        $ext = explode('.', $this->getVar('filename'));
        $ext = $ext[1];
        $sourceFolder = $this->getVar('sourceFolder');
        $destinyFolder = $this->getVar('destinyFolder');

        self::log("Moving data from '{$sourceFolder}' to '{$destinyFolder}'...");

        $files = $this->getDirectoryList($sourceFolder, $ext);
        foreach ($files as $key => $file) {
            rename(Mage::getBaseDir() . DS . $sourceFolder . DS . $file, Mage::getBaseDir() . DS . $destinyFolder . DS . $file);
        }

        self::log("Done Moving Data !!");
    }

    public function generateArrayFile() {

        define('MAGENTO', Mage::getBaseDir());

        // MT-1440: Remove the old images file in the destination folder
        $this->removeFiles($this->getVar('destinyFolder'));

        self::log('START : generateArrayFile');

        // Staring import image from Amazon S3 Bucket
        self::log('Connecting...');

        $bucket = Mage::getStoreConfig('allure_imagecdn/amazons3/bucket');

        $remove_source = Mage::getStoreConfig('allure_imagecdn/general/remove_source');

        $s3 = Mage::getSingleton('uploadimages/connect_amazon_s3')->connect();

        self::log('Connected');

        self::log('Loading Objects...');
        $list = $s3->getObjectsByBucket($bucket,array('prefix' => 'magentoimport/'));
        self::log('Objects Loaded!');

        self::log('Downloading Objects...');
        if (is_array($list) && !empty($list)) {
            unset($list[0]);
            foreach ($list as $key => $file) {
                $object_name = $bucket.'/'.$file;

                self::log('Downloading Object "'.$object_name.'" ...');

                $image = $s3->getObject($object_name);

                file_put_contents(Mage::getBaseDir() . DS . $this->getVar('destinyFolder') . DS . basename($file), $image);

                self::log('Object Downloaded "'.$object_name.'"');

                //$s3->delete_object($bucket, $file);
            }
        } else {
            $message = 'Objects Downloading Failed !!';
            self::log($message);
            Mage::throwException($message);
        }

        self::log('Objects Downloaded!');

        ////////////////////////////////////////////////////////////////////////////////
        self::log('Creating Files Data...');

        $dir = MAGENTO . DS . $this->getVar('destinyFolder') . DS;
        $fileCount = 0;
        $badFileCount = 0;
        $imagesArray = array();
        if ($gd = opendir($dir)) {
            while ($file = readdir($gd)) {

                if (($file == '.') || ($file == '..')) continue; // ignore directory

                if (substr($file, -4) == ('.jpg' || '.png')) {
                    $tmpFile = explode('#', $file);
                    if (count($tmpFile) == 2) {
                        $sku = $tmpFile[0];
                        $tmpFile = explode('.', $tmpFile[1]);

                        if (count($tmpFile) == 2) {
                            $fileCount++;
                            if (isset($imagesArray[$sku])) {
                                $imagesArray[$sku][$tmpFile[0]]['num'] = $tmpFile[0];
                                $imagesArray[$sku][$tmpFile[0]]['ext'] = $tmpFile[1];
                            } else {
                                $imagesArray[$sku][$tmpFile[0]]['num'] = $tmpFile[0];
                                $imagesArray[$sku][$tmpFile[0]]['ext'] = $tmpFile[1];
                            }
                        } else {
                            self::log('IGNORE: Incorrect File Name Syntax "' . $file.'"');
                            $badFileCount++;
                        }
                    } else {
                        self::log('IGNORE: Incorrect File Name Syntax "' . $file.'"');
                        $badFileCount++;
                    }
                } else {
                    if ($file != '.' && $file != '..') {
                        self::log('IGNORE: Invalid File Name Extension', null, $logName);
                        $badFileCount++;
                    }
                }
            }
            closedir($gd);
        }

        self::log('Files Data Created');

        self::log('GOOD IMAGES: ' . $fileCount);
        self::log('BAD IMAGES: ' . $badFileCount);
        ////////////////////////////////////////////////////////////////////////////////
        $file = MAGENTO . '/' . $this->getVar('destinyFolder') . '/' . $this->getVar('outputFilename');

        self::log('Saving Data to File : ' . $file);

        $tempFile = fopen($file, "w");

        if (!$tempFile) {
            $message = 'Failed Opening Data File : ' . $file;
            self::log($message);
            die($message);
        }
        fputs($tempFile, 'sku;image;ext');
        fputs($tempFile, "\n");
        foreach ($imagesArray as $sku => $images) {
            ksort($images);
            foreach ($images as $name) {
                $skuReplace = str_replace("-", '|', $sku);
                $line = $skuReplace . ';' . $name['num'] . ';' . $name['ext'];
                fputs($tempFile, $line);
                fputs($tempFile, "\n");
            }
        }
        fclose($tempFile);

        self::log('Wrote Data to File : ' . $file);

//         if ((int) $this->getVar('removes3'))
//             foreach ($response as $key => $file)
//                 $s3->delete_object($bucket, $file);

        return true;
    }

    public function moveAndClear() {
        $this->move();
        $sourceFolder = $this->getVar('sourceFolder');
        $files = $this->getDirectoryList($sourceFolder);

        self::log("Cleaning Source Data...");
        foreach ($files as $key => $file) {
            unlink(Mage::getBaseDir() . DS . $sourceFolder . DS . $file);
        }
        self::log("Done Cleaning Source Data !!");
    }

    public function removeFiles($rmFolder = null) {

        if (!isset($rmFolder))
            return null;

        $folder = $rmFolder;

        self::log("Removing Files from ".$rmFolder);

        self::log('Remove jpg files..');

        // remove jpg files
        $jpgFiles = $this->getDirectoryList($folder, 'jpg');

        self::log('Found #'.count($jpgFiles));

        foreach ($jpgFiles as $key => $file) {

            $fileName = Mage::getBaseDir() . DS . $folder . DS . $file;
            self::log('Delete file ' . $fileName);

            try {
                unlink($fileName);
                $message = "Removed the old file " . $fileName;
                $this->addException($message);
            }
            catch (Exception $e) {
                $message = 'Could not delete the old file: ' . $fileName . '. Exception: ' . $e->getMessage();
                Mage::throwException($message);
            }

        }

        self::log('Done Removing jpg files!!');

        self::log('Remove png files..');

        // remove png files
        $pngFiles = $this->getDirectoryList($folder, 'png');

        self::log('Found #'.count($pngFiles));

        foreach ($pngFiles as $key => $file) {
            $fileName = Mage::getBaseDir() . DS . $folder . DS . $file;
            self::log('Delete file ' . $fileName);

            try {
                unlink($fileName);
                $message = "Removed the old file " . $fileName;
                $this->addException($message);
            }
            catch (Exception $e) {
                $message = 'Could not delete the old file: ' . $fileName . '. Exception: ' . $e->getMessage();
                Mage::throwException($message);
            }
        }

        self::log('Done Removing png files!!');
    }
}
