<?php
/**
 * Magento
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
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Reports
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Reports Recently Viewed Products Block
 *
 * @category   Mage
 * @package    Mage_Reports
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Ecp_Recentlyviewed_Block_Product_Viewed extends Mage_Reports_Block_Product_Viewed
{
    protected function  _prepareLayout() {
        $head = $this->getLayout()->getBlock('head');
        //$head->addJs('jquery/jquery.carouFredSel-6.1.0-packed.js');
        //$head->addCss('css/recently.viewed.css');
        return parent::_prepareLayout();
    }

  
    protected function _toHtml()
    {
        $this->setTemplate('reports/product_viewed.phtml');
    
       
        if (!$this->getCount()) {
            return '';
        }
        $this->setRecentlyViewedProducts($this->getItemsCollection());
        return parent::_toHtml();
    }
}
