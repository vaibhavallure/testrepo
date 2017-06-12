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
 * @package     Ecp_Shippingtext
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Shippingtext
 *
 * @category    Ecp
 * @package     Ecp_Shippingtext
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Shippingtext_Block_Shippingtext extends Mage_Core_Block_Template {

    public function _prepareLayout() {
        return parent::_prepareLayout();
    }
    
    public function _construct() {
        $tmp = Mage::getModel('ecp_shippingtext/shippingtext')->getCollection()
                ->addFieldToFilter('status',1);
        
        if((int)$tmp->getSize()==1){
            $this->setCanShow(true);
            $content = Mage::helper('cms')
                    ->getPageTemplateProcessor()
                    ->filter($tmp->getFirstItem()->getBlockContent());
            $tmp->getFirstItem()->setBlockContent($content);
            $this->setActive($tmp->getFirstItem());
        }else {
            $this->setCanShow(false);
        }
        parent::_construct();
    }

    public function _toHtml() {
        $this->setTemplate('ecp/header/shippingtext.phtml');
        return parent::_toHtml();
    }

}
