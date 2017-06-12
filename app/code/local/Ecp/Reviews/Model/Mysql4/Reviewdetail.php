<?php
/**
 * Entrepids
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
 * needs please refer to Entrepids Event-Observer for more information.
 * 
 * @category    Ecp
 * @package     Ecp_Reviews
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Reviews
 *
 * @category    Ecp
 * @package     Ecp_Reviews
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Reviews_Model_Mysql4_Reviewdetail extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the reviews_id refers to the key field in your database table.
        $this->_init('ecp_reviews/reviewdetail', 'detail_id');
    }
}