<?php
class Teamwork_Common_Model_Chq_Xml_Response_Category extends Teamwork_Common_Model_Chq_Xml_Response_Abstract
{
    public function parse()
    {
        parent::parse();
        $this->_parseChannelCategories();
    }
    
    protected function _parseChannelCategories()
    {
        $xmlObject = $this->chqStaging->getResponse();
        if( !empty($xmlObject->ECommerceCategories) )
        {
            $i=0;
            $processedChannels = array();
            foreach($xmlObject->ECommerceCategories->children() as $category)
            {
                $categoryGuid = $this->_getElement($category, 'ECommerceCategoryID');
                $channelId = $this->_getElement($category, 'ECommerceChannelId');
                $categoryEntity = Mage::getModel('teamwork_common/staging_category')->loadByChannelAndGuid($channelId, $categoryGuid);
                
                if( !in_array($channelId,$processedChannels) )
                {
                    $processedChannels[] = $channelId;
                }
                
                $categoryEntity->setData($categoryEntity->getGuidField(), $categoryGuid)
                    ->setChannelId($channelId)
                    ->setRequestId($this->chqStaging->getData('ApiDocumentId'))
                    ->setParentId($this->_getElement($category, 'ParentECommerceCategoryID'))
                    ->setCategoryName($this->_getElement($category, 'Name'))
                    ->setDescription($this->_getElement($category, 'Description'))
                    ->setKeywords($this->_getElement($category, 'Keywords'))
                    ->setCustomtext1($this->_getElement($category, 'CustomText1'))
                    ->setCustomtext2($this->_getElement($category, 'CustomText2'))
                    ->setCustomtext3($this->_getElement($category, 'CustomText3'))
                    ->setCustomtext4($this->_getElement($category, 'CustomText4'))
                    ->setCustomdate1($this->_prepareDBDatetime($this->_getElement($category, 'CustomDate1')))
                    ->setCustomdate2($this->_prepareDBDatetime($this->_getElement($category, 'CustomDate2')))
                    ->setCustomdate3($this->_prepareDBDatetime($this->_getElement($category, 'CustomDate3')))
                    ->setCustomdate4($this->_prepareDBDatetime($this->_getElement($category, 'CustomDate4')))
                    ->setCustomnumber1($this->_getElement($category, 'CustomNumber1'))
                    ->setCustomnumber2($this->_getElement($category, 'CustomNumber2'))
                    ->setCustomnumber3($this->_getElement($category, 'CustomNumber3'))
                    ->setCustomnumber4($this->_getElement($category, 'CustomNumber4'))
                    ->setCustomdeсimal1($this->_getElement($category, 'CustomDecimal1'))
                    ->setCustomdeсimal2($this->_getElement($category, 'CustomDecimal2'))
                    ->setCustomdeсimal3($this->_getElement($category, 'CustomDecimal3'))
                    ->setCustomdeсimal4($this->_getElement($category, 'CustomDecimal4'))
                    ->setCustomflag1($this->_getElement($category, 'CustomFlag1'))
                    ->setCustomflag2($this->_getElement($category, 'CustomFlag2'))
                    ->setCustomflag3($this->_getElement($category, 'CustomFlag3'))
                    ->setCustomflag4($this->_getElement($category, 'CustomFlag4'))
                    ->setCustomlookup1($this->_getElement($category, 'CustomLookup1'))
                    ->setCustomlookup2($this->_getElement($category, 'CustomLookup2'))
                    ->setCustomlookup3($this->_getElement($category, 'CustomLookup3'))
                    ->setCustomlookup4($this->_getElement($category, 'CustomLookup4'))
                    ->setChanged($i++)
                    ->setDisplayOrder($this->_getElement($category, 'OrderNo'))
                    ->setIsActive($this->_getElement($category, 'IsActive'))
                    ->setIsDeleted($this->_isDeleted($category))
                ->save()
                ;
            }
            if( !empty($processedChannels) )
            {
                foreach($processedChannels as $channelId)
                {
                   $this->_registrateEcm($channelId, $this->chqStaging->getData('ApiDocumentId'), Teamwork_Common_Model_Staging_Service::PROCESSABLE_TYPE_CATEGORIES, Teamwork_Common_Model_Staging_Service::STATUS_NEW); 
                }
            }
        }
    }
}