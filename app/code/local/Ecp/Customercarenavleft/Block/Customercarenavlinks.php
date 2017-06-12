<?php

/**
 * Entrepids
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
 * needs please refer to Entrepids Event-Observer for more information.
 * 
 * @category    Ecp
 * @package     Ecp_Customercarenavleft
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Customercarenavleft
 *
 * @category    Ecp
 * @package     Ecp_Customercarenavleft
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Customercarenavleft_Block_Customercarenavlinks extends Mage_Core_Block_Template {

    public function getCustomercarenavleft() {
        $cms_pages = Mage::getModel('cms/page')->getCollection()->addFieldToFilter('customer_care_navigation', '1');
        $cms_pages->getSelect()->order('customer_care_navigation_order ASC');
        return $cms_pages->load();
    }
    
    public function _toHtml() {
        $this->setTemplate('ecp/customercarenavleft/linkscustomernav.phtml');
        return parent::_toHtml();
    }

}