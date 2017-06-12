<?php
/**
 * Description of Sizechart
 *
 * @category    Ecp
 * @package     Ecp_Sizechart
 */
class Ecp_Sizechart_Model_Sizechart extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('ecp_sizechart/sizechart');
    }
}