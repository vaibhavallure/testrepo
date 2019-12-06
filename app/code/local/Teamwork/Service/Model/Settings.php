<?php
class Teamwork_Service_Model_Settings extends Mage_Core_Model_Abstract
{
    protected $_db, $_settingType, $_settings;
    protected $_channelIds = array();
    protected $_db_tables = array();
    protected $_isFillSettings = true;
    protected $_restrictedAttributeCodes = array('sku', 'gallery', 'media_gallery', 'tier_price', 'group_price');
    protected $_supportedTypes = array(
            'text'        => 'text',
            'textarea'    => 'text',
            'weight'      => 'decimal',
            'date'        => 'date',
            'price'       => 'decimal',
            'media_image' => 'image',
            'gallery'     => 'image'
        );
    protected $_skipNode = array('Design', 'Recurring Profile');
    protected $_itemRequiredFields = array('weight', 'price');
    protected $_settingsToClearBeforeFilling = array(); // array containing list of settings we should clear before filling
    const CONST_COMPLETED_ORDERS = 'OnlyCompleted';

    const SHIPPING_NAME_DELIMITER = '_';

    public function _construct()
    {
        $this->_db = Mage::getModel('teamwork_service/adapter_db');
        $this->_settingsToClearBeforeFilling = array(
            Teamwork_Service_Model_Mapping::SETTING_MAPPING_PREFIX . Teamwork_Service_Model_Mapping::MAPPING_DEFAULT_STYLE,
            Teamwork_Service_Model_Mapping::SETTING_MAPPING_PREFIX . Teamwork_Service_Model_Mapping::MAPPING_DEFAULT_ITEM ,
            Teamwork_Service_Model_Mapping::SETTING_MAPPING_PREFIX . Teamwork_Service_Model_Mapping::MAPPING_CUSTOM_STYLE ,
            Teamwork_Service_Model_Mapping::SETTING_MAPPING_PREFIX . Teamwork_Service_Model_Mapping::MAPPING_CUSTOM_ITEM
            );
    }

    public function prepareParams($params)
    {
		$parameterArray = array();
		if(!empty($params))
        {
            $parameterArray = explode(";", $params);
        }
		
		$this->prepareChannelIds($parameterArray);
        if(!empty($parameterArray[1]))
        {
            $this->_settingType = $parameterArray[1];
        }

        if($this->_isFillSettings)
        {
            $this->fillPaymentSettings();
            $this->fillShippingSettings();
            $this->fillMappingSettings();
        }
    }

    public function prepareChannelIds($parameterArray)
	{
		$channelIds = array();
        if(!empty($parameterArray[0]))
        {
            $channelIds[$parameterArray[0]] = trim($parameterArray[2]);
        }
        else
        {
            if($channels = $this->_db->getAll('service_channel'))
            {
                foreach($channels as $channel)
                {
                    $channelIds[$channel['channel_id']] = trim($channel['channel_name']);
                }
            }
        }

        if(!empty($channelIds))
        {
            foreach (Mage::app()->getWebsites() as $website)
            {
                foreach ($website->getGroups() as $group)
                {
                    $stores = $group->getStores();
                    foreach ($stores as $store)
                    {
                        if( ($channelGuid = array_search(trim($store->getCode()), $channelIds)) !== FALSE )
                        {
                            $this->_channelIds[$channelGuid] = $store->getId();
                        }
                    }
                }
            }
        }
	}
	
    public function generateXml($params)
    {
        $this->prepareParams($params);
        $this->_settings = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Settings xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://microsoft.com/wsdl/types/"></Settings>');
        switch($this->_settingType)
        {
            case 'PaymentMethods':
                $this->getPaymentMethods();
            break;

            case 'ShipmentMethods':
                $this->getShipmentMethods();
            break;

            case 'Mapping':
                $this->getMappingFields();
            break;

            case 'TaxCategories':
                $this->getTaxCategories();
            break;

            default:
                $this->getPaymentMethods();
                $this->getShipmentMethods();
                $this->getTaxCategories();
                $this->getMappingFields();
            break;
        }
        if(empty($this->_channelIds) && $this->_settingType != 'Mapping')
        {
            $errors = $this->createAttrNode($this->_settings, 'Errors');
            $err = $errors->addChild('Error', Mage::helper('teamwork_service')->__('Wrong channel name'));
            $err->addAttribute('code', 2);
        }

        return base64_encode($this->_settings->asXML());

    }

    public function parseXml($xml)
    {
        $xml = simplexml_load_string($xml);
        $channelId = (string)$xml['EComChannelId'];

        /*update channel info if needed*/
        $this->updateChannelInfo($channelId, (string)$xml['EComChannelName']);

        /* clear given list settings before writing*/
        $where  = array(
            $this->_db->quoteInto('channel_id = ?', $channelId),
            $this->_db->quoteInto('setting_name IN (?)', $this->_settingsToClearBeforeFilling)
            );
        $this->_db->straightDelete('service_settings', $where);

        foreach($this->_settingsToClearBeforeFilling as $setting)
        {
            $this->_db->delete('service_settings', array('setting_name' => $setting, 'channel_id' => $channelId));
        }
        
        /*create chq mapping fields*/
        $this->_setShqMappingFields($xml);
        
        /*save rich content template fields*/
        if (isset($xml->Templates->Template))
        {
            $this->_setTemplateElements($xml->Templates->Template->Elements, $channelId);
        }
        else
        {
            $this->_db->delete('service_settings', array('setting_name' => Teamwork_Service_Model_Richmedia::SETTINGS_TEMPLATE_ELEMENTS, 'channel_id' => $channelId));
            $richmedia = Mage::getModel('teamwork_service/richmedia');
            $richmedia->deleteMapping($channelId);
        }
        
        $this->_setPaymentMethodsFlags($xml->PaymentMethods, $channelId);

        foreach($xml as $key => $settings)
        {
            if(!in_array(strtolower($key), array(Teamwork_Service_Model_Mapping::CONST_CUSTOM, Teamwork_Service_Model_Mapping::CONST_DEFAULT)))
            {
                foreach($settings as $setting) {
                    $data = array(
                        'setting_name'  => (string)$setting['Name'],
                        'channel_id'    => $channelId,
                        'setting_value' => (string)$setting['Value'],
                    );
                    if($this->_db->getOne('service_settings', array('setting_name' => $data['setting_name'], 'channel_id' => $channelId)))
                    {
                        $this->_db->update('service_settings', $data, array('setting_name' => $data['setting_name'], 'channel_id' => $channelId));
                    }
                    else
                    {
                        $this->_db->insert('service_settings', $data);
                    }
                }
            }
        }
        $mapping = Mage::getModel('teamwork_service/mapping');
        return $mapping->parseXml($xml);
    }

    public function updateChannelInfo($channelId, $channelName)
    {
        $channelName = strtolower(preg_replace("/[^\w]+/", "_", $channelName));
        $data = array(
            'channel_id'    => $channelId,
            'channel_name'  => $channelName,
        );

        if($oldRecord = $this->_db->getOne('service_channel', array('channel_name' => $channelName)))
        {
            if ($oldRecord['channel_id'] != $channelId)
			{
                $this->_db->update('service_channel', $data, array('channel_id' => $oldRecord['channel_id']));
                $this->_setErrorECMStatuses($oldRecord['channel_id']);
            }
        }
        elseif($oldRecord = $this->_db->getOne('service_channel', array('channel_id' => $channelId)))
        {
            if ($oldRecord['channel_name'] != $channelName)
			{
				$this->_db->update('service_channel', $data, array('channel_id' => $channelId));
			}
        }
		else
		{
			$this->_db->insert('service_channel', $data);
		}
    }
    
    protected function _setShqMappingFields($xml)
    {
        /*add fields for chq mapping*/
        foreach (get_object_vars($xml->MappingFields) AS $type => $fields)
        {
           foreach ($fields->Field as $field)
           {
                $data = array(
                    'label'  => (string)$field['Label'][0],
                    'value'    => (string)$field['Value'][0],
                    'type' => (string)$field['Type'][0],
                    'type_id' => (string)($type)
                );
                
                $collection = Mage::getModel('teamwork_service/chqmappingfields')->getCollection()
                    ->addFieldToFilter('value', (string)$field['Value'][0])
                    ->load();
                
                if (!$collection->getSize())
                {
                    $chqmappingModel = Mage::getModel('teamwork_service/chqmappingfields');
                    $chqmappingModel->setData($data);
                    $chqmappingModel->save();
                }
                else
                {
                    $entityId = $collection->getFirstItem()->getEntityId();
                    $chqmappingModel = Mage::getModel('teamwork_service/chqmappingfields')->setData($data)->load($entityId);
                    $chqmappingModel->save();
                }
                
            }
        }
    }
    
    protected function _setTemplateElements($settingValue, $channelId)
    {
        
        $elementData = array();
        
        foreach ($settingValue->Element as $element)
        {
            /*if field type description*/
            if ((int)$element->RcmTypeId == 0)
            {
                $name = (string)$element->Name;
                $ecIndex = (string)$element->EcIndex;
                $data = array(
                    'name'  => $name,
                    'ecIndex'    => $ecIndex,
                );
                
                $elementData[] = $data;
            }
        }
        
        $data = array(
            'setting_name'  => Teamwork_Service_Model_Richmedia::SETTINGS_TEMPLATE_ELEMENTS,
            'channel_id'    => $channelId,
            'setting_value' => serialize($elementData),
        );
               
        if($this->_db->getOne('service_settings', array('setting_name' => Teamwork_Service_Model_Richmedia::SETTINGS_TEMPLATE_ELEMENTS, 'channel_id' => $channelId)))
        {
            $this->_db->update('service_settings', $data, array('setting_name' => Teamwork_Service_Model_Richmedia::SETTINGS_TEMPLATE_ELEMENTS, 'channel_id' => $channelId));
            $richmedia = Mage::getModel('teamwork_service/richmedia');
            $richmedia->deleteMapping($channelId, $elementData);
        }
        else
        {
            $this->_db->insert('service_settings', $data);
        }
    }
    
    protected function _setPaymentMethodsFlags($paymentMethods, $channelId)
    {
        $this->_resetPaymentMethodsFlagsToDefaultStatement($channelId);
        if($paymentMethods->PaymentMethod)
        {
            foreach($paymentMethods->PaymentMethod as $paymentMethod)
            {
                $paymentCode = $paymentMethod->ECommerceCode;
                if( $this->getPaymentMethodByName($paymentCode, $channelId) )
                {
                    $data = array(
                        'allow_authorize_only'  => ((string)$paymentMethod->AllowAuthorizeOnly === 'false') ? false : true,
                        'refund_in_teamwork'    => ((string)$paymentMethod->RefundInTeamwork === 'false') ? false : true,
                        'payment_method_id'     => (string)$paymentMethod['PaymentMethodId'],
                    );
                    $this->_db->update('service_setting_payment', $data, array('name' => $paymentCode, 'channel_id' => $channelId));
                }
            }
        }
    }

    protected function _resetPaymentMethodsFlagsToDefaultStatement($channelId)
    {
        $payments = $this->getAllPaymentMethods($channelId);
        $data = array(
            'allow_authorize_only'  => false,
            'refund_in_teamwork'    => false,
            'payment_method_id'     => null,
        );
        foreach($payments as $payment)
        {
            $this->_db->update('service_setting_payment', $data, array('name' => $payment['name'], 'channel_id' => $channelId));
        }
    }
    
    protected function _setErrorECMStatuses($channelId)
    {
        $data = array('status' => 'error');
        $this->_db->update('service', $data, array('channel_id' => $channelId, 'status' => array('NOT IN' => '("done", "reindex")')));

        return true;
    }
    
    protected function getPaymentMethods()
    {
        if(!empty($this->_channelIds))
        {
            $methods = $this->createAttrNode($this->_settings, 'PaymentMethods');
            $payments = $this->getAllPaymentMethods( array_keys($this->_channelIds) );
            foreach($payments as $payment)
            {
                $temp = $this->createAttrNode($methods, 'PaymentMethod', array(
                    'EComChannelId'     => $payment['channel_id'],
                    'Name'              => $payment['name'],
                    'Description'       => $payment['description'],
                    'IsActive'          => $payment['active']
                ));
            }
        }
    }
    
    public function getAllPaymentMethods( $channelGuids=array() )
    {
        if( !is_array($channelGuids) )
        {
            $channelGuids = (array)$channelGuids;
        }
        $select = "SELECT * FROM " . $this->_db->getTable('service_setting_payment') . " WHERE channel_id IN ('" . implode("','", $channelGuids) . "')";
        return $this->_db->getResults($select);
    }
    
    public function getPaymentMethodByName($name, $channelId)
    {
        return $this->_db->getOne('service_setting_payment', array('name' => $name, 'channel_id' => $channelId));
    }

    protected function getShipmentMethods()
    {
        if(!empty($this->_channelIds))
        {
            $methods = $this->createAttrNode($this->_settings, 'ShipmentMethods');
            $select = "SELECT * FROM " . $this->_db->getTable('service_setting_shipping') . " WHERE channel_id IN ('" . implode("','", array_keys($this->_channelIds)) . "')";
            $carriers = $this->_db->getResults($select);

            foreach ($carriers as $carrier)
            {
                $temp = $this->createAttrNode($methods, 'ShipmentMethod', array(
                    'EComChannelId'     => $carrier['channel_id'],
                    'Name'                => $carrier['name'],
                    'Description'        => $carrier['description'],
                    'IsActive'            => $carrier['active']
                ));
            }
        }
    }

    protected function getTaxCategories()
    {
        if(!empty($this->_channelIds))
        {
            $tax = $this->createAttrNode($this->_settings, 'TaxCategories');
            foreach (Mage::getModel('tax/class_source_product')->getAllOptions() as $category)
            {
                $temp = $this->createAttrNode($tax, 'TaxCategory', array(
                    'EComChannelId'     => current(array_keys($this->_channelIds)),
                    'Name'                => $category['value'],
                    'Description'        => $category['label']
                ));
            }
        }
    }

    protected function getMappingFields()
    {
        $select = "SELECT * FROM " . $this->_db->getTable('service_setting_mapping') . " WHERE type != 'image'";

        if($fields = $this->_db->getResults($select))
        {
            $mapping = $this->createAttrNode($this->_settings, 'Mapping');
            $style = $this->createAttrNode($mapping, 'Style');
            $item = $this->createAttrNode($mapping, 'Item');

            foreach ($fields as $field)
            {
                $style_insert = $item_insert = array(
                    'Name'                => $field['name'],
                    'Description'        => $field['description'],
                    'Type'                => $field['type']
                );

                if($this->_channelIds)
                {
                    $item_insert['EComChannelId'] = $style_insert['EComChannelId'] = key((array)$this->_channelIds);
                }

                if(in_array($field['name'], $this->_itemRequiredFields))
                {
                    $item_insert['Required'] = $field['required'];
                    $style_insert['Required'] = 0;
                }
                else
                {
                    $item_insert['Required'] = 0;
                    $style_insert['Required'] = $field['required'];
                }

                $this->createAttrNode($style, 'Field', $style_insert);
                $this->createAttrNode($item, 'Field', $item_insert);
                unset($insert);
            }

            $select = "SELECT * FROM " . $this->_db->getTable('service_setting_mapping') . " WHERE type = 'image'";

            if($fields = $this->_db->getResults($select))
            {
                $richMedia = $this->createAttrNode($mapping, 'RichMedia');
                foreach ($fields as $field)
                {
                    $insert = array(
                        'Name'                => $field['name'],
                        'Description'        => $field['description'],
                        'Required'            => $field['required'],
                        'Type'                => $field['type']
                    );
                    if($this->_channelIds)
                    {
                        $insert['EComChannelId'] = key((array)$this->_channelIds);
                    }

                    $this->createAttrNode($richMedia, 'Field', $insert);
                }
            }
        }
    }

    protected function fillPaymentSettings()
    {
        if(!empty($this->_channelIds))
        {
            foreach($this->_channelIds as $channelId => $storeId)
            {
                $payments = Mage::getStoreConfig('payment', $storeId);

                foreach ($payments as $paymentCode => $paymentModel)
                {
                    $paymentTitle = Mage::getStoreConfig('payment/'.$paymentCode.'/title');

                    $data = array(
                        'name'             => $paymentCode,
                        'channel_id'     => $channelId,
                        'description'     => ($paymentTitle ? $paymentTitle : $paymentCode)
                    );

                    if (Mage::getStoreConfigFlag('payment/' . $paymentCode . '/active', $storeId))
                    {
                        $data['active'] = 1;
                    }
                    else
                    {
                        $data['active'] = 0;
                    }

                    if( $this->getPaymentMethodByName($paymentCode, $channelId) )
                    {
                        $this->_db->update('service_setting_payment', $data, array('name' => $paymentCode, 'channel_id' => $channelId));
                    }
                    else
                    {
                        $this->_db->insert('service_setting_payment', $data);
                    }
                }
            }
        }
    }

    protected function fillShippingSettings()
    {
        if(!empty($this->_channelIds))
        {
            foreach($this->_channelIds as $channelId => $storeId)
            {
                $carriers = Mage::getStoreConfig('carriers', $storeId);
                try
                {
                    /*magento default dhl carrier settings fix: part1*/
                    /*set the all shipping methods as inactive*/
                    $this->_db->update('service_setting_shipping', array('active' => '0'), array('channel_id' => $channelId));

                    foreach($carriers as $carrierCode => $carrier)
                    {
                        /*magento default dhl carrier settings fix: part2*/
                        /*fix to skip disabled carriers*/
                        if( empty($carrier['active']) || empty($carrier['model']) )
                        {
                            continue;
                        }

                        $carrierModel = Mage::getModel($carrier['model']);
                        if($carrier['model'] == 'matrixrate_shipping/carrier_matrixrate')
                        {
                            $this->matrixrateCustomShipping($carrierCode, $carrier, $channelId);
                        }
                        elseif($carrierCode == 'msmultiflat')
                        {

                            foreach($carrier as $key => $value)
                            {
                                if(strpos($key, 'name') === 0 && !empty($value))
                                {
                                    $prefix = ($carrierCode == 'chcanpost2module') ? 'tablerate_' : '';
                                    $data = array (
                                        'name'            => trim($prefix . $this->getShippingName($carrierCode, $value)),
                                        'channel_id'      => $channelId,
                                        'description'     => $carrier['title'] . ': ' . $value,
                                        'active'          => $carrier['active']
                                    );
                                    if($this->_db->getOne('service_setting_shipping', array('name' => $data['name'], 'channel_id' => $channelId)))
                                    {
                                        $this->_db->update('service_setting_shipping', $data, array('name' => $data['name'], 'channel_id' => $channelId));
                                    }
                                    else
                                    {
                                        $this->_db->insert('service_setting_shipping', $data);
                                    }
                                }
                            }
                        }
                        else
                        {
                            foreach($carrierModel->getAllowedMethods() as $method_key => $method)
                            {
                                $data = array (
                                    'name'            => trim($this->getShippingName($carrierCode, $method_key)),
                                    'channel_id'      => $channelId,
                                    'description'     => $carrier['title'] . ': ' . $method,
                                    'active'          => $carrier['active'],
                                );
                                if($this->_db->getOne('service_setting_shipping', array('name' => $data['name'], 'channel_id' => $channelId)))
                                {
                                    $this->_db->update('service_setting_shipping', $data, array('name' => $data['name'], 'channel_id' => $channelId));
                                }
                                else
                                {
                                    $this->_db->insert('service_setting_shipping', $data);
                                }
                            }
                        }
                    }
                }
                catch(Exception $e)
                {
                    Mage::log($e->getTraceAsString());
                }
            }
        }
    }

    protected function matrixrateCustomShipping($carrierCode, $carrier, $channelId)
    {
        $data = array(
            'name'          => 'matrixrate_matrixrate',
            'channel_id'    => $channelId,
            'description'   => $carrier['title'],
            'active'        => 1
        );
        if($this->_db->getOne('service_setting_shipping', array('name' => $data['name'], 'channel_id' => $channelId)))
        {
            $this->_db->update('service_setting_shipping', $data, array('name' => $data['name'], 'channel_id' => $channelId));
        }
        else
        {
            $this->_db->insert('service_setting_shipping', $data);
        }
        
        $subMethods = array (
            1 => "Domestic Ground",
            2 => "Domestic Expedited",
            3 => "International"
        );
        
        foreach ($subMethods as $index => $name) {
            $data = array(
                'name'          => 'matrixrate_matrixrate_'.$index,
                'channel_id'    => $channelId,
                'description'   => $name,
                'active'        => 1
            );
            if($this->_db->getOne('service_setting_shipping', array('name' => $data['name'], 'channel_id' => $channelId)))
            {
                $this->_db->update('service_setting_shipping', $data, array('name' => $data['name'], 'channel_id' => $channelId));
            }
            else
            {
                $this->_db->insert('service_setting_shipping', $data);
            }
        }
    }

    protected function nameToCode($str)
    {
        return strtolower(preg_replace('/[^a-zA-Z0-9_]+/', '_', trim($str)));
    }

    protected function fillMappingSettings()
    {
        if(!$this->_db->getOne('service_setting_mapping'))
        {
            $_set_id = Mage::getModel('catalog/product')->getResource()->getEntityType()->getDefaultAttributeSetId();

            if($groups = Mage::getModel('eav/entity_attribute_group')
                ->getResourceCollection()
                ->setAttributeSetFilter($_set_id)
            ->setSortOrder()->load())
            {
                foreach($groups as $node)
                {
                    if(in_array($node->getAttributeGroupName(), $this->_skipNode)) continue;

                    if($nodeChildren = Mage::getResourceModel('catalog/product_attribute_collection')
                        ->setAttributeGroupFilter($node->getId())
                        ->addVisibleFilter()
                        ->checkConfigurableProducts()
                    ->load())
                    {
                        foreach ($nodeChildren->getItems() as $child)
                        {
                            if(array_key_exists($child['frontend_input'], $this->_supportedTypes) && !$child->getIsUserDefined() && !in_array($child['attribute_code'], $this->_restrictedAttributeCodes))
                            {
                                $data = array(
                                    'description'    => $child['frontend_label'],
                                    'name'             => $child['attribute_code'],
                                    'required'        => $child['is_required'],
                                    'type'            => $this->_supportedTypes[$child->getFrontendInput()],
                                    'node'            => $node->getAttributeGroupName()
                                );

                                $this->_db->insert('service_setting_mapping', $data);
                            }
                        }
                    }
                }
            }
        }
    }

    public function createAttrNode(&$parent, $child, $attribute = null, $canBeNull = true)
    {
        $new = $parent->addChild($child);

        if($attribute)
        {
            foreach($attribute as $k => $v)
            {
                if(!isset($v) && $canBeNull)
                {
                    $new->addAttribute('xsi:nil', 'true', 'http://www.w3.org/2001/XMLSchema-instance');
                    break;
                }
                else
                {
                    $new->addAttribute($k, $v);
                }
            }
        }

        return $new;
    }

    /**
     * Converts carrier and shipping method codes to shipping name (used for filling 'service_setting_shipping' table)
     *
     * @param  string $carrierCode
     * @param  string $shippingMethodCode
     *
     * @return string
     */
    public function getShippingName($carrierCode, $shippingMethodCode)
    {
        return $carrierCode . self::SHIPPING_NAME_DELIMITER  . $shippingMethodCode;
    }

    /**
     * Extracts carrier code from shipping name (stored in 'service_setting_shipping' table)
     *
     * @param  string $shippingName
     *
     * @return string
     */
    public function getCarrierCodeFromShippingName($shippingName)
    {
        $codes = explode(self::SHIPPING_NAME_DELIMITER, $shippingName);
        return $codes[0];
    }
}