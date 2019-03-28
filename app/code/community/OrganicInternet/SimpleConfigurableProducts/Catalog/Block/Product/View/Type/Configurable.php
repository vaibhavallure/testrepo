<?php

class OrganicInternet_SimpleConfigurableProducts_Catalog_Block_Product_View_Type_Configurable
    extends Amasty_Stockstatus_Block_Rewrite_Product_View_Type_Configurable
{
    protected $_optionProducts;

    protected function _afterToHtml($html)
    {
        $attributeIdsWithImages = Mage::registry('amconf_images_attrids');
        $html = parent::_afterToHtml($html);
        if (!Mage::getStoreConfig('amconf/general/enable'))
        {
            return $html;
        }
        if ('product.info.options.configurable' == $this->getNameInLayout())
        {
            if (Mage::getStoreConfig('amconf/general/hide_dropdowns'))
            {
                if (!empty($attributeIdsWithImages))
                {
                    foreach ($attributeIdsWithImages as $attrIdToHide)
                    {
                        $html = preg_replace('@(id="attribute' . $attrIdToHide . '")(\s+)(class=")(.*?)(super-attribute-select)@', '$1$2$3$4$5 no-display', $html);
                    }
                }
            }

            $simpleProducts = $this->getProduct()->getTypeInstance(true)->getUsedProducts(null, $this->getProduct());

            if ($this->_optionProducts)
            {
                $noimgUrl = Mage::helper('amconf')->getNoimgImgUrl();
                $this->_optionProducts = array_values($this->_optionProducts);
                foreach ($simpleProducts as $simple)
                {
                    $key = array();
                    for ($i = 0; $i < count($this->_optionProducts); $i++)
                    {
                        foreach ($this->_optionProducts[$i] as $optionId => $productIds)
                        {
                            if (in_array($simple->getId(), $productIds))
                            {
                                $key[] = $optionId;
                            }
                        }
                    }
                    if ($key)
                    {
                        // @todo check settings:
                        // array key here is a combination of choosen options
                        $confData[implode(',', $key)] = array(
                            'short_description' => $simple->getShortDescription(),
                            'description'       => $simple->getDescription(),
                        );

                        if (Mage::getStoreConfig('amconf/general/reload_name'))
                        {
                            $confData[implode(',', $key)]['name'] = $simple->getName();
                        }

                        if (Mage::getStoreConfig('amconf/general/reload_images'))
                        {
                            $confData[implode(',', $key)]['media_url'] = $this->getUrl('amconf/media', array('id' => $simple->getId(),'cpid' => $this->getProduct()->getId(),'optionId'=>$key[0], '_secure' =>  (bool) Mage::app()->getFrontController()->getRequest()->isSecure())); // media_url should only exist if we need to re-load images
                        } elseif ($noimgUrl)
                        {
                            $confData[implode(',', $key)]['noimg_url'] = $noimgUrl;
                        }
                    }
                }

                $html .= '<script type="text/javascript">var confData = new AmConfigurableData(' . Zend_Json::encode($confData) . ');</script>';
                if (Mage::getStoreConfig('amconf/general/hide_dropdowns'))
                {
                    $html .= '<script type="text/javascript">Event.observe(window, \'load\', spConfig.processEmpty);</script>';
                }
                $html .= '<script type="text/javascript">confData.textNotAvailable = "' . $this->__('Choose previous option please...') . '";</script>';
                $html .= '<script type="text/javascript">confData.mediaUrlMain = "' . $this->getUrl('amconf/media', array('id' => $this->getProduct()->getId())) . '";</script>';
                $html .= '<script type="text/javascript">confData.oneAttributeReload = "' . (boolean) Mage::getStoreConfig('amconf/general/oneselect_reload') . '";</script>';
                if ('true' == (string)Mage::getConfig()->getNode('modules/Amasty_Lbox/active'))
                {
                    $html .= '<script type="text/javascript">confData.amlboxInstalled = true;</script>';
                }
            }
        }

        return $html;
    }
    public function getJsonConfig()
    {
        $config = Zend_Json::decode(parent::getJsonConfig());

        $childProducts = array();

        //Create the extra price and tier price data/html we need.
        foreach ($this->getAllowProducts() as $product) {
            $productId  = $product->getId();
            $childProducts[$productId] = array(
                "price" => $this->_registerJsPrice($this->_convertPrice($product->getPrice())),
                "finalPrice" => $this->_registerJsPrice($this->_convertPrice($product->getFinalPrice()))
            );

            if (Mage::getStoreConfig('SCP_options/product_page/change_name')) {
                $childProducts[$productId]["productName"] = $product->getName();
            }
            if (Mage::getStoreConfig('SCP_options/product_page/change_description')) {
                $childProducts[$productId]["description"] = $this->helper('catalog/output')->productAttribute($product, $product->getDescription(), 'description');
            }
            if (Mage::getStoreConfig('SCP_options/product_page/change_short_description')) {
                $childProducts[$productId]["shortDescription"] = $this->helper('catalog/output')->productAttribute($product, nl2br($product->getShortDescription()), 'short_description');
            }

            if (Mage::getStoreConfig('SCP_options/product_page/change_attributes')) {
                $childBlock = $this->getLayout()->createBlock('catalog/product_view_attributes');
                $childProducts[$productId]["productAttributes"] = $childBlock->setTemplate('catalog/product/view/attributes.phtml')
                    ->setProduct($product)
                    ->toHtml();
            }

            #if image changing is enabled..
            if (Mage::getStoreConfig('SCP_options/product_page/change_image')) {
                #but dont bother if fancy image changing is enabled
                if (!Mage::getStoreConfig('SCP_options/product_page/change_image_fancy')) {
                    #If image is not placeholder...
                    if($product->getImage()!=='no_selection') {
                        $childProducts[$productId]["imageUrl"] = (string)Mage::helper('catalog/image')->init($product, 'image');
                    }
                }
            }
        }

        //Remove any existing option prices.
        //Removing holes out of existing arrays is not nice,
        //but it keeps the extension's code separate so if Varien's getJsonConfig
        //is added to, things should still work.
        if (is_array($config['attributes'])) {
            foreach ($config['attributes'] as $attributeID => &$info) {
                if (is_array($info['options'])) {
                    foreach ($info['options'] as &$option) {
                        unset($option['price']);
                    }
                    unset($option); //clear foreach var ref
                }
            }
            unset($info); //clear foreach var ref
        }
        foreach ($config['attributes'] as $attributeId => $attribute)
        {
            if (Mage::getModel('amconf/attribute')->load($attributeId, 'attribute_id')->getUseImage())
            {
                $config['attributes'][$attributeId]['use_image'] = 1;
                $attributeIdsWithImages[] = $attributeId;
            }
            foreach ($attribute['options'] as $i => $option)
            {
                $this->_optionProducts[$attributeId][$option['id']] = $option['products'];
                if (Mage::getModel('amconf/attribute')->load($attributeId, 'attribute_id')->getUseImage())
                {
                    $config['attributes'][$attributeId]['options'][$i]['image'] = Mage::helper('amconf')->getImageUrl($option['id']);
                }
            }
        }
        Mage::register('amconf_images_attrids', $attributeIdsWithImages, true);
        $p = $this->getProduct();
        $config['childProducts'] = $childProducts;
        if ($p->getMaxPossibleFinalPrice() != $p->getFinalPrice()) {
            $config['priceFromLabel'] = $this->__('Price From:');
        } else {
            $config['priceFromLabel'] = $this->__('');
        }
        $config['ajaxBaseUrl'] = Mage::getUrl('oi/ajax/');
        $config['productName'] = $p->getName();
        $config['description'] = $this->helper('catalog/output')->productAttribute($p, $p->getDescription(), 'description');
        $config['shortDescription'] = $this->helper('catalog/output')->productAttribute($p, nl2br($p->getShortDescription()), 'short_description');

        if (Mage::getStoreConfig('SCP_options/product_page/change_image')) {
            $config["imageUrl"] = (string)Mage::helper('catalog/image')->init($p, 'image');
        }

        $childBlock = $this->getLayout()->createBlock('catalog/product_view_attributes');
        $config["productAttributes"] = $childBlock->setTemplate('catalog/product/view/attributes.phtml')
            ->setProduct($this->getProduct())
            ->toHtml();

        if (Mage::getStoreConfig('SCP_options/product_page/change_image')) {
            if (Mage::getStoreConfig('SCP_options/product_page/change_image_fancy')) {
                $childBlock = $this->getLayout()->createBlock('catalog/product_view_media');
                $config["imageZoomer"] = $childBlock->setTemplate('catalog/product/view/media.phtml')
                    ->setProduct($this->getProduct())
                    ->toHtml();
            }
        }

        if (Mage::getStoreConfig('SCP_options/product_page/show_price_ranges_in_options')) {
            $config['showPriceRangesInOptions'] = true;
            $config['rangeToLabel'] = $this->__('to');
        }
        return Zend_Json::encode($config);
        //parent getJsonConfig uses the following instead, but it seems to just break inline translate of this json?
        //return Mage::helper('core')->jsonEncode($config);
    }
}
