<?php
/**
 * Created by allure.
 * User: indrajitpatil
 * Date: 28/03/19
 * Time: 15:25 PM
 */
class Allure_BrownThomas_Model_Resource_Price extends Mage_Core_Model_Resource_Db_Abstract{
    protected function _construct()
    {
        $this->_init('brownthomas/price','row_id');
    }

}