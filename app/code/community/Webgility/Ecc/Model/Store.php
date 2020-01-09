<?php
/*� Copyright 2019 Webgility Inc
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 
*/
class Webgility_Ecc_Model_Store
{
    private $Store = array();

    public function setStoreID($StoreID)
    {
        $this->Store['StoreID'] = $StoreID ? $StoreID :'';
    }
    public function setStoreName($StoreName)
    {
        $this->Store['StoreName'] = $StoreName ? $StoreName : '';
    }
    public function setStoreWebsiteId($StoreWebsiteId)
    {
        $this->Store['StoreWebsiteId'] = $StoreWebsiteId ? $StoreWebsiteId : '';
    }
    public function setStoreWebsiteName($StoreWebsiteName)
    {
        $this->Store['StoreWebsiteName'] = $StoreWebsiteName ? $StoreWebsiteName : '';
    }
    public function setStoreRootCategoryId($StoreRootCategoryId)
    {
        $this->Store['StoreRootCategoryId'] = $StoreRootCategoryId ? $StoreRootCategoryId : '';
    }
    public function setStoreDefaultStoreId($StoreDefaultStoreId)
    {
        $this->Store['StoreDefaultStoreId'] = $StoreDefaultStoreId ? $StoreDefaultStoreId : '';
    }
    public function getStore()
    {Mage::log('In Store.php',Zend_Log::DEBUG,'ind.log',true);
        return $this->Store;
    }
}