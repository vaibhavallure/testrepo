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
 * @package     Ecp_Video
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Video
 *
 * @category    Ecp
 * @package     Ecp_Video
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Video_Model_Video extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('ecp_video/video');
    }

    public function generateKey()
    {
    	$conn = Mage::getSingleton('core/resource')->getConnection('core_write');
    	$qry = "SELECT uuid() as uuid;";         
        $resultat = $conn->query($qry);
        $row = $resultat->fetch();
        return $row['uuid'];
    }
}