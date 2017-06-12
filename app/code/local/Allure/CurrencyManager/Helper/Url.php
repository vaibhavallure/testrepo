<?php
/**
 * @category   Allure
 * @package    Allure_CurrencyManager
 * @copyright  Copyright (c) 2016 Allure Inc (http://www.allureinc.co)
 * @contacts   support@allureinc.co
 */

class Allure_CurrencyManager_Helper_Url extends Mage_Directory_Helper_Url
{
    /**
     * Retrieve switch currency url
     *
     * @param array $params Additional url params
     * @return string
     */
    public function getSwitchCurrencyUrl($params = array())
    {
        $params = is_array($params) ? $params : array();

        $url = $this->getCurrentUrl();
        
        $params[Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED] = Mage::helper('core')->urlEncode($url);


        return $this->_getUrl('directory/currency/switch', $params);
    }

}
