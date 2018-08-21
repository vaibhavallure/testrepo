<?php
/**
 * Created by PhpStorm.
 * User: swapnil
 * Date: 8/14/18
 * Time: 6:38 PM
 */
class Allure_Virtualstore_Model_Resource_Virtualstore_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    public function _construct(){
        $this->_init("allure_virtualstore/virtualstore");
    }

}

