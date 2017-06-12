<?php
/**
 * Process AJAX requests
 *
 * @category  StageStores_Ajaxify
 * @package   StageStores
 * @author    Oleksandr Zirka <oleksandr.zirka@smile.fr>
 * @copyright 2013 Smile
 */
class ECP_Ajaxify_Model_Processor
{
    /**
     * Url pattern for AJAX requests
     */
    const AJAXIFY_URL_PATTERN = '^(\/index\.php)?\/ajaxify\/.*';

    /**
     * Class constructor
     */
    public function __construct()
    {
        if ($pattern = preg_match_all('/'.self::AJAXIFY_URL_PATTERN.'/', Mage::app()->getRequest()->getRequestUri(), $matches)) {

            if (false && !Mage::app()->getRequest()->isXmlHttpRequest()) {
                Mage::app()->getResponse()
                           ->setHttpResponseCode(404)
                           ->setBody(
                               'Oops! It’s looking like you may have taken a wrong turn.'.
                               'Don’t worry... it happens to the best of us.'
                           )
                           ->sendResponse();
            } else {
                $ajaxify = new ECP_Ajaxify_Controller();
                $ajaxify->run();
            }
            exit; // Avoid dispatching front controller
        }
    }
    
    /**
     * Get page content from cache storage
     *
     * @param string $content
     * @return string | false
     */
    public function extractContent($content)
    {
        if (!$content && $this->isAllowed()) {

            $subprocessorClass = $this->getMetadata('cache_subprocessor');
            if (!$subprocessorClass) {
                return $content;
            }

            /*
             * @var Enterprise_PageCache_Model_Processor_Default
             */
            $subprocessor = new $subprocessorClass;
            $cacheId = $this->prepareCacheId($subprocessor->getPageIdWithoutApp($this));

            $content = Mage::app()->loadCache($cacheId);

            if ($content) {
                if (function_exists('gzuncompress')) {
                    $content = gzuncompress($content);
                }
                $content = $this->_processContent($content);

                // renew recently viewed products
                $productId = Mage::app()->loadCache($this->getRequestCacheId() . '_current_product_id');
                $countLimit = Mage::app()->loadCache($this->getRecentlyViewedCountCacheId());
                if ($productId && $countLimit) {
                    Enterprise_PageCache_Model_Cookie::registerViewedProducts($productId, $countLimit);
                }
            }

        }
        return $content;
    }
    /**
     * Check if processor is allowed for current HTTP request.
     * Disable processing HTTPS requests and requests with "NO_CACHE" cookie
     *
     * @return bool
     */
    public function isAllowed()
    {
        if (!isset($this->_requestId)) {
            return false;
        }
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            return false;
        }
        if (isset($_COOKIE['NO_CACHE'])) {
            return false;
        }
        if (isset($_GET['no_cache'])) {
            return false;
        }
        return true;
    }
}
