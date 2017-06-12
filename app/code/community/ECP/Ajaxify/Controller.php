<?php
/**
 * Load blocks via AJAX
 *
 * @category  Ajaxify
 * @package   Ajaxify
 * @author    Oleksandr Zirka <oleksandr.zirka@smile.fr>
 * @copyright 2013 Smile
 */
class ECP_Ajaxify_Controller
{
        /**
     * Analyze block names and send block content or error message
     *
     * @return void
     */
    public function run()
    {  
        $helper = new ECP_Ajaxify_Helper_Data;
        $helper->initLayout();
        $blocks = array();
        foreach (Mage::app()->getRequest()->getParams() as $key => $val) {
            if (isset($val) && !empty($val)){
                $cacheId = $helper->getBlockCacheId($key);
                if (Mage::registry($cacheId)!=$val){
                        Mage::app()->getCache()->remove($cacheId);
                        Mage::register($cacheId, $val);
                }  
                Mage::log($key,Zend_Log::DEBUG,'abc',true);
                $val = @split(',', $val);
            }
            
            $blocks[$key] = $helper->getBlockContent($key,$val);
        }
        $helper->sendBlockContent($helper->jsonEncode($blocks));
    }
}
