<?php
/**
 * Description of Celebrities
 *
 * @category    Ecp
 * @package     Ecp_Celebrities
 */
class Ecp_Celebrities_Model_Celebrities extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('ecp_celebrities/celebrities');
    }
}