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
					foreach ($attrValues as $val)
					{
						$attr = Mage::getModel('catalog/product')->getResource()->getAttribute('diamond_color');
						if ($attr->usesSource()) 
						{
							$optionId[] = $attr->getSource() ->getOptionId($val);
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
					foreach ($attrValues as $val)
					{
						$attr = Mage::getModel('catalog/product')->getResource()->getAttribute('gemstone');
						if ($attr->usesSource()) 
						{
							$optionId[] = $attr->getSource()->getOptionId($val);
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
}