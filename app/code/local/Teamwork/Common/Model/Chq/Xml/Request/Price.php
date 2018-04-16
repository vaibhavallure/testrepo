<?php
class Teamwork_Common_Model_Chq_Xml_Request_Price extends Teamwork_Common_Model_Chq_Xml_Request_Abstract
{
    protected $_styles = array();
    protected $_priceLevels = array();
    public $stylesMode = false;
    public function __construct(Varien_Object $requestData)
    {
        parent::__construct($requestData);
        if( $requestData->getHostDocumentId() )
        {
            $this->stylesMode = true;
            foreach(Mage::getModel('teamwork_common/staging_channel')->getChannels() as $channelId => $channel )
            {
                $filter = new Varien_Object();
                $filter->setRequestId(
                    Mage::helper('teamwork_common/staging_abstract')->getSaltedRequestId($requestData->getHostDocumentId(), $channelId)
                );
                
                $styleCollection = Mage::getModel('teamwork_common/staging_style')->loadCollectionByVarienFilter($filter);
                foreach($styleCollection as $style)
                {
                    if( !in_array($style->getStyleId(), $this->_styles) )
                    {
                        $this->_styles[] = $style->getStyleId();
                    }
                }
            }
        }
        
        $filter = new Varien_Object();
        $filter->setSettingName(Teamwork_Common_Model_Staging_Settings::SETTING_NAME_PRICELEVELSETTINGS);
        
        foreach(Mage::getModel('teamwork_common/staging_settings')->loadCollectionByVarienFilter($filter) as $settingEntity)
        {
            // TODO getSettingValue for collections!!!!!
            $settingValues = (array) Mage::helper('teamwork_common/parser')->deserializeXml( $settingEntity->getSettingValue() );
            foreach($settingValues as $priceMapping)
            {
                if( !empty($priceMapping) )
                {
                    if(!is_array($priceMapping))
                    {
                        $priceMapping = array($priceMapping);
                    }
                    foreach($priceMapping as $line)
                    {
                        $levelId = Mage::helper('teamwork_common/parser')->getXmlElementString($line,'PriceLevelId');
                        if( !in_array($levelId, $this->_priceLevels) )
                        {
                            $this->_priceLevels[] = $levelId;
                        }
                    }
                }
            }
        }
        /* if($this->stylesMode && !$this->_styles){} */ //TODO ???
    }
    
    protected function addTop()
    {
        parent::addTop();
        if( empty($this->stylesMode) )
        {
            $this->_xmlElement->Request->addChild(
                'Top',
                Mage::helper('teamwork_common/adminsettings')->getEntitiesPerButch(Teamwork_Common_Helper_Adminsettings::CHQ_API_PRICE_PER_BUTCH)
            );
        }
    }
    
    protected function addSkip()
    {
        if($this->_requestData->getProcessed())
        {
            $this->_xmlElement->Request->addChild('Skip', $this->_requestData->getProcessed());
        }
    }
    
    protected function addFilters($addRecModified=true)
    {
        $filters = parent::addFilters(false);
        
        $effectiveDateFilter = $filters->addChild('Filter');
            $effectiveDateFilter->addAttribute('Field', 'EffectiveDate');
            $effectiveDateFilter->addAttribute('Operator', 'Equal');
        $effectiveDateFilter->addAttribute('Value', Mage::helper('teamwork_common/dates')->getMagentoDatetime());
        
        if($this->_priceLevels)
        {
            $levelFilter = $filters->addChild('Filter');
                $levelFilter->addAttribute('Field', 'PriceLevelId');
                $levelFilter->addAttribute('Operator', 'Contains');
            $levelFilter->addAttribute('Value', implode(',', $this->_priceLevels));
        }
            
        if( !empty($this->stylesMode) )
        {
            $styleFilter = $filters->addChild('Filter');
                $styleFilter->addAttribute('Field', 'StyleId');
                $styleFilter->addAttribute('Operator', 'Contains');
            $styleFilter->addAttribute('Value', implode(',', $this->_styles));
        }
        else
        {
            $recModified = $this->_requestData->getMaxDate();
            if($recModified)
            {
                $recModifiedGreaterFilter = $filters->addChild('Filter');
                    $recModifiedGreaterFilter->addAttribute('Field', 'RecModified');
                    $recModifiedGreaterFilter->addAttribute('Operator', 'Greater than');
                $recModifiedGreaterFilter->addAttribute('Value', $recModified);
            }
        }
    }
    
    protected function addSettings($addSettings=false)
    {
        $settings = parent::addSettings(true);
        $settings->addChild('ItemIdentifierSetting', 'TeamworkId');
        $settings->addChild('PricesExportVersionToUseSetting', 'Simplified');
    }
    
    protected function addSortDescriptions($addSortDescription=true)
    {
        $sortDescriptions = parent::addSortDescriptions(false);
        
        $styleSort = $sortDescriptions->addChild('SortDescription');
        $styleSort->addAttribute('Name', 'StyleId');
        $styleSort->addAttribute('Direction', 'Ascending');
        
        $itemSort = $sortDescriptions->addChild('SortDescription');
        $itemSort->addAttribute('Name', 'ItemId');
        $itemSort->addAttribute('Direction', 'Ascending');
        
        $priceSort = $sortDescriptions->addChild('SortDescription');
        $priceSort->addAttribute('Name', 'PriceLevelId');
        $priceSort->addAttribute('Direction', 'Ascending');
    }
}