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
 * @package     Ecp_DiscoverNavigation
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of DiscoverNavigation
 *
 * @category    Ecp
 * @package     Ecp_DiscoverNavigation
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_DiscoverNavigation_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {           
        $categoryId = $this->getRequest()->getParam('content_id');        

        if(empty($categoryId)){
            $categoryId = Mage::getModel('ecp_discovernavigation/discovernavigation')->getCollection()->addFieldToFilter('type',2)->addOrder('sort_order','asc')->getFirstItem()->getCategoryId();
        }                
        
        $category = Mage::getModel('catalog/category')->load($categoryId);
        $customlayout = $category->getCustomLayoutUpdate();  
        
        $customlayout = str_replace( '<block type="ecp_discovernavigation/discoverNavigation" name="discovernavigation" template="ecp/discoverNavigation/discoverNavigationMenu.phtml" />', "" , $customlayout );
                
        $layout = $this->loadLayout(null,true,false);
        $layout->getLayout()->getUpdate()->addUpdate($customlayout);
        $this->generateLayoutXml();
        $this->generateLayoutBlocks();
        $this->renderLayout();
    }
    
    public function loadLinkContentAction(){
        
        $categoryId = $this->getRequest()->getParam('content_id');
        $category = Mage::getModel('catalog/category')->load($categoryId);
        
        $customlayout = $category->getCustomLayoutUpdate();
        
        $layout = $this->loadLayout(null,true,false);
        $layout->getLayout()->getUpdate()->addUpdate($customlayout);
        $this->generateLayoutXml();
        $this->generateLayoutBlocks();
        $this->renderLayout();
    }
}