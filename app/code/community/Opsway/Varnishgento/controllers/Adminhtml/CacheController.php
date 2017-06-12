<?php
/**
 * Varnish adminhtml cache controller
 *
 * @category Opsway
 * @package  Opsway_Varnishgento
 * @author   Ivan Shcherbakov <ivan.shcherbakov@smile.fr>
 * @author   Oleksandr Zirka <olzir@smile.fr>
 * @author   Alexandr Vronskiy <alvro@opsway.com>
 */

include_once("Mage/Adminhtml/controllers/CacheController.php");
class Opsway_Varnishgento_Adminhtml_CacheController extends Mage_Adminhtml_CacheController
{
    /**
     * Action flush varnish cache by URL
     */
    public function flushVarnishAction()
    {
        try {
            $url = $this->getRequest()->getParam('purge_url');
            $url = str_replace('?','.',$url);
            Mage::helper('opsway_varnishgento')->flushByUrlManually(trim($url)?$url:".*");
            $url = trim($url)?$url:'All';
        	$this->_getSession()->addSuccess(Mage::helper('adminhtml')->__("Varnish cache has been flushed. Frontal caches (%s) will be updated in several minutes", $url));
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper('adminhtml')->__("Error: %s", $e->getMessage()));
        }

    	$this->_redirect('*/*');
    }


    // Fixed SUPEE 6285 ACL bug
    function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config/opsway_varnishgento');
    }
}
