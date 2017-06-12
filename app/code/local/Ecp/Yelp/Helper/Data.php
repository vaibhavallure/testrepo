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
 * @package     Ecp_Yelp
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Yelp
 *
 * @category    Ecp
 * @package     Ecp_Yelp
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Yelp_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getYelp(){
         $configs = array();
         $options = unserialize(Mage::getStoreConfig('ecp_yelp/yelp/api_yelp_new_config'));     
         foreach($options as $info){
             $configs[] = trim($info['id']);
         }
         return $configs;
    }
}