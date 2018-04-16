<?php
class Teamwork_Common_Model_Chq_Xml_Response_Location extends Teamwork_Common_Model_Chq_Xml_Response_Abstract
{
    public function parse()
    {
        parent::parse();
        $this->_parseLocation();
    }
    
    protected function _parseLocation()
    {
        $xmlObject = $this->chqStaging->getResponse();
        if( !empty($xmlObject->Locations) )
        {
            foreach($xmlObject->Locations->children() as $location)
            {  
                $locationGuid = $this->_getElement($location, 'LocationID');
                $locationEntity = Mage::getModel('teamwork_common/staging_location')->loadByGuid($locationGuid); //check loadByChannelAndGuid
                
                if( $this->_isDeleted($location) )
                {
                    // $locationEntity->delete();
                    continue;
                }
                
                $locationEntity->setLocationId($locationGuid)
                    ->setCode($this->_getElement($location, 'LocationCode'))
                    ->setName($this->_getElement($location, 'Name'))
                    ->setContact($this->_getElement($location, 'Contact'))
                    ->setAddress($this->_getElement($location, 'Address1'))
                    ->setAddress2($this->_getElement($location, 'Address2'))
                    ->setAddress3($this->_getElement($location, 'Address3'))
                    ->setAddress4($this->_getElement($location, 'Address4'))
                    ->setPostalCode($this->_getElement($location, 'PostalCode'))
                    ->setCity($this->_getElement($location, 'City'))
                    ->setState($this->_getElement($location, 'State'))
                    ->setCountry($this->_getElement($location, 'CountryCode'))
                    ->setLongitude($this->_getElement($location, 'Longitude'))
                    ->setLatitude($this->_getElement($location, 'Latitude'))
                    ->setPhone($this->_getElement($location, 'Phone1'))
                    ->setFax($this->_getElement($location, 'Fax'))
                    ->setEmail($this->_getElement($location, 'EMail'))
                    ->setHomePage($this->_getElement($location, 'HomePage'))
                    ->setAlias($this->_getElement($location, 'ECommerceAlias'))
                    ->setIsOpen($this->_getElement($location, 'IsActive'))
                    ->setLocationPriceGroup($this->_getElement($location, 'PriceGroupCode'))
                    ->setCustomDate1($this->_prepareDBDatetime($this->_getElement($location, 'CustomDate1')))
                    ->setCustomDate2($this->_prepareDBDatetime($this->_getElement($location, 'CustomDate2')))
                    ->setCustomDate3($this->_prepareDBDatetime($this->_getElement($location, 'CustomDate3')))
                    ->setCustomDate4($this->_prepareDBDatetime($this->_getElement($location, 'CustomDate4')))
                    ->setCustomDate5($this->_prepareDBDatetime($this->_getElement($location, 'CustomDate5')))
                    ->setCustomDate6($this->_prepareDBDatetime($this->_getElement($location, 'CustomDate6')))
                    ->setCustomFlag1($this->_getElement($location, 'CustomFlag1'))
                    ->setCustomFlag2($this->_getElement($location, 'CustomFlag2'))
                    ->setCustomFlag3($this->_getElement($location, 'CustomFlag3'))
                    ->setCustomFlag4($this->_getElement($location, 'CustomFlag4'))
                    ->setCustomFlag5($this->_getElement($location, 'CustomFlag5'))
                    ->setCustomFlag6($this->_getElement($location, 'CustomFlag6'))
                    ->setCustomFlag7($this->_getElement($location, 'CustomFlag7'))
                    ->setCustomFlag8($this->_getElement($location, 'CustomFlag8'))
                    ->setCustomFlag9($this->_getElement($location, 'CustomFlag9'))
                    ->setCustomFlag10($this->_getElement($location, 'CustomFlag10'))
                    ->setCustomFlag11($this->_getElement($location, 'CustomFlag11'))
                    ->setCustomFlag12($this->_getElement($location, 'CustomFlag12'))
                    ->setCustomLookup1($this->_getElement($location, 'CustomLookup1'))
                    ->setCustomLookup2($this->_getElement($location, 'CustomLookup2'))
                    ->setCustomLookup3($this->_getElement($location, 'CustomLookup3'))
                    ->setCustomLookup4($this->_getElement($location, 'CustomLookup4'))
                    ->setCustomLookup5($this->_getElement($location, 'CustomLookup5'))
                    ->setCustomLookup6($this->_getElement($location, 'CustomLookup6'))
                    ->setCustomLookup7($this->_getElement($location, 'CustomLookup7'))
                    ->setCustomLookup8($this->_getElement($location, 'CustomLookup8'))
                    ->setCustomLookup9($this->_getElement($location, 'CustomLookup9'))
                    ->setCustomLookup10($this->_getElement($location, 'CustomLookup10'))
                    ->setCustomLookup11($this->_getElement($location, 'CustomLookup11'))
                    ->setCustomLookup12($this->_getElement($location, 'CustomLookup12'))
                    ->setCustomNumber1($this->_getElement($location, 'CustomDecimal1'))
                    ->setCustomNumber2($this->_getElement($location, 'CustomDecimal2'))
                    ->setCustomNumber3($this->_getElement($location, 'CustomDecimal3'))
                    ->setCustomNumber4($this->_getElement($location, 'CustomDecimal4'))
                    ->setCustomNumber5($this->_getElement($location, 'CustomDecimal5'))
                    ->setCustomNumber6($this->_getElement($location, 'CustomDecimal6'))
                    ->setCustomInteger1($this->_getElement($location, 'CustomNumber1'))
                    ->setCustomInteger2($this->_getElement($location, 'CustomNumber2'))
                    ->setCustomInteger3($this->_getElement($location, 'CustomNumber3'))
                    ->setCustomInteger4($this->_getElement($location, 'CustomNumber4'))
                    ->setCustomInteger5($this->_getElement($location, 'CustomNumber5'))
                    ->setCustomInteger6($this->_getElement($location, 'CustomNumber6'))
                    ->setCustomText1($this->_getElement($location, 'CustomText1'))
                    ->setCustomText2($this->_getElement($location, 'CustomText2'))
                    ->setCustomText3($this->_getElement($location, 'CustomText3'))
                    ->setCustomText4($this->_getElement($location, 'CustomText4'))
                    ->setCustomText5($this->_getElement($location, 'CustomText5'))
                    ->setCustomText6($this->_getElement($location, 'CustomText6'))
                ->save();
                
                $this->_parseLocationSchedule($location);
            }
        }
    }
    
    protected function _parseLocationSchedule($location)
    {
        if( !empty($location->Schedule) )
        {
            $locationScheduleGuid = $this->_getElement($location, 'LocationID');
            foreach($location->Schedule->children() as $dayOfWeek => $locationSchedule)
            {
                $dayOfWeekInt = date('N', strtotime($dayOfWeek));
                
                $locationScheduleEntity = Mage::getModel('teamwork_common/staging_locationschedule');
                $locationScheduleEntity->loadByAttributes(
                    array(
                        $locationScheduleEntity->getGuidField() => $locationScheduleGuid,
                        'day'                                   => $dayOfWeekInt,
                    )
                );
                
                $locationScheduleEntity->setLocationId($locationScheduleGuid)
                    ->setOpenTime($this->_prepareDBDatetime($this->_getElement($locationSchedule, 'OpenTime')))
                    ->setCloseTime($this->_prepareDBDatetime($this->_getElement($locationSchedule, 'CloseTime')))
                    ->setDay($dayOfWeekInt)
                    ->setClosed((int)$this->_getAttribute($locationSchedule, 'closed'));
                $locationScheduleEntity->save();
            }
        }
    }
}