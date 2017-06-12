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
 * @package     Ecp_Tryon
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Tryon
 *
 * @category    Ecp
 * @package     Ecp_Tryon
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Tryon_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/tryon?id=15 
    	 *  or
    	 * http://site.com/tryon/id/15 	
    	 */
    	/* 
		$tryon_id = $this->getRequest()->getParam('id');

  		if($tryon_id != null && $tryon_id != '')	{
			$tryon = Mage::getModel('ecp_tryon/tryon')->load($tryon_id)->getData();
                     
                        
		} else {
			$tryon = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($tryon == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$tryonTable = $resource->getTableName('tryon');
			
			$select = $read->select()
			   ->from($tryonTable,array('tryon_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$tryon = $read->fetchRow($select);
		}
		Mage::register('tryon', $tryon);
		*/

			
		$this->loadLayout();     
                
                
                
		$this->renderLayout();
    }
}