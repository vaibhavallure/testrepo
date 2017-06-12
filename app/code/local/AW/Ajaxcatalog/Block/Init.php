<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Ajaxcatalog
 * @version    2.0.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Ajaxcatalog_Block_Init extends Mage_Page_Block_Html_Pager
{
    protected function _toHtml()
    {
        if (AW_Ajaxcatalog_Helper_Config::isEnabled()) {
            $this->setTemplate('aw_ajaxcatalog/init.phtml');
        }
        return parent::_toHtml();
    }

    /**
     * @return bool
     */
    public function isScrollMode()
    {
        $mode = AW_Ajaxcatalog_Helper_Config::getActionType();
        $scrollModeValue = AW_Ajaxcatalog_Model_System_Config_Source_Actiontype::TYPE_SCROLL_VALUE;
        return $mode === $scrollModeValue;
    }

    /**
     * @return string
     */
    public function getButtonHtml()
    {
        $block = $this->getLayout()->createBlock('core/template')
            ->setTemplate('aw_ajaxcatalog/button.phtml')
        ;
        return $block->toHtml();
    }

    /**
     * @return string
     */
    public function getLoadingHtml()
    {
        $block = $this->getLayout()->createBlock('core/template')
            ->setTemplate('aw_ajaxcatalog/loader.phtml')
        ;
        return $block->toHtml();
    }

    /**
     * @return integer
     */
    public function getPageSize()
    {
        return intval($this->getLimit());
    }

    /**
     * @return integer
     */
    public function getTotalSize()
    {
        return $this->getCollection()->getSize();
    }

    /**
     * @return boolean
     */
    public function isBackToTopEnabled()
    {
        return AW_Ajaxcatalog_Helper_Config::isBackToTopEnabled();
    }

    /**
     * @return string
     */
    public function getBackToTopLabel()
    {
        return AW_Ajaxcatalog_Helper_Config::getBackToTopLabel();
    }
}