<?php
class Teamwork_Common_Model_Staging_Settings extends Teamwork_Common_Model_Staging_Abstractchanneled
{
    protected $_guidField = 'setting_name';
    const SETTING_NAME_SETTING_NAME = 'setting_name';
    const SETTING_NAME_RTA_MODIFIED_TIME = 'rta_modified_time';
    const SETTING_NAME_TEMPLATE_ELEMENTS = 'template_elements';
    const SETTING_NAME_MAPPING_DEFAULT_IMAGE = 'mapping_default_image';
    const SETTING_NAME_CHQ_API_DOCUMENT_ID = 'chq_api_document_id';
    const SETTING_NAME_MAPPING_DEFAULT_STYLE = 'mapping_default_style';
    const SETTING_NAME_MAPPING_DEFAULT_ITEM = 'mapping_default_item';
    const SETTING_NAME_ONLYCOMPLETED = 'OnlyCompleted';
    const SETTING_NAME_DONOTUPLOADFROMSTAGING = 'DoNotUploadFromStaging';
    const SETTING_NAME_RTQSERVERURL = 'RTQServerUrl';
    const SETTING_NAME_RCMSITEURL = 'RcmSiteUrl';
    const SETTING_NAME_WEBORDERPROCESSINGAREA = 'WebOrderProcessingArea';
    const SETTING_NAME_DEFAULTLOCATION = 'DefaultLocation';
    const SETTING_NAME_PAYMENTMETHODSETTINGS = 'PaymentMethodSettings';
    const SETTING_NAME_TAXCATEGORYSETTINGS = 'TaxCategorySettings';
    const SETTING_NAME_SHIPPINGMETHODSETTINGS = 'ShippingMethodSettings';
    const SETTING_NAME_PRICELEVELSETTINGS = 'PriceLevelSettings';
    
    protected $_xmlGroup = array(
        self::SETTING_NAME_PAYMENTMETHODSETTINGS,
        self::SETTING_NAME_TAXCATEGORYSETTINGS,
        self::SETTING_NAME_SHIPPINGMETHODSETTINGS,
        self::SETTING_NAME_PRICELEVELSETTINGS,
    );
    
    protected $_serializedGroup = array(
        self::SETTING_NAME_TEMPLATE_ELEMENTS,
        self::SETTING_NAME_MAPPING_DEFAULT_IMAGE,
        self::SETTING_NAME_MAPPING_DEFAULT_STYLE,
        self::SETTING_NAME_MAPPING_DEFAULT_ITEM,
    );

    public function _construct()
    {
        parent::_construct();
        $this->_init('teamwork_common/settings');
    }
    
    /* public function getSettingValue()
    {
        if( is_string($this->getData('setting_value')) )
        {
            $this->_deserializeFields();
        }
        return $this->getData('setting_value');
    }*/
    
    protected function _beforeSave()
    {
        if( in_array($this->getData($this->_guidField),$this->_xmlGroup) && $this->getSettingValue() ) //TODO check instanceof SimpleXMLElement
        {
            $this->setSettingValue($this->getSettingValue()->asXml());
        }
        
        return parent::_beforeSave();
    }
    
    protected function _deserializeFields()
    {
        if( in_array($this->getData($this->_guidField),$this->_xmlGroup) && $this->getSettingValue() ) //TODO check instanceof SimpleXMLElement
        {
            $xmlObject = Mage::helper('teamwork_common/parser')->deserializeXml( $this->getSettingValue() );
            
            $this->setSettingValue($xmlObject->children());
        }
    }
    
    protected function _afterLoad()
    {
        $this->_deserializeFields();
        
        return parent::_afterLoad();
    }
}