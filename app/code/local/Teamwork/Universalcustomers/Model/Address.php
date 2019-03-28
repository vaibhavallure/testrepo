<?php
class Teamwork_Universalcustomers_Model_Address extends Mage_Core_Model_Abstract
{
    protected $_staticAddressElements;
    public static $twUcAddressGuid = 'tw_uc_address_guid';
    public static $twUcAddressType = 'tw_uc_address_type';

    public function __construct()
    {
        $this->_staticAddressElements = (array)Mage::getConfig()->getNode('teamwork_universalcustomers/static_customer_address_fields');
    }

    public function rebuildAddressesForSvs(array &$customerData, Mage_Customer_Model_Customer $customer, $customerAddress=null)
    {
        $addressSet = $customer->getAddressesCollection();
        
        if( !empty($customerAddress) )
        {
            if( $entityId = $customerAddress->getEntityId() )
            {
                $customerAddress->setData( self::$twUcAddressGuid, $addressSet->getItemById($entityId)->getData(self::$twUcAddressGuid));
            }
            $addressSet = array($customerAddress);
        }

        foreach($addressSet as $address)
        {
            if($address['_deleted'] === true)
            {
                if( !empty($address[self::$twUcAddressGuid]) )
                {
                    $customerData['addresses'][$address[self::$twUcAddressGuid]] = null;
                }
            }
            else
            {
                if( empty($address[self::$twUcAddressGuid]) )
                {
                    $address[self::$twUcAddressGuid] = Mage::helper('teamwork_universalcustomers')->generateGuid();
                    if( !empty($address['entity_id']) )
                    {
                        Mage::getResourceSingleton('customer/address')->saveAttribute($address, self::$twUcAddressGuid);
                    }
                }

                if( $address["is_default_shipping"] || ( !empty($address['entity_id']) && $customer["default_shipping"] == $address['entity_id'] ) )
                {
                    $customerData['default_shipping_address_id'] = $address[self::$twUcAddressGuid];
                }
                if( $address["is_default_billing"] || ( !empty($address['entity_id']) && $customer["default_billing"] == $address['entity_id'] ) )
                {
                    $customerData['default_billing_address_id'] = $address[self::$twUcAddressGuid];
                }

                $svsAddress = array();
                foreach($this->_staticAddressElements as $svsKey => $magentoKey)
                {
                    if( $magentoKey == $this->_staticAddressElements['region'] )
                    {
                        $svsAddress[$svsKey] = $this->_prepareRegionForSvs($address['region_id'], $address['country_id'], $address['region']);
                    }
                    elseif( $magentoKey == $this->_staticAddressElements['type'] && empty($address[$magentoKey]) && empty($address['entity_id']) )
                    {
                        $svsAddress[$svsKey] = Mage::getModel('teamwork_universalcustomers/type')->getType($addressSet, $address);
                    }
                    else
                    {
                        $svsAddress[$svsKey] = empty($address[$magentoKey]) ? null : trim($address[$magentoKey]);
                    }
                }
                $customerData['addresses'][$address[self::$twUcAddressGuid]] = $svsAddress;
            }
        }
    }

    public function updateAddressesAfterLogin(array &$customerData, Mage_Customer_Model_Customer $customer, array $profile)
    {
        if( $customer->getId() )
        {
            $svsAddressesForModification = !empty($profile['addresses']) ? array_keys($profile['addresses']) : array();
            foreach($customer->getAddressesCollection() as $address)
            {
                $addressGuid = $address->getData( self::$twUcAddressGuid);
                if( !$addressGuid || ($key = array_search($addressGuid, $svsAddressesForModification)) === FALSE )
                {
                    $address->setData('_deleted', true);
                }
                else
                {
                    $address->addData( $this->_buidAddress($profile['addresses'][$addressGuid]) );
                    $address->setIsDefaultBilling( $profile['default_billing_address_id'] == $addressGuid ? 1 : 0 );
                    $address->setIsDefaultShipping( $profile['default_shipping_address_id'] == $addressGuid ? 1 : 0 );

                    unset($svsAddressesForModification[$key]);
                }
            }

            if( !empty($svsAddressesForModification) )
            {
                foreach($profile['addresses'] as $addressGuid => $svsAddress)
                {
                    if(in_array($addressGuid, $svsAddressesForModification) !== FALSE)
                    {
                        $this->_addAddress($customer, $profile, $addressGuid);
                    }
                }
            }

        }
        elseif( !empty($profile['addresses']) )
        {
            foreach($profile['addresses'] as $addressGuid => $svsAddress)
            {
                $this->_addAddress($customer, $profile, $addressGuid);
            }
        }
    }

    protected function _prepareRegionForSvs($region_id, $country_id, $addressRegion)
    {
        if( !empty($region_id) && !empty($country_id) )
        {
            $countryRegions = Mage::getResourceModel('directory/region_collection')
                ->addCountryFilter($country_id)
                ->load()
            ->toOptionArray();

            if( !empty($countryRegions) )
            {
                foreach($countryRegions as $countryRegion)
                {
                    if($region_id == $countryRegion['value'])
                    {
                        $region = Mage::getModel('directory/region')->load( $region_id )->getCode(); //TODO: from AZ to Arizona?
                        break;
                    }
                }
            }
        }

        if( empty($region) )
        {
            $region = !empty($addressRegion) ? trim($addressRegion) : null;
        }
        return $region;
    }

    protected function _buidAddress(array $svsAddress)
    {
        $magentoStandartAddress = array();
        foreach($this->_staticAddressElements as $svsKey => $magentoKey)
        {
            if($svsKey == $this->_staticAddressElements['region'])
            {
                $magentoStandartAddress += $this->_prepareRegionForMagento($svsAddress['region'], $svsAddress['country_id']);
            }
            else
            {
                $magentoStandartAddress[$magentoKey] =  empty($svsAddress[$svsKey]) ? null : trim($svsAddress[$svsKey]);
            }
        }
        return $magentoStandartAddress;
    }

    protected function _prepareRegionForMagento($region, $country_id)
    {
        $regionArray = array();
        if( !empty($country_id) )
        {
            $countryRegions = Mage::getResourceModel('directory/region_collection')
                ->addCountryFilter($country_id)
                ->load()
            ->toOptionArray();

            if( !empty($countryRegions) )
            {
                $region_id = Mage::getModel('directory/region')->loadByCode($region, $country_id)->getRegionId();
                if( empty($region_id) )
                {
                    $region_id = Mage::getModel('directory/region')->loadByName($region, $country_id)->getRegionId();
                }
                //TODO region: from AR to Arizona ?

                if( !empty($region_id) )
                {
                    $regionArray = array(
                        'region_id' => $region_id,
                        'region'    => $region
                    );
                }
            }
            else
            {
                $regionArray['region'] = $region;
            }
        }
        return $regionArray;
    }

    protected function _addAddress(Mage_Customer_Model_Customer $customer, array $profile, $addressGuid)
    {
        $address = Mage::getModel('customer/address');
        $address->addData( $this->_buidAddress($profile['addresses'][$addressGuid]) );
        $address->setIsDefaultBilling( $profile['default_billing_address_id'] == $addressGuid ? 1 : 0 );
        $address->setIsDefaultShipping( $profile['default_shipping_address_id'] == $addressGuid ? 1 : 0 );
        $address->setData( self::$twUcAddressGuid, $addressGuid);

        $customer->addAddress( $address );
    }
}
