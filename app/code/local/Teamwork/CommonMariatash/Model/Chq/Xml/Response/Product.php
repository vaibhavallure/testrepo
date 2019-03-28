<?php
class Teamwork_CommonMariatash_Model_Chq_Xml_Response_Product extends Teamwork_Common_Model_Chq_Xml_Response_Product
{
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
                                    ->setCustomlongtext16($this->_getElement($style, 'CustomLongText16'))
                                    ->setCustomlongtext17($this->_getElement($style, 'CustomLongText17'))
                                    ->setCustommultiselect1(serialize((array)$style->CustomMultiselects1))
                                    ->setCustommultiselect2(serialize((array)$style->CustomMultiselects2))
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
							->setCVlu($this->_getElement($item, 'VLU'))
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
}
