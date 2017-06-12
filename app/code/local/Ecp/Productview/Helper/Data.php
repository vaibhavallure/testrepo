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
class Ecp_Productview_Helper_Data extends Mage_Core_Helper_Abstract {

    public function curPageURL() {
        $pageURL = '';
        //if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
          //  $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }
   
    public function resizeImg($fileName, $width, $height = ''){
    	try{
        $folderURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product/';
        $imageURL = $folderURL . $fileName;

        $basePath = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA). DS .'catalog'. DS .'product' . DS . $fileName;
        $newPath = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA). DS .'catalog'. DS .'product' . DS . $fileName;
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
		}catch(exception $e){
			return  Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'thumbnail/video/galleryThumb-video.png';
		}
    }
    
    public function makeBitlyUrl($url,$login,$appkey,$format = 'xml',$version = '2.0.1'){
        //create the URL
        $bitly = 'http://api.bit.ly/shorten?version='.$version.'&longUrl='.urlencode($url).'&login='.$login.'&apiKey='.$appkey.'&format='.$format;

        //get the url
        //could also use cURL here
        $response = file_get_contents($bitly);

        //parse depending on desired format
        if(strtolower($format) == 'json')
        {
          $json = @json_decode($response,true);
          return $json['results'][$url]['shortUrl'];
        }
        else //xml
        {
          $xml = simplexml_load_string($response);
          return 'http://bit.ly/'.$xml->results->nodeKeyVal->hash;
        }
    }
    
    public function removeQueryString($url) { 
        list($shorturl) = explode('?', $url);
        return $shorturl;
    }

    
}