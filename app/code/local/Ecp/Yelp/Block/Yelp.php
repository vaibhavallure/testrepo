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
class Ecp_Yelp_Block_Yelp extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface {

    protected function _construct(){
        parent::_construct();
    }        
    
    public function _prepareLayout(){
        return parent::_prepareLayout();
    }
    
    protected function _toHtml() {

        $tmp = Mage::getStoreConfig('ecp_yelp/yelp');

        $url = $tmp['detail_api_url'].$this->getData('yelp_account');
        $ck = $tmp['consumerkey'];
        $cs = $tmp['consumersecret'];
        $t = $tmp['token'];
        $ts = $tmp['tokensecret'];
        
        $this->setYelpInfo(Mage::getModel('ecp_yelp/yelp')->getReviews($url,$ck,$cs,$t,$ts));

        $this->setTemplate('ecp/yelp/yelp.phtml');
        return parent::_toHtml();
    }
    public function getYelpUrl(){
        $id_code = $this->getData('yelp_account');
        $options = unserialize(Mage::getStoreConfig('ecp_yelp/yelp/api_yelp_new_config_url')); 
        foreach($options as $info){
            if($info['id'] == $id_code){
                return $info['url'];
            }
        }
        return '#';
    }
}