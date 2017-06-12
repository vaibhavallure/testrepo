<?php
/**
 * Ecp
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
 * needs please refer to Ecp Event-Observer for more information.
 * 
 * @category    Ecp
 * @package     Ecp_Searchbox
 * @copyright   Copyright (c) 2010 Ecp Inc. (http://www.ecp.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Searchbox
 *
 * @category    Ecp
 * @package     Ecp_Searchbox
 * @author      Ecp Core Team <core@ecp.com>
 */
class Ecp_Searchbox_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/searchbox?id=15 
    	 *  or
    	 * http://site.com/searchbox/id/15 	
    	 */
    	/* 
		$searchbox_id = $this->getRequest()->getParam('id');

  		if($searchbox_id != null && $searchbox_id != '')	{
			$searchbox = Mage::getModel('ecp_searchbox/searchbox')->load($searchbox_id)->getData();
                        /*@var $model Ecp_Searchbox_Model_Searchbox 
                        
		} else {
			$searchbox = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($searchbox == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$searchboxTable = $resource->getTableName('searchbox');
			
			$select = $read->select()
			   ->from($searchboxTable,array('searchbox_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$searchbox = $read->fetchRow($select);
		}
		Mage::register('searchbox', $searchbox);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
}