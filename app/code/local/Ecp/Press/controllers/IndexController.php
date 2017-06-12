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
class Ecp_Press_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/press?id=15 
    	 *  or
    	 * http://site.com/press/id/15 	
    	 */
    	/* 
		$press_id = $this->getRequest()->getParam('id');

  		if($press_id != null && $press_id != '')	{
			$press = Mage::getModel('ecp_press/press')->load($press_id)->getData();
              
                        
		} else {
			$press = null;
		}	
		
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($press == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$pressTable = $resource->getTableName('press');
			
			$select = $read->select()
			   ->from($pressTable,array('press_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$press = $read->fetchRow($select);
		}
		Mage::register('press', $press);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
    
    public function sendAction(){
        Mage::register('page',$this->getRequest()->getParam('page'));
        $this->loadLayout();
        $this->renderLayout();
    }
}