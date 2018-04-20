<?php
class Teamwork_Common_Model_Chq_Xml_Response_Price extends Teamwork_Common_Model_Chq_Xml_Response_Abstract
{
    public $basePriceCode = 'BASEPRICE';
    
    protected $_channels = array();
    protected $_priceMapping = array();
    protected $_skipLastProduct = false;
    protected $_additionalDataFromOlderSibling = false;
    
    public function parse()
    {
        parent::parse();
        $this->_helper = Mage::helper('teamwork_common/staging_product');
        $this->_channels = Mage::getModel('teamwork_common/staging_channel')->getChannels();
        $this->_collectPriceMapping();
        $this->_checkAdditionalData();
        $this->_populatePrice();
    }
    
    protected function _collectPriceMapping()
    {
        foreach($this->_channels as $channelId => $channel)
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
    
    protected function _checkAdditionalData()
    {
        $filter = new Varien_Object();
        $filter
            ->setDocumentId( array('neq' => $this->workingDocument->getDocumentId()) )
            ->setParentDocumentId(
                $this->workingDocument->getParentDocumentId() ? 
                    $this->workingDocument->getParentDocumentId()
                : $this->workingDocument->getDocumentId()
            )
        ->setStatus(Mage::helper('teamwork_common/staging_chq')->getWaitStatuses());
        $siblingWaitDocuments = Mage::getModel('teamwork_common/staging_chq')->loadCollectionByVarienFilter($filter);
        
        $entitiesPerChunk = (int)(Mage::helper('teamwork_common/adminsettings')->getEntitiesPerButch(
            Teamwork_Common_Model_Chq_Api_Type::getSettingForChunk($this->workingDocument->getApiType())
        ));
        
        if( !$this->workingDocument->getHostDocumentId() && (count($siblingWaitDocuments) > 0 || $this->chqStaging->getData('TotalRecords') > $entitiesPerChunk) )
        {
            $this->_skipLastProduct = true;
        }
        
        if($this->workingDocument->getParentDocumentId())
        {
            $filter = new Varien_Object();
            $filter->setStatus(Mage::helper('teamwork_common/staging_chq')->getSuccessfulStatuses());
            
            $additionalFilter = array(
                array(array('parent_document_id', 'document_id'), array($this->workingDocument->getParentDocumentId(),$this->workingDocument->getParentDocumentId()))
            );
            $olderSiblings = Mage::getModel('teamwork_common/staging_chq')->loadCollectionByVarienFilter($filter, $additionalFilter);
            
            if(count($olderSiblings) > 0 && $olderSiblings->getFirstItem()->getAdditionalData() )
            {
                $this->_additionalDataFromOlderSibling = unserialize($olderSiblings->getFirstItem()->getAdditionalData());
            }
        }
    }
    
    protected function _populatePrice()
    {
        $xmlObject = $this->chqStaging->getResponse();
        $requestId = $this->chqStaging->getData('ApiDocumentId');
        if( !empty($xmlObject->Prices) )
        {
            $sortablePrices = array();
            if( !empty($this->_additionalDataFromOlderSibling['last_product']) )
            {
                foreach($this->_additionalDataFromOlderSibling['last_product'] as $itemGuid => $priceInfoPack)
                {
                    foreach($priceInfoPack as $priceInfo)
                    {
                        $this->_buildPrice($sortablePrices,$itemGuid,$priceInfo['priceLevelCode'],$priceInfo['priceLevelId'],$priceInfo['price']);
                    }
                }
            }
            
            $lastProduct = array();
            foreach($xmlObject->Prices->children() as $priceNode)
            {
                $itemGuid = $this->_getElement($priceNode, 'ItemIdentifier');
                $priceLevelCode = $this->_getElement($priceNode, 'PriceLevel');
                $priceLevelId = $this->_getElement($priceNode, 'PriceLevelId');
                $price = $this->_getElement($priceNode, 'Price');
                
                if($this->_skipLastProduct)
                {
                    if( !empty($lastProduct) && key($lastProduct) != $itemGuid)
                    {
                        $lastProduct = array();
                    }
                    $lastProduct[$itemGuid][] = array(
                        'priceLevelCode'    => $priceLevelCode,
                        'priceLevelId'      => $priceLevelId,
                        'price'             => $price,
                    );
                }
                $this->_buildPrice($sortablePrices,$itemGuid,$priceLevelCode,$priceLevelId,$price);
            }
            if($this->_skipLastProduct)
            {
                foreach($sortablePrices as $channelId => $sortablePrice)
                {
                    unset($sortablePrices[$channelId][key($lastProduct)]);
                }
                $this->workingDocument->setAdditionalData( array('last_product' => $lastProduct) ); //TODO merge with other additional_data
            }
            
            foreach($sortablePrices as $channelId => $item)
            {
                foreach($item as $itemId => $pricePack)
                {
                    foreach($pricePack as $priceLevel => $priceAmount)
                    {
                        $priceEntity = Mage::getModel('teamwork_common/staging_price');
                        $priceEntity->loadByAttributes(
                            array(
                                'item_id'       => $itemId,
                                'channel_id'    => $channelId,
                                'price_level'   => $priceLevel,
                            )
                        );
                        
                        $priceEntity
                            ->setItemId($itemId)
                            ->setChannelId($channelId)
                            ->setPriceLevel($priceLevel)
                            ->setRequestId($this->chqStaging->getData('ApiDocumentId'))
                            ->setPrice($priceAmount)
                        ->save();
                    }
                }
                if( !$this->workingDocument->getHostDocumentId() )
                {
                    $this->_registrateEcm(
                        $channelId,
                        $this->chqStaging->getData('ApiDocumentId'),
                        Teamwork_Common_Model_Staging_Service::PROCESSABLE_TYPE_PRICES,
                        Teamwork_Common_Model_Staging_Service::STATUS_NEW
                    );
                }
            }
        }
    }
    
    protected function _buildPrice(&$sortablePrices,$itemGuid,$priceLevelCode,$priceLevelId,$price)
    {
        foreach($this->_priceMapping as $channelId => $priceMapping)
        {
            foreach($priceMapping as $priceMappingLevelId => $priceMappingLevel)
            {
                $level = $this->_getElement($priceMappingLevel, 'OrderNo');
                
                if(($priceLevelId == $priceMappingLevelId && $price !== '') ||
                    ($priceLevelCode == $this->basePriceCode && !isset($sortablePrices[$channelId][$itemGuid][$level]))
                )
                {
                    $sortablePrices[$channelId][$itemGuid][$level] = (float)$price;
                }
            }
        }
    }
}