<?php
/**
 * Allure_InstaCatalog
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category    Allure
 * @package     Allure_InstaCatalog
 * @copyright   Copyright© 2016, Allure Inc
 * @license     http://opensource.org/licenses/mit-license.php MIT License
 * @author      Team Allure <extensions@allureinc.co>
 */
/**
 * AdvInstagram default helper
 *
 * @category    Allure
 * @package     Allure_InstaCatalog
 * @author      Team Allure <extensions@allureinc.co>
 */
class Allure_InstaCatalog_Helper_Data extends Mage_Core_Helper_Abstract
{
	const TYPE_INSTAGRAM = 0;
	const TYPE_SHOP_BY_LOOK = 1;
	
	const mainArr = array("#ear","#nose","#navel","#dermal","#oral");
	const earArr = array("#full year","#earlobe","#helix","#tragus",
				"#tash rook","#ear head","#conch","#rook","#daith");
	
	const arr = array('ear'=>array());
	
    public function getEnabled()
	{
		return Mage::getStoreConfig('lookbook/general/enabled');
	}
    
    public function getMaxImageWidth()
	{
		//return intval(Mage::getStoreConfig('lookbook/general/max_image_width'));
		return 640;
	}

    public function getMaxImageHeight()
	{
		//return intval(Mage::getStoreConfig('lookbook/general/max_image_height'));
		return 640;
	}

    public function getMinImageWidth()
	{
		//return intval(Mage::getStoreConfig('lookbook/general/min_image_width'));
		return 300;
	}

    public function getMinImageHeight()
	{
		//return intval(Mage::getStoreConfig('lookbook/general/min_image_height'));
		return 300;
	}
 
    public function getMaxUploadFilesize()
	{
		//return intval(Mage::getStoreConfig('lookbook/general/max_upload_filesize'));
		return 2097152;
	}
  
    public function getAllowedExtensions()
	{
		//return Mage::getStoreConfig('lookbook/general/allowed_extensions');
		return "jpg,jpeg,png,gif";
	} 
    
    public  function getLimit(){
    	return 10;
    }
    
}
