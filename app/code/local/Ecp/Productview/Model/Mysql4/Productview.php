<?php
/**
 * Ecp
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future. If you wish to customize for your
 * needs please refer to Ecp Event-Observer for more information.
 * 
 * @category    Ecp
 * @package     Ecp_Productview
 * @copyright   Copyright (c) 2010 Ecp Inc. (http://www.ecp.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Slideshow
 *
 * @category    Ecp
 * @package     Ecp_Slideshow
 */
class Ecp_Productview_Model_Mysql4_Productview extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the productview_id refers to the key field in your database table.
        $this->_init('ecp_productview/productview', 'productview_id');
        
    }
}