<?php
class Ecp_Storelogo_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getConfigCmsBlock(){        
        return Mage::getStoreConfig('ecp_storelogo/storelogo/value');
    }
}