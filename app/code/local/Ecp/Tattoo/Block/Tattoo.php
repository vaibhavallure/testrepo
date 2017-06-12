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
 * @package     Ecp_Tattoo
 * @copyright   Copyright (c) 2010 Entrepids Inc. (http://www.entrepids.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Description of Tattoo
 *
 * @category    Ecp
 * @package     Ecp_Tattoo
 * @author      Entrepids Core Team <core@entrepids.com>
 */
class Ecp_Tattoo_Block_Tattoo extends Mage_Core_Block_Template
{
    protected $_tattoo = null;
    
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    
    public function getTattooArtist()     
    {         
        if (!$this->_tattoo)
            $this->_tattoo = Mage::registry('tattoo_artist');
        return $this->_tattoo;
        
    }
    
    public function getImage()
    {      
        return ($this->_tattoo->getImage())
            ? Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$this->_tattoo->getImage()
            : Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).DS. 'frontend/mt/default/images/tattoo/no-pic_artist.jpg';
    }
    
    public function getTabs()
    {         
        $collection = Mage::getModel('ecp_tattoo/tattoo_artist_work')->getCollection()->addFieldToFilter('tattoo_artist_id',$this->_tattoo->getId());
        $collection->getSelect()->group('categoryartist');
        
        return $collection;
    }
    
    public function getWorksInTab($tab)
    {         
        $tmp = Mage::getModel('ecp_tattoo/tattoo_artist_work')->getCollection()
                ->addFieldToFilter('tattoo_artist_id',$this->_tattoo->getId())
                ->addFieldToFilter('categoryartist',$tab)
                ->addFieldToFilter('enabled',1);
        
        $tmp->getSelect()->order('main_table.sortorder ASC');
        
        return $tmp;
    }
    
    public function getOtherArtist()
    {
        $collection = Mage::getModel('ecp_tattoo/tattoo_artist')->getCollection()
            ->addFieldToFilter('tattoo_artist_id',array('neq'=>$this->_tattoo->getId()))
            ->addFieldToFilter('status', 1)
        ;
        $tmp = $collection->getColumnValues('tattoo_artist_id');
        $random = $tmp[array_rand($tmp)];
        return Mage::getModel('ecp_tattoo/tattoo_artist')->load($random);
    }
    
    public function getOtherImage($other){
        return ($other->getImage())
            ? Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$other->getImage()
            : Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).DS. 'frontend/mt/default/images/tattoo/no-min-pic_artist.jpg';
    }
    
    public function getTattooerName()
    {
        return $this->_tattoo->getName();
    }
    
    public function getTattooerEmail()
    {
        return $this->_tattoo->getEmail();
    }
    
    public function getTattooerId()
    {
        return $this->_tattoo->getId();
    }
    
    public function getHourBlock(){
        $blockId = $this->_tattoo->getHours();
        if(empty($blockId)) return;
        $block = Mage::getModel('cms/block')->load($blockId);
        return Mage::helper('cms')
                    ->getPageTemplateProcessor()
                    ->filter($block->getContent());
    }
    
    public function getBannerBlock(){
        $blockId = $this->_tattoo->getBannerGift();
        if(empty($blockId)) return;
        $block = Mage::getModel('cms/block')->load($blockId);
        return Mage::helper('cms')
                    ->getPageTemplateProcessor()
                    ->filter($block->getContent());
    }
}