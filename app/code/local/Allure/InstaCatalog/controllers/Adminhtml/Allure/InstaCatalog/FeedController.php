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
 * Feed admin controller
 *
 * @category    Allure
 * @package     Allure_InstaCatalog
 * @author      Team Allure <extensions@allureinc.co>
 */
class Allure_InstaCatalog_Adminhtml_Allure_InstaCatalog_FeedController extends Allure_InstaCatalog_Controller_Adminhtml_AdvInstagram
{
    /**
     * init the feed
     *
     * @access protected
     * @return Allure_InstaCatalog_Model_Feed
     */
    protected function _initFeed()
    {
        $feedId  = (int) $this->getRequest()->getParam('id');
        $feed    = Mage::getModel('allure_instacatalog/feed');
        if ($feedId) {
            $feed->load($feedId);
        }
        Mage::register('current_feed', $feed);
        return $feed;
    }
    
    /**
     * default action
     *
     * @access public
     * @return void
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_title(Mage::helper('allure_instacatalog')->__('Instagram'))
        ->_title(Mage::helper('allure_instacatalog')->__('Feeds'));
        $this->renderLayout();
    }
    
    
    public function shopAction()
    {
        $this->loadLayout();
        $this->_title(Mage::helper('allure_instacatalog')->__('Instagram'))
        ->_title(Mage::helper('allure_instacatalog')->__('Feeds'));
        $this->renderLayout();
    }
    
    
    /**
     * grid action
     *
     * @access public
     * @return void
     */
    public function gridAction()
    {
        $this->loadLayout()->renderLayout();
    }
    
    /**
     * edit feed - action
     *
     * @access public
     * @return void
     */
    public function editAction()
    {
        $feedId    = $this->getRequest()->getParam('id');
        $feed      = $this->_initFeed();
        if ($feedId && !$feed->getId()) {
            $this->_getSession()->addError(
                Mage::helper('allure_instacatalog')->__('This feed no longer exists.')
                );
            $this->_redirect('*/*/');
            return;
        }
        $data = Mage::getSingleton('adminhtml/session')->getFeedData(true);
        if (!empty($data)) {
            $feed->setData($data);
        }
        Mage::register('feed_data', $feed);
        $this->loadLayout();
        $this->_title(Mage::helper('allure_instacatalog')->__('Instagram'))
        ->_title(Mage::helper('allure_instacatalog')->__('Feeds'));
        if ($feed->getId()) {
            $this->_title($feed->getMediaId());
        } else {
            $this->_title(Mage::helper('allure_instacatalog')->__('Add feed'));
        }
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        $this->renderLayout();
    }
    
    /**
     * new feed action
     *
     * @access public
     * @return void
     */
    public function newAction()
    {
        //$this->_forward('edit');
        $feedId    = $this->getRequest()->getParam('id');
        $feed      = $this->_initFeed();
        if ($feedId && !$feed->getId()) {
            $this->_getSession()->addError(
                Mage::helper('allure_instacatalog')->__('This feed no longer exists.')
                );
            $this->_redirect('*/*/');
            return;
        }
        $data = Mage::getSingleton('adminhtml/session')->getFeedData(true);
        if (!empty($data)) {
            $feed->setData($data);
        }
        Mage::register('feed_data', $feed);
        $this->loadLayout();
        $this->_title(Mage::helper('allure_instacatalog')->__('Instagram'))
        ->_title(Mage::helper('allure_instacatalog')->__('Feeds'));
        if ($feed->getId()) {
            $this->_title($feed->getMediaId());
        } else {
            $this->_title(Mage::helper('allure_instacatalog')->__('Add feed'));
        }
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        $this->renderLayout();
    }
    
    /**
     * save feed - action
     *
     * @access public
     * @return void
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost('feed')) {
            try {
                $feed = $this->_initFeed();
                $productIds = array();
                if(!empty($data[hotspots]) && isset($data[hotspots])){
                    $hotspots = json_decode($data[hotspots]);
                    foreach($hotspots as $key) {
                        //var_dump($key);
                        if($key->product);
                        $productIds[] = $key->product;
                    }
                    if(!empty($productIds)){
                        $data['product_ids'] = implode(",", $productIds);
                    }
                }
                
                $feed->addData($data);
                $feed->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('allure_instacatalog')->__('Feed was successfully saved')
                    );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $feed->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFeedData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('allure_instacatalog')->__('There was a problem saving the feed.')
                    );
                Mage::getSingleton('adminhtml/session')->setFeedData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('allure_instacatalog')->__('Unable to find feed to save.')
            );
        $this->_redirect('*/*/');
    }
    
    /**
     * delete feed - action
     *
     * @access public
     * @return void
     */
    public function deleteAction()
    {
        if ( $this->getRequest()->getParam('id') > 0) {
            try {
                $feed = Mage::getModel('allure_instacatalog/feed');
                $feed->setId($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('allure_instacatalog')->__('Feed was successfully deleted.')
                    );
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('allure_instacatalog')->__('There was an error deleting feed.')
                    );
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                Mage::logException($e);
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('allure_instacatalog')->__('Could not find feed to delete.')
            );
        $this->_redirect('*/*/');
    }
    
    /**
     * mass delete feed - action
     *
     * @access public
     * @return void
     */
    public function massDeleteAction()
    {
        $feedIds = $this->getRequest()->getParam('feed');
        if (!is_array($feedIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('allure_instacatalog')->__('Please select feeds to delete.')
                );
        } else {
            try {
                foreach ($feedIds as $feedId) {
                    $feed = Mage::getModel('allure_instacatalog/feed');
                    $feed->setId($feedId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('allure_instacatalog')->__('Total of %d feeds were successfully deleted.', count($feedIds))
                    );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('allure_instacatalog')->__('There was an error deleting feeds.')
                    );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/index');
    }
    
    /**
     * mass status change - action
     *
     * @access public
     * @return void
     */
    public function massStatusAction()
    {
        $feedIds = $this->getRequest()->getParam('feed');
        if (!is_array($feedIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('allure_instacatalog')->__('Please select feeds.')
                );
        } else {
            try {
                foreach ($feedIds as $feedId) {
                    $feed = Mage::getSingleton('allure_instacatalog/feed')->load($feedId)
                    ->setStatus($this->getRequest()->getParam('status'))
                    ->setIsMassupdate(true)
                    ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d feeds were successfully updated.', count($feedIds))
                    );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('allure_instacatalog')->__('There was an error updating feeds.')
                    );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/index');
    }
    
    /**
     * export as csv - action
     *
     * @access public
     * @return void
     */
    public function exportCsvAction()
    {
        $fileName   = 'feed.csv';
        $content    = $this->getLayout()->createBlock('allure_instacatalog/adminhtml_feed_grid')
        ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }
    
    /**
     * export as MsExcel - action
     *
     * @access public
     * @return void
     */
    public function exportExcelAction()
    {
        $fileName   = 'feed.xls';
        $content    = $this->getLayout()->createBlock('allure_instacatalog/adminhtml_feed_grid')
        ->getExcelFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }
    
    /**
     * export as xml - action
     *
     * @access public
     * @return void
     */
    public function exportXmlAction()
    {
        $fileName   = 'feed.xml';
        $content    = $this->getLayout()->createBlock('allure_instacatalog/adminhtml_feed_grid')
        ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }
    
    /**
     * Check if admin has permissions to visit related pages
     *
     * @access protected
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('allure_instacatalog/feed');
    }
    
    public function searchAjaxAction(){
        $searchQuery = $this->getRequest()->getParam('query');
        
        $collection = Mage::getModel('catalog/product')->getCollection()
        ->addAttributeToSelect('id,name,sku');
        /* ->addAttributeToFilter('name', ['like' => $searchQuery . '%']);
         $collection->addAttributeToFilter('sku', ['like' => $searchQuery . '%']); */
        
        $collection->addAttributeToFilter( array(
            array('attribute'=> 'name','like' => $searchQuery . '%'),
            array('attribute'=> 'sku','like' => $searchQuery . '%'),
        ) )
        ->addAttributeToFilter('type_id', array('eq' => 'configurable'));
        
        //Mage::log($collection->getSelect()->__toString(),Zend_Log::DEBUG,'abc',true);
        
        $data = array('result'=>$collection->getData());
        $jsonData = json_encode(compact('success', 'message', 'data'));
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }
    
    
    public function saveAjaxAction(){
        $params     = $this->getRequest()->getParams();
        $feedId = $params['feed_id'];
        $hotspots = $params['hotspots'];
        $feed    = Mage::getModel('allure_instacatalog/feed');
        if(!empty($feedId) && !empty($hotspots)){
            //$status = 0;
            //if($params['status']=="true")
            //	$status=1;
                $feed->load($feedId);
                
                $productIds = array();
                foreach(json_decode($hotspots) as $key) {
                    //var_dump($key);
                    if($key->product)
                        $productIds[] = $key->product;
                        else {
                            $productId=Mage::getModel("catalog/product")->getIdBySku($key->text);
                            if($productId)
                                $productIds[]=$productId;
                        }
                }
                Mage::log($productIds,Zend_log::DEBUG,'abc',true);
                $products="";
                if(!empty($productIds)){
                    $products = implode(",", $productIds);
                }
                $feed->setProductIds($products);
                
                //$feed->setStatus($status);
                $cnt = count(json_decode($hotspots));
                $feed->setProductCount($cnt);
                $feed->setHotspots($hotspots)->save();
        }
        $response = array();
        if (!is_null($feed)) {
            if ($feed->getId()!=0)
            {
                $result= 1;
                $length = count(json_decode($hotspots));
                $response['success']=1;
                $response['length']=$length;
                $response['feedstatus']=($feed->getStatus())?true:false;
            }
        }
        else
        {
            $result =0;
            $response['success']=0;
        }
        $response = json_encode($response);
        $this->getResponse()->setBody($response);
    }
    
    public function saveFeedStatusAjaxAction(){
        $params     = $this->getRequest()->getParams();
        $feedId = $params['feed_id'];
        $feed    = Mage::getModel('allure_instacatalog/feed');
        if(!empty($feedId) ){
            $status = 0;
            if($params['status']=="true")
                $status=1;
                $feed->load($feedId);
                $feed->setStatus($status)->save();
        }
        $response = array();
        if (!is_null($feed)) {
            if ($feed->getId()!=0)
            {
                $result= 1;
                $response['success']=1;
                $response['feedstatus']=($feed->getStatus())?true:false;
            }
        }
        else
        {
            $result =0;
            $response['success']=0;
        }
        $response = json_encode($response);
        $this->getResponse()->setBody($response);
    }
    
    public function syncFeedAction(){
        Mage::getModel('allure_instacatalog/cron')->syncFeeds();
        $this->_redirect('*/*/index');
    }
    
    public function syncShopFeedAction(){
        Mage::getModel('allure_instacatalog/cron')->syncShopFeeds();
        $this->_redirect('*/*/shop');
    }
    public function syncExistingFeedAction(){
        $params = $this->getRequest()->getParams();
        $allure = $params['type'];
        $key = $params['akey'];
        $url = "https://foursixty.com/api/v2/MariaTash/admin-timeline/?admin=true&scheduled=false&uploaded=false&from_connector=10037";
        if(!empty($allure) && !empty($key)){
            if($allure=="allure" && $key=="mariatash")
                $this->syncExistingFeed($url);
        }
        
    }
    
    private function syncExistingFeed($url){
        
        $cookie = Mage::getStoreConfig('allure_instacatalog/shop_feed/extra_cookie');
        if(empty($cookie))
            $cookie = 'sessionid=atqz4tu33edmwy7bxwlhhyo7xbbdcjg2; _gat=1; csrftoken=ZWxa4wwgUCHhiy4YCBRru3ssicDxxHGU; _cioid=prod_8142; _ga=GA1.2.1811701776.1483679319';
            
            $headers   = array();
            $headers[] = 'Cookie: ' . $cookie;
            
            while($url!=null){
                $options = array(
                    CURLOPT_RETURNTRANSFER => true,   // return web page
                    CURLOPT_HEADER         => false,  // don't return headers
                    CURLOPT_FOLLOWLOCATION => true,   // follow redirects
                    CURLOPT_MAXREDIRS      => 10,     // stop after 10 redirects
                    CURLOPT_ENCODING       => "",     // handle compressed
                    CURLOPT_USERAGENT      => "test", // name of client
                    CURLOPT_AUTOREFERER    => true,   // set referrer on redirect
                    CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
                    CURLOPT_TIMEOUT        => 120,    // time-out on response
                    CURLOPT_HTTPHEADER =>  $headers,
                );
                
                $ch = curl_init($url);
                curl_setopt_array($ch, $options);
                $response  = json_decode(curl_exec($ch));
                curl_close($ch);
                
                foreach ($response->results as $post){
                    $postLoad = Mage::getModel('allure_instacatalog/feed')->load($post->resource_url,'username');
                    if($postLoad->getUsername()!=$post->resource_url)
                    {
                        $caption = json_encode($post->title);
                        $mode = 3; //existing old instagram
                        $timestamp = strtotime($post->time_posted);
                        
                        $feedData = array('username'=> $post->resource_url,
                            'status'=>'1','caption'=>$caption,'image'=>$post->main_image_url,
                            'standard_resolution'=>$post->main_image_url,
                            'text'=>$caption,
                            'created_timestamp'=>$timestamp,'lookbook_mode'=>$mode
                        );
                        
                        $post = Mage::getModel('allure_instacatalog/feed');
                        $post->addData($feedData);
                        $insertId =$post->save()->getId();
                    }
                }
                
                $url = $response->next;
            }
            $this->_redirect('*/*/index');
            
    }
    public function reloadimageAction(){
        $request = $this->getRequest()->getPost();
        if(!empty($request['media_id']) || !empty($request['username'])){
            $collection = Mage::getModel('allure_instacatalog/feed')
            ->getCollection()
            ->addFieldToFilter('media_id',array('eq'=>$request['media_id']));
            $collection=$collection->getFirstItem();
            if(empty($collection->getSize())){
                $collection = Mage::getModel('allure_instacatalog/feed')
                ->getCollection()
                ->addFieldToFilter('username',array('eq'=>$request['username']));
                $collection=$collection->getFirstItem();
            }
            $media_id=$request['media_id'];
            $access_token=$access_token = Mage::getStoreConfig('allure_instacatalog/feed/access_token');
            if (!empty($request['media_id'])) {
                $url = 'https://api.instagram.com/v1/media/' . $media_id . '?access_token=' . $access_token;
                $data = file_get_contents($url);
                $response = json_decode($data);
                $image = $response->data->images->standard_resolution->url;
                Mage::log($image, Zend_log::DEBUG, 'ajay.log', true);
                if (! empty($image)) {
                    $collection->setStandardResolution($image);
                    $collection->save();
                }else{
                    $api = file_get_contents("https://api.instagram.com/oembed/?url=".$request['username']);
                    $apiObj = json_decode($api,true);
                    if(!empty($apiObj['thumbnail_url'])) {
                       $collection->setStandardResolution($apiObj['thumbnail_url']);
                       $collection->save();
                    }
                }
            }else{
                $api = file_get_contents("https://api.instagram.com/oembed/?url=".$request['username']);
                $apiObj = json_decode($api,true);
                if(!empty($apiObj['thumbnail_url'])) {
                    $collection->setStandardResolution($apiObj['thumbnail_url']);
                    $collection->save();
                }
            }
        }
        
        $jsonData = json_encode(compact('success', 'message', 'data'));
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }
}
