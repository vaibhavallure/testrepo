<?php
class Teamwork_Common_Model_Chq_Xml_Response_Channel extends Teamwork_Common_Model_Chq_Xml_Response_Abstract
{
    const ECOMMERCE_CHANNEL_ENTITY_LOCATION = 'Location';
    const ECOMMERCE_CHANNEL_ENTITY_DISCOUNT_REASON = 'DiscountReason';
    const ECOMMERCE_CHANNEL_ENTITY_SERVICE_FEE = 'ServiceFee';
    const ECOMMERCE_CHANNEL_ENTITY_TAX_CATEGORY = 'TaxCategory';
    
    protected $_channelId;
    
    public function parse()
    {
        parent::parse();
        $this->_parseChannel();
    }
    
    protected function _parseChannel()
    {
        $xmlObject = $this->chqStaging->getResponse();
        if( !empty($xmlObject->ECommerceChannels) )
        {
            $stores = $this->getStoreForProcessing();
            foreach($xmlObject->ECommerceChannels->children() as $eCommerceChannel)
            {
                $this->_channelId = $this->_getElement($eCommerceChannel, 'ECommerceChannelId');
                $eCommerceChannelEntity = Mage::getModel('teamwork_common/staging_channel')->loadByGuid($this->_channelId);
                
                if( !$eCommerceChannelEntity->getId() )
                {
                    $eCommerceChannelEntity = Mage::getModel('teamwork_common/staging_channel')->loadByAttributes(
                        array('channel_name' => $this->_getElement($eCommerceChannel, 'Name'))
                    );
                }
                
                if( $this->_isDeleted($eCommerceChannel) || $this->_getElement($eCommerceChannel, 'ChannelType') != 'CommonChannel' || !in_array($this->_getElement($eCommerceChannel, 'Name'),$stores) )
                {
                    if( $eCommerceChannelEntity->getId() )
                    {
                        // $eCommerceChannelEntity->delete();
                    }
                    continue;
                }
                
                $eCommerceChannelEntity->setChannelId($this->_channelId)
                    ->setChannelName($this->_getElement($eCommerceChannel, 'Name'))
                ->save();
                
                $this->_parseChannelEntities($eCommerceChannel);
                $this->_parseChannelSettings($eCommerceChannel);
            }
        }
    }
    
    protected function _parseChannelEntities($eCommerceChannel)
    {
        if( !empty($eCommerceChannel->Entities) )
        {
            foreach($eCommerceChannel->Entities->children() as $entities)
            {
                if( !empty($entities) && $entities->children() )
                {
                    foreach($entities->children() as $entityName => $entity)
                    {
                        $this->_parseEntity($entityName, $entity);
                    }
                }
            }
        }
    }
    
    protected function _parseEntity($entityName,$entity)
    {
        switch($entityName)
        {
            case self::ECOMMERCE_CHANNEL_ENTITY_LOCATION:
            {
                $this->_parseLocationStatus($entity);
                break;
            }
            case self::ECOMMERCE_CHANNEL_ENTITY_DISCOUNT_REASON:
            {
                $this->_parseDiscountStatus($entity);
                break;
            }
            case self::ECOMMERCE_CHANNEL_ENTITY_SERVICE_FEE:
            {
                $this->_parseFeeStatus($entity);
                break;
            }
        }
    }
    
    protected function _parseLocationStatus($entity)
    {
        $locationGuid = $this->_getAttribute($entity, 'EntityId');
        $locationEntity = Mage::getModel('teamwork_common/staging_locationstatus')->loadByChannelAndGuid($this->_channelId, $locationGuid);
        
        $locationEntity->setData($locationEntity->getGuidField(), $locationGuid)
            ->setChannelId($this->_channelId)
            ->setEnabled($this->_getAttribute($entity, 'EComEnabled'))
        ->save();
    }
    
    protected function _parseDiscountStatus($entity)
    {
        $discountGuid = $this->_getAttribute($entity, 'EntityId');
        $discountEntity = Mage::getModel('teamwork_common/staging_discountstatus')->loadByChannelAndGuid($this->_channelId, $discountGuid);
        
        $discountEntity->setData($discountEntity->getGuidField(), $discountGuid)
            ->setChannelId($this->_channelId)
            ->setEnabled($this->_getAttribute($entity, 'EComEnabled'))
        ->save();
    }
    
    protected function _parseFeeStatus($entity)
    {
        $statusGuid = $this->_getAttribute($entity, 'EntityId');
        $statusEntity = Mage::getModel('teamwork_common/staging_feestatus')->loadByChannelAndGuid($this->_channelId, $statusGuid);
        
        $statusEntity->setData($statusEntity->getGuidField(), $statusGuid)
            ->setChannelId($this->_channelId)
            ->setEnabled($this->_getAttribute($entity, 'EComEnabled'))
        ->save();
    }
    
    protected function _parseChannelSettings($eCommerceChannel)
    {
        if( !empty($eCommerceChannel->Settings) )
        {
            foreach($eCommerceChannel->Settings->children() as $settingKey => $settings)
            {
                $settingEntity = Mage::getModel('teamwork_common/staging_settings')->loadByChannelAndGuid($this->_channelId, $settingKey);
                $settingEntity->setData($settingEntity->getGuidField(), $settingKey)
                    ->setChannelId($this->_channelId)
                    ->setSettingValue($settings)
                ->save();
                
                switch($settingKey)
                {
                    case Teamwork_Common_Model_Staging_Settings::SETTING_NAME_PAYMENTMETHODSETTINGS: 
                        $this->_populatePayment($settings);
                    break;
                    //TODO ?
                }
            }
        }
    }
    
    protected function _populatePayment($settings)
    {
        foreach($settings->children() as $setting)
        {
            $settingKey = $this->_getElement($setting, 'EcomPaymentMethod');
            $settingEntity = Mage::getModel('teamwork_common/staging_settingpayment')->loadByChannelAndGuid($this->_channelId, $settingKey);
            $settingEntity->setData($settingEntity->getGuidField(), $settingKey)
                ->setChannelId($this->_channelId)
                ->setDescription($this->_getElement($setting, 'EcomDescription')) 
                ->setAllowAuthorizeOnly($this->_getElement($setting, 'AllowAuthorizeOnly',true)) 
                ->setRefundInTeamwork($this->_getElement($setting, 'RefundOnCancel',true)) 
                ->setPaymentMethodId($this->_getElement($setting, 'PaymentMethodId'))
            ->save();
        }
    }
}