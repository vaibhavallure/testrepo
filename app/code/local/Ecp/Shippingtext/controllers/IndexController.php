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
class Ecp_Shippingtext_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/shippingtext?id=15 
    	 *  or
    	 * http://site.com/shippingtext/id/15 	
    	 */
    	/* 
		$shippingtext_id = $this->getRequest()->getParam('id');

  		if($shippingtext_id != null && $shippingtext_id != '')	{
			$shippingtext = Mage::getModel('ecp_shippingtext/shippingtext')->load($shippingtext_id)->getData();
		} else {
			$shippingtext = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($shippingtext == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$shippingtextTable = $resource->getTableName('shippingtext');
			
			$select = $read->select()
			   ->from($shippingtextTable,array('shippingtext_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$shippingtext = $read->fetchRow($select);
		}
		Mage::register('shippingtext', $shippingtext);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
}