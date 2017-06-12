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
 * @package     Ecp_Reviews
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Reviews
 *
 * @category    Ecp
 * @package     Ecp_Reviews
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Reviews_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/reviews?id=15 
    	 *  or
    	 * http://site.com/reviews/id/15 	
    	 */
    	/* 
		$reviews_id = $this->getRequest()->getParam('id');

  		if($reviews_id != null && $reviews_id != '')	{
			$reviews = Mage::getModel('ecp_reviews/reviews')->load($reviews_id)->getData();
                 
		} else {
			$reviews = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($reviews == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$reviewsTable = $resource->getTableName('reviews');
			
			$select = $read->select()
			   ->from($reviewsTable,array('reviews_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$reviews = $read->fetchRow($select);
		}
		Mage::register('reviews', $reviews);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
    
    public function saveAction()
    {
        $post = $this->getRequest()->getPost("reviews");
        $model = Mage::getModel('ecp_reviews/reviews');
        $model->setName($post['name']);
        $model->setEmail($post['email']);
        $model->setReview($post['message']);
        $model->setStatus(2);
        $lastinsertid = (int)$model->save()->getId();
        
        /*
        $post = $this->getRequest()->getPost("reviews");
        $model = Mage::getModel('ecp_reviews/review');
        $model->setEntityId(8);
        $model->setEntityPkValue(0);
        $model->setStatusId(2);
        $lastinsertid = $model->save()->getId();
        //echo $lastinsertid;
       
        $reviewdetail = Mage::getModel('ecp_reviews/reviewdetail');
        $reviewdetail->setReviewId($lastinsertid);
        $reviewdetail->setTitle('');
        $reviewdetail->setDetail($post['message']);
        $reviewdetail->setNickname('');
        $reviewdetail->setNameofuser($post['name']);
        $reviewdetail->setEmail($post['email']);
        $lastinsertids = (int)$reviewdetail->save()->getId();
         * */
        if( is_int( $lastinsertid ) ){
            echo 1;
        }
        else{
            echo 0;
        }
       
    }
    
}