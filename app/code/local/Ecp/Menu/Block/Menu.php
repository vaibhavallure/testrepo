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
 * @package     Ecp_Menu
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Menu
 *
 * @category    Ecp
 * @package     Ecp_Menu
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Menu_Block_Menu 
    extends Mage_Core_Block_Template
    //implements Mage_Widget_Block_Interface
{
    
    public function __construct() {
        parent::__construct();
    }
    
    public function _prepareLayout()
    {
        
        return parent::_prepareLayout();
    }
    
    protected function _toHtml(){   
        $this->setTemplate('ecp/menu/menu.phtml');
        return parent::_toHtml();
    }
    
     public function getMenu()     
     { 
        if (!$this->hasData('menu')) {
            $this->setData('menu', Mage::registry('menu'));
        }
        return $this->getData('menu');
        
    }
}