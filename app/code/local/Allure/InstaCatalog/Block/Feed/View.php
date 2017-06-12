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
 * Feed view block
 *
 * @category    Allure
 * @package     Allure_InstaCatalog
 * @author      Team Allure <extensions@allureinc.co>
 */
class Allure_InstaCatalog_Block_Feed_View extends Mage_Core_Block_Template
{
    public function getMediaId(){
    	return Mage::app()->getRequest()->getParam('id');
    }
    
    public function getFeed(){
    	$feed    = Mage::getModel('allure_instacatalog/feed');
    	$feedId = $this->getMediaId();
    	if ($feedId) {
    		$feed->load($feedId);//load($feedId,'media_id');
    	}
    	return $feed;
    }
}
