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
 * @package     Ecp_PiercingGallery
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of PiercingGallery
 *
 * @category    Ecp
 * @package     Ecp_PiercingGallery
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_PiercingGallery_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        Mage::register('current_piercing_location',$this->getRequest()->getParam('locationtype',null));
        Mage::register('piercing_gallery_category',Mage::getModel('catalog/category')->load($this->getRequest()->getParam('locationtype',null)));
        $this->loadLayout();
	$this->renderLayout();
    }

    public function getGalleryProductsAction(){
        $lookId = $this->getRequest()->getParam('lookId');

        if(!empty($lookId)){
            $arrayProducts = array();

            $look = Mage::getModel('catalog/category')->load();
            $products = $look->getProductCollection();

            foreach ($products as $product) {
                 $arrayProducts[] = $product;
            }

            die(json_encode($arrayProducts));
        }

        die(json_encode('There are no Products Matching the Selection'));
    }

    public function sendAction(){
        Mage::register('page',$this->getRequest()->getParam('page'));
        $this->loadLayout();
        $this->renderLayout();
    }
}