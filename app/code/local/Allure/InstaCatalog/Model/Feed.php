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
 * Feed model
 *
 * @category    Allure
 * @package     Allure_InstaCatalog
 * @author      Team Allure <extensions@allureinc.co>
 */
class Allure_InstaCatalog_Model_Feed extends Mage_Core_Model_Abstract
{
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY    = 'allure_instacatalog_feed';
    const CACHE_TAG = 'allure_instacatalog_feed';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'allure_instacatalog_feed';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'feed';

    /**
     * constructor
     *
     * @access public
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('allure_instacatalog/feed');
    }

    /**
     * before save feed
     *
     * @access protected
     * @return Allure_InstaCatalog_Model_Feed
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        $now = Mage::getSingleton('core/date')->gmtDate();
        if ($this->isObjectNew()) {
            $this->setCreatedAt($now);
        }
        $this->setUpdatedAt($now);
        return $this;
    }

    /**
     * save feed relation
     *
     * @access public
     * @return Allure_InstaCatalog_Model_Feed
     */
    protected function _afterSave()
    {
        return parent::_afterSave();
    }

    /**
     * get default values
     *
     * @access public
     * @return array
     */
    public function getDefaultValues()
    {
        $values = array();
        $values['status'] = 1;
        return $values;
    }
    
}
