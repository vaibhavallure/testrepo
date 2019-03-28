<?php
class Teamwork_Service_Model_Mapping extends Mage_Core_Model_Abstract
{
    protected $_db, $_channel_id, $_error;

    /**
     * Object for parsing XML
     *
     * @var Teamwork_Service_Helper_Parse
     */
    protected $_parser;

    protected $_errorLevels = array(
        'success' => 'Success',
        'error'   => 'Error',
        'warning' => 'Warning'
    );

    protected $price = array();
    protected $dcss = array();
    protected $acss = array();
    protected $media = array();

    const CONST_STYLE   = 'style';
    const CONST_ITEM    = 'item';
    const CONST_ECPRICE = 'ecprices';
    const CONST_CUSTOM  = 'custom';
    const CONST_DCSS    = 'dcss';
    const CONST_ACSS    = 'acss';
    const CONST_RICH    = 'richcontent';
    const CONST_DEFAULT = 'default';

    const FIELD_ITEM_ID          = 'item_id';
    const FIELD_ITEM_PLU         = 'plu';
    const FIELD_STYLE_ID         = 'style_id';
    const FIELD_STYLE_INVETTYPE  = 'inventype';
    const FIELD_STYLE_NO         = 'no';
    const ITEM_ATTRIBUTE_PREFIX  = 'item.';
    const STYLE_ATTRIBUTE_PREFIX = 'style.';

    const MAPPING_DEFAULT_STYLE  = 'default_style';
    const MAPPING_DEFAULT_ITEM   = 'default_item';
    const MAPPING_DEFAULT_IMAGE  = 'default_image';
    const MAPPING_CUSTOM_STYLE   = 'custom_style';
    const MAPPING_CUSTOM_ITEM    = 'custom_item';

    const SETTING_MAPPING_PREFIX = 'mapping_';

    protected $_map = array();
    protected $_mappingFields = array();

    public function _construct()
    {
        $this->_db     = Mage::getModel('teamwork_service/adapter_db');
        $this->_parser = Mage::helper('teamwork_service/parse');
    }

    public function parseXml($xml)
    {
        $this->parseMapping($xml);
        if(empty($this->_error))
        {
            $this->setMapping();
        }
        //$this->showResponse();
        return $this->response();
    }

    public function setError($string)
    {
        $this->_error[] = $string;
    }

    public function parseMapping($xml)
    {
        $this->_channel_id = $this->_parser->getElementVal($xml, false, 'EComChannelId');
        foreach($xml as $key1 => $type)
        {
            if(in_array(strtolower($key1), array(self::CONST_CUSTOM, self::CONST_DEFAULT)))
            {
                foreach($type as $key2 => $product)
                {
                    $key2 = trim(strtolower($key2));
                    $key1 = trim(strtolower($key1));
                    foreach($product->Field as $key3 => $field)
                    {
                        $map = $this->_parser->getElementVal($field, false, 'EcmName') ? $this->_parser->getElementVal($field, false, 'EcmName') : $this->_parser->getElementVal($field, false, 'Title');
                        $key = $this->_parser->getElementVal($field, false, 'Name');
                        if($key1 == self::CONST_DEFAULT)
                        {
                            if($key2 == self::CONST_STYLE)
                            {
                                $this->_map[self::MAPPING_DEFAULT_STYLE][$this->_channel_id][$key] = $map;
                            }
                            elseif($key2 == self::CONST_ITEM)
                            {
                                $this->_map[self::MAPPING_DEFAULT_ITEM][$this->_channel_id][$key] = $map;
                            }
                            elseif($key2 == self::CONST_RICH)
                            {
                                $this->_map[self::MAPPING_DEFAULT_IMAGE][$this->_channel_id][$key] = $map;
                            }
                        }
                        elseif($key1 == self::CONST_CUSTOM)
                        {
                            if($key2 == self::CONST_STYLE)
                            {
                                $this->_map[self::MAPPING_CUSTOM_STYLE][$this->_channel_id][$key] = $map;
                            }
                            elseif($key2 == self::CONST_ITEM)
                            {
                                $this->_map[self::MAPPING_CUSTOM_ITEM][$this->_channel_id][$key] = $map;
                            }
                            $this->checkReserved($key);
                        }
                    }
                }
            }
        }
    }

    public function checkMapping()
    {
        return $this->_map;
    }

    protected function checkReserved($attribute_name)
    {
        $attribute_code = preg_replace('/[^a-z0-9_]+/', '_', strtolower(str_replace(array(' '), '_', trim($attribute_name))));
        $entityTypeId = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
        $reserved = Mage::getModel('catalog/product')->getReservedAttributes();
        $attribute_id = Mage::getModel('catalog/resource_eav_attribute')->loadByCode($entityTypeId, $attribute_code)->getId();

        if(in_array($attribute_code, $reserved) && !$attribute_id)
        {
            $this->_error[$attribute_name] = "The attribute name is reserved by system. Please try another attribute name";
        }

        $attrNameBeginsWithInteger = Mage::helper('teamwork_service')->stringBeginsWithInteger($attribute_name);
        if($attrNameBeginsWithInteger && !$attribute_id)
        {
            $this->_error[$attribute_name] = "The attribute name cannot begin with number";
        }
    }

    protected function setMapping()
    {
        $existed = $this->getMapping();
        if(!empty($this->_map))
        {
            foreach($this->_map as $key => $value)
            {
                foreach($value as $channelId => $channelVal)
                {
                    $setting_name = $this->getSettingName($key);
                    if(isset($existed[$setting_name][$channelId]))
                    {
                        $this->_db->update('service_settings', array('setting_value' => serialize($channelVal)), array('setting_name' => $setting_name, 'channel_id' => $channelId));
                    }
                    else
                    {
                        $data = array(
                            'setting_name' => $setting_name,
                            'channel_id' => $channelId,
                            'setting_value' => serialize($channelVal),
                        );
                        $this->_db->insert('service_settings', $data);
                    }
                }
            }
        }
    }

    protected function getSettingName($key)
    {
        return self::SETTING_MAPPING_PREFIX.$key;
    }

    public function getMapping($serialized=false)
    {
        $mappingPreffix = self::SETTING_MAPPING_PREFIX;
        $query = "SELECT * FROM {$this->_db->getTable('service_settings')} WHERE setting_name LIKE '{$mappingPreffix}%'";
        $result = $this->_db->getResults($query);
        if(!empty($result))
        {
            $return = array();
            foreach($result as $v)
            {
                if (!isset($return[$v['setting_name']])) $return[$v['setting_name']] = array();
                $return[$v['setting_name']][$v['channel_id']] = $serialized ? @unserialize($v['setting_value']) : $v['setting_value'];
            }
            return $return;
        }
        return false;
    }

    protected function response()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><Response xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://microsoft.com/wsdl/types/"></Response>';
        $response = new SimpleXMLElement($xml);
        $status = !empty($this->_error) ? $this->_errorLevels['error'] : $this->_errorLevels['success'];
        $response->addChild('Status', $status);
        $errors = $response->addChild('Errors');

        if(!empty($this->_error))
        {
            foreach($this->_error as $key => $e)
            {
                $error = $errors->addChild('Error', $e);
                if(!is_numeric($key))
                {
                    $error->addAttribute('field', $key);
                    $error->addAttribute('code', '999'); //TODO
                }
            }
        }
        return base64_encode($response->asXML());
    }

    public function getMappingFields($channel, $addDefault=false)
    {
        $mapping = $this->getMapping(true);
        if(!empty($mapping))
        {
            foreach($mapping as $parameter => $channelsInfo)
            {
                $parameter =  $this->_cutPrefix($parameter, self::SETTING_MAPPING_PREFIX);
                if(!empty($channelsInfo[$channel]))
                {
                    foreach($channelsInfo[$channel] as $key => $map)
                    {
                        $map = strtolower(trim($map));
                        if($this->_stringHasPrefix($map, self::STYLE_ATTRIBUTE_PREFIX))
                        {
                            $map = $this->_cutPrefix($map, self::STYLE_ATTRIBUTE_PREFIX);
                            if($this->_stringHasPrefix($map, self::CONST_DCSS))
                            {
                                $this->dcss[] = $map;
                            }
                            elseif($this->_stringHasPrefix($map, self::CONST_ACSS))
                            {
                                $this->acss[] = $map;
                            }
                        }
                        else
                        {
                            if($this->_stringHasPrefix($map, self::CONST_ECPRICE, strlen(self::ITEM_ATTRIBUTE_PREFIX)))
                            {
                                $this->price[$key] = $this->_cutPrefix($map, self::ITEM_ATTRIBUTE_PREFIX);
                            }
                        }
                        $this->_mappingFields[$parameter][$key] = $map;
                    }
                }
            }
        }

        if($addDefault)
        {
            $this->_mappingFields[self::MAPPING_DEFAULT_STYLE]['sku'] = 'no';
            $this->_mappingFields[self::MAPPING_DEFAULT_ITEM]['sku'] = 'item.plu';
        }
        
        $this->_getMappingPriceForAttribute($channel);
    }
    
    private function _getMappingPriceForAttribute($channel)
    {
        $collection = Mage::getModel('teamwork_service/mappingproperty')->getCollection()
            ->addFieldToFilter('channel_id', $channel)
            ->addFieldToFilter('type_id', self::CONST_ITEM)
            ->load();
        
        foreach ($collection as $val)
        {
            $attribute = Mage::getModel('eav/entity_attribute')->load($val->getAttributeId());
            
            $field = Mage::getModel('teamwork_service/chqmappingfields')->load($val->getFieldId());
            $value = trim(substr($field->getValue(), strlen(self::CONST_ITEM)+1));
            
            if (trim(substr(strtolower($value), 0, 8)) == self::CONST_ECPRICE)
            {
                $this->price[$attribute->getAttributeCode()] = strtolower($value);
            }   
        }
    }

    public function getDcss()
    {
        return $this->dcss;
    }

    public function getAcss()
    {
        return $this->acss;
    }

    public function getMedia()
    {
        return $this->media;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getMapDefaultStyle()
    {
        return !empty($this->_mappingFields[self::MAPPING_DEFAULT_STYLE]) ? $this->_mappingFields[self::MAPPING_DEFAULT_STYLE] : null;
    }

    public function getMapDefaultItem()
    {
        return !empty($this->_mappingFields[self::MAPPING_DEFAULT_ITEM]) ? $this->_mappingFields[self::MAPPING_DEFAULT_ITEM] : null;
    }

    public function getMapDefaultImage()
    {
        return !empty($this->_mappingFields[self::MAPPING_DEFAULT_IMAGE]) ? $this->_mappingFields[self::MAPPING_DEFAULT_IMAGE] : null;
    }

    public function getMapCustomStyle()
    {
        return !empty($this->_mappingFields[self::MAPPING_CUSTOM_STYLE]) ? $this->_mappingFields[self::MAPPING_CUSTOM_STYLE] : null;
    }

    public function getMapCustomItem()
    {
        return !empty($this->_mappingFields[self::MAPPING_CUSTOM_ITEM]) ? $this->_mappingFields[self::MAPPING_CUSTOM_ITEM] : null;
    }

    /**
     * Checks whether given map attribute is an item one
     *
     * @param string $mapAttrName
     *
     * @return bool
     */
    public function isItemAttribute($mapAttrName)
    {
        return $this->_stringHasPrefix($mapAttrName, self::ITEM_ATTRIBUTE_PREFIX);
    }

    /**
     * Cuts the item prefix in map attribute name
     *
     * @param  string $mapAttrName
     *
     * @return string
     */
    public function cutItemPrefixInAttributeName($mapAttrName)
    {
        return $this->_cutPrefix($mapAttrName,self::ITEM_ATTRIBUTE_PREFIX);
    }

    /**
     * Checks whether given map attribute has an acss prefix
     *
     * @param string $mapAttrName
     *
     * @return bool
     */
    public function isAcssAttribute($mapAttrName)
    {
        return $this->_stringHasPrefix($mapAttrName, self::CONST_ACSS);
    }

    /**
     * Checks whether given string has an acss prefix
     *
     * @param string $string
     * @param string $prefix
     * @param int    $searchFromPosition
     *
     * @return bool
     */
    protected function _stringHasPrefix($string, $prefix, $searchFromPosition = 0)
    {
        return (substr($string, $searchFromPosition, strlen($prefix)) == $prefix);
    }

    /**
     * Cuts given prefix in a given string
     *
     * @param string $string
     * @param string $prefix
     *
     * @return string
     */
    protected function _cutPrefix($string, $prefix)
    {
        return ($this->_stringHasPrefix($string, $prefix)) ? substr($string, strlen($prefix)) : $string;
    }

}
?>