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
 * @package     Ecp_Tryon
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Tryon
 *
 * @category    Ecp
 * @package     Ecp_Tryon
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Tryon_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getJewerliesBlock() {
        return Mage::getSingleton('core/layout')->createBlock(
                        'Ecp_Tryon_Block_Tryon', 'tryon_block', array('template' => 'ecp/tryon/jewerlies.phtml'))->toHtml();
    }

    public function createJSONTryOn($_productCollection) {
        $regions = Mage::getStoreConfig('ecp_tryon/regions');
        $arrayRegions = array();
            foreach($regions as $region){
                $arrayRegions[$region['code']] = 'region';
                if(!empty($region['subregions'])){
                    foreach($region['subregions'] as $key => $subregion){
                        $arrayRegions[$subregion['code']] = 'subregion';
                    }
                }
            }
            
        $_productCollection->addAttributeToFilter('tryonids', array('neq' => ''))->load();
        
        //echo '<pre>';  var_dump($_productCollection->getData()); die;
        
        $tmpProducts = array();
        
        foreach ($_productCollection->getData() as $tmpArray) {
            $product = new Varien_Object();
            $product->setData($tmpArray);
            $regions = array_flip(explode(',',$product->getTryonids()));

            $tmpRegions = array();
            foreach($regions as $region => $index) if(!empty($region)){
                switch($arrayRegions[$region]){
                    case 'region': 
                        if(!is_array($tmpRegions[$region]))
                            $tmpRegions[$region] = array();
                        break;
                    case 'subregion': 
                        $tmpRegion = explode('-',$region);
                        $tmpRegions[$tmpRegion[0]]['subregions'][$region] = array(
                            'image'=>'1.png'
                        );
                        break;
                }
            }
            
            $tmpProducts['product'.$product->getEntityId()] = array(
                    'isOnTryon' => 1,
                    'regions' => $tmpRegions
            );
        }
        
        return json_encode($tmpProducts);
            
    }

}