<?php

/**
 * Magento
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
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog product form gallery content
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      protected $_uploaderType = 'uploader/multiple'; Magento Core Team <core@magentocommerce.com>
 */
class Ecp_Tattoo_Block_Adminhtml_Catalog_Product_Helper_Form_Gallery_Content extends Mage_Adminhtml_Block_Widget {

	protected $_uploaderType = 'uploader/multiple';
    public function __construct() {
        parent::__construct();
        $this->setTemplate('ecp/tattoo/gallery.phtml');
        $this->setArtist(Mage::registry('tattoo_data'));
        $this->images = Mage::getModel('ecp_tattoo/tattoo_artist_work')
                ->getCollection()
                ->addFieldToFilter('tattoo_artist_id', $this->getArtist()->getId());
    }

    protected function _prepareLayout1() {
        $this->setChild('uploader', $this->getLayout()->createBlock('adminhtml/media_uploader')
        );

        $this->getUploader()->getConfig()
                ->setUrl(Mage::getModel('adminhtml/url')->addSessionParam()->getUrl('*/adminhtml_tattoo_gallery/upload'))
                ->setFileField('image')
                ->setFilters(array(
                    'images' => array(
                        'label' => Mage::helper('adminhtml')->__('Images (.gif, .jpg, .png)'),
                        'files' => array('*.gif', '*.jpg', '*.jpeg', '*.png')
                    )
                ));

        $tmp = $this->getUploader()->getConfig()->getParams();
        $tmp['artist'] = $this->getArtist()->getId();
        $this->getUploader()->getConfig()->setParams($tmp);

        Mage::dispatchEvent('catalog_product_gallery_prepare_layout', array('block' => $this));

        return parent::_prepareLayout();
    }
    
    //allure code for upload
    protected function _prepareLayout()
    {
    	$this->setChild('uploader',
    			$this->getLayout()->createBlock($this->_uploaderType)
    			);
    
    	$this->getUploader()->getUploaderConfig()
    	->setFileParameterName('image')
    	->setTarget(Mage::getModel('adminhtml/url')->addSessionParam()->getUrl('*/adminhtml_tattoo_gallery/upload'));
    
    	$browseConfig = $this->getUploader()->getButtonConfig();
    	$browseConfig
    	->setAttributes(array(
    			'accept' => $browseConfig->getMimeTypesByExtensions('gif, png, jpeg, jpg'),
    			'artist'=>$this->getArtist()->getId()
    	));
    
    	Mage::dispatchEvent('catalog_product_gallery_prepare_layout', array('block' => $this));
    
    	return parent::_prepareLayout();
    }

    //allure code for upload
    protected function _prepareLayout()
    {
    	$this->setChild('uploader',
    			$this->getLayout()->createBlock($this->_uploaderType)
    			);
    
    	$this->getUploader()->getUploaderConfig()
    	->setFileParameterName('image')
    	->setTarget(Mage::getModel('adminhtml/url')->addSessionParam()->getUrl('*/adminhtml_tattoo_gallery/upload'));
    
    	$browseConfig = $this->getUploader()->getButtonConfig();
    	$browseConfig
    	->setAttributes(array(
    			'accept' => $browseConfig->getMimeTypesByExtensions('gif, png, jpeg, jpg'),
    			'artist'=>$this->getArtist()->getId()
    	));
    
    	Mage::dispatchEvent('catalog_product_gallery_prepare_layout', array('block' => $this));
    
    	return parent::_prepareLayout();
    }
    
    /**
     * Retrive uploader block
     *
     * @return Mage_Adminhtml_Block_Media_Uploader
     */
    public function getUploader() {
        return $this->getChild('uploader');
    }

    /**
     * Retrive uploader block html
     *
     * @return string
     */
    public function getUploaderHtml() {
        return $this->getChildHtml('uploader');
    }

    public function getJsObjectName() {
        return $this->getHtmlId() . 'JsObject';
    }

    public function getAddImagesButton() {
        return $this->getButtonHtml(
                        Mage::helper('catalog')->__('Add New Images'), $this->getJsObjectName() . '.showUploader()', 'add', $this->getHtmlId() . '_add_images_button'
        );
    }

    public function getImagesJson() {
        $tmp = array();

        if ($this->images->getSize() > 0) {
            $images = Mage::getModel('ecp_tattoo/tattoo_artist_work')
                    ->getCollection()
                    ->addFieldToFilter('tattoo_artist_id', $this->getArtist()->getId());

            $i = 0;
            foreach ($images as $image) {
                $tmp[] = array(
                    'value_id' =>$image->getId(),
                    'file' =>$image->getImage(),
                    'label' =>$image->getLabel(),
                    'position' =>$image->getSortorder(),
                    'disabled' => ($image->getEnabled() == 1 ) ? 0 : 1,
                    'label_default' => null,
                    'position_default' => 1,
                    'disabled_default' => 0,
                    'url' => $image->getImage()
                );
                /* $image['url'] = Mage::getSingleton('catalog/product_media_config')
                  ->getMediaUrl($image['file']); */
            }
            return Mage::helper('core')->jsonEncode($tmp);
        }
        return '[]';
    }

    public function getImagesValuesJson() {
        $values = array();
        if ($this->images->getSize() > 0) {
            
        } else {
            //$values = array('image'=>'no selection');
        }
        /* foreach ($this->getMediaAttributes() as $attribute) {
          $values[$attribute->getAttributeCode()] = $this->getElement()->getDataObject()->getData(
          $attribute->getAttributeCode()
          );
          } */
        return Mage::helper('core')->jsonEncode($values);
    }

    /**
     * Enter description here...
     *
     * @return array
     */
    public function getImageTypes() {
        $imageTypes = array();
        foreach ($this->getMediaAttributes() as $attribute) {
            /* @var $attribute Mage_Eav_Model_Entity_Attribute */
            $imageTypes[$attribute->getAttributeCode()] = array(
                'label' => $attribute->getFrontend()->getLabel() . ' '
                . Mage::helper('catalog')->__($this->getElement()->getScopeLabel($attribute)),
                'field' => $this->getElement()->getAttributeFieldName($attribute)
            );
        }
        return $imageTypes;
    }

    public function hasUseDefault() {
        foreach ($this->getMediaAttributes() as $attribute) {
            if ($this->getElement()->canDisplayUseDefault($attribute)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Enter description here...
     *
     * @return array
     */
    public function getMediaAttributes() {
        return $this->getElement()->getDataObject()->getMediaAttributes();
    }

    public function getImageTypesJson() {
        return Mage::helper('core')->jsonEncode($this->getImageTypes());
    }

}
