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
 * @package     Ecp_Video
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Video
 *
 * @category    Ecp
 * @package     Ecp_Video
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Video_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/video?id=15 
    	 *  or
    	 * http://site.com/video/id/15 	
    	 */
    	/* 
		$video_id = $this->getRequest()->getParam('id');
*/  //echo 'video';
  		if($video_id != null && $video_id != '')	{
			$video = Mage::getModel('ecp_video/video')->load($video_id)->getData();
                        /*@var $model Ecp_Video_Model_Video */
                        
		} else {
			$video = null;
		}	
		
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($video == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$videoTable = $resource->getTableName('video');
			
			$select = $read->select()
			   ->from($videoTable,array('video_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$video = $read->fetchRow($select);
		}
		Mage::register('video', $video);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }

    public function videoviewAction(){
    	//$video_id = $this->getRequest()->getParam('keyvideo');
    	$this->loadLayout();     
		$this->renderLayout();
    }
}