<?php
class Teamwork_TransferMariatash_Model_Class_Item extends Teamwork_CEGiftcards_Transfer_Model_Class_Item
{
	
	protected function _beforeAddData(&$productData, &$style, &$typeId, &$item, &$topProduct)
	{   
		if ($topProduct)
		{
			/*ring_closing_mechanism*/
			if (!empty($style['customlongtext16']))
			{
				$optionId = null;
				$attr = Mage::getModel('catalog/product')->getResource()->getAttribute('ring_closing_mechanism');
					if ($attr->usesSource()) 
					{
						$optionId = $attr->getSource()->getOptionId($style['customlongtext16']);
					}
				$productData['ring_closing_mechanism'] = $optionId;
			}
			/*ring_closing_mechanism*/
			
			/*ring_diameter_as_filter*/
			if (!empty($style['customlongtext17']))
			{
				$optionId = null;
				$attr = Mage::getModel('catalog/product')->getResource()->getAttribute('ring_diameter_as_filter');
					if ($attr->usesSource()) 
					{
						$optionId = $attr->getSource()->getOptionId($style['customlongtext17']);
					}
				$productData['ring_diameter_as_filter'] = $optionId;
			}
			/*ring_diameter_as_filter*/
			
			/*diamond_color*/
			if (!empty($style['custommultiselect1']))
			{
				$attrValues = unserialize($style['custommultiselect1']);
				$optionId = array();
				if(isset($attrValues['LookupName']))
				{
					$attrValues = $attrValues['LookupName'];
					if(is_array($attrValues))
					{
						foreach ($attrValues as $val)
						{
							$attr = Mage::getModel('catalog/product')->getResource()->getAttribute('diamond_color');
							if ($attr->usesSource()) 
							{
								$optionId[] = $attr->getSource() ->getOptionId($val);
							}
							
						}
					}
					else
					{
						$attr = Mage::getModel('catalog/product')->getResource()->getAttribute('diamond_color');
						if ($attr->usesSource()) 
						{
							$optionId[] = $attr->getSource() ->getOptionId($attrValues);
						}
					}
				}
			}
			
			$productData['diamond_color'] = $optionId;
			/*diamond_color*/
			
			/*gemstone*/
			if (!empty($style['custommultiselect2']))
			{
				$attrValues = unserialize($style['custommultiselect2']);
				$optionId = array();
				if(isset($attrValues['LookupName']))
				{
					$attrValues = $attrValues['LookupName'];
					if(is_array($attrValues))
					{
						foreach ($attrValues as $val)
						{
							$attr = Mage::getModel('catalog/product')->getResource()->getAttribute('gemstone');
							if ($attr->usesSource()) 
							{
								$optionId[] = $attr->getSource()->getOptionId($val);
							}
							
						}
					}
					else
					{
						$attr = Mage::getModel('catalog/product')->getResource()->getAttribute('gemstone');
						if ($attr->usesSource()) 
						{
							$optionId[] = $attr->getSource() ->getOptionId($attrValues);
						}
					}
				}
			}
			
			$productData['gemstone'] = $optionId;
			/*gemstone*/
		}
		else
		{
			/*vendor_item_no*/
			if (!empty($item['c_vlu']))
			{
				$productData['vendor_item_no'] = $item['c_vlu'];
			}
			/*vendor_item_no*/
			/*thread_type*/
			if (!empty($item['Attribute3']))
			{
				$optionId = null;
				$attr = Mage::getModel('catalog/product')->getResource()->getAttribute('thread_type');
				if ($attr->usesSource()) 
				{
					$optionId = $attr->getSource()->getOptionId($item['Attribute3']);
				}
				$productData['thread_type'] = $optionId;
			}
			/*thread_type*/

			/*ball_size*/
			if (!empty($item['Attribute3']))
			{
				$optionId = null;
				$attr = Mage::getModel('catalog/product')->getResource()->getAttribute('ball_size');
				if ($attr->usesSource()) 
				{
					$optionId = $attr->getSource()->getOptionId($item['Attribute3']);
				}
			$productData['ball_size'] = $optionId;
			}
			/*ball_size*/
		}
	}
	
	protected function _getProductData(&$style, $typeId = Mage_Catalog_Model_Product_Type::TYPE_SIMPLE, $item = null)
    {
        if($typeId == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE || $this->_productTypes[$style[Teamwork_Service_Model_Mapping::FIELD_STYLE_INVETTYPE]] == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
        {
            $entity = &$style;
            $topProduct = true;
        }
        else
        {
            $entity = &$item;
            $topProduct = false;
        }

        $productData = array(
            'tax_class_id' => $style['taxcategory'],
        );

        if (!empty($entity['url_key']))
        {
            $productData['url_key'] = $entity['url_key'];
        }

        if(Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_IMPORT_CATEGORIES))
        {
            $productData['category_ids'] = ($topProduct ? $this->_categoryModel->getStyleMagentoCategories($style['style_id']) : $this->_categoryModel->getItemMagentoCategories($item['item_id']));
        }

        if(empty($entity['internal_id']) || Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_UPDATE_VISIBILITY))
        {
            $productData['visibility'] = $topProduct ? Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH : Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE;
        }

        if(empty($entity['internal_id']) || Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_UPDATE_STATUSES))
        {
            if(strtolower($entity['ecomerce']) == strtolower('EC Offer'))
            {
                $productData['status'] = Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
            }
            else
            {
                $productData['status'] = Mage_Catalog_Model_Product_Status::STATUS_DISABLED;
            }
        }

        $this->_fillFieldsFromMapping($productData, $style, $item, $topProduct);

        foreach($this->_mapModel->getPrice() as $attributeCode => $mappingPriceLevel)
        {
            if( $attributeCode != 'price' && $productData[$attributeCode] == 0 )
            {
                $productData[$attributeCode] = '';
            }
            elseif($attributeCode == 'group_price')/**/
            {
                $groupPricingData = array();
                if( !empty($productData[$attributeCode]) )
                {
                    $groupPricingData = array (
                        array('website_id' => 0, 'cust_group' => 2, 'price' => $productData[$attributeCode]),
                    );
                }
                $productData[$attributeCode] = $groupPricingData;
            }
        }

        if($typeId == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
        {
            if( empty($productData['weight']) )
            {
                $productData['weight'] = $this->_defaultWeight;
            }
        }

        $this->_beforeAddData($productData, $style, $typeId, $item, $topProduct);
        
        //mapping rich content
        $this->_mappingRichContent($productData, $style, $this->_globalVars['channel_id'], $item, $typeId);

		// it's important to add name suffix at the end of _getProductData, when product name won't be changed by any function
        if(!$topProduct)
        {
            $suffix = '';
            for($i = 1; $i <= 3; $i++)
            {
                if(!empty($item["attribute{$i}_id"]) && !empty($this->_attributeValues[$item["attribute{$i}_id"]]) && $this->_attributes[$this->_attributeValues[$item["attribute{$i}_id"]]['attribute_set_id']]['is_active'])
                {
                    $suffix .= ' ' . trim($this->_attributeValues[$item["attribute{$i}_id"]]['attribute_value']);
                }
            }
            
            if(!empty($suffix))
            {
                $prefix = !empty($productData['name']) ? $productData['name'] : '';
                $productData['name'] = $prefix . $suffix;
            }
			/*set sku in simple products*/
			$suffix = '';
            for($i = 1; $i <= 3; $i++)
            {
                if(!empty($item["attribute{$i}_id"]) && !empty($this->_attributeValues[$item["attribute{$i}_id"]]) && $this->_attributes[$this->_attributeValues[$item["attribute{$i}_id"]]['attribute_set_id']]['is_active'])
                {
                    $suffix .= '|' . trim($this->_attributeValues[$item["attribute{$i}_id"]]['attribute_value']);
                }
            }
            
            if(!empty($suffix))
            {
                $prefix = !empty($style['no']) ? $style['no'] : '';
                $productData['sku'] = $prefix . $suffix;
            }
			
        }
        
        //push once
        $this->_pushOnce($productData, $style, $typeId, $item);

        return $productData;
    }
    
    public function getConfigurableAttributesData($product, &$children, &$style, $forceSetParentPrices = true)
    {
        $configurableAttributesTempAttributes = array();
        $optionSets = array();
        $pricingTempAttributes = array();

        $minChildPrice = null;
        $minPriceChildProd = null;
        $maxChildPrice = null;
        $maxPriceChildProd = null;

        $disabledAll = true;
        foreach($children as $childId => $childAttributes)
        {
            foreach($childAttributes as $childAttribute)
            {
                if ($childAttribute['product_status'] == Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                {
                    $disabledAll = false;
                    break 2;
                }
                break;
            }
        }
        
        foreach($children as $childId => $childAttributes)
        {
            foreach($childAttributes as $childAttribute)
            {
                $optionSets[$childAttribute['plu']][$childAttribute['attribute_id']] = $childAttribute['value_index'];
                
                $price = floatval($childAttribute['pricing_value']);
                if (($disabledAll || $childAttribute['product_status'] == Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                        && (is_null($minChildPrice) || $price < $minChildPrice))
                {
                    $minChildPrice = $price;
                    $minPriceChildProd = $childAttribute['price_data_object'];
                }
                if (($disabledAll || $childAttribute['product_status'] == Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                        && (is_null($maxChildPrice) || $price > $maxChildPrice))
                {
                    $maxChildPrice = $price;
                    $maxPriceChildProd = $childAttribute['price_data_object'];
                }

                $configurableAttributesTempAttributes[$childAttribute['attribute_id']][$childAttribute['value_index']] = $childAttribute;
                if(!isset($pricingTempAttributes[$childAttribute['attribute_id']]))
                {
                    $pricingTempAttributes[$childAttribute['attribute_id']] = array('skip me' => false, 'values' => array());
                }

                if($pricingTempAttributes[$childAttribute['attribute_id']]['skip me'])
                {
                    continue;
                }

                if(!isset($pricingTempAttributes[$childAttribute['attribute_id']]['values'][$childAttribute['value_index']]))
                {
                    $pricingTempAttributes[$childAttribute['attribute_id']]['values'][$childAttribute['value_index']] = $price;
                }
                elseif($pricingTempAttributes[$childAttribute['attribute_id']]['values'][$childAttribute['value_index']] != $price)
                {
                    $pricingTempAttributes[$childAttribute['attribute_id']]['skip me'] = true;
                    unset($pricingTempAttributes[$childAttribute['attribute_id']]['values']);
                }
            }
        }
       
        /*get attributes to skip using collected data*/
        $skipPricingAttributeIds = array();
        $foundPricingAttr = 0;
        /*get attributes to skip using collected data*/
        foreach($pricingTempAttributes as $attrId => $data)
        {
            if ($data['skip me'] || $foundPricingAttr) {
                $skipPricingAttributeIds[] = $attrId;
            } else {
                $foundPricingAttr = true;
            }
        }

        unset($pricingTempAttributes);

        $priceStyle = $minChildPrice;

        /*if found no pricing attributes*/
        if (!$foundPricingAttr)
        {
            $priceStyle = $maxChildPrice;

            $styleNo = "";
            if (empty($style['no']))
            {
                if (!empty($style['internal_id']))
                {
                    $select = $this->_db->select()
                        ->from(Mage::getSingleton('core/resource')->getTableName('service_style'), array('no'))
                    ->where('internal_id = ?', $style['internal_id']);
                    $noTemp = $this->_db->fetchOne($select);
                    if (!empty($noTemp))
                    {
                        $styleNo = $noTemp;
                    }
                }
            }
            else
            {
                $styleNo = $style['no'];
            }

            if (strlen($styleNo))
            {
                if (!isset($this->_cache['error']['pricing']['style_no'][$styleNo]))
                {
                    $this->_cache['error']['pricing']['style_no'][$styleNo] = true;
                    if(Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_THROW_WRONG_PRICING_ERRORS))
                    {
                        $this->_addWarningMsg(sprintf("Pricing error detected for style no %s, %s price will be used for all variants", $styleNo, $priceStyle), false);
                    }
                }
            }
            else if (!empty($style['internal_id']))
            {
                if (!isset($this->_cache['error']['pricing']['internal_id'][$style['internal_id']]))
                {
                    $this->_cache['error']['pricing']['internal_id'][$style['internal_id']] = true;
                    if(Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_THROW_WRONG_PRICING_ERRORS))
                    {
                        $this->_addWarningMsg(sprintf("Pricing error detected (magento internal id: %s), %s price will be used for all variants", $style['internal_id'], $priceStyle), false);
                    }
                }
            }
            else
            {
                if(Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_THROW_WRONG_PRICING_ERRORS))
                {
                    $this->_addWarningMsg("Pricing error detected.", false);
                }
            }
            if(Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_THROW_WRONG_PRICING_ERRORS))
            {
                $this->_getLogger()->addMessage(sprintf("Error occured while detecting pricing attributes: sku: %s: detected the following attribute ids: %s", $product->getSku(), implode(',', $skipPricingAttributeIds)), Zend_Log::ERR);
            }
        }
        
        $superAttributes = array();
        if( !empty($style['internal_id']) )
        {
            $superAttributes = (array)$product->getTypeInstance(true)->getConfigurableAttributes($product)->getData();
            /* $product->getTypeInstance()->setUsedProductAttributeIds(
                array_keys($configurableAttributesTempAttributes)
            ); */
        }

        $configurableAttributesData = array();
        $unnasignedChildren = array();
        $position = 1;
        foreach($configurableAttributesTempAttributes as $attribute_id => $options)
        {
            $superAttributeId = null;
            if( count($superAttributes) > 0 )
            {
                foreach($superAttributes as $superAttribute)
                {
                    if($superAttribute['attribute_id'] == $attribute_id)
                    {
                        $superAttributeId = $superAttribute['product_super_attribute_id'];
                        break;
                    }
                    if( !in_array($superAttribute['attribute_id'],array_keys($configurableAttributesTempAttributes)) )
                    {
                        $superAttributeForDelete = Mage::getModel('catalog/product_type_configurable_attribute')
                           ->setId($superAttribute['product_super_attribute_id'])
                        ->setProductId($style['internal_id']);
                        
                        $superAttributeForDelete->isDeleted(true);
                        $superAttributeForDelete->save();
                    }
                }
            }

            $attribute = current($options);
            $attributeToSave = array(
                'id'                 => $superAttributeId,
                'attribute_id'       => $attribute_id,
                'attribute_code'     => $attribute['attribute_code'],
                'label'              => $attribute['attribute_name'],
                'frontend_label'     => $attribute['attribute_name'],
                'store_label'        => $attribute['attribute_name'],
                'use_default'        => Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_DEFAULT_VALUE_FOR_CONFIGURABLE_ATTRIBUTE) || $product->getStoreId() == Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID || !Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_SEVERAL_STORES),
                'html_id'            => 'configurable__attribute_' . ($position-1),
                'position'           => $position++,
            );

            foreach($options as $option)
            {
                $pricingValue = in_array($attribute_id, $skipPricingAttributeIds) ? 0 : $option['pricing_value'] - $priceStyle;
                $attributeToSave['values'][$option['value_index']] = array(
                    'product_super_attribute_id'   => $superAttributeId,
                    'pricing_value'                => $pricingValue,
                    'value_index'                  => $option['value_index'],
                    'label'                        => $option['value_label'],
                    'default_label'                => $option['value_label'],
                    'store_label'                  => $option['value_label'],
                    'is_percent'                   => 0,
                    //'use_default_value'            => true,
                    'use_default_value'            => Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_SEVERAL_STORES) ? false : true,
                    'can_edit_price'               => true,
                    'can_read_price'               => true,
                );
            }
            $configurableAttributesData[$attribute_id] = $attributeToSave;
            $this->_unassignWrongChildren($children, $attribute_id, $unnasignedChildren);
        }

        if( !empty($unnasignedChildren) )
        {
            $productIds = Mage::getModel('catalog/product')->getResource()->getProductsSku($unnasignedChildren);
            $skus = array();
            foreach($productIds as $productId)
            {
                $skus[] = $productId['sku'];
            }
            $this->_addWarningMsg( sprintf("Product %s has not accepted some items to be assigned: %s", $product->getSku(), implode(', ', $skus)) );
        }
        
        if( !$this->_checkDoubledAttributeUsage($children, $configurableAttributesData, $product) )
        {
            $this->_checkDoubledAttributeOptionUsage($optionSets, $configurableAttributesData);
        }

        if ($forceSetParentPrices)
        {
            $sourceProduct = $foundPricingAttr ? $minPriceChildProd : $maxPriceChildProd;
            if ($sourceProduct instanceof Varien_Object)
            {
                /*copy price attributes from child but skip style mapped to prevent overwriting*/
                $this->copyPriceData($sourceProduct, $product, $this->getMappedStyleAttributes());
            }
            else
            {
                $message =  'Wrong items attributes data';
                if (!empty($style['no']))
                {
                    $message .= ": Style #{$style['no']}";
                }
                $message .= ", sku #{$product->getSku()}. Please check style configuration.";
                $this->_addWarningMsg($message);
            }
        }
        return $configurableAttributesData;
    }
}