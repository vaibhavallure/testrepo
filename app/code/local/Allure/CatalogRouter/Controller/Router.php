<?php 
/**
 * 
 * @author allure
 *
 */
class Allure_CatalogRouter_Controller_Router extends Mage_Core_Controller_Varien_Router_Standard
{
    public function match(Zend_Controller_Request_Http $request)
    {
        $identifier = trim($request->getPathInfo(), '/');
        $colorArray = array( "-white", "-yellow", "-rose", "-black" );
        $currentColor = "";
        foreach ($colorArray as $color){
            if (preg_match("/{$color}/", $identifier)) {
                $colors = explode($color, $identifier);
                $newColor = $color . $colors[1];
                $currentColor = str_replace(".html" ,"",$newColor);
                break;
            }
        }
        
        if($currentColor){
            $identifier = str_replace($currentColor,"",$identifier);
            $request->setPathInfo($identifier);
            
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
            $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
            
            $select = $connection->select()->from($tablePrefix . 'core_url_rewrite')->
            where('request_path = ? AND store_id = ' . Mage::app()->getStore()->getId(), $identifier);
            $rewrite = $connection->fetchRow($select);
            
            $urlRewrite = Mage::getModel('core/url_rewrite')->load($rewrite['url_rewrite_id']);
            
            if($urlRewrite->getId()){
                $request->setModuleName("catalog");
                $request->setControllerName("product");
                $request->setActionName("view");
                $request->setParam("id", $urlRewrite->getData("product_id"));
                return true;
            }
            
        }
        
        return false;
    }
}