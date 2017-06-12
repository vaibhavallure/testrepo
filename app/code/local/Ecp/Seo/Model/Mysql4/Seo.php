<?php
/**
 * @category    Ecp
 * @package     Ecp_Seo
 */

/**
 * Description of Seo
 *
 * @category    Ecp
 * @package     Ecp_Seo
 */
class Ecp_Seo_Model_Mysql4_Seo extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the seo_id refers to the key field in your database table.
        $this->_init('ecp_seo/seo', 'seo_id');
    }
}