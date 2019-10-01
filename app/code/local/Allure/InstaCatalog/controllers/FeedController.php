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
			if ($feeds)
    		foreach ($feeds as $_post) {
    			$mediaId = $_post->getMediaId();
    			$username = $_post->getUsername();
	    		$instagramObj = @unserialize($_post->getInstagramData());
	    		$class_css = "post_item_with_link";
	    		if (strpos($username,'instagram') !== false)
	    			$class_css = "post_item_without_link";

	    			$str .=	'<div id="main_'.$mediaId.'" class="instagram-post individual-post col-sm-4 col-xs-6 col-xxs-12 post_item '.$class_css.'">';
	    			$str .= '<div class="instagram_post_wrapper">';
	    			$str .= '<div class="instagram_post_image">';
	    			$str .= '<img src="'.$instagramObj->images->$resolution->url.'">';
	    			$str .= '</div>';
	    			$str .= '<div class="instagram_post_overlay">';
	    			$str .= '<div class="instagram_shop">';
	    			$str .= '<div>';
	    			$str .= '<i onclick="instagramView.show(this);" media-id="'.$mediaId.'" class="fa fa-instagram"></i>';
	    			$str .= '</div>';
	    			$str .= '<a id="shop-'.$mediaId.'" class="instagram_shop_button" href="'.$username.'" target="_blank">';
	    			$str .= '<span class="shop-it-button">SHOP IT</span>';
	    			$str .= '</a></div></div></div></div>';

    		}

    		$data = array('html'=>$str);
    		$jsonData = json_encode(compact('success', 'message', 'data'));
    		$this->getResponse()->setHeader('Content-type', 'application/json');
    		$this->getResponse()->setBody($jsonData);

    }

    public function loadMoreAjaxAction(){
    	$request = $this->getRequest()->getPost();
    	$pageNo=2;
    	if($request['page'])
    		$pageNo=$request['page'];

    		$helper=Mage::helper('allure_instacatalog');

    		$limit=$helper->getLimit();
    		$limit= 12;

    		$feeds = Mage::getResourceModel('allure_instacatalog/feed_collection')
    		->addFieldToFilter('status', 1)
    		->setPageSize($limit)
    		->setCurPage($pageNo)
    		->addFieldToFilter('lookbook_mode',array('neq'=>1))
    		->setOrder('created_timestamp', 'desc');

    		$strHtml = "";
			if ($feeds)
    		foreach ($feeds as $_post) {
	    		$mediaId = $_post->getId();//getMediaId();
	    		$username = $_post->getUsername();
	    		$class_css = "post_item_without_link";
	    		/* if (strpos($username,'instagram') !== false)  */
	    		if($_post->getProductCount()>0)
	    			$class_css = "post_item_with_link";

	    			$instagramObj = @unserialize($_post->getInstagramData());

	    			$shareUrl = Mage::getBaseUrl('web')."instacatalog/feed/shareview/id/".$mediaId;
	    			$createDate = $_post->getCreatedTimestamp();
	    			if($createDate!=null){
	    				$createDate = date('d M Y', $createDate);
	    			}

	    			$instaCaption = json_decode($_post->getCaption());
	    			if(empty($instaCaption))
	    				$instaCaption = $_post->getCaption();

	    			$_options = json_decode($_post->getHotspots());
	    			$points = "";
	    			$productsLinks = "";
	    			$numberOfProducts = 0;
					if ($_options)
	    			foreach ($_options as $option){
	    				$sku = $option->text;
	    				$numberOfProducts += 1;
	    				$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
	    				$productName = "";
	    				$productId = 0;
	    				$productUrl = "";
	    				if($product){
	    				    $productName = $product->getName();
	    				    $productId = $product->getId();
	    				    $productUrl = $product->getProductUrl();
	    				}

	    				$arrayOfParentIds = Mage::getSingleton('catalog/product_type_configurable')->getParentIdsByChild($productId);
	    				$parentId = (count($arrayOfParentIds) > 0 ? $arrayOfParentIds[0] : null);

	    				if(!is_null($parentId)){
	    					$productUrl = Mage::getModel("catalog/product")->load($parentId)->getProductUrl();
	    				}

	    				$points .= '<a data-original-url="'.$productUrl.'"';
	    				$points .= ' href="'.$productUrl.'" target="_blank">';
	    				$points .= '<div id="fs_overlink_'.$productId.'" class="fs-overlink" ';
	    				$points .= ' data-link-id="'.$productId.'" ';
	    				$points .= ' style="position: absolute; color: rgb(34, 34, 34); top:'. (($option->top*100)/$option->imgH).'%; left:'.(($option->left*100)/$option->imgW).'%;">';
	    				$points .= '<div>'.$numberOfProducts.'</div>';
	    				$points .= '<div class="fs-overlink-text fs-overlink-text-right">';
	    				$points .= '<div class="fs-arrow-up"></div>';
	    				$points .= 'Shop it // " '.$productName.'</div></div></a>';

	    				$quickViewUrl = Mage::getBaseUrl('web').'quickview/index/index/id/'.$productId;

	    				$productsLinks .= '<div class="fs-text-link-container ">';
	    				$productsLinks .= '<a class="fs-text-product fs-link-list" ';
	    				$productsLinks .= 'data-original-url="'.$productUrl.'"';
	    				$productsLinks .= ' id="fs_link_'.$productId.'" target="_blank" data-link-id="'.$productId.'"';
	    				$productsLinks .= ' href="'.$productUrl.'"> <span class="fs-link-text-all">';
	    				$productsLinks .= '<span class="fs-link-text-number">'.$numberOfProducts.'</span> <span class="fs-slashes">  </span>';
	    				$productsLinks .= '<span class="fs-link-text"> '.$productName.' </span></span>';
                        $productsLinks.='<div class="quick_link link-button d-none d-xs-none d-md-none d-lg-block d-xl-block"><a href="'.$quickViewUrl.'sourceOfReq/quickview/" class="fancybox fancybox.iframe btn-quickview">Quick View</a></div>
                                  <div class="quick_link link-button d-block d-xs-block d-md-block d-lg-none d-xl-none"><a href="'.$productUrl.'" >Buy</a></div> ';
                        $productsLinks .= '<div class="fs-text-product-cta"></div></a></div>';
	    			}

	    			$strHtml .= '<div class="fs-entry-container">';
	    			$strHtml .= '<div id="fs-post-'.$mediaId.'" class="fs-timeline-entry" onclick="instagramView.show(this);" media-id="'.$mediaId.'"';
	 	    		$strHtml .= 'style="cursor: pointer; background-image: url('.$_post->getStandardResolution().');">';
	 	    		$strHtml .= '<div class="fs-text-container">';
	 	    		$strHtml .= '<div class="fs-service-icon">';
	 	    		$strHtml .= '<i media-id="'.$mediaId.'" class="fs-icon fs-fa-instagram"></i>';
	 	    		$strHtml .= '</div>';
	 	    		$strHtml .= '<span id="shop-'.$mediaId.'" class="instagram_shop_button '.$class_css.'" target="_blank">';
	 	    		$strHtml .= '<span class="shop-it-button">SHOP IT</span>';
	 	    		$strHtml .= '</span>';
	 	    		$strHtml .= '<div style="display: none;" id="details-insta-'.$mediaId.'"';
	 	    		$strHtml .= ' data-user-name="'.$_post->getUsername().'" data-img-url="'.$_post->getStandardResolution().'"';
	 	    		$strHtml .= ' data-create-date="'.$createDate.'" data-share-url="'.$shareUrl.'">';
	 	    		$strHtml .= '<div id="insta-caption-'.$mediaId.'" style="display: none;">'.$instaCaption.'</div>';
	 	    		$strHtml .= '<div id="insta-product-mark-'.$mediaId.'" style="display: none;">'.$points.'</div>';
	 	    		$strHtml .= '<div id="insta-product-details-'.$mediaId.'" style="display: none;">'.$productsLinks.'</div>';
	 	    		$strHtml .= '</div>';
	 	    		$strHtml .= '</div>';
	 	    		$strHtml .= '</div>';
	 	    		$strHtml .= '</div>';
			}


			$data = array('html'=>$strHtml);
    		$jsonData = json_encode(compact('success', 'message', 'data'));
    		$this->getResponse()->setHeader('Content-type', 'application/json');
    		$this->getResponse()->setBody($jsonData);
    }

    //AWS12
    public function loadMoreMobileAjaxAction(){
        $request = $this->getRequest()->getPost();
        $pageNo=2;
        if($request['page'])
            $pageNo=$request['page'];

        $helper=Mage::helper('allure_instacatalog');

        $limit=$helper->getLimit();
        $limit= 2;

        $feeds = Mage::getResourceModel('allure_instacatalog/feed_collection')
        ->addFieldToFilter('status', 1)
        ->setPageSize($limit)
        ->setCurPage($pageNo)
        ->addFieldToFilter('lookbook_mode',array('neq'=>1))
        ->setOrder('created_timestamp', 'desc');

        $strHtml = "";
		if ($feeds)
        foreach ($feeds as $_post) {
            $mediaId = $_post->getId();//getMediaId();
            $username = $_post->getUsername();
            $class_css = "post_item_without_link";
            /* if (strpos($username,'instagram') !== false)  */
            if($_post->getProductCount()>0)
                $class_css = "post_item_with_link";

            $instagramObj = @unserialize($_post->getInstagramData());

            $shareUrl = Mage::getBaseUrl('web')."instacatalog/feed/shareview/id/".$mediaId;
            $createDate = $_post->getCreatedTimestamp();
            if($createDate!=null){
                $createDate = date('d M Y', $createDate);
            }

            $instaCaption = json_decode($_post->getCaption());
            if(empty($instaCaption))
                $instaCaption = $_post->getCaption();

            $_options = json_decode($_post->getHotspots());
            $points = "";
            $productsLinks = "";
            $numberOfProducts = 0;
			if ($_options)
            foreach ($_options as $option){
                $sku = $option->text;
                $numberOfProducts += 1;
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
                $productName = "";
                $productId = 0;
                $productUrl = "";
                if($product){
                    $productName = $product->getName();
                    $productId = $product->getId();
                    $productUrl = $product->getProductUrl();
                }

                $arrayOfParentIds = Mage::getSingleton('catalog/product_type_configurable')->getParentIdsByChild($productId);
                $parentId = (count($arrayOfParentIds) > 0 ? $arrayOfParentIds[0] : null);

                if(!is_null($parentId)){
                    $productUrl = Mage::getModel("catalog/product")->load($parentId)->getProductUrl();
                }

                $points .= '<a data-original-url="'.$productUrl.'"';
                $points .= ' href="'.$productUrl.'" target="_blank">';
                $points .= '<div id="fs_overlink_'.$productId.'" class="fs-overlink" ';
                $points .= ' data-link-id="'.$productId.'" ';
                $points .= ' style="position: absolute; color: rgb(34, 34, 34); top:'. (($option->top*100)/$option->imgH).'%; left:'.(($option->left*100)/$option->imgW).'%;">';
                $points .= '<div>'.$numberOfProducts.'</div>';
                $points .= '<div class="fs-overlink-text fs-overlink-text-right">';
                $points .= '<div class="fs-arrow-up"></div>';
                $points .= 'Shop it // " '.$productName.'</div></div></a>';

                $productsLinks .= '<div class="fs-text-link-container ">';
                $productsLinks .= '<a class="fs-text-product fs-link-list" ';
                $productsLinks .= 'data-original-url="'.$productUrl.'"';
                $productsLinks .= ' id="fs_link_'.$productId.'" target="_blank" data-link-id="'.$productId.'"';
                $productsLinks .= ' href="'.$productUrl.'"> <span class="fs-link-text-all">';
                $productsLinks .= '<span class="fs-link-text-number">'.$numberOfProducts.' <span class="fs-slashes">  </span>';
                $productsLinks .= '<span class="fs-link-text"> // Shop it // '.$productName.' </span></span>';
                $productsLinks .= '<div class="fs-text-product-cta"></div></a></div>';
            }

            $strHtml .= '<div class="fs-entry-container">';
            $strHtml .= '<div id="fs-post-'.$mediaId.'" class="fs-timeline-entry" onclick="instagramView.show(this);" media-id="'.$mediaId.'"';
            $strHtml .= 'style="cursor: pointer; background-image: url('.$_post->getStandardResolution().');">';
            $strHtml .= '<div class="fs-text-container">';
            $strHtml .= '<div class="fs-service-icon">';
            $strHtml .= '<i media-id="'.$mediaId.'" class="fs-icon fs-fa-instagram"></i>';
            $strHtml .= '</div>';
            $strHtml .= '<span id="shop-'.$mediaId.'" class="instagram_shop_button '.$class_css.'" target="_blank">';
            $strHtml .= '<span class="shop-it-button">SHOP IT</span>';
            $strHtml .= '</span>';
            $strHtml .= '<div style="display: none;" id="details-insta-'.$mediaId.'"';
            $strHtml .= ' data-user-name="'.$_post->getUsername().'" data-img-url="'.$_post->getStandardResolution().'"';
            $strHtml .= ' data-create-date="'.$createDate.'" data-share-url="'.$shareUrl.'">';
            $strHtml .= '<div id="insta-caption-'.$mediaId.'" style="display: none;">'.$instaCaption.'</div>';
            $strHtml .= '<div id="insta-product-mark-'.$mediaId.'" style="display: none;">'.$points.'</div>';
            $strHtml .= '<div id="insta-product-details-'.$mediaId.'" style="display: none;">'.$productsLinks.'</div>';
            $strHtml .= '</div>';
            $strHtml .= '</div>';
            $strHtml .= '</div>';
            $strHtml .= '</div>';
        }

        $data = array('html'=>$strHtml);
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
    public function postAction(){

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
}
