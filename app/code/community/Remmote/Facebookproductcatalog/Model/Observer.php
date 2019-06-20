<?php 
/**
 * @extension   Remmote_Facebookproductcatalog
 * @author      Remmote    
 * @copyright   2016 - Remmote.com
 * @descripion  Observer
 */
class Remmote_Facebookproductcatalog_Model_Observer
{

   /**
    * Method called by Magento Crontab
    * Export product catalog to media/facebook_productcatalog folder
    * @return [type]
    * @author edudeleon
    * @date   2016-11-29
    */
    public function export_catalog(){
        //Intiantate product catalog model
        $product_catalog_model = Mage::getModel('remmote_facebookproductcatalog/productcatalog');

        //Load websites
        $websites = Mage::app()->getWebsites();
        if(count($websites) > 1){
            foreach ($websites as $website) {
                //Checks if extension is enable for webiste
                if (Mage::helper('remmote_facebookproductcatalog')->isEnabled($website->getId(), TRUE)){
                    //Call method that exports the product catalog
                    $product_catalog_model->exportCatalog($website->getCode());          
                }
            }
        } else {
            //Call method that exports the product catalog
            if(Mage::helper('remmote_facebookproductcatalog')->isEnabled()) {
                $product_catalog_model->exportCatalog();
            }
        }
    }
}
