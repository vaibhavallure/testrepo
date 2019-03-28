<?php
class Teamwork_TransferMariatash_Model_Class_Price extends Teamwork_Transfer_Model_Class_Price
{
    protected function _updateProducts($items)
    {
        if(!empty($items) && is_array($items))
        {
            $data = array();
            foreach($items as $id => $item)
            {
                $data[$item['style_internal_id']]['items'][$item['internal_id']][$item['price_level']] = $item['price'];
                $data[$item['style_internal_id']]['attributes'][$item['internal_id']]['attribute1_id'] = $item['attribute1_id'];
                $data[$item['style_internal_id']]['attributes'][$item['internal_id']]['attribute2_id'] = $item['attribute2_id'];
                $data[$item['style_internal_id']]['attributes'][$item['internal_id']]['attribute3_id'] = $item['attribute3_id'];
                $data[$item['style_internal_id']]['attributes'][$item['internal_id']]['plu'] = $item['plu'];
                $data[$item['style_internal_id']]['style']['attributeset1'] = $item['attributeset1'];
                $data[$item['style_internal_id']]['style']['attributeset2'] = $item['attributeset2'];
                $data[$item['style_internal_id']]['style']['attributeset3'] = $item['attributeset3'];
                $data[$item['style_internal_id']]['inventype'] = $item['inventype'];
                unset($items[$id]);
            }
            unset($items);
            
            $priceAttributeCodes = $this->_itemModel->getPriceAttributeCodes();
            
            $storeId = null;
            if (Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_SEVERAL_STORES)
                    && array_key_exists('store_id', $this->_globalVars))
            {
                $storeId = $this->_globalVars['store_id'];
            }
            
            $parentAttributesData = array();
            foreach($data as $parentId => $info)
            {
                $childCollection = Mage::getModel('catalog/product')->getCollection();
                if (!is_null($storeId))
                {
                    $childCollection->setStore($storeId);
                }
                $childCollection->addIdFilter(array_keys($info['items']));
                $childCollection->addAttributeToSelect($priceAttributeCodes);

                $parentProductType = $this->_itemModel->getProductTypeByInventype($info['inventype']);
                foreach($childCollection as $child)
                {
                    $childId = $child->getId();
                    if (array_key_exists($childId, $info['items']))
                    {
                        foreach($this->_priceMapping as $attributeCode => $mappingPriceLevel)
                        {
                            $level = (int)substr($mappingPriceLevel, -1);
                            $amount = !empty( $info['items'][$childId][$level] ) ? floatval($info['items'][$childId][$level]) : '';
                            if( $attributeCode == 'price' && empty($amount) )
                            {
                                $amount = floatval(0);
                            }
                            elseif($attributeCode == 'group_price')/**/
                            {
                                $groupPricingData = array();
                                if( !empty($amount) )
                                {
                                    $groupPricingData = array (
                                        array('website_id' => 0, 'cust_group' => 2, 'price' => $amount),
                                    );
                                }
                                $amount = $groupPricingData;
                            }
                            $child->setData($attributeCode, $amount);
                        }
                        $this->_itemModel->removeEqualPriceData($child);
                        
                        $doSave = $this->saveChecker($child);
                        if($doSave)
                        {
                            $obj = new Varien_Object();
                            $this->_itemModel->copyPriceData($child, $obj);
                            if (count($obj->getData()))
                            {
                                try
                                {
                                    Mage::getSingleton('catalog/product_api')->update($childId, $obj->getData(), $storeId);
                                }
                                catch(Exception $e)
                                {
                                    $this->_addErrorMsg(sprintf("Error occured while price save for simple product trought API: simple product %s - %s", $childId, $e->getMessage()), true);
                                    $this->_getLogger()->addException($e);
                                }
                            }
                            unset($obj);
                            $this->checkLastUpdateTime();
                        }
                        if ($parentProductType == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
                        {
                            if (!array_key_exists($parentId, $parentAttributesData))
                            {
                                $parentAttributesData[$parentId] = array(
                                    'children'          => array(),
                                    'children_saved'    => false,
                                );
                            } 
                            $parentAttributesData[$parentId]['children_saved'] |= $doSave;
                            $attrArray = $this->_itemModel->addAttributeFromSets($child, $info['attributes'][$childId], true, $info['style']);
                            if(!empty($attrArray))
                            {
                                $parentAttributesData[$parentId]['children'][$childId] = $attrArray;
                            }
                        }
                    }
                }
                if (array_key_exists($parentId, $parentAttributesData) && (!$parentAttributesData[$parentId]['children_saved']))
                {
                    unset($parentAttributesData[$parentId]);
                }
            }
            
            if (count($parentAttributesData))
            {
                $parentIds = array_keys($parentAttributesData);
                foreach($parentIds as $productId)
                {
                    try
                    {
                        $configurableProduct = $this->_itemModel->_loadProduct($productId, Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);
                        if( !$configurableProduct->isConfigurable() )
                        {
                            continue;
                        }
                        
                        $style = array('internal_id' => $productId);
                        $attributesData = $this->_itemModel->getConfigurableAttributesData($configurableProduct, $parentAttributesData[$productId]['children'], $style);
                        if ($this->_itemModel->hasErrorMsgs())
                        {
                            $this->_addErrorMsgs($this->_itemModel->getErrorMsgs());
                            $this->_itemModel->cleanUpErrorMsgs();
                        }
                        $configurableProduct->setConfigurableAttributesData($attributesData);
                        $configurableProduct->setCanSaveConfigurableAttributes(true);
                        $configurableProduct->setAffectConfigurableProductAttributes(true);
                    
                        $configurableProduct->save();
                    }
                    catch(Exception $e)
                    {
                        $this->_addErrorMsg(sprintf("Error occured while super attribute price data saving for configurable product: style %s - %s", $productId, $e->getMessage()), true);
                        $this->_getLogger()->addException($e);
                    }
                    $this->checkLastUpdateTime();
                }
                unset($parentCollection);
            }
            unset($childCollection);
        }
    }
}