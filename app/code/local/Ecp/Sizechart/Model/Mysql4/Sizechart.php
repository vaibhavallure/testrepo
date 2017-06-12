<?php
/**
 * Description of Sizechart
 *
 * @category    Ecp
 * @package     Ecp_Sizechart
 */
class Ecp_Sizechart_Model_Mysql4_Sizechart extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the sizechart_id refers to the key field in your database table.
        $this->_init('ecp_sizechart/sizechart', 'sizechart_id');
    }
}