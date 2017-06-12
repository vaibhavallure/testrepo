<?php
/**
 * Allure_InstaCatalog
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category    Allure
 * @package     Allure_InstaCatalog
 * @copyright   Copyright© 2016, Allure Inc
 * @license     http://opensource.org/licenses/mit-license.php MIT License
 * @author      Team Allure <extensions@allureinc.co>
 */
/**
 * Feed front contrller
 *
 * @category    Allure
 * @package     Allure_InstaCatalog
 * @author      Team Allure <extensions@allureinc.co>
 */
class Allure_InstaCatalog_FeedController extends Mage_Core_Controller_Front_Action
{

    /**
      * default action
      *
      * @access public
      * @return void
      */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('checkout/session');
        if (Mage::helper('allure_instacatalog/feed')->getUseBreadcrumbs()) {
            if ($breadcrumbBlock = $this->getLayout()->getBlock('breadcrumbs')) {
                $breadcrumbBlock->addCrumb(
                    'home',
                    array(
                        'label' => Mage::helper('allure_instacatalog')->__('Home'),
                        'link'  => Mage::getUrl(),
                    )
                );
                $breadcrumbBlock->addCrumb(
                    'feeds',
                    array(
                        'label' => Mage::helper('allure_instacatalog')->__('Shop Our Instagram'),
                        'link'  => '',
                    )
                );
            }
        }
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->setTitle(Mage::helper('allure_instacatalog')->__('Shop Our Instagram').' | '.$headBlock->getTitle());
            $headBlock->addLinkRel('canonical', Mage::helper('allure_instacatalog/feed')->getFeedsUrl());
        }
        $this->renderLayout();
    }
    
    public function loadAjaxAction(){
    	$request = $this->getRequest()->getPost();
    	$pageNo=1;
    	if($request['page'])
    		$pageNo=$request['page'];
    		$helper=Mage::helper('allure_instacatalog');
    		 
    		$limit=$helper->getLimit();
    		$resolution = Mage::getStoreConfig('allure_instacatalog/feed_features/resolution');
    		$feeds = Mage::getResourceModel('allure_instacatalog/feed_collection')
    		->addFieldToFilter('status', 1)
    		/*     ->setPageSize($limit) */
    		->setOrder('created_at', 'desc');
    		 
    		$feeds->setCurPage($pageNo);
    		$feeds ->setPageSize($limit);
    		$instagram_logo = Mage::getDesign()->getSkinUrl('images/your-image.jpg');//Mage::app()->getSkinUrl('images/instagram-logo.png');
    		$str = "";
    		foreach ($feeds as $_post) {
    			$media_id = $_post->getMediaId();
    			$username = $_post->getUsername();
	    		$instagramObj = unserialize($_post->getInstagramData());
	    		$class_css = "post_item_with_link";
	    		if (strpos($username,'instagram') !== false)
	    			$class_css = "post_item_without_link";
	    		
	    			$str .=	'<div id="main_'.$media_id.'" class="instagram-post individual-post col-sm-4 col-xs-6 col-xxs-12 post_item '.$class_css.'">';
	    			$str .= '<div class="instagram_post_wrapper">';
	    			$str .= '<div class="instagram_post_image">';
	    			$str .= '<img src="'.$instagramObj->images->$resolution->url.'">';
	    			$str .= '</div>';
	    			$str .= '<div class="instagram_post_overlay">';
	    			$str .= '<div class="instagram_shop">';
	    			$str .= '<div>';
	    			$str .= '<i onclick="instagramView.show(this);" media-id="'.$media_id.'" class="fa fa-instagram"></i>';
	    			$str .= '</div>';
	    			$str .= '<a id="shop-'.$media_id.'" class="instagram_shop_button" href="'.$username.'" target="_blank">';
	    			$str .= '<span class="shop-it-button">SHOP IT</span>';
	    			$str .= '</a></div></div></div></div>';
    		 
    		}
    
    		$data = array('html'=>$str);
    		$jsonData = json_encode(compact('success', 'message', 'data'));
    		$this->getResponse()->setHeader('Content-type', 'application/json');
    		$this->getResponse()->setBody($jsonData);
    
    }
    
    
    public function viewAction(){
    	$this->loadLayout();
    	$this->renderLayout();
    }
    
    public function shareviewAction(){
    	$this->loadLayout();
    	$this->renderLayout();
    }
}
