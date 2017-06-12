<?php

class ECP_Ajaxify_Helper_Data
    extends Mage_Core_Helper_Data
{   
    /**
     * Cache lifetime for AJAX blocks
     */
    const AJAX_BLOCK_CACHE_LIFETIME = 6000;

    /**
     * @var Mage_Core_Model_Layout Layout
     */
    protected $_layout;

    /**
     * @var Varien_Cache_Core Cache instance
     */
    protected $_cache;

    /**
     * Init cache
     */
    public function __construct()
    {
        $this->_cache = Mage::app()->getCache();
        Mage::app()->setCurrentStore(Mage_Core_Model_Store::DEFAULT_CODE);
    }   
    
    /**
     * Get block cache id
     *
     * @param string $blockName Block name
     *
     * @return string
     */
    public function getBlockCacheId($blockName = '')
    {
        $cacheId = false;
        if (isset($_COOKIE['frontend'])) {
            $sessionId = $_COOKIE['frontend'];
            $cacheId = strtolower('ajaxify_'.$blockName.'_'.$sessionId);
        }
        return $cacheId;
    }

    /**
     * Send error response
     *
     * @return void
     */
    static public function sendErrorResponse()
    {
        Mage::app()->getResponse()
            ->setHttpResponseCode(404)
            ->setBody(
                'Oops! It’s looking like you may have taken a wrong turn.'.
                'Don’t worry... it happens to the best of us.'
            )
            ->sendResponse();
    }

    /**
     * Generate url for AJAX request by block name
     *
     * @param string $blockName  Block name
     * @param bool   $secureFlag Secure Flag
     *
     * @return string
     */
    public function getXHRUrl($secureFlag = false)
    {           
        return Mage::getBaseUrl(Mage_Core_model_Store::URL_TYPE_WEB, $secureFlag).'ajaxify/';
    }
    
    /**
     * Get block content from cache or generate it
     *
     * @param string $blockName Block name
     *
     * @return bool|string
     */
    public function getBlockContent($blockName, $params)
    {
        $content = $this->_getCachedBlockContent($blockName);       
        if ($content === false) {
            $content = $this->_generateBlockContent($blockName, $params);
            if ($content !== false) {
                $this->_saveCachedBlockContent($blockName, $content);
            }
        }
        return $content;
    }

    /**
     * Send block content or error message if content is 'false'
     *
     * @param string $content Block content
     *
     * @return void
     */
    public function sendBlockContent($content)
    {   
        if ($content !== false) {
            $this->_sendResponse($content);
        } else {
            $this->sendErrorResponse();
        }
    }

    /**
     * Init Magento layout
     *
     * @return void
     */
    function initLayout()
    {
        Mage::getSingleton('core/session', array('name' => 'frontend'));
        Mage::app()->getConfig()->init();
        Mage::app()->getFrontController()->init();
        //@TODO: set valid referer url for the link 'Delete from cart'
        //Mage::app()->getRequest()->setParam('referer', base64_encode($this->_getRefererUrl()));

        // Add specific layout handles to our layout and then load them
        $this->_layout = Mage::app()->getLayout();
        $this->_layout->getUpdate()
            ->addHandle('ecp_ajaxify')
            ->load();

        // Generate blocks, but XML from previously loaded layout handles must be loaded first
        $this->_layout->generateXml()
               ->generateBlocks();
    }

    /**
     * Get cached block content or false if block content is not found into cache
     *
     * @param string $blockName Block name
     *
     * @return bool|mixed
     */
    protected function _getCachedBlockContent($blockName)
    {
        $content = false;
        $cacheId = $this->getBlockCacheId($blockName);
        if ($cacheId!=false) {
            if ($content = $this->_cache->load($cacheId)) {
                if (function_exists('gzuncompress')) {
                    $content = gzuncompress($content);
                }
            }
        }
        return $content;
    }

    /**
     * Save block content into cache
     *
     * @param string $blockName Block name
     * @param string $content   Block content
     *
     * @return void
     */
    protected function _saveCachedBlockContent($blockName, $content)
    {
        $cacheId = $this->getBlockCacheId($blockName);
        if ($cacheId!=false) {
            if (function_exists('gzcompress')) {
                $content = gzcompress($content);
            }

            $this->_cache->save(
                $content,
                $cacheId,
                array(Mage_Catalog_Model_Product::CACHE_TAG),
                self::AJAX_BLOCK_CACHE_LIFETIME
            );
        }
    }

    /**
     * Generate block content or return false if block is not found
     *
     * @param string $blockName Block name
     *
     * @return string|bool
     */
    protected function _generateBlockContent($blockName, $params)
    {   
        $block = $this->_layout->getBlock($blockName);
        if ($block) {
            $output = $block->setAjaxifyParams($params)->toHtml();
        } else {
            $output = false;
        }
        return $output;
    }

    /**
     * Send response to user
     *
     * @param string $response Response
     *
     * @return void
     */
    protected function _sendResponse($response)
    {   
        Mage::app()->getResponse()
            ->setHeader('Cache-Control', 'max-age=0, no-cache, no-store, must-revalidate')
            ->setHeader('Expires', '-1')
            ->setHeader('Content-Type', 'application/json')
            ->setBody($response)
            ->sendResponse();
    }

    /**
     * Identify referer url via all accepted methods (HTTP_REFERER, regular or base64-encoded request param)
     *
     * @return string
     */
    protected function _getRefererUrl()
    {
        $refererUrl = Mage::app()->getRequest()->getServer('HTTP_REFERER');
        if ($url = Mage::app()->getRequest()->getParam(Mage_Core_Controller_Varien_Action::PARAM_NAME_REFERER_URL)) {
            $refererUrl = $url;
        }
        if ($url = Mage::app()->getRequest()->getParam(Mage_Core_Controller_Varien_Action::PARAM_NAME_BASE64_URL)) {
            $refererUrl = Mage::helper('core')->urlDecode($url);
        }
        if ($url = Mage::app()->getRequest()->getParam(Mage_Core_Controller_Varien_Action::PARAM_NAME_URL_ENCODED)) {
            $refererUrl = Mage::helper('core')->urlDecode($url);
        }

        return $refererUrl;
    }
}
