<?php
/**
 * Description of Celebrities_outfits
 *
 * @category    Ecp
 * @package     Ecp_Outfits
 */
class Ecp_Celebrities_Model_Outfits extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('ecp_celebrities/outfits');
    }
}