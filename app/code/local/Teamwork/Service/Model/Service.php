<?php

class Teamwork_Service_Model_Service extends Mage_Core_Model_Abstract
{
    protected $_xml, $_db, $_request_id, $_status;

    /**
     * Object for parsing XML
     *
     * @var Teamwork_Service_Helper_Parse
     */
    protected $_parser;

    protected $_errors = array();
    protected $_responseAsXml = true;
    protected $_nameForMediaSetting = 'RcmSiteUrl';
    protected $_defaultTaxCategory = '0';
    protected $_useRealtimeavailability = false;
    protected $_errorLevels = array(
        'success'   => 'Success',
        'error'     => 'Error',
        'warning'   => 'Warning'
    );
    protected $_mediaLink;
    protected $richMediaTypes = array(
        'Thumbnails',
        'LargeImages',
        'LongDescription',
        'Videos',
        'VideoLinks',
        'ImageLinks'
    );

    protected $_skipEcmType = array('Channels', 'Settings', 'Collections',
                      'Fees', 'LineDiscounts', 'GlobalDiscounts', 'Discounts',
                      'TaxCategories', 'ShipMethods');

    const PREFIX_FOR_UNSUITABLE_ATTRIBUTE_CODE = 'teamwork_';

    protected function _construct()
    {
        $this->_db = Mage::getModel('teamwork_service/adapter_db');
        $this->_parser = Mage::helper('teamwork_service/parse');
        $this->_useRealtimeavailability = Mage::helper('teamwork_service')->useRealtimeavailability();
        
        Mage::helper('teamwork_service')->fatalErrorObserver();
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
        set_time_limit(0);

        if($this->_useRealtimeavailability)
        {
            $this->_skipEcmType[] = 'Qtys';
        }
    }
    public function setRequestId($requestId)
    {
        $this->_request_id = $requestId;
    }

    public function parseXml($xml)
    {
        $this->_xml = simplexml_load_string($xml);

        if(!empty($this->_xml))
        {
            if(isset($this->_xml->EcmLines->Categories->Category))
            {
                $this->getCategories();
            }
            elseif(isset($this->_xml->EcmLines->ShipMethods->ShipMethod))
            {
                $this->getShipMethods();
            }
            elseif(isset($this->_xml->EcmLines->Locations->Location))
            {
                $this->getLocations();
            }
            elseif(isset($this->_xml->EcmLines->LineDiscounts->LineDiscount) || isset($this->_xml->EcmLines->GlobalDiscounts->GlobalDiscount))
            {
                $this->getDiscounts();
            }
            elseif(isset($this->_xml->EcmLines->Settings))
            {
                $this->getSettings();
            }
            elseif(isset($this->_xml->EcmLines->Fees->Fee))
            {
                $this->getFees();
            }
            elseif(isset($this->_xml->EcmLines->Collections->Collection))
            {
                $this->getCollections();
            }
            elseif(isset($this->_xml->EcmLines->Prices->PriceRecord))
            {
                $this->getPrices();
            }
            elseif(isset($this->_xml->EcmLines->Styles->Style))
            {
                $this->getStyles();
            }
            elseif(isset($this->_xml->EcmLines->Qtys->Qty) && !$this->_useRealtimeavailability)
            {
                $this->getInventory();
            }
            elseif(isset($this->_xml->EcmLines->Packages->Package))
            {
                $this->getPackages();
            }
            elseif(isset($this->_xml->EcmLines->AttributeSets->AttributeSet))
            {
                $this->getAttributeSet();
            }
        }
    }

    protected function getInventory($Qtys = array())
    {
        $table = 'service_inventory';
        if(empty($Qtys))
        {
            $Qtys = $this->_xml->EcmLines->Qtys->Qty;
        }

        $channel = (string) $this->_xml->Channel;
        foreach($Qtys as $qty)
        {
            $item_id = (string)$qty->Item;
            $location = (string)$qty->Location;
            $quantity = (int)$qty->Qty;
            
            $data = array(
                'item_id'       => $item_id,
                'request_id'    => $this->_request_id,
                'channel_id'    => $channel,
                'location_id'   => $location,
                'quantity'      => $quantity
            );

            $item_id = $this->_db->getOne($table, array('item_id' => $item_id, 'channel_id' => $channel, 'location_id' => $location), 'item_id');
            if($item_id)
            {
                $this->_db->update($table, $data, array('item_id' => $item_id, 'channel_id' => $channel, 'location_id' => $location));
            }
            else
            {
                $this->_db->insert($table, $data);
            }
        }
    }

    protected function getCategories()
    {
        $table = 'service_category';
        $i = 0;

        foreach ($this->_xml->EcmLines->Categories->Category as $category)
        {
            $category_id = $this->_parser->getElementVal($category, false, 'CategoryId');
            $is_deleted = $this->_parser->getElementVal($category->IsDeleted) == 'true' ? 1 : 0;
            $data = array(
                'category_id'       => $category_id,
                'request_id'        => $this->_request_id,
                'channel_id'        => $this->_xml->Channel,
                'parent_id'         => $this->_parser->getElementVal($category->ParentCategoryId) ? $this->_parser->getElementVal($category->ParentCategoryId) : 0,
                'category_name'     => $this->_parser->getElementVal($category->Name),
                'description'       => $this->_parser->getElementVal($category->Description),
                'keywords'          => $this->_parser->getElementVal($category->Keywords),
                'display_order'     => $this->_parser->getElementVal($category->DisplayOrder),
                'changed'           => ++$i,
                'is_active'         => $this->_parser->getElementVal($category->IsActive) == 'true' ? 1 : 0,
                'is_deleted'        => $is_deleted
            );

            if($this->_db->getOne($table, array('category_id' => $category_id, 'channel_id' => $this->_parser->getElementVal($this->_xml->Channel)), 'category_id'))
            {
                $this->_db->update($table, $data, array('category_id' => $category_id, 'channel_id' => $this->_parser->getElementVal($this->_xml->Channel)));
                $this->getMedia($category, 'category', $category_id);
            }
            elseif( !$is_deleted )
            {
                $this->_db->insert($table, $data);
                $this->getMedia($category, 'category', $category_id);
            }
        }
    }

    protected function getLocations()
    {
        $table = 'service_location';
        $table_status = 'service_location_status';
        $table_schedule = 'service_location_schedule';
        $channel = $this->_xml->Channel;

        foreach($this->_xml->EcmLines->Locations->Location as $location)
        {
            $locationID = $this->_parser->getElementVal($location, false, 'LocationId');
            $data = array(
                'location_id'           => $locationID,
                'code'                  => $this->_parser->getElementVal($location->Code),
                'name'                  => $this->_parser->getElementVal($location->Name),
                'contact'               => $this->_parser->getElementVal($location->Contact),
                'address'               => $this->_parser->getElementVal($location->Address),
                'address2'              => $this->_parser->getElementVal($location->Address2),
                'address3'              => $this->_parser->getElementVal($location->Address3),
                'address4'              => $this->_parser->getElementVal($location->Address4),
                'postal_code'           => $this->_parser->getElementVal($location->PostalCode),
                'city'                  => $this->_parser->getElementVal($location->City),
                'state'                 => $this->_parser->getElementVal($location->State),
                'country'               => $this->_parser->getElementVal($location->Country),
                'longitude'             => $this->_parser->getElementVal($location->Longitude),
                'latitude'              => $this->_parser->getElementVal($location->Latitude),
                'phone'                 => $this->_parser->getElementVal($location->Phone),
                'fax'                   => $this->_parser->getElementVal($location->Fax),
                'email'                 => $this->_parser->getElementVal($location->EMail),
                'home_page'             => $this->_parser->getElementVal($location->HomePage),
                'alias'                 => $this->_parser->getElementVal($location->ECommAlias),
                'is_open'               => $this->_parser->getElementVal($location->IsOpen),
                'location_price_group'  => $this->_parser->getElementVal($location->LocationPriceGroup),
                'custom_date1'          => $this->_parser->getElementVal($location->CustomDate1),
                'custom_date2'          => $this->_parser->getElementVal($location->CustomDate2),
                'custom_date3'          => $this->_parser->getElementVal($location->CustomDate3),
                'custom_date4'          => $this->_parser->getElementVal($location->CustomDate4),
                'custom_date5'          => $this->_parser->getElementVal($location->CustomDate5),
                'custom_date6'          => $this->_parser->getElementVal($location->CustomDate6),
                'custom_flag1'          => $this->_parser->getElementVal($location->CustomFlag1),
                'custom_flag2'          => $this->_parser->getElementVal($location->CustomFlag2),
                'custom_flag3'          => $this->_parser->getElementVal($location->CustomFlag3),
                'custom_flag4'          => $this->_parser->getElementVal($location->CustomFlag4),
                'custom_flag5'          => $this->_parser->getElementVal($location->CustomFlag5),
                'custom_flag6'          => $this->_parser->getElementVal($location->CustomFlag6),
                'custom_flag7'          => $this->_parser->getElementVal($location->CustomFlag7),
                'custom_flag8'          => $this->_parser->getElementVal($location->CustomFlag8),
                'custom_flag9'          => $this->_parser->getElementVal($location->CustomFlag9),
                'custom_flag10'         => $this->_parser->getElementVal($location->CustomFlag10),
                'custom_flag11'         => $this->_parser->getElementVal($location->CustomFlag11),
                'custom_flag12'         => $this->_parser->getElementVal($location->CustomFlag12),
                'custom_lookup1'        => $this->_parser->getElementVal($location->CustomLookup1, 'Value'),
                'custom_lookup2'        => $this->_parser->getElementVal($location->CustomLookup2, 'Value'),
                'custom_lookup3'        => $this->_parser->getElementVal($location->CustomLookup3, 'Value'),
                'custom_lookup4'        => $this->_parser->getElementVal($location->CustomLookup4, 'Value'),
                'custom_lookup5'        => $this->_parser->getElementVal($location->CustomLookup5, 'Value'),
                'custom_lookup6'        => $this->_parser->getElementVal($location->CustomLookup6, 'Value'),
                'custom_lookup7'        => $this->_parser->getElementVal($location->CustomLookup7, 'Value'),
                'custom_lookup8'        => $this->_parser->getElementVal($location->CustomLookup8, 'Value'),
                'custom_lookup9'        => $this->_parser->getElementVal($location->CustomLookup9, 'Value'),
                'custom_lookup10'       => $this->_parser->getElementVal($location->CustomLookup10, 'Value'),
                'custom_lookup11'       => $this->_parser->getElementVal($location->CustomLookup11, 'Value'),
                'custom_lookup12'       => $this->_parser->getElementVal($location->CustomLookup12, 'Value'),
                'custom_number1'        => $this->_parser->getElementVal($location->CustomNumber1),
                'custom_number2'        => $this->_parser->getElementVal($location->CustomNumber2),
                'custom_number3'        => $this->_parser->getElementVal($location->CustomNumber3),
                'custom_number4'        => $this->_parser->getElementVal($location->CustomNumber4),
                'custom_number5'        => $this->_parser->getElementVal($location->CustomNumber5),
                'custom_number6'        => $this->_parser->getElementVal($location->CustomNumber6),
                'custom_integer1'       => $this->_parser->getElementVal($location->CustomInteger1),
                'custom_integer2'       => $this->_parser->getElementVal($location->CustomInteger2),
                'custom_integer3'       => $this->_parser->getElementVal($location->CustomInteger3),
                'custom_integer4'       => $this->_parser->getElementVal($location->CustomInteger4),
                'custom_integer5'       => $this->_parser->getElementVal($location->CustomInteger5),
                'custom_integer6'       => $this->_parser->getElementVal($location->CustomInteger6),
                'custom_text1'          => $this->_parser->getElementVal($location->CustomText1),
                'custom_text2'          => $this->_parser->getElementVal($location->CustomText2),
                'custom_text3'          => $this->_parser->getElementVal($location->CustomText3),
                'custom_text4'          => $this->_parser->getElementVal($location->CustomText4),
                'custom_text5'          => $this->_parser->getElementVal($location->CustomText5),
                'custom_text6'          => $this->_parser->getElementVal($location->CustomText6),
            );

            if( $this->_db->getOne($table, array('location_id' => $locationID), 'location_id') )
            {
                $this->_db->update($table, $data, array('location_id' => $locationID));
            }
            else
            {
                $this->_db->insert($table, $data);
            }
            
            $data = array(
                'location_id'   => $locationID,
                'channel_id'    => $channel,
                'enabled'       => ($this->_parser->getElementVal($location->ECommEnabled) == 'true') ? 1 : 0,
            );
            
            if( $this->_db->getOne($table_status, array('location_id' => $locationID, 'channel_id'  => $channel), 'location_id') )
            {
                $this->_db->update($table_status, $data, array('location_id' => $locationID, 'channel_id'  => $channel));
            }
            else
            {
                $this->_db->insert($table_status, $data);
            }

            $this->getMedia($location, 'location', $locationID);
            for($i = 1; $i <= 7; $i++)
            {
                if(isset($location->OperatingSchedule->{'Day'.$i}))
                {
                    $day = $location->OperatingSchedule->{'Day'.$i};
                    $data = array(
                        'location_id'   => $locationID,
                        'open_time'     => $day->OpenTime,
                        'close_time'    => $day->CloseTime,
                        'day'           => $i,
                        'closed'        => $day->Closed
                    );

                    if($this->_db->getOne($table_schedule, array('location_id' => $locationID, 'day' => $i), 'location_id'))
                    {
                        $this->_db->update($table_schedule, $data, array('location_id' => $locationID, 'day' => $i));
                    }
                    else
                    {
                        $this->_db->insert($table_schedule, $data);
                    }
                }
                else
                {
                    break;
                }
            }
        }
    }

    protected function getDiscounts()
    {
        $channel = (string)$this->_xml->Channel;
        $table = 'service_discount';
        $table_status = 'service_discount_status';
        if($this->_xml->EcmLines->LineDiscounts->LineDiscount)
        {
            foreach($this->_xml->EcmLines->LineDiscounts->LineDiscount as $discount)
            {
                $discountId = $this->_parser->getElementVal($discount, false, 'LineDiscountId');
                
                $data = array(
                    'discount_id'  => $discountId,
                    'code'         => $this->_parser->getElementVal($discount->Code),
                    'description'  => $this->_parser->getElementVal($discount->Description),
                    'type'         => 0,
                    'default_perc' => $this->_parser->getElementVal($discount->DefaultPerc)
                );
                if($this->_db->getOne($table, array('discount_id' => $discountId), 'discount_id'))
                {
                    $this->_db->update($table, $data, array('discount_id' => $discountId));
                }
                else
                {
                    $this->_db->insert($table, $data);
                }
                
                $data = array(
                    'discount_id'   => $discountId,
                    'channel_id'    => $channel,
                    'enabled'       => $this->_parser->getElementVal($discount->ECommEnabled) == 'true' ? 1 : 0,
                );
                if($this->_db->getOne($table_status, array('discount_id' => $discountId, 'channel_id' => $channel), 'discount_id'))
                {
                    $this->_db->update($table_status, $data, array('discount_id' => $discountId, 'channel_id' => $channel));
                }
                else
                {
                    $this->_db->insert($table_status, $data);
                }
            }
        }

        if($this->_xml->EcmLines->GlobalDiscounts->GlobalDiscount)
        {
            foreach($this->_xml->EcmLines->GlobalDiscounts->GlobalDiscount as $discount)
            {
                $discountId = $this->_parser->getElementVal($discount, false, 'GlobalDiscountId');
                if($this->_parser->getElementVal($discount->ECommEnabled) != 'true')
                {
                    $this->_db->delete($table, array('discount_id' => $discountId));
                    continue;
                }
                $data = array(
                    'discount_id'  => $discountId,
                    'code'         => $this->_parser->getElementVal($discount->Code),
                    'description'  => $this->_parser->getElementVal($discount->Description),
                    'type'         => 1,
                    'default_perc' => $this->_parser->getElementVal($discount->DefaultPerc)
                );
                
                if($this->_db->getOne($table, array('discount_id' => $discountId), 'discount_id'))
                {
                    $this->_db->update($table, $data, array('discount_id' => $discountId));
                }
                else
                {
                    $this->_db->insert($table, $data);
                }
            }
        }
    }

    protected function getSettings()
    {
        $all = $this->_xml->EcmLines->Settings;
        $table = 'service_settings';
        $channel = (string)$this->_xml->Channel;

        foreach ((array)$all as $key => $setting)
        {
            $data = array(
                'setting_name'  => $key,
                'channel_id'    => $channel,
                'setting_value' => $setting
            );

            if($this->_db->getOne($table, array('setting_name' => $key,  'channel_id'  => $channel), 'setting_name'))
            {
                $this->_db->update($table, $data, array('setting_name' => $key, 'channel_id'  => $channel));
            }
            else
            {
                $this->_db->insert($table, $data);
            }
        }
    }

    protected function getFees()
    {
        $channel = (string)$this->_xml->Channel;
        foreach ($this->_xml->EcmLines->Fees->Fee as $fee)
        {
            $feeID = $this->_parser->getElementVal($fee, false, 'FeeId');
            $data = array(
                'fee_id'         => $feeID,
                'code'           => $this->_parser->getElementVal($fee->Code),
                'description'    => $this->_parser->getElementVal($fee->Description),
                'alias'          => $this->_parser->getElementVal($fee->ECommAlias),
                'item_level'     => $this->_parser->getElementVal($fee->ItemLevel) == 'true' ? 1 : 0,
                'global_level'   => $this->_parser->getElementVal($fee->GlobalLevel) == 'true' ? 1 : 0,
                'default_perc'   => $this->_parser->getElementVal($fee->DefaultPerc),
                'default_amount' => $this->_parser->getElementVal($fee->DefaultAmount)
            );
            
            $table = 'service_fee';
            if($this->_db->getOne($table, array('fee_id' => $feeID), 'fee_id'))
            {
                $this->_db->update($table, $data, array('fee_id' => $feeID));
            }
            else
            {
                $this->_db->insert($table, $data);
            }
            
            $data = array(
                'fee_id'        => $feeID,
                'channel_id'    => $channel,
                'enabled'       => $this->_parser->getElementVal($fee->ECommEnabled) == 'true' ? 1 : 0
            );
            
            $table = 'service_fee_status';
            if($this->_db->getOne($table, array('fee_id' => $feeID, 'channel_id' => $channel), 'fee_id'))
            {
                $this->_db->update($table, $data, array('fee_id' => $feeID, 'channel_id' => $channel));
            }
            else
            {
                $this->_db->insert($table, $data);
            }
            
        }
    }

    /**
     * Looks whether $newMedia contains media info that differs from $oldMedia
     *
     * @param  array $oldMedia
     * @param  array $newMedia
     *
     * @return bool
     */
    protected function _mediaWasChanged($oldMedia, $newMedia)
    {
        $result = false;
        foreach ($newMedia as $field => $value)
        {
            if ($field == 'request_id')
            {
                continue;
            }
            if ($oldMedia[$field] != $value)
            {
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * Generates unique media ID. Inserted for upgrading from 4.1.21 to 4.1.22
     *
     * @param  string $namespace
     *
     * @return string
     */
    public function generateMediaId($media, $restrictedMediaIds = array())
    {
        // wipe fields we don't want to form a hash
        $fieldsToExclude = array('request_id', 'order');
        foreach ($fieldsToExclude as $field)
        {
            if (isset($media[$field]))
            {
                unset($media[$field]);
            }
        }

        $mediaId = Mage::helper('teamwork_service')->getGuidFromString(join('',$media));
        $repeatedCount = 0;
        while (in_array($mediaId, $restrictedMediaIds))
        {
            $media['repeat_no'] = ++$repeatedCount;
            $mediaId            = Mage::helper('teamwork_service')->getGuidFromString(join('',$media));
        }
        return $mediaId;
    }

    protected function getMedia($node, $type, $hostID)
    {
        $mainTable       = 'service_media';
        $internalIdTable = 'service_media_value';
        $channel = (string)$this->_xml->Channel;

        $i = 0;
        $dataToInsert = array();

        foreach($this->richMediaTypes as $richMediaType)
        {
            if(isset($node->RichMedia->$richMediaType->RichMediaElement))
            {
                foreach($node->RichMedia->$richMediaType->RichMediaElement as $media)
                {

                    $data = array(
                        'media_id'          => Mage::helper('teamwork_service')->getGuidFromString($media['Id'] . $media['Uri']),
                        'media_uri'         => $media['Uri'],
                        'host_type'         => $type,
                        'host_id'           => $hostID,
                        'channel_id'        => $channel,
                        'request_id'        => $this->_request_id,
                        'media_type'        => $richMediaType,
                        'media_name'        => $media['ContentName'],
                        'media_sub_type'    => $media['Type'],
                        'media_index'       => $media['Index'],
                        'order'             => $i
                    );

                    foreach($media->attributes() as $key => $val)
                    {
                        if ($key == 'DirectUri') $data['direct_uri'] = (string)$val;
                    }

                    if(isset($media->Attribute))
                    {
                        foreach($media->Attribute as $attribute)
                        {
                            $data['attribute' . $attribute['Index']] = (string)$attribute['Id'];
                        }
                    }

                    $dataToInsert[] = $data;
                    $i++;
                }
            }
        }


        // delete and reinsert info about images
        $this->_db->delete($mainTable, array('host_id' => $hostID, 'host_type' => $type, 'channel_id' => $channel));
        foreach ($dataToInsert as $data)
        {
            $this->_db->insert($mainTable, $data);
        }
    }

    protected function getVendorData($entity)
    {
        $vendorData = array('OrderCost' => 0, 'VendorNo' => null);
        foreach($entity->Vendors->Vendor as $vendor)
        {
            if( $this->_parser->getElementVal($vendor, false, 'IsPrimary') )
            {
                $vendorData['OrderCost'] = $this->_parser->getElementVal($vendor->OrderCost);
                $vendorData['VendorNo'] = $this->_parser->getElementVal($vendor->VendorNo);
                break;
            }
        }
        return $vendorData;
    }
    
    protected function getCollections()
    {
        $table = 'service_collection';

        foreach ($this->_xml->EcmLines->Collections->Collection as $collection)
        {
            $collection_id = $this->_parser->getElementVal($collection, false, 'CollectionId');
            $data = array(
                'collection_id' => $collection_id,
                'name'          => $this->_parser->getElementVal($collection->Name),
                'description'   => $this->_parser->getElementVal($collection->Description)
            );

            if($this->_db->getOne($table, array('collection_id' => $collection_id), 'collection_id'))
            {
                $this->_db->update($table, $data, array('collection_id' => $collection_id));
            }
            else
            {
                $this->_db->insert($table, $data);
            }

            $this->_db->delete('service_collection_category', array('collection_id' => $collection_id));
            $this->getCollectionCategories($collection_id, $collection->Categories);
            $this->getMedia($collection, 'collection', $collection_id);
        }
    }

    protected function getCollectionCategories($collection_id, $categories)
    {
        $table = 'service_collection_category';

        foreach ($categories->Category as $category)
        {
            $data = array(
                'collection_id'    => $collection_id,
                'category_id'    => $this->_parser->getElementVal($category, false, 'CategoryId')
            );

            $this->_db->insert($table, $data);

            if(isset($category->SubCategories))
            {
                $this->getCollectionCategories($collection_id, $category->SubCategories);
            }
        }
    }

    protected function getPrices()
    {
        foreach ($this->_xml->EcmLines->Prices->PriceRecord as $price)
        {
            $item_id = $this->_parser->getElementVal($price, 'Item');
            $this->getItemsPrices($item_id, $price->ECPrices);
        }
    }

    /* protected function getTaxCategories()
    {
        foreach($this->_xml->EcmLines->TaxCategories->TaxCategory as $tax_category)
        {
            $this->getTaxCategory($tax_category);
        }
    }

    protected function getTaxCategory($tax_category)
    {
        $table = 'sevice_tax_category';
        $tax_category_id = $this->_parser->getElementVal($tax_category, false, 'TaxCategoryId');
        $data = array(
            'tax_category_id'     => $tax_category_id,
            'request_id'         => $this->_request_id,
            'name'                => $this->_parser->getElementVal($tax_category->Name),
            'description'        => $this->_parser->getElementVal($tax_category->Description)
        );

        if($this->_db->getOne($table, array('tax_category_id' => $tax_category_id), 'tax_category_id'))
        {
            $this->_db->update($table, $data, array('tax_category_id' => $tax_category_id));
        }
        else
        {
            $this->_db->insert($table, $data);
        }
    } */

    protected function getPackages()
    {
        $table = 'service_package';
        $table_package_channel = 'service_package_channel';
        $table_package_collection = 'service_package_collection';

        foreach ($this->_xml->EcmLines->Packages->Package as $package)
        {
            $package_id = $this->_parser->getElementVal($package, false, 'PackageId');
            $data = array(
                'package_id'  => $package_id,
                'request_id'  => $this->_request_id,
                'description' => $this->_parser->getElementVal($package->Description),
                'notes'       => $this->_parser->getElementVal($package->Notes)
            );

            if($this->_db->getOne($table, array('package_id' => $package_id), 'package_id'))
            {
                $this->_db->update($table, $data, array('package_id' => $package_id), 'package_id');
            }
            else
            {
                $this->_db->insert($table, $data);
            }

            $this->getPackageCategories($package_id, $package->Categories);

            $channel = $this->_xml->Channel;

            $data = array(
                'package_id' => $package_id,
                'channel_id' => $channel
            );

            if(!$this->_db->getOne($table_package_channel, array('package_id' => $package_id, 'channel_id' => $channel), 'package_id'))
            {
                $this->_db->insert($table_package_channel, $data);
            }

            $collections = $package->Collections;

            if(!empty($collections))
            {
                $this->_db->delete($table_package_collection, array('package_id' => $package_id));

                foreach($collections->Collection as $collection)
                {
                    $data = array(
                        'package_id'    => $package_id,
                        'collection_id' => $this->_parser->getElementVal($collection, false, 'CollectionId'),
                    );

                    $this->_db->insert($table_package_collection, $data);
                }
            }

            $this->getPackageComponents($package_id, $package->Components);
        }
    }

    /**
     * If attribute code is reserved by Magento, add a prefix before it
     *
     * @param  string $attributeCode
     *
     * @return string
     */
    public function getSafeAttributeCode($attributeCode)
    {
        $attrCodeIsReserved        = in_array(strtolower($attributeCode), Mage::getModel('catalog/product')->getReservedAttributes());
        $attrCodeBeginsWithInteger = Mage::helper('teamwork_service')->stringBeginsWithInteger($attributeCode);

        return ($attrCodeIsReserved || $attrCodeBeginsWithInteger) ? self::PREFIX_FOR_UNSUITABLE_ATTRIBUTE_CODE . $attributeCode : $attributeCode;
    }

    protected function getAttributeSet()
    {
        $table = 'service_attribute_set';
        $table_values = 'service_attribute_value';
        foreach ($this->_xml->EcmLines->AttributeSets->AttributeSet as $attributeSet)
        {
            $attributeSetId = $this->_parser->getElementVal($attributeSet, false, 'AttributeSetId');
            $data = array(
                 'attribute_set_id'  => $attributeSetId,
                 'request_id'        => $this->_request_id,
                 'code'              => $this->getSafeAttributeCode($this->_parser->getElementVal($attributeSet->Code)),
                 'description'       => $this->_parser->getElementVal($attributeSet->Description),
                 'alias'             => $this->_parser->getElementVal($attributeSet->Alias)
             );
            if( empty($data['description']) && empty($data['alias']) )
            {
                $data['description'] = $this->_parser->getElementVal($attributeSet->Code);
            }

            if($this->_db->getOne($table, array('attribute_set_id' => $data['attribute_set_id']), 'attribute_set_id'))
            {
                $this->_db->update($table, $data, array('attribute_set_id' => $data['attribute_set_id']));
            }
            else
            {
                $this->_db->insert($table, $data);
            }

            foreach ($attributeSet->Values->AttributeSetValue as $attributeValue)
            {
                $data = array(
                     'attribute_value_id'   => $this->_parser->getElementVal($attributeValue, false, 'AttributeId'),
                     'attribute_set_id'     => $attributeSetId,
                     'request_id'           => $this->_request_id,
                     'attribute_value'      => $this->_parser->getElementVal($attributeValue->Value),
                     'attribute_alias'      => $this->_parser->getElementVal($attributeValue->Alias),
                     'attribute_alias2'     => $this->_parser->getElementVal($attributeValue->Alias2),
                     'order'                => (int)$this->_parser->getElementVal($attributeValue->Order)
                );

                if($this->_db->getOne($table_values, array('attribute_value_id' => $data['attribute_value_id']), 'attribute_value_id'))
                {
                    $this->_db->update($table_values, $data, array('attribute_value_id' => $data['attribute_value_id']));
                }
                else
                {
                    $this->_db->insert($table_values, $data);
                }
            }
        }
    }


    protected function getPackageCategories($package_id, $categories)
    {
        $table = 'service_package_category';
        $this->_db->delete($table, array('package_id' => $package_id));

        foreach($categories->Category as $category)
        {
            $category_id = (string)($category['CategoryId']);
            $data = array(
                'category_id'     => $category_id,
                'package_id'     => $package_id
            );

            $this->_db->insert($table, $data);
        }
    }

    protected function getPackageComponents($package_id, $components)
    {
        $i = 0;
        $table = 'service_package_component';

        foreach($components->Component as $component)
        {
            $data = array(
                'package_id'        => $package_id,
                'request_id'        => $this->_request_id,
                'comp_no'           => $i,
                'description'       => $this->_parser->getElementVal($component->Description),
                'allow_none'        => $component->AllowNone == "true" ? 1 : 0,
                'allow_multiple'    => $component->AllowMultiple == "true" ? 1: 0
            );

            if($this->_db->getOne($table, array('package_id' => $package_id, 'comp_no' => $i), 'package_id'))
            {
                $this->_db->update($table, $data, array('package_id' => $package_id, 'comp_no' => $i));
            }
            else
            {
                $this->_db->insert($table, $data);
            }

            $this->getComponentElements($package_id, $i, $component->Elements);
            $i++;
        }
    }

    protected function getComponentElements($package_id, $no, $elements)
    {
        $table = 'service_package_component_element';

        foreach ($elements->Element as $element)
        {
            $data = array(
                'package_id'           => $package_id,
                'request_id'           => $this->_request_id,
                'no'                   => $no,
                'item_id'              => $this->_parser->getElementVal($element->Item, false, "ItemId"),
                'price'                => (float)$this->_parser->getElementVal($element->Price),
                'is_component_default' => $element->IsComponentDefault == "true" ? 1 : 0,
                'quantity'             => $this->_parser->getElementVal($element->Quantity),
            );

            if($this->_db->getOne($table, array('package_id' => $package_id, 'no' => $no, 'item_id' => $this->_parser->getElementVal($element->Item, false, 'ItemId')), 'package_id'))
            {
                $this->_db->update($table, $data, array('package_id' => $package_id, 'no' => $no, 'item_id' => $this->_parser->getElementVal($element->Item, false, 'ItemId')));
            }
            else
            {
                $this->_db->insert($table, $data);
            }
        }
    }

    protected function getStyles()
    {
        $table = 'service_style';
        $table_attribute_set = 'service_attribute_set';
        $table_style_channel = 'service_style_channel';

        foreach ($this->_xml->EcmLines->Styles->Style as $style)
        {
            $xml_style_id = $this->_parser->getElementVal($style, false, 'StyleId');
            $channel = $this->_parser->getElementVal($this->_xml->Channel);

            $data = array(
                'style_id'          => $xml_style_id,
                'channel_id'        => $channel,
                'request_id'        => $this->_request_id,
                'no'                => $this->_parser->getElementVal($style->No),
                'inventype'         => $this->_parser->getElementVal($style->InvenType),
                'description'       => $this->_parser->getElementVal($style->Description),
                'description2'      => $this->_parser->getElementVal($style->Description2),
                'description3'      => $this->_parser->getElementVal($style->Description3),
                'description4'      => $this->_parser->getElementVal($style->Description4),
                'ecommdescription'  => $this->_parser->getElementVal($style->ECommDescription),
                'ecomerce'          => $this->_parser->getElementVal($style->ECommerce),
                'dcss'              => $this->_parser->getElementVal($style->DCSS, false, 'DCSSId'),
                'acss'              => $this->_parser->getElementVal($style->ACSS, false, 'ACSSId'),
                'attributeset1'     => $this->_parser->getElementVal($style->AttributeSet1, false, 'AttributeSetId'),
                'attributeset2'     => $this->_parser->getElementVal($style->AttributeSet2, false, 'AttributeSetId'),
                'attributeset3'     => $this->_parser->getElementVal($style->AttributeSet3, false, 'AttributeSetId'),
                'brand'             => $this->_parser->getElementVal($style->Brand, false, 'BrandId'),
                'url_key'           => $this->_parser->getElementVal($style->UrlKey),

                'customdate1'       => $this->_parser->getElementVal($style->CustomDate1),
                'customdate2'       => $this->_parser->getElementVal($style->CustomDate2),
                'customdate3'       => $this->_parser->getElementVal($style->CustomDate3),
                'customdate4'       => $this->_parser->getElementVal($style->CustomDate4),
                'customdate5'       => $this->_parser->getElementVal($style->CustomDate5),
                'customdate6'       => $this->_parser->getElementVal($style->CustomDate6),

                'customflag1'       => $this->_parser->getElementVal($style->CustomFlag1),
                'customflag2'       => $this->_parser->getElementVal($style->CustomFlag2),
                'customflag3'       => $this->_parser->getElementVal($style->CustomFlag3),
                'customflag4'       => $this->_parser->getElementVal($style->CustomFlag4),
                'customflag5'       => $this->_parser->getElementVal($style->CustomFlag5),
                'customflag6'       => $this->_parser->getElementVal($style->CustomFlag6),

                'customlookup1'     => $this->_parser->getElementVal($style->CustomLookup1, 'Value'),
                'customlookup2'     => $this->_parser->getElementVal($style->CustomLookup2, 'Value'),
                'customlookup3'     => $this->_parser->getElementVal($style->CustomLookup3, 'Value'),
                'customlookup4'     => $this->_parser->getElementVal($style->CustomLookup4, 'Value'),
                'customlookup5'     => $this->_parser->getElementVal($style->CustomLookup5, 'Value'),
                'customlookup6'     => $this->_parser->getElementVal($style->CustomLookup6, 'Value'),
                'customlookup7'     => $this->_parser->getElementVal($style->CustomLookup7, 'Value'),
                'customlookup8'     => $this->_parser->getElementVal($style->CustomLookup8, 'Value'),
                'customlookup9'     => $this->_parser->getElementVal($style->CustomLookup9, 'Value'),
                'customlookup10'    => $this->_parser->getElementVal($style->CustomLookup10, 'Value'),
                'customlookup11'    => $this->_parser->getElementVal($style->CustomLookup11, 'Value'),
                'customlookup12'    => $this->_parser->getElementVal($style->CustomLookup12, 'Value'),

                'customnumber1'     => $this->_parser->getElementVal($style->CustomNumber1),
                'customnumber2'     => $this->_parser->getElementVal($style->CustomNumber2),
                'customnumber3'     => $this->_parser->getElementVal($style->CustomNumber3),
                'customnumber4'     => $this->_parser->getElementVal($style->CustomNumber4),
                'customnumber5'     => $this->_parser->getElementVal($style->CustomNumber5),
                'customnumber6'     => $this->_parser->getElementVal($style->CustomNumber6),

                'custominteger1'    => $this->_parser->getElementVal($style->CustomInteger1),
                'custominteger2'    => $this->_parser->getElementVal($style->CustomInteger2),
                'custominteger3'    => $this->_parser->getElementVal($style->CustomInteger3),
                'custominteger4'    => $this->_parser->getElementVal($style->CustomInteger4),
                'custominteger5'    => $this->_parser->getElementVal($style->CustomInteger5),
                'custominteger6'    => $this->_parser->getElementVal($style->CustomInteger6),

                'customtext1'       => $this->_parser->getElementVal($style->CustomText1),
                'customtext2'       => $this->_parser->getElementVal($style->CustomText2),
                'customtext3'       => $this->_parser->getElementVal($style->CustomText3),
                'customtext4'       => $this->_parser->getElementVal($style->CustomText4),
                'customtext5'       => $this->_parser->getElementVal($style->CustomText5),
                'customtext6'       => $this->_parser->getElementVal($style->CustomText6),
                
                'customlongtext1'   => html_entity_decode($this->_parser->getElementVal($style->CustomLongText1)),
                'customlongtext2'   => html_entity_decode($this->_parser->getElementVal($style->CustomLongText2)),
                'customlongtext3'   => html_entity_decode($this->_parser->getElementVal($style->CustomLongText3)),
                'customlongtext4'   => html_entity_decode($this->_parser->getElementVal($style->CustomLongText4)),
                'customlongtext5'   => html_entity_decode($this->_parser->getElementVal($style->CustomLongText5)),
                'customlongtext6'   => html_entity_decode($this->_parser->getElementVal($style->CustomLongText6)),
                'customlongtext7'   => html_entity_decode($this->_parser->getElementVal($style->CustomLongText7)),
                'customlongtext8'   => html_entity_decode($this->_parser->getElementVal($style->CustomLongText8)),
                'customlongtext9'   => html_entity_decode($this->_parser->getElementVal($style->CustomLongText9)),
                'customlongtext10'  => html_entity_decode($this->_parser->getElementVal($style->CustomLongText10)),

                'dateavailable'     => $this->_parser->getElementVal($style->DateAvailable),
                'inactive'          => $this->_parser->getElementVal($style->Inactive),
                'manufacturer'      => $this->_parser->getElementVal($style->Manufacturer, false, 'ManufacturerId'),
                'date_updated'      => new Zend_Db_Expr('NOW()')
            );

            if(isset($style->TaxCategory))
            {
                $data['taxcategory'] = $this->_parser->getElementVal($style->TaxCategory, false, 'Name');
                //$this->getTaxCategory($style->TaxCategory);
            }
            else
            {
                $data['taxcategory'] = $this->_defaultTaxCategory;
            }
            
            $vendorData = $this->getVendorData($style);
            $data['order_cost'] = $vendorData['OrderCost'];
            $data['vendor_no'] = $vendorData['VendorNo'];

            if($this->_db->getOne($table, array('style_id' => $xml_style_id, 'channel_id' => $channel), 'style_id'))
            {
                $this->_db->update($table, $data, array('style_id' => $xml_style_id, 'channel_id' => $channel));
            }
            else
            {
                $data['date_inserted'] = new Zend_Db_Expr('NOW()');
                $this->_db->insert($table, $data);
            }

            if(!empty($style->DCSS))
            {
                $this->getDcss($style->DCSS);
            }

            if(!empty($style->ACSS))
            {
                $this->getAcss($style->ACSS);
            }

            if(!empty($style->Collections))
            {
                $this->getStyleCollections($xml_style_id, $style->Collections);
            }

            if(!empty($style->Categories))
            {
                $this->getStyleCategories($xml_style_id, $style->Categories);
            }

            if(isset($style->RelatedStyles))
            {
                $this->getRelatedStyles($xml_style_id, $style->RelatedStyles);
            }

            //to do!!!!
            if(!empty($style->RichMedia))
            {
                $this->getMedia($style, 'style', $xml_style_id);
            }

            //add brand
            if(!empty($style->Brand))
            {
                $this->getBrand($style->Brand);
            }

            //add manufacturer
            if(!empty($style->Manufacturer))
            {
                $this->getManufacturer($style->Manufacturer);
            }

            //attribute_set from 1 to 3
            $attribute_set_id = array();
            for($i=1;$i<=3;$i++)
            {
                $attribute_set_id[$i] = $this->_parser->getElementVal($style->{'AttributeSet'.$i}, false, 'AttributeSetId');
                if(!empty($attribute_set_id[$i]))
                {
                    $data = array(
                        'attribute_set_id'  => $attribute_set_id[$i],
                        'request_id'        => $this->_request_id,
                        'code'              => $this->getSafeAttributeCode($this->_parser->getElementVal($style->{'AttributeSet'.$i}, 'Code')),
                        'description'       => $this->_parser->getElementVal($style->{'AttributeSet'.$i}, 'Description'),
                        'alias'             => $this->_parser->getElementVal($style->{'AttributeSet'.$i}, 'Alias')
                    );

                    if( empty($data['description']) && empty($data['alias']) )
                    {
                        $data['description'] = $this->_parser->getElementVal($style->{'AttributeSet'.$i}, 'Code');
                    }

                    if($this->_db->getOne($table_attribute_set, array('attribute_set_id' => $attribute_set_id[$i]), 'attribute_set_id'))
                    {
                        $this->_db->update($table_attribute_set, $data, array('attribute_set_id' => $attribute_set_id[$i]));
                    }
                    else
                    {
                        $this->_db->insert($table_attribute_set, $data);
                    }
                }
            }

            $data = array(
                'style_id'      => $xml_style_id,
                'channel_id'    => $channel
            );

            if(!$this->_db->getOne($table_style_channel, array('style_id' => $xml_style_id, 'channel_id' => $channel), 'style_id'))
            {
                $this->_db->insert($table_style_channel, $data);
            }

            $this->getItems($xml_style_id, $style->Items, $attribute_set_id);
        }
    }

    protected function getItemUrlKey($itemId)
    {
        foreach ($this->_xml->EcmLines->ECommOptions->ECommOption as $eCommOption)
        {
            if ($this->_parser->getElementVal($eCommOption->Item) == $itemId)
            {
                return $this->_parser->getElementVal($eCommOption->UrlKey);
            }
        }
    }

    protected function getItems($style_id, $items, $attribute_set_id)
    {
        $itemData = array();
        $itemIds = array();
        $table = 'service_items';
        $table_item_channel = 'service_item_channel';
        $rtaItems = array();
        
        foreach($items->Item as $item)
        {
            $xml_item_id = $this->_parser->getElementVal($item, false, 'ItemId');
            $channel = $this->_parser->getElementVal($this->_xml->Channel);

            $data = array(
                'item_id'        => $xml_item_id,
                'channel_id'     => $channel,
                'request_id'     => $this->_request_id,
                'style_id'       => $style_id,
                'plu'            => $this->_parser->getElementVal($item->PLU),
                'ecomerce'       => $this->_parser->getElementVal($item->ECommerce),
                'attribute1_id'  => $this->_parser->getElementVal($item->Attribute1, false, 'AttributeId'),
                'attribute2_id'  => $this->_parser->getElementVal($item->Attribute2, false, 'AttributeId'),
                'attribute3_id'  => $this->_parser->getElementVal($item->Attribute3, false, 'AttributeId'),
                'weight'         => $this->_parser->getElementVal($item->Weight),
                'width'          => $this->_parser->getElementVal($item->Width),
                'height'         => $this->_parser->getElementVal($item->Height),
                'length'         => $this->_parser->getElementVal($item->Length),
                'skukey'         => $this->_parser->getElementVal($item->SKUKey),
                'url_key'        => $this->getItemUrlKey($xml_item_id),
                'customdate1'    => $this->_parser->getElementVal($item->CustomDate1),
                'customdate2'    => $this->_parser->getElementVal($item->CustomDate2),
                'customdate3'    => $this->_parser->getElementVal($item->CustomDate3),
                'customdate4'    => $this->_parser->getElementVal($item->CustomDate4),
                'customdate5'    => $this->_parser->getElementVal($item->CustomDate5),
                'customdate6'    => $this->_parser->getElementVal($item->CustomDate6),

                'customflag1'    => $this->_parser->getElementVal($item->CustomFlag1),
                'customflag2'    => $this->_parser->getElementVal($item->CustomFlag2),
                'customflag3'    => $this->_parser->getElementVal($item->CustomFlag3),
                'customflag4'    => $this->_parser->getElementVal($item->CustomFlag4),
                'customflag5'    => $this->_parser->getElementVal($item->CustomFlag5),
                'customflag6'    => $this->_parser->getElementVal($item->CustomFlag6),

                'customlookup1'  => $this->_parser->getElementVal($item->CustomLookup1, 'Value'),
                'customlookup2'  => $this->_parser->getElementVal($item->CustomLookup2, 'Value'),
                'customlookup3'  => $this->_parser->getElementVal($item->CustomLookup3, 'Value'),
                'customlookup4'  => $this->_parser->getElementVal($item->CustomLookup4, 'Value'),
                'customlookup5'  => $this->_parser->getElementVal($item->CustomLookup5, 'Value'),
                'customlookup6'  => $this->_parser->getElementVal($item->CustomLookup6, 'Value'),
                'customlookup7'  => $this->_parser->getElementVal($item->CustomLookup7, 'Value'),
                'customlookup8'  => $this->_parser->getElementVal($item->CustomLookup8, 'Value'),
                'customlookup9'  => $this->_parser->getElementVal($item->CustomLookup9, 'Value'),
                'customlookup10' => $this->_parser->getElementVal($item->CustomLookup10, 'Value'),
                'customlookup11' => $this->_parser->getElementVal($item->CustomLookup11, 'Value'),
                'customlookup12' => $this->_parser->getElementVal($item->CustomLookup12, 'Value'),

                'customnumber1'  => $this->_parser->getElementVal($item->CustomNumber1),
                'customnumber2'  => $this->_parser->getElementVal($item->CustomNumber2),
                'customnumber3'  => $this->_parser->getElementVal($item->CustomNumber3),
                'customnumber4'  => $this->_parser->getElementVal($item->CustomNumber4),
                'customnumber5'  => $this->_parser->getElementVal($item->CustomNumber5),
                'customnumber6'  => $this->_parser->getElementVal($item->CustomNumber6),

                'custominteger1' => $this->_parser->getElementVal($item->CustomInteger1),
                'custominteger2' => $this->_parser->getElementVal($item->CustomInteger2),
                'custominteger3' => $this->_parser->getElementVal($item->CustomInteger3),
                'custominteger4' => $this->_parser->getElementVal($item->CustomInteger4),
                'custominteger5' => $this->_parser->getElementVal($item->CustomInteger5),
                'custominteger6' => $this->_parser->getElementVal($item->CustomInteger6),

                'customtext1'    => $this->_parser->getElementVal($item->CustomText1),
                'customtext2'    => $this->_parser->getElementVal($item->CustomText2),
                'customtext3'    => $this->_parser->getElementVal($item->CustomText3),
                'customtext4'    => $this->_parser->getElementVal($item->CustomText4),
                'customtext5'    => $this->_parser->getElementVal($item->CustomText5),
                'customtext6'    => $this->_parser->getElementVal($item->CustomText6)
            );
            
            $vendorData = $this->getVendorData($item);
            $data['order_cost'] = $vendorData['OrderCost'];
            $data['vendor_no'] = $vendorData['VendorNo'];

            if($this->_db->getOne($table, array('item_id' => $xml_item_id, 'channel_id' => $channel), 'item_id'))
            {
                $this->_db->update($table, $data, array('item_id' => $xml_item_id, 'channel_id' => $channel));
            }
            else
            {
                $this->_db->insert($table, $data);
            }

            $data = array(
                'item_id'       => $xml_item_id,
                'channel_id'    => $channel
            );

            if(!$this->_db->getOne($table_item_channel, array('item_id' => $xml_item_id, 'channel_id' => $channel), 'item_id'))
            {
                $this->_db->insert($table_item_channel, $data);
            }

            if(!empty($item->Identifiers))
            {
                $this->getItemsIdentifiers($xml_item_id, $item->Identifiers);
            }

            if(!empty($item->ECPrices))
            {
                $this->getItemsPrices($xml_item_id, $item->ECPrices);
            }

            if(!empty($item->Collections))
            {
                $this->getItemCollections($xml_item_id, $item->Collections);
            }

            if(isset($item->Categories))
            {
                $this->getItemsCategories($xml_item_id, $item->Categories);
            }
            
            if($this->_useRealtimeavailability)
            {
                if(!in_array($xml_item_id, $rtaItems))
                {
                    $rtaItems[] = $xml_item_id;
                    $this->_db->delete('service_inventory', array('item_id'  => $xml_item_id, 'channel_id' => $channel));
                }
            }
            elseif( !empty($item->Qtys) )
            {
                $this->getInventory($item->Qtys->Qty);
            }
            $this->getAttributeValue($item, $attribute_set_id);
            $this->getMedia($item, 'item', $xml_item_id);
        }
        
        if( !empty($rtaItems) )
        {
            $this->_callRta($rtaItems, $channel);
        }
    }

    protected function getAttributeValue($item, $attribute_id)
    {
        $table = 'service_attribute_value';

        for($i = 1; $i <= 3; $i++)
        {
            if(!empty($attribute_id[$i]) && $this->_parser->getElementVal($item->{'Attribute'.$i}, false, 'AttributeId'))
            {
                $data = array(
                    'attribute_set_id'      => $attribute_id[$i],
                    'attribute_value_id'    => $this->_parser->getElementVal($item->{'Attribute'.$i}, false, 'AttributeId'),
                    'request_id'            => $this->_request_id,
                    'attribute_value'       => $this->_parser->getElementVal($item->{'Attribute'.$i}, 'Value'),
                    'attribute_alias'       => $this->_parser->getElementVal($item->{'Attribute'.$i}, 'Alias'),
                    'attribute_alias2'      => $this->_parser->getElementVal($item->{'Attribute'.$i}, 'Alias2'),
                    'order'                 => (int)$this->_parser->getElementVal($item->{'Attribute'.$i}, 'Order')
                );

                if($this->_db->getOne($table, array('attribute_value_id' => $this->_parser->getElementVal($item->{'Attribute'.$i}, false, 'AttributeId'), 'attribute_set_id' => $attribute_id[$i]), 'attribute_value_id'))
                {
                    $this->_db->update($table, $data, array('attribute_value_id' => $this->_parser->getElementVal($item->{'Attribute'.$i}, false, 'AttributeId'), 'attribute_set_id' => $attribute_id[$i]));
                }
                else
                {
                    $this->_db->insert($table, $data);
                }
            }
        }
    }

    protected function getItemCollections($item_id, $collections)
    {
        $table = 'service_item_collection';
        $this->_db->delete($table, array('item_id' => $item_id));

        foreach($collections->Collection as $collection)
        {
            $xml_col_id = $this->_parser->getElementVal($collection, false, 'CollectionId');
            $data = array(
                'collection_id' => $xml_col_id,
                'item_id'         => $item_id
            );

            $this->_db->insert($table, $data);
        }
    }

    protected function getItemsCategories($item_id, $categories)
    {
        $table = 'service_item_category';
        $this->_db->delete($table, array('item_id' => $item_id, 'channel_id' => (string) $this->_xml->Channel));

        foreach($categories->children() as $category)
        {
            $xml_cat_id = (string)($category['CategoryId']);
            $data = array(
                'category_id'     => $xml_cat_id,
                'item_id'         => $item_id,
                'channel_id'     => (string) $this->_xml->Channel
            );

            $this->_db->insert($table, $data);

            if(!empty($category->SubCategories))
            {
                foreach ($category->SubCategories as $subcat)
                {
                    $this->getItemSubCategories($item_id, $subcat);
                }
            }
        }
    }

    protected function getItemSubCategories($item_id, $cat)
    {
        $table ='service_item_category';
        $xml_cat_id = $this->_parser->getElementVal($cat->Category, false, 'CategoryId');

        if(!empty($xml_cat_id))
        {
            $data = array(
                'category_id'     => $xml_cat_id,
                'item_id'         => $item_id,
                'channel_id'     => (string) $this->_xml->Channel
            );

            $this->_db->insert($table, $data);
        }

        if (!empty($cat->Category->SubCategories))
        {
            foreach($cat->Category->SubCategories as $subcat)
            {
                $this->getItemSubCategories($item_id, $subcat);
            }
        }
    }

    protected function getItemsPrices($item_id, $prices)
    {
        $table = 'service_price';
        $this->_db->delete($table, array('item_id' => $item_id, 'channel_id' => (string) $this->_xml->Channel));

        foreach($prices->Price as $price)
        {
            $data = array(
                'item_id'     => $item_id,
                'request_id'  => $this->_request_id,
                'price_level' => $this->_parser->getElementVal($price, false, 'LevelNo'),
                'channel_id'  => (string) $this->_xml->Channel,
                'price'       => $this->_parser->getElementVal($price)
            );
            $this->_db->insert($table, $data);
        }
    }

    protected function getItemsIdentifiers($item_id, $identifiers)
    {
        $table = 'service_identifier';
        $this->_db->delete($table, array('item_id' => $item_id));

        foreach($identifiers->Identifier as $identifier)
        {
            $data = array
            (
                'identifier_id' => $this->_parser->getElementVal($identifier, false, 'IdentifierId'),
                'item_id'       => $item_id,
                'idclass'       => $this->_parser->getElementVal($identifier, 'IdClass'),
                'value'         => $this->_parser->getElementVal($identifier, 'Value')
            );

            if(!empty($data['identifier_id']))
            {
                $this->_db->insert($table, $data);
            }
        }
    }

    protected function getManufacturer($manufacturer)
    {
        $table = 'service_manufacturer';
        $xml_manufacturer_id = $this->_parser->getElementVal($manufacturer, false, 'ManufacturerId');

        if(!empty($xml_manufacturer_id))
        {
            $data = array(
                'manufacturer_id' => $xml_manufacturer_id,
                'name'            => $this->_parser->getElementVal($manufacturer, 'Name')
            );

            if($this->_db->getOne($table, array('manufacturer_id' => $xml_manufacturer_id), 'manufacturer_id'))
            {
                $this->_db->update($table, $data, array('manufacturer_id' => $xml_manufacturer_id));
            }
            else
            {
                $this->_db->insert($table, $data);
            }
        }
    }

    protected function getBrand($brand)
    {
        $table = 'service_brand';
        $xml_brand_id = $this->_parser->getElementVal($brand, false, 'BrandId');

        if(!empty($xml_brand_id))
        {
            $data = array(
                'brand_id'   => $xml_brand_id,
                'name'       => $this->_parser->getElementVal($brand, 'Name')
            );

            if($this->_db->getOne($table, array('brand_id' => $xml_brand_id), 'brand_id'))
            {
                $this->_db->update($table, $data, array('brand_id' => $xml_brand_id));
            }
            else
            {
                $this->_db->insert($table, $data);
            }
        }
    }

    protected function getStyleCollections($style_id, $collections)
    {
        $table = 'service_style_collection';
        $this->_db->delete($table, array('style_id' => $style_id));

        foreach($collections->Collection as $collection)
        {
            $xml_col_id = $this->_parser->getElementVal($collection, false, 'CollectionId');

            $data = array(
                'collection_id' => $xml_col_id,
                'style_id'      => $style_id
            );

            $this->_db->insert($table, $data);
        }
    }

    protected function getStyleCategories($style_id, $categories)
    {
        $table = 'service_style_category';
        $this->_db->delete($table, array('style_id' => $style_id, 'channel_id'     => (string) $this->_xml->Channel));

        foreach($categories->Category as $category)
        {
            $xml_cat_id = $this->_parser->getElementVal($category, false, 'CategoryId');

            $data = array(
                'category_id' => $xml_cat_id,
                'style_id'    => $style_id,
                'channel_id'  => (string) $this->_xml->Channel
            );

            $this->_db->insert($table, $data);

            if(isset($category->SubCategories))
            {
                foreach ($category->SubCategories->Category as $subcat)
                {
                    $this->getStyleSubCategories($style_id, $subcat);
                }
            }
        }
    }

    protected function getStyleSubCategories($style_id, $cat)
    {
        $table = 'service_style_category';
        $xml_cat_id = $this->_parser->getElementVal($cat, false, 'CategoryId');

        $data = array(
            'category_id' => $xml_cat_id,
            'style_id'    => $style_id,
            'channel_id'  => (string) $this->_xml->Channel
        );

        $this->_db->insert($table, $data);

        if($this->_parser->getElementVal($cat->SubCategories))
        {
            foreach($cat->SubCategories->Category as $subcat)
            {
                $this->getStyleSubCategories($style_id, $subcat);
            }
        }
    }

    protected function getRelatedStyles($style_id, $related_styles)
    {
        $this->_db->delete('service_style_related', array('style_id' => $style_id));
        foreach($related_styles->RelatedStyle as $style)
        {
            $data = array(
                'style_id'           => $style_id,
                'item_id'            => $this->_parser->getElementVal($style, false, 'PrimaryItemId')? $this->_parser->getElementVal($style, false, 'PrimaryItemId') : NULL,
                'related_style_id'   => $this->_parser->getElementVal($style, false, 'RelatedStyleId'),
                'related_item_id'    => $this->_parser->getElementVal($style, false, 'RelatedItemId')? $this->_parser->getElementVal($style, false, 'RelatedItemId') : NULL,
                'related_style_type' => $this->_parser->getElementVal($style, false, 'RelatedStyleType'),
                'relation_kind'      => $this->_parser->getElementVal($style, false, 'RelationKind')
            );
            $this->_db->insert('service_style_related', $data);
        }
    }

    protected function getAcss($acss)
    {
        $table = 'service_acss';
        $table1 = 'service_acss_level1';
        $table2 = 'service_acss_level2';
        $table3 = 'service_acss_level3';
        $table4 = 'service_acss_level4';
        $xml_acss_id = $this->_parser->getElementVal($acss, false, 'ACSSId');
        $code = $this->_parser->getElementVal($acss, false, 'Code');

        if(!empty($xml_acss_id))
        {
            $data = array(
                'acss_id'    => $xml_acss_id,
                'code'       => $code,
                'level1_id'  => $this->_parser->getElementVal($acss->Level1, false, 'LevelId'),
                'level2_id'  => $this->_parser->getElementVal($acss->Level2, false, 'LevelId'),
                'level3_id'  => $this->_parser->getElementVal($acss->Level3, false, 'LevelId'),
                'level4_id'  => $this->_parser->getElementVal($acss->Level4, false, 'LevelId')
            );

            if($this->_db->getOne($table, array('acss_id' => $xml_acss_id), 'acss_id'))
            {
                $this->_db->update($table, $data, array('acss_id' => $xml_acss_id));
            }
            else
            {
                $this->_db->insert($table, $data);
            }
        }

        $xml_level1_id = $this->_parser->getElementVal($acss->Level1, false, 'LevelId');

        if(!empty($xml_level1_id))
        {
            $data = array(
                'level1_id'  => $xml_level1_id,
                'code'       => $this->_parser->getElementVal($acss->Level1, 'Code'),
                'name'       => $this->_parser->getElementVal($acss->Level1, 'Name')
            );

            if($this->_db->getOne($table1, array('level1_id' => $xml_level1_id), 'level1_id'))
            {
                $this->_db->update($table1, $data, array('level1_id' => $xml_level1_id));
            }
            else
            {
                $this->_db->insert($table1, $data);
            }
        }

        $xml_level2_id = $this->_parser->getElementVal($acss->Level2, false, 'LevelId');

        if(!empty($xml_level2_id))
        {
            $data = array(
                'level2_id'  => $xml_level2_id,
                'code'       => $this->_parser->getElementVal($acss->Level2, 'Code'),
                'name'       => $this->_parser->getElementVal($acss->Level2, 'Name')
            );

            if($this->_db->getOne($table2, array('level2_id' => $xml_level2_id), 'level2_id'))
            {
                $this->_db->update($table2, $data, array('level2_id' => $xml_level2_id));
            }
            else
            {
                $this->_db->insert($table2, $data);
            }
        }

        $xml_level3_id = $this->_parser->getElementVal($acss->Level3, false, 'LevelId');

        if(!empty($xml_level3_id))
        {
            $data = array(
                'level3_id'  => $xml_level3_id,
                'code'       => $this->_parser->getElementVal($acss->Level3, 'Code'),
                'name'       => $this->_parser->getElementVal($acss->Level3, 'Name')
            );

            if($this->_db->getOne($table3, array('level3_id' => $xml_level3_id), 'level3_id'))
            {
                $this->_db->update($table3, $data, array('level3_id' => $xml_level3_id));
            }
            else
            {
                $this->_db->insert($table3, $data);
            }
        }

        $xml_level4_id = $this->_parser->getElementVal($acss->Level4, false, 'LevelId');

        if(!empty($xml_level4_id))
        {
            $data = array(
                'level4_id'  => $xml_level4_id,
                'code'       => $this->_parser->getElementVal($acss->Level4, 'Code'),
                'name'       => $this->_parser->getElementVal($acss->Level4, 'Name')
            );

            if($this->_db->getOne($table4, array('level4_id' => $xml_level4_id), 'level4_id'))
            {
                $this->_db->update($table4, $data, array('level4_id' => $xml_level4_id));
            }
            else
            {
                $this->_db->insert($table4, $data);
            }
        }
    }

    protected function getDcss($dcss)
    {
        $table = 'service_dcss';
        $xml_dcss_id = $this->_parser->getElementVal($dcss, false, 'DCSSId');
        $code = $this->_parser->getElementVal($dcss, false, 'Code');

        if(!empty($xml_dcss_id))
        {
            $data = array(
                'dcss_id'       => $xml_dcss_id,
                'code'          => $code,
                'department_id' => $this->_parser->getElementVal($dcss->Department, false, 'DepartmentId'),
                'class_id'      => $this->_parser->getElementVal($dcss->Class, false, 'ClassId'),
                'subclass1_id'  => $this->_parser->getElementVal($dcss->SubClass1, false, 'SubclassId'),
                'subclass2_id'  => $this->_parser->getElementVal($dcss->SubClass2, false, 'SubclassId')
            );

            if($this->_db->getOne($table, array('dcss_id' => $xml_dcss_id), 'dcss_id'))
            {
                $this->_db->update($table, $data, array('dcss_id' => $xml_dcss_id));
            }
            else
            {
                $this->_db->insert($table, $data);
            }
        }

        $table = 'service_dcss_department';
        $xml_department_id = $this->_parser->getElementVal($dcss->Department, false, 'DepartmentId');

        if(!empty($xml_department_id))
        {
            $data = array(
                'department_id' => $xml_department_id,
                'code'          => $this->_parser->getElementVal($dcss->Department, 'Code'),
                'name'          => $this->_parser->getElementVal($dcss->Department, 'Name')
            );

            if($this->_db->getOne($table, array('department_id' => $xml_department_id), 'department_id'))
            {
                $this->_db->update($table, $data, array('department_id' => $xml_department_id));
            }
            else
            {
                $this->_db->insert($table, $data);
            }
        }

        $table = 'service_dcss_class';
        $xml_class_id = $this->_parser->getElementVal($dcss->Class, false, 'ClassId');

        if(!empty($xml_class_id))
        {
            $data = array(
                'class_id'   => $xml_class_id,
                'code'       => $this->_parser->getElementVal($dcss->Class, 'Code'),
                'name'       => $this->_parser->getElementVal($dcss->Class, 'Name')
            );

            if($this->_db->getOne($table, array('class_id' => $xml_class_id), 'class_id'))
            {
                $this->_db->update($table, $data, array('class_id' => $xml_class_id));
            }
            else
            {
                $this->_db->insert($table, $data);
            }
        }

        $table = 'service_dcss_subclass1';
        $xml_subclass1_id = $this->_parser->getElementVal($dcss->SubClass1, false, 'SubclassId');

        if(!empty($xml_subclass1_id))
        {
            $data = array(
                'subclass1_id' => $xml_subclass1_id,
                'code'         => $this->_parser->getElementVal($dcss->SubClass1, 'Code'),
                'name'         => $this->_parser->getElementVal($dcss->SubClass1, 'Name')
            );

            if($this->_db->getOne($table, array('subclass1_id' => $xml_subclass1_id), 'subclass1_id'))
            {
                $this->_db->update($table, $data, array('subclass1_id' => $xml_subclass1_id));
            }
            else
            {
                $this->_db->insert($table, $data);
            }
        }

        $table = 'service_dcss_subclass2';
        $xml_subclass2_id = $this->_parser->getElementVal($dcss->SubClass2, false, 'SubclassId');

        if(!empty($xml_subclass2_id))
        {
            $data = array(
                'subclass2_id' => $xml_subclass2_id,
                'code'         => $this->_parser->getElementVal($dcss->SubClass2, 'Code'),
                'name'         => $this->_parser->getElementVal($dcss->SubClass2, 'Name')
            );

            if($this->_db->getOne($table, array('subclass2_id' => $xml_subclass2_id), 'subclass2_id'))
            {
                $this->_db->update($table, $data, array('subclass2_id' => $xml_subclass2_id));
            }
            else
            {
                $this->_db->insert($table, $data);
            }
        }
    }

    public function registrateChunk($xml)
    {
        $table = 'service';
        $info = $this->_db->getOne($table, array('request_id' => $this->_request_id));
        $type = $this->getEcmType($xml);
        
        $response = array();
        if( !empty($info['response']) )
        {
            $response = @unserialize($info['response']);
        }

        if( !empty($type) && empty($response[$type]) )
        {
            $response[$type] = array(
                'status'    => in_array($type, $this->_skipEcmType) ? 'Success' : 'Wait',
                'errors'    => array(),
                'warnings'  => array()
            );
        }

        if( empty($info) )
        {
            $data = array(
                'request_id'    => $this->_request_id,
                'rec_creation'  => gmdate("Y-m-d H:i:s"),
                'total_chunks'  => $xml->NumberOfChunks,
                'channel_id'    => (string)$xml->Channel,
                'response'      => serialize($response)
            );
            $this->_db->insert($table, $data);
        }
        else
        {
            $data = array(
                'response'  => serialize($response)
            );
            $this->_db->update($table, $data, array('request_id' => $this->_request_id));
        }
    }

    public function getEcmType($xml)
    {
        if(isset($xml->EcmLines) && !empty($xml->EcmLines))
        {
            foreach ($xml->EcmLines->children() as $child)
            {
                if(strpos($child->getName(), 'Discounts') !== false)
                {
                    return 'Discounts';
                }
                else
                {
                    return $child->getName();
                }

                break;
            }
        }

        return false;
    }

    public function setEcmStatus($status)
    {
        if(!empty($status) && !empty($this->_request_id))
        {
            $this->_db->update('service', array('status' => $status), array('request_id' => $this->_request_id));
        }
    }

    public function response($error=false, $critic=false)
    {
        if($this->_responseAsXml)
        {
            if(!empty($error))
            {
                $this->_errors = array_merge($this->_errors, $error);
            }

            if($critic)
            {
                $this->_status = $this->_errorLevels['error'];
            }
            elseif($this->_errors)
            {
                $this->_status = $this->_errorLevels['warning'];
            }
            else
            {
                $this->_status = $this->_errorLevels['success'];
            }

            $xml = '<?xml version="1.0" encoding="UTF-8"?><Response xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://microsoft.com/wsdl/types/"></Response>';
            $response = new SimpleXMLElement($xml);
            $response->addAttribute('RequestId', $this->_request_id);
            $response->addChild('Status', $this->_status);
            $errors = $response->addChild('Errors');
            if($this->_errors)
            {
                foreach($this->_errors as $e)
                {
                    $errors->addChild('Error', $e);
                }
            }
            $response = base64_encode($response->asXML());
        }
        else
        {
            if($error)
            {
                $response = $error;
            }
            else
            {
                $response = 'OK';
            }
        }

        return $response;
    }

    public function getMagentoProducts($params)
    {
        $params = base64_decode($params);
        $result = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Response xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://microsoft.com/wsdl/types/"></Response>');
        if (!empty($params))
        {
            $params = simplexml_load_string($params);
            $guids = array();
            if (!empty($params->products->product))
            {
                foreach($params->products->product as $product)
                {
                    if (!empty($product->guid))
                    {
                        $guids[] = (string)$product->guid;
                    }
                }
            }
            if (count($guids))
            {
                $guids = array_unique($guids);
                $productEntityTableName = $this->_db->getTable('catalog_product_entity');
                $serviceStyleTableName = $this->_db->getTable('service_style');
                $serviceItemsTableName = $this->_db->getTable('service_items');
                $guidSqlList = '"' . implode('","' , $guids) . '"';
                $query = "SELECT cpe.entity_id, ss.style_id, si.item_id FROM {$productEntityTableName} cpe LEFT JOIN {$serviceStyleTableName} ss ON cpe.entity_id=ss.internal_id LEFT JOIN {$serviceItemsTableName} si ON si.internal_id=cpe.entity_id  WHERE ss.style_id IN ({$guidSqlList}) OR si.item_id IN ({$guidSqlList})";
                $products = $this->_db->getResults($query);
                $dataForResponse = array();
                foreach($products as $product)
                {
                    $guid = null;
                    if ($product['style_id'])
                    {
                        $guid = strtolower($product['style_id']);
                        if (!isset($dataForResponse[$guid])) $dataForResponse[$guid] = array();
                        $dataForResponse[$guid][$product['entity_id']] = true;

                    }
                    if ($product['item_id'])
                    {
                        $guid = strtolower($product['item_id']);
                        if (!isset($dataForResponse[$guid])) $dataForResponse[$guid] = array();
                        $dataForResponse[$guid][$product['entity_id']] = true;

                    }
                }
                $products = $result->addChild('products');
                foreach($guids as $guid)
                {
                    if (isset($dataForResponse[strtolower($guid)]))
                    {
                        $product = $products->addChild('product');
                        $product->addChild('guid', $guid);

                        foreach(array_keys($dataForResponse[strtolower($guid)]) as $entityId)
                        {
                            $product->addChild('entity_id', $entityId);
                        }
                    }
                }
            }
        }

        return base64_encode($result->asXml());
    }

    protected function _callRta($items, $channel_id)
    {
        if( !empty($items) )
        {
            $table = 'service_inventory';
            $rtaModel = Mage::getSingleton('teamwork_realtimeavailability/realtimeavailability');
            $step = $rtaModel->_itemLimitPerBatch;  
            
            for($i=0,$j=count($items); $i<=$j; $i=$i+$step)
            {
                $guids = array_slice($items, $i, $step);
                $itemQuantities = $rtaModel->getInventory( $guids );
                if( !empty($itemQuantities) && isset($itemQuantities['itemQuantities']) )
                {
                    foreach($itemQuantities['itemQuantities'] as $item)
                    {
                        foreach($item['quantities'] as $location)
                        {
                            $data = array(
                                'item_id'       => $item['itemId'],
                                'request_id'    => $this->_request_id,
                                'channel_id'    => $channel_id,
                                'location_id'   => $location['locationId'],
                                'quantity'      => $location['available']
                            );
                            $this->_db->insert($table, $data);
                        }
                    }
                }
            }
        }   
    }
    
    public function getCounter()
    {
        $table = 'service';
        $info = $this->_db->getOne($table, array('request_id' => $this->_request_id));
        return $info['chunk'];
    }
    
    public function incrementCounter()
    {
        $table = 'service';
        $info = $this->_db->getOne($table, array('request_id' => $this->_request_id));
        
        $newCounter = $info['chunk'] + 1;
        $data = array(
            'chunk'     => $newCounter
        );

        $this->_db->update($table, $data, array('request_id' => $this->_request_id));
        return $newCounter;
    }
}