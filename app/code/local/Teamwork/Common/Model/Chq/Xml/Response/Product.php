<?php
class Teamwork_Common_Model_Chq_Xml_Response_Product extends Teamwork_Common_Model_Chq_Xml_Response_Abstract
{
    const PRODUCT_ECTYPE_NONEC = 'NonEc';
    const PRODUCT_ECTYPE_ECSUSPENDED = 'EcSuspended';
    const PRODUCT_ECTYPE_ECOFFER = 'EcOffer';
    const PRODUCT_ECTYPE_ECDISCONTINUED = 'EcDiscontinued';
    
    const DB_ECTYPE_ECSUSPENDED = 'ec suspended';
    const DB_ECTYPE_ECOFFER = 'ec offer';
    const DB_ECTYPE_ECDISCONTINUED = 'ec discontinued';
    
    protected $_channels = array();
    protected $_taxMapping = array();
    protected $_priceMapping = array();
    protected $_helper;
    protected $_defaultTax = 0; //TODO add to admin panel
    
    public function parse()
    {
        parent::parse();
        $this->_helper = Mage::helper('teamwork_common/staging_product');
        $this->_channels = Mage::getModel('teamwork_common/staging_channel')->getChannels();
        $this->_parseStyle();
    }
    
    protected function _collectTaxMapping($channelId)
    {
        if( empty($this->_taxMapping[$channelId]) )
        {
            $settingEntity = Mage::getModel('teamwork_common/staging_settings')->loadByChannelAndGuid($channelId, Teamwork_Common_Model_Staging_Settings::SETTING_NAME_TAXCATEGORYSETTINGS);
            $taxMappingCollection = $settingEntity->getSettingValue();
            
            if( !empty($taxMappingCollection) )
            {
                foreach($taxMappingCollection as $taxMapping)
                {
                    $this->_taxMapping[$channelId][$this->_getElement($taxMapping, 'Code')] = $taxMapping;
                }
            }
        }
    }
    
    protected function _collectPriceMapping($channelId)
    {
        if( empty($this->_priceMapping[$channelId]) )
        {
            $settingEntity = Mage::getModel('teamwork_common/staging_settings')->loadByChannelAndGuid($channelId, Teamwork_Common_Model_Staging_Settings::SETTING_NAME_PRICELEVELSETTINGS);
            $priceMappingCollection = $settingEntity->getSettingValue();
            if( !empty($priceMappingCollection) )
            {
                foreach($priceMappingCollection as $priceMapping)
                {
                    $this->_priceMapping[$channelId][$this->_getElement($priceMapping, 'PriceLevelId')] = $priceMapping;
                }
            }
        }
    }
    
    protected function _getTaxcategory($channelId,$taxCode)
    {
        return !empty($this->_taxMapping[$channelId][$taxCode]) ?
            $this->_getElement($this->_taxMapping[$channelId][$taxCode], 'EcomTaxCategory') :
        $this->_defaultTax;
    }
    
    protected function _parseStyle()
    {
        $xmlObject = $this->chqStaging->getResponse();
        $requestId = $this->chqStaging->getData('ApiDocumentId');
        $processedChannels = array();
        $itemsForRta = array();
        
        if( !empty($xmlObject->Styles) )
        {
            foreach($xmlObject->Styles->children() as $style)
            {
                if( !empty($style->EChannels) )
                {
                    $styleGuid = $this->_getElement($style, 'StyleId');
                    $isStyleProcessed = false;
                    foreach($style->EChannels->children() as $channel)
                    {
                        $channelId = array_search($this->_getAttribute($channel, 'Name'), $this->_channels);
                        $ecType = $this->_getAttribute($channel, 'Status');
                        if($channelId && in_array($ecType, $this->_helper->getProcessedEcTypes()))
                        {
                            $this->_collectTaxMapping($channelId);
                            // $this->_collectPriceMapping($channelId);
                            $styleEntity = Mage::getModel('teamwork_common/staging_style')->loadByChannelAndGuid($channelId, $styleGuid);
                            $this->_populateCategories($styleGuid, $channelId, $channel->children());
                            
                            $processedItems = $this->_parseItem($style,$channel);
                            if($processedItems)
                            {
                                $this->workingDocument->setRunDependency(true);
                                $itemsForRta = array_unique( array_merge($itemsForRta,$processedItems) );
                                if( !in_array($channelId,$processedChannels) )
                                {
                                    $processedChannels[] = $channelId;
                                }
                                $isStyleProcessed=true;
                                $styleEntity->setData($styleEntity->getGuidField(), $styleGuid)
                                    ->setChannelId($channelId)
                                    ->setRequestId($requestId)
                                    ->setNo($this->_getElement($style, 'StyleNo'))
                                    ->setInventype($this->_getElement($style, 'InvenType'))
                                    ->setDescription($this->_getElement($style, 'Description1'))
                                    ->setDescription2($this->_getElement($style, 'Description2'))
                                    ->setDescription3($this->_getElement($style, 'Description3'))
                                    ->setDescription4($this->_getElement($style, 'Description4'))
                                    ->setEcommdescription($this->_getElement($style, 'ECommDescription'))
                                    ->setEcomerce($this->_helper->convertEcType($ecType))
                                    ->setTaxcategory($this->_getTaxcategory($channelId, $this->_getElement($style, 'TaxGroupCode')))
                                    ->setDcss($this->_getElement($style, 'DeptSetId'))
                                    ->setAcss($this->_getElement($style, 'AltDeptSetId'))
                                    ->setAttributeset1($this->_getElement($style, 'AttributeSet1Id'))
                                    ->setAttributeset2($this->_getElement($style, 'AttributeSet2Id'))
                                    ->setAttributeset3($this->_getElement($style, 'AttributeSet3Id'))
                                    ->setBrand($this->_getElement($style, 'BrandId'))
                                    ->setManufacturer($this->_getElement($style, 'ManufacturerId'))
                                    ->setUrlKey($this->_getAttribute($channel, 'UrlKey'))
                                    ->setOrderCost($this->_getElement($style, 'OrderCost'))
                                    ->setVendorNo($this->_getElement($style, 'PrimaryVendor'))
                                    ->setCustomdate1($this->_prepareDBDatetime($this->_getElement($style, 'CustomDate1')))
                                    ->setCustomdate2($this->_prepareDBDatetime($this->_getElement($style, 'CustomDate2')))
                                    ->setCustomdate3($this->_prepareDBDatetime($this->_getElement($style, 'CustomDate3')))
                                    ->setCustomdate4($this->_prepareDBDatetime($this->_getElement($style, 'CustomDate4')))
                                    ->setCustomdate5($this->_prepareDBDatetime($this->_getElement($style, 'CustomDate5')))
                                    ->setCustomdate6($this->_prepareDBDatetime($this->_getElement($style, 'CustomDate6')))
                                    ->setCustomflag1($this->_getElement($style, 'CustomFlag1'))
                                    ->setCustomflag2($this->_getElement($style, 'CustomFlag2'))
                                    ->setCustomflag3($this->_getElement($style, 'CustomFlag3'))
                                    ->setCustomflag4($this->_getElement($style, 'CustomFlag4'))
                                    ->setCustomflag5($this->_getElement($style, 'CustomFlag5'))
                                    ->setCustomflag6($this->_getElement($style, 'CustomFlag6'))
                                    ->setCustomlookup1($this->_getElement($style, 'CustomLookup1'))
                                    ->setCustomlookup2($this->_getElement($style, 'CustomLookup2'))
                                    ->setCustomlookup3($this->_getElement($style, 'CustomLookup3'))
                                    ->setCustomlookup4($this->_getElement($style, 'CustomLookup4'))
                                    ->setCustomlookup5($this->_getElement($style, 'CustomLookup5'))
                                    ->setCustomlookup6($this->_getElement($style, 'CustomLookup6'))
                                    ->setCustomlookup7($this->_getElement($style, 'CustomLookup7'))
                                    ->setCustomlookup8($this->_getElement($style, 'CustomLookup8'))
                                    ->setCustomlookup9($this->_getElement($style, 'CustomLookup9'))
                                    ->setCustomlookup10($this->_getElement($style, 'CustomLookup10'))
                                    ->setCustomlookup11($this->_getElement($style, 'CustomLookup11'))
                                    ->setCustomlookup12($this->_getElement($style, 'CustomLookup12'))
                                    ->setCustomnumber1($this->_getElement($style, 'CustomDecimal1'))
                                    ->setCustomnumber2($this->_getElement($style, 'CustomDecimal2'))
                                    ->setCustomnumber3($this->_getElement($style, 'CustomDecimal3'))
                                    ->setCustomnumber4($this->_getElement($style, 'CustomDecimal4'))
                                    ->setCustomnumber5($this->_getElement($style, 'CustomDecimal5'))
                                    ->setCustomnumber6($this->_getElement($style, 'CustomDecimal6'))
                                    ->setCustominteger1($this->_getElement($style, 'CustomNumber1'))
                                    ->setCustominteger2($this->_getElement($style, 'CustomNumber2'))
                                    ->setCustominteger3($this->_getElement($style, 'CustomNumber3'))
                                    ->setCustominteger4($this->_getElement($style, 'CustomNumber4'))
                                    ->setCustominteger5($this->_getElement($style, 'CustomNumber5'))
                                    ->setCustominteger6($this->_getElement($style, 'CustomNumber6'))
                                    ->setCustomtext1($this->_getElement($style, 'CustomText1'))
                                    ->setCustomtext2($this->_getElement($style, 'CustomText2'))
                                    ->setCustomtext3($this->_getElement($style, 'CustomText3'))
                                    ->setCustomtext4($this->_getElement($style, 'CustomText4'))
                                    ->setCustomtext5($this->_getElement($style, 'CustomText5'))
                                    ->setCustomtext6($this->_getElement($style, 'CustomText6'))
                                    ->setCustomlongtext1($this->_getElement($style, 'CustomLongText1'))
                                    ->setCustomlongtext2($this->_getElement($style, 'CustomLongText2'))
                                    ->setCustomlongtext3($this->_getElement($style, 'CustomLongText3'))
                                    ->setCustomlongtext4($this->_getElement($style, 'CustomLongText4'))
                                    ->setCustomlongtext5($this->_getElement($style, 'CustomLongText5'))
                                    ->setCustomlongtext6($this->_getElement($style, 'CustomLongText6'))
                                    ->setCustomlongtext7($this->_getElement($style, 'CustomLongText7'))
                                    ->setCustomlongtext8($this->_getElement($style, 'CustomLongText8'))
                                    ->setCustomlongtext9($this->_getElement($style, 'CustomLongText9'))
                                    ->setCustomlongtext10($this->_getElement($style, 'CustomLongText10'))
                                    ->setDateavailable($this->_getElement($style, 'DateavAilable'))
                                    ->setInactive($this->_getElement($style, 'Inactive'))
                                ->save();
                            }
                        }
                    }
                    if( $isStyleProcessed )
                    {
                        $this->_populateManufacturer($style);
                        $this->_populateRelations($style);
                    }
                }
            }
            
            if( !empty($processedChannels) )
            {
                $this->_populateInventory($itemsForRta,$processedChannels);
            }
        }
    }
    
    protected function _parseItem($style,$styleChannel)
    {
        $channelName = $this->_getAttribute($styleChannel, 'Name');
        $channelId = array_search($channelName, $this->_channels);
        $processedItems = array();
        foreach($style->Items->children() as $item)
        {
            $itemGuid = $this->_getElement($item, 'ItemId');
            $this->_populateIdentifier($item);
            // $this->_populatePrice($item,$channelId);
            foreach($item->EChannels->children() as $itemChannel)
            {
                if( $channelName == $this->_getAttribute($itemChannel, 'Name') )
                {
                    $itemEcType = $this->_getAttribute($itemChannel, 'Status');
                    if( in_array($itemEcType, $this->_helper->getProcessedEcTypes()) && !$this->_getElement($item, 'Inactive') )
                    {
                        $processedItems[] = $itemGuid;
                        $itemEntity = Mage::getModel('teamwork_common/staging_items')->loadByChannelAndGuid($channelId, $itemGuid);
                        
                        $itemEntity->setData($itemEntity->getGuidField(), $itemGuid)
                            ->setChannelId($channelId)
                            ->setRequestId($this->chqStaging->getData('ApiDocumentId'))
                            ->setStyleId($this->_getElement($style, 'StyleId'))
                            ->setPlu($this->_getElement($item, 'PLU'))
                            ->setEcomerce($this->_helper->convertEcType($itemEcType))
                            ->setAttribute1Id($this->_getElement($item, 'AttributeSetValue1ID'))
                            ->setAttribute2Id($this->_getElement($item, 'AttributeSetValue2ID'))
                            ->setAttribute3Id($this->_getElement($item, 'AttributeSetValue3ID'))
                            ->setWeight($this->_getElement($item, 'Weight'))
                            ->setWidth($this->_getElement($item, 'Width'))
                            ->setHeight($this->_getElement($item, 'Height'))
                            ->setLength($this->_getElement($item, 'Length'))
                            ->setSkukey($this->_getElement($item, 'SKUKey'))
                            ->setUrlKey($this->_getAttribute($itemChannel, 'UrlKey'))
                            ->setOrderCost($this->_getElement($item, 'OrderCost'))
                            // ->setVendorNo($this->_getElement($item, 'PrimaryVendorId')) //TODO PrimaryVendorId VendorNo
                            ->setCustomdate1($this->_prepareDBDatetime($this->_getElement($item, 'CustomDate1')))
                            ->setCustomdate2($this->_prepareDBDatetime($this->_getElement($item, 'CustomDate2')))
                            ->setCustomdate3($this->_prepareDBDatetime($this->_getElement($item, 'CustomDate3')))
                            ->setCustomdate4($this->_prepareDBDatetime($this->_getElement($item, 'CustomDate4')))
                            ->setCustomdate5($this->_prepareDBDatetime($this->_getElement($item, 'CustomDate5')))
                            ->setCustomdate6($this->_prepareDBDatetime($this->_getElement($item, 'CustomDate6')))
                            ->setCustomflag1($this->_getElement($item, 'CustomFlag1'))
                            ->setCustomflag2($this->_getElement($item, 'CustomFlag2'))
                            ->setCustomflag3($this->_getElement($item, 'CustomFlag3'))
                            ->setCustomflag4($this->_getElement($item, 'CustomFlag4'))
                            ->setCustomflag5($this->_getElement($item, 'CustomFlag5'))
                            ->setCustomflag6($this->_getElement($item, 'CustomFlag6'))
                            ->setCustomlookup1($this->_getElement($item, 'CustomLookup1'))
                            ->setCustomlookup2($this->_getElement($item, 'CustomLookup2'))
                            ->setCustomlookup3($this->_getElement($item, 'CustomLookup3'))
                            ->setCustomlookup4($this->_getElement($item, 'CustomLookup4'))
                            ->setCustomlookup5($this->_getElement($item, 'CustomLookup5'))
                            ->setCustomlookup6($this->_getElement($item, 'CustomLookup6'))
                            ->setCustomlookup7($this->_getElement($item, 'CustomLookup7'))
                            ->setCustomlookup8($this->_getElement($item, 'CustomLookup8'))
                            ->setCustomlookup9($this->_getElement($item, 'CustomLookup9'))
                            ->setCustomlookup10($this->_getElement($item, 'CustomLookup10'))
                            ->setCustomlookup11($this->_getElement($item, 'CustomLookup11'))
                            ->setCustomlookup12($this->_getElement($item, 'CustomLookup12'))
                            ->setCustomnumber1($this->_getElement($item, 'CustomDecimal1'))
                            ->setCustomnumber2($this->_getElement($item, 'CustomDecimal2'))
                            ->setCustomnumber3($this->_getElement($item, 'CustomDecimal3'))
                            ->setCustomnumber4($this->_getElement($item, 'CustomDecimal4'))
                            ->setCustomnumber5($this->_getElement($item, 'CustomDecimal5'))
                            ->setCustomnumber6($this->_getElement($item, 'CustomDecimal6'))
                            ->setCustominteger1($this->_getElement($item, 'CustomNumber1'))
                            ->setCustominteger2($this->_getElement($item, 'CustomNumber2'))
                            ->setCustominteger3($this->_getElement($item, 'CustomNumber3'))
                            ->setCustominteger4($this->_getElement($item, 'CustomNumber4'))
                            ->setCustominteger5($this->_getElement($item, 'CustomNumber5'))
                            ->setCustominteger6($this->_getElement($item, 'CustomNumber6'))
                            ->setCustomtext1($this->_getElement($item, 'CustomText1'))
                            ->setCustomtext2($this->_getElement($item, 'CustomText2'))
                            ->setCustomtext3($this->_getElement($item, 'CustomText3'))
                            ->setCustomtext4($this->_getElement($item, 'CustomText4'))
                            ->setCustomtext5($this->_getElement($item, 'CustomText5'))
                            ->setCustomtext6($this->_getElement($item, 'CustomText6'))
                            ->setData('IsChargeItem', $this->_getElement($item, 'IsChargeItem'))
                            ->setData('ChargeItemType', $this->_getElement($item, 'ChargeItemType'))
                            ->setData('EligibleForDiscount', $this->_getElement($item, 'EligibleForDiscount'))
                            ->setData('NeverChargeShipping', $this->_getElement($item, 'NeverChargeShipping'))
                        ->save();
                        $this->_populateCategories($itemGuid,$channelId,$itemChannel->children(),true);
                        $this->_populateRelations($style,$item);
                    }
                    break;
                }
            }
        }
        return array_unique($processedItems);
    }

    protected function _populateManufacturer($style)
    {
        $manufacturerGuid = $this->_getElement($style, 'ManufacturerId');
        if($manufacturerGuid)
        {
            $manufacturerEntity = Mage::getModel('teamwork_common/staging_manufacturer')->loadByGuid($manufacturerGuid);
            
            $manufacturerEntity->setData($manufacturerEntity->getGuidField(), $manufacturerGuid)
                ->setName($this->_getElement($style, 'Manufacturer'))
            ->save();
        }
    }
    
    protected function _populateIdentifier($item)
    {
        $itemGuid = $this->_getElement($item, 'ItemId');
        $identifierHelper = Mage::helper('teamwork_common/staging_identifier');
        
        $filter = new Varien_Object();
        $filter->setItemId($itemGuid);
        $existingIdentifiers = Mage::getModel('teamwork_common/staging_identifier')->loadCollectionByVarienFilter($filter);
        
        $identifiers = array();
        if( !empty($item->EID) )
        {
            $identifierGuid = $this->_getAttribute($item->EID, 'IdentifierId');
            if($identifierGuid)
            {
                $identifierEntity = Mage::getModel('teamwork_common/staging_identifier')->loadByGuid($identifierGuid);
                
                $identifierEntity->setData($identifierEntity->getGuidField(), $identifierGuid)
                    ->setItemId($itemGuid)
                    ->setIdclass($identifierHelper->getIdentifier(Teamwork_Common_Helper_Staging_Identifier::CHQ_IDENTIFIER_EID))
                    ->setValue($this->_getElement($item, 'EID'))
                ->save();
                $identifiers[] = $identifierGuid;
            }
        }
        
        if( !empty($item->CLU) )
        {
            $identifierGuid = $this->_getAttribute($item->CLU, 'IdentifierId');
            if($identifierGuid)
            {
                $identifierEntity = Mage::getModel('teamwork_common/staging_identifier')->loadByGuid($identifierGuid);
                
                $identifierEntity->setData($identifierEntity->getGuidField(), $identifierGuid)
                    ->setItemId($itemGuid)
                    ->setIdclass($identifierHelper->getIdentifier(Teamwork_Common_Helper_Staging_Identifier::CHQ_IDENTIFIER_CLU))
                    ->setValue($this->_getElement($item, 'CLU'))
                ->save();
                $identifiers[] = $identifierGuid;
            }
        }
        
        if( !empty($item->UPCs) )
        {
            foreach($item->UPCs->children() as $upc)
            {
                $identifierGuid = $this->_getAttribute($upc, 'IdentifierId');
                if($identifierGuid)
                {
                    $identifierEntity = Mage::getModel('teamwork_common/staging_identifier')->loadByGuid($identifierGuid);
                    
                    $identifierEntity->setData($identifierEntity->getGuidField(), $identifierGuid)
                        ->setItemId($itemGuid)
                        ->setIdclass($identifierHelper->getIdentifier(Teamwork_Common_Helper_Staging_Identifier::CHQ_IDENTIFIER_UPC))
                        ->setValue($this->_getAttribute($upc, 'Value'))
                    ->save();
                    $identifiers[] = $identifierGuid;
                }
            }
        }
        
        foreach($existingIdentifiers as $existingIdentifier)
        {
            if( array_search($existingIdentifier->getData($existingIdentifier->getGuidField()), $identifiers) === FALSE )
            {
                $existingIdentifier->delete();
            }
        }
    }
    
    protected function _populatePrice($item,$channelId)
    {
        $itemGuid = $this->_getElement($item, 'ItemId');
        
        $prices = array();
        if( !empty($this->_priceMapping[$channelId]) && !empty($item->BasePrice) )
        {
            foreach($this->_priceMapping[$channelId] as $priceMapping)
            {
                $level = $this->_getElement($priceMapping, 'OrderNo');
                $prices[$level] = $item->BasePrice;
            }
        }
        
        if(!empty($item->Prices))
        {
            foreach($item->Prices->children() as $price)
            {
                $priceId = $this->_getAttribute($price, 'PriceLevelId');
                if(!empty($this->_priceMapping[$channelId][$priceId]))
                {
                    $level = $this->_getElement($this->_priceMapping[$channelId][$priceId], 'OrderNo');
                    $prices[$level] = $this->_getAttribute($price, 'Price');
                }
                
            }
        }
        
        $filter = new Varien_Object();
        $filter
            ->setItemId($itemGuid)
        ->setChannelId($channelId);
        $priceEntity = Mage::getModel('teamwork_common/staging_price')->loadCollectionByVarienFilter($filter);
        
        foreach($priceEntity as $price)
        {
            $level = $price->getPriceLevel();
            if(!array_key_exists($level, $prices))
            {
                $price->delete();
            }
            else
            {
                $price
                    ->setPrice($prices[$level])
                    ->setRequestId($this->chqStaging->getData('ApiDocumentId'))
                ->save();
                unset($prices[$level]);
            }
        }
        
        if( !empty($prices) )
        {
            foreach($prices as $level => $price)
            {
                Mage::getModel('teamwork_common/staging_price')
                    ->setItemId($itemGuid)
                    ->setChannelId($channelId)
                    ->setPriceLevel($level)
                    ->setRequestId($this->chqStaging->getData('ApiDocumentId'))
                    ->setPrice($price)
                ->save();
            }
        }
    }
    
    protected function _populateRelations($style,$item=null)
    {
        return;
        $relationHelper = Mage::helper('teamwork_common/staging_relation');
        // TODO: how to delete relations?
        
        $entity = $style;
        $itemGuid = null;
        if( !empty($item) )
        {
            $entity = $item;
            $itemGuid = $this->_getElement($item, 'ItemId');
        }
        
        $styleGuid = $this->_getElement($style, 'StyleId');
        if( !empty($entity->Related) )
        {
            foreach($entity->Related->children() as $relation)
            {
                $relationType = $relationHelper->getRelationType($this->_getAttribute($relation, 'name'));
                foreach($relation->children() as $relatedEntities)
                {
                    if(!empty($relatedEntities))
                    {
                        foreach($relatedEntities->children() as $relatedEntityKey => $relatedEntity)
                        {
                            $relationKind = $relationHelper->getRelationKind((bool)$item,$relatedEntityKey);
                            
                            $relatedStyleGuid = $this->_getAttribute($relatedEntity, 'StyleId');
                            $relatedItemGuid = $this->_getAttribute($relatedEntity, 'ItemId');
                            
                            $filter = new Varien_Object();
                            $filter->setStyleId($styleGuid)
                                ->setItemId($itemGuid)
                                ->setRelatedStyleId($relatedStyleGuid)
                                ->setRelatedItemId($relatedItemGuid)
                                ->setRelatedStyleType($relationType)
                            ->setRelationKind($relationKind);
                            
                            $relationEntity = Mage::getModel('teamwork_common/staging_relation')->loadCollectionByVarienFilter($filter);
                            
                            
                           /*  $itemEntity->setData($itemEntity->getGuidField(), $itemGuid)
                                ->setChannelId($channelId)
                                ->setRequestId($this->chqStaging->getData('ApiDocumentId'))
                                ->setStyleId($this->_getElement($style, 'StyleId'))
                                ->setPlu($this->_getElement($item, 'PLU'))
                                ->setEcomerce($this->_helper->convertEcType($itemEcType))
                            // ->save()
                            ; */
                            // StyleId
                            // RelatedStyleId
                            // ItemId
                            // RelatedItemId
                            // RelatedStyleType
                            // RelationKind
                        }
                    }
                }
            }
        }
    }
    
    protected function _populateCategories($entityId,$channelId,$eCategories,$isItem=false)
    {
        $modelName = 'teamwork_common/staging_stylecategory';
        if($isItem)
        {
            $modelName = 'teamwork_common/staging_itemcategory';
        }
        
        $categoryIds = array();
        if(!empty($eCategories->ECategories))
        {
            foreach($eCategories->ECategories->children() as $category)
            {
                $categoryId = $this->_getAttribute($category, 'ECommerceCategoryid');
                $categoryIds[$categoryId] = $categoryId;
            }
        }
        
        $categoryRelationEntity = Mage::getModel($modelName);
        
        $filter = new Varien_Object();
        $filter
            ->setData($categoryRelationEntity->getGuidField(), $entityId)
        ->setChannelId($channelId);
        
        $categoryRelationEntity = $categoryRelationEntity->loadCollectionByVarienFilter($filter);
        
        foreach($categoryRelationEntity as $categoryRelation)
        {
            $categoryId = $categoryRelation->getCategoryId();
            if( !in_array($categoryId, $categoryIds) )
            {
                $categoryRelation->delete();
            }
            else
            {
                unset($categoryIds[$categoryId]);
            }
        }
        
        if( !empty($categoryIds) )
        {
            foreach($categoryIds as $categoryId)
            {
                $newCategory = Mage::getModel($modelName);
                $newCategory->setData($newCategory->getGuidField(), $entityId)
                    ->setChannelId($channelId)
                    ->setCategoryId($categoryId)
                ->save();
            }
        }
    }
    
    protected function _populateInventory($itemGuids, $channels)
    {
        if($itemGuids)
        {
            $inventoryInformation = $this->_callRta($itemGuids);
            
            if( !empty($inventoryInformation) )
            {
                foreach($inventoryInformation as $item)
                {
                    foreach($item['quantities'] as $location)
                    {
                        foreach($channels as $channelId)
                        {
                            $inventoryEntity = $this->_loadInventory($item['itemId'], $location['locationId'], $channelId);
                            $inventoryEntity->setData($inventoryEntity->getGuidField(), $location['locationId'])
                                ->setChannelId($channelId)
                                ->setItemId($item['itemId'])
                                ->setRequestId($this->chqStaging->getData('ApiDocumentId'))
                                ->setQuantity($location['available'])
                            ->save();
                        }
                    }
                }
            }
        }
    }
    
    protected function _callRta($items)
    {
        $inventory = array();
        if( !empty($items) )
        {
            $rtaModel = Mage::getSingleton('teamwork_realtimeavailability/realtimeavailability');
            $step = $rtaModel->_itemLimitPerBatch;  
            
            for($i=0,$j=count($items); $i<=$j; $i=$i+$step)
            {
                $guids = array_slice($items, $i, $step);
                $itemQuantities = $rtaModel->getInventory( $guids );
                if(!empty($itemQuantities['itemQuantities']))
                {
                    $inventory += $itemQuantities['itemQuantities'];
                }
            }
        }
        return $inventory;
    }
    
    protected function _loadInventory($productGuid, $locationGuid, $channelId)
    {
        $filter = new Varien_Object();
        $filter->setItemId($productGuid)
            ->setLocationId($locationGuid)
        ->setChannelId($channelId);
    
        $inventory = Mage::getModel('teamwork_common/staging_inventory')->loadCollectionByVarienFilter($filter);
        if( !empty($inventory) )
        {
            foreach($inventory as $inventoryLine)
            {
                return $inventoryLine;
            }
        }
        return Mage::getModel('teamwork_common/staging_inventory');
    }
}