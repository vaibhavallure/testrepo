<?php
/**
 * 
 * @author allure
 *
 */
class Allure_CustomUrl_Adminhtml_CustomurlController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->_title($this->__("Custom Urls"));
        $this->_addContent($this->getLayout()->createBlock('allure_customurl/adminhtml_customurl'));
        $this->renderLayout();
    }
    
    protected function _initFeed()
    {
        $urlId  = (int) $this->getRequest()->getParam('id');
        $customUrlObj    = Mage::getModel('allure_customurl/url');
        if ($urlId) {
            $customUrlObj->load($urlId);
        }
        Mage::register('current_customurl', $customUrlObj);
        return $customUrlObj;
    }
    
    public function newAction()
    {
        $this->_forward("edit");
    }
    
    public function editAction()
    {
        $urlId    = $this->getRequest()->getParam('id');
        $customUrlObj      = $this->_initFeed();
        if ($urlId && !$customUrlObj->getId()) {
            $this->_getSession()->addError(
                Mage::helper('allure_customurl')->__('This custom url no longer exists.')
                );
            $this->_redirect('*/*/');
            return;
        }
        $data = Mage::getSingleton('adminhtml/session')->getCustomUrlData(true);
        if (!empty($data)) {
            $customUrlObj->setData($data);
        }
        Mage::register('customurl_data', $customUrlObj);
        $this->loadLayout();
        $this->_title(Mage::helper('allure_customurl')->__('Custom Url Edit'))
        ->_title(Mage::helper('allure_customurl')->__('Custom Url'));
        if ($customUrlObj->getId()) {
            $this->_title($customUrlObj->getUrlId());
        } else {
            $this->_title(Mage::helper('allure_customurl')->__('Add Custom Url'));
        }
        
        $this->getLayout()
            ->getBlock("head")
            ->setCanLoadExtJs(true);
        $this
            ->_addContent($this->getLayout()->createBlock('allure_customurl/adminhtml_customurl_edit'))
            ->_addLeft($this->getLayout()->createBlock('allure_customurl/adminhtml_customurl_edit_tabs'))
        ;
        $this->renderLayout();
    }
    
    public function saveAction ()
    {
        $postData = $this->getRequest()->getPost("customurl");
        /* echo "<pre>";
        print_r($postData);die; */
        if ($postData) {
            try {
                $message = "New custom url created successfully.";
                
                $urlId = 0;
                $csutomUrlObj = Mage::getModel('allure_customurl/url');
                if(isset($postData["url_id"]) && !empty($postData["url_id"])){
                    $urlId = $postData["url_id"];
                    $urlId = $postData["url_id"];
                    $csutomUrlObj->load($urlId);
                    $message = "Existing custom url updated successfully.";
                }else{
                    unset($postData["url_id"]);
                }
                
                if(($postData["is_rewrite_url"] == 1)){
                    $options = (isset($postData["options"]) && !empty($postData["options"])) ? $postData["options"] : null;
                    $rewrite = Mage::getModel('core/url_rewrite');
                    if($csutomUrlObj->getRewriteUrlId()){
                        $rewrite->load($csutomUrlObj->getRewriteUrlId());
                    }else{
                        $rewriteCollection = Mage::getModel('core/url_rewrite')->getCollection();
                        $rewriteCollection->addFieldToFilter("id_path",$postData["target_path"]);
                        $rewriteCollection->addFieldToFilter("request_path",$postData["request_path"]);
                        $rewriteCollection->addFieldToFilter("target_path",$postData["target_path"]);
                        $rewriteCollection->addFieldToFilter("store_id",$postData["store_id"]);
                        $rewrite = $rewriteCollection->getFirstItem();
                    }
                    $rewrite->setStoreId($postData["store_id"]);
                    $rewrite->setCategoryId(null); //not for categories
                    $rewrite->setProductId(null);//not for products
                    $rewrite->setIsSystem(0); //it's not system generated - it won't get deleted on a reindex.
                    $rewrite->setRequestPath($postData["request_path"]);
                    $rewrite->setTargetPath($postData["target_path"]);
                    $rewrite->setIdPath($postData["target_path"]); //generate a random unique id path
                    $rewrite->setOptions($options); //301 redirect - permanent redirect
                    $rewrite->save();
                    $postData["rewrite_url_id"] = $rewrite->getId();
                    
                }else{
                    if($csutomUrlObj->getRewriteUrlId() ||(isset($postData["rewrite_url_id"]) && !empty($postData["rewrite_url_id"]))){
                        $rewrite = Mage::getModel('core/url_rewrite')->load($csutomUrlObj->getRewriteUrlId());
                        if($rewrite->getId()){
                            $rewrite->delete();
                        }
                        unset($postData["rewrite_url_id"]);
                    }
                }
                
                $csutomUrlObj->addData($postData)
                    ->save();
                
                Mage::getSingleton("adminhtml/session")->addSuccess(
                    Mage::helper("adminhtml")->__($message));
                Mage::getSingleton("adminhtml/session")->setCustomUrlData(false);
            } catch (Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                Mage::getSingleton("adminhtml/session")->setCustomUrlData($this->getRequest()
                    ->getPost("customurl"));
                $this->_redirect("*/*/edit", array(
                    "id" => $this->getRequest()
                    ->getParam("id")
                ));
                return;
            }
        }
        $this->_redirect("*/*/");
    }
}