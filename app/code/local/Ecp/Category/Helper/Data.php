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
 * @package     Ecp_Citysearch
 * @copyright   Copyright (c) 2010 Ecp Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Citysearch
 *
 * @category    Ecp
 * @package     Ecp_Citysearch
 * @author      Ecp Core Team <core@entrepids.com>
 */
class Ecp_Category_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getAllArrayValues($array, $k2f) {
        $tmp = array();
        foreach ($array as $key => $value) {
            $tmp[] = $value[$k2f];
        }
        return $tmp;
    }

    public function checkRemoteFile($url) {
		//return false;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        // don't download content
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        return (curl_exec($ch) !== FALSE);
    }

}