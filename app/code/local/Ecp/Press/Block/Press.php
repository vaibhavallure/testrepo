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
 * @package     Ecp_Press
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Press
 *
 * @category    Ecp
 * @package     Ecp_Press
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Press_Block_Press extends Mage_Core_Block_Template
{
    protected $lastPage=1;

	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getPress()     
     { 
        if (!$this->hasData('press')) {
            $this->setData('press', Mage::registry('press'));
        }
        return $this->getData('press');

    }
    public function getPressCol()
    {
    $collection = Mage::getModel('ecp_press/press')->getCollection()
        ->addFilter('status', 1)
        ->setOrder('publish_date');
        $collection->setCurPage(1);
      $collection->setPageSize(9);

        $this->lastPage=$collection->getLastPageNumber();

    return $collection;
    }
    public function getPressActionUrl()
    {
        return $this->getUrl('ecppress/index/getPress', array('_secure' => true));
    }
    public function getPopUpActionUrl()
    {
        return $this->getUrl('ecppress/index/getPopup', array('_secure' => true));
    }
    public function getImagePath()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'press/';
    }
    public function getLastPage()
    {
        return $this->lastPage;
    }
}