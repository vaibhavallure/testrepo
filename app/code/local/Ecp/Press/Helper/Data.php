<?php

/**
 * Ecp
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future. If you wish to customize for your
 * needs please refer to Ecp Event-Observer for more information.
 * 
 * @category    Ecp
 * @package     Ecp_Slideshow
 * @copyright   Copyright (c) 2010 Ecp Inc. (http://www.ecp.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Slideshow
 *
 * @category    Ecp
 * @package     Ecp_Slideshow
 * @author      Ecp Core Team <core@ecp.com>
 */
class Ecp_Press_Helper_Data extends Mage_Core_Helper_Abstract {

    public function resizeImg($fileName, $width, $height = ''){
        $folderURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'press/';
        $imageURL = $folderURL . $fileName;

        $basePath = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA). DS . 'press' . DS . $fileName;
        $newPath = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA). DS . 'press' . DS . rand(2).$fileName;
        //if width empty then return original size image's URL
        if ($width != '') {

                $imageObj = new Varien_Image($basePath);
                $imageObj->constrainOnly(TRUE);
                $imageObj->keepAspectRatio(FALSE);
                $imageObj->keepFrame(FALSE);
                $imageObj->resize($width, $height);
                $imageObj->save($newPath);
          
         }
         return $imageURL;
    }
    
}