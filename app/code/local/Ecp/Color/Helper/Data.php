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
 * @package     Ecp_Color
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Color
 *
 * @category    Ecp
 * @package     Ecp_Color
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Color_Helper_Data extends Mage_Core_Helper_Abstract {
    public function getAttributeLabel($attribute_id){
          $resource = Mage::getSingleton('core/resource');
          $read = $resource->getConnection('core_read');
          $select = $read->select()
			    ->from(array('e' => $resource->getTableName('eav_attribute_label')), array('value'))
                ->where('e.store_id =?',Mage::app()->getStore()->getStoreId())
                ->where('e.attribute_id =?',$attribute_id);        
          $result = $read->fetchRow($select);
          if(is_array($result)){
            return $result['value'];
          }
          return '';
    }
}