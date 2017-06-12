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
 * Feed helper
 *
 * @category    Allure
 * @package     Allure_InstaCatalog
 * @author      Team Allure <extensions@allureinc.co>
 */
class Allure_InstaCatalog_Helper_Feed extends Mage_Core_Helper_Abstract
{
    /**
     * get the url to the feeds list page
     *
     * @access public
     * @return string
     */
    public function getFeedsUrl()
    {
        if ($listKey = Mage::getStoreConfig('allure_instacatalog/feed/url_rewrite_list')) {
            return Mage::getUrl('', array('_direct'=>$listKey));
        }
        return Mage::getUrl('allure_instacatalog/feed/index');
    }

    /**
     * check if breadcrumbs can be used
     *
     * @access public
     * @return bool
     */
    public function getUseBreadcrumbs()
    {
        return Mage::getStoreConfigFlag('allure_instacatalog/feed/breadcrumbs');
    }
}
