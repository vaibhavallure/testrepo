<?php
/**
 * Attributes updating model
 *
 * @category    Teamwork
 * @package     Teamwork_Transfer
 * @author      Teamwork
 */

class Teamwork_Transfer_Model_Class_Attribute extends Teamwork_Transfer_Model_Transfer
{
    /**
     * Working group of attribute set (see init method)
     *
     * @var int
     */
    protected $_groupId;

    /**
     * Magento attributes' simple info cache
     *
     * @var array
     */
    protected $_attributeData = array();

    /**
     * Working magento attribute set (see init method)
     *
     * @var int
     */
    public $_attributeSetId;

    /**
     * Magento 'catalog_product' entity type (see init method)
     *
     * @var int
     */
    public $_entityTypeId;

    /**
     * Magento attribute options' ids cache
     *
     * @var array
     */
    public $_attributeOptions;

    /**
     * List of 'special attributes'
     *
     * @var array (<name> => <class>)
     */
    protected $_specialAttributes = array();

    /**
     * All classes that process special attributes
     *
     * @var array
     */
    protected $_specialAttributesClasses = array(
        'select', 'collection', 'classification', 'sku', 'identifier', 'static'
        );

    /**
     * Cache dict to keep attributeset data
     *
     * @var array
     */
    protected $_attributeSetCache = array('default' => '', 'ids' => array());

    /**
     * Prepare working objects
     *
     * @param array $globalVars
     * @param array  $specAttributeModelsParams (usualy contains "class_item_object" => Teamwork_Transfer_Model_Class_Item)
     */
    public function init($globalVars=array(), $specAttributeModelsParams = NULL)
    {
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $this->_globalVars = $globalVars;
        $this->_db = Mage::getSingleton('core/resource')->getConnection('core_write');

        $this->_attributeSetId = Mage::getModel('catalog/product')->getResource()->getEntityType()->getDefaultAttributeSetId();
        $this->_groupId = Mage::getModel('eav/entity_attribute_set')->getDefaultGroupId($this->_attributeSetId);
        $this->_entityTypeId = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();

        $this->_initSpecialAttributes($specAttributeModelsParams);
    }

    /**
     * Entry point
     */
    public function execute()
    {
        try
        {
            $this->_getStagingAttributes();
        }
        catch(Exception $e)
        {
            $this->_getLogger()->addMessage(sprintf("Should not be here: file: %s; line: %s", __FILE__, __LINE__), Zend_Log::DEBUG);
            $this->_getLogger()->addException($e);
            $this->_addErrorMsg("Internal error (exception): " . $e->getMessage(), false);
        }
        return $this;
    }

    /**
     * Convert nonmagento attribute name to magento attribute code
     *
     * @param string $attributeName
     *
     * @return string
     */
    public function getAttributeCodeByName($attributeName)
    {
        $string = strtolower(str_replace(array(' '), '_', trim($attributeName)));
        $return = preg_replace('/[^a-z0-9_]+/', '_', $string);
        return $return;
    }

    /**
     * Initiate import process
     */
    protected function _getStagingAttributes()
    {
        $this->_updateAttributes();
        $this->_updateAttributeValues();
    }

    /**
     * Gather special attributes list
     *
     * @param array  $specAttributeModelsParams (usualy contain "class_item_object" => Teamwork_Transfer_Model_Class_Item)
     */
    protected function _initSpecialAttributes($specAttributeModelsParams = NULL)
    {
        if (count($this->_specialAttributes) == 0)
        {
            foreach ($this->_specialAttributesClasses as $className)
            {
                foreach ($this->_getSpecialAttributeObject($className, $specAttributeModelsParams)->getAllAttributes() as $mapAttrName)
                {
                    $this->_specialAttributes[$mapAttrName] = $className;
                }
            }
        }
    }

    /**
     * Create attributes/update attribute labels using data from staging table
     */
    protected function _updateAttributes()
    {
        $table = Mage::getSingleton('core/resource')->getTableName('service_attribute_set');
        $select = $this->_db->select()->from($table)
            ->where('request_id = ?', $this->_globalVars['request_id'])
        ->orWhere('internal_id is null');

        if( $attributes = $this->_db->fetchAll($select) )
        {
            foreach($attributes as $attribute)
            {
                $attributeName = $this->getAttributeName($attribute);
                if (!empty($attribute['internal_id']))
                {
                    $attributeM = $this->getAttributeById($attribute['internal_id']);
                    if (!$attributeM->getId()) $attribute['internal_id'] = null;
                }
                if(empty($attribute['internal_id']))
                {
                    /*create magento attribute if needed and get attribute id*/
                    $attrId = $this->getAttributeId($attribute['code'], 'select', $attributeName, true);
                    /*update staging table*/
                    $this->_db->update($table, array('internal_id' => $attrId), "attribute_set_id = '{$attribute['attribute_set_id']}'");
                }
                elseif( !$this->isMergedAttribute($attribute['internal_id']) )
                {
                    /*update attribute label*/
                    $this->_updateAttributeLabel($attribute['internal_id'], $attributeName);
                }
                /*updating may spend some time, so recall that ECM on the go*/
                $this->checkLastUpdateTime();
            }
        }
    }

    /**
     * Create attributes/update magento attribute values using data from staging table
     */
    protected function _updateAttributeValues()
    {
        $attributeTable = Mage::getSingleton('core/resource')->getTableName('service_attribute_set');
        $optionTable = Mage::getSingleton('core/resource')->getTableName('service_attribute_value');
        $select = $this->_db->select()
            ->from(array('val' => $optionTable), array('val.attribute_value_id','val.attribute_value', 'val.attribute_alias', 'val.attribute_alias2', 'option_internal_id' => 'val.internal_id', 'attribute_internal_id' => 'set.internal_id', 'option_order' => 'val.order', 'set.values_mapping'))
            ->join(array('set' => $attributeTable), 'val.attribute_set_id = set.attribute_set_id')
            ->where('set.internal_id is not null')
        ->where('val.internal_id is null OR val.request_id = ?', $this->_globalVars['request_id']);
        
        $result = $this->_db->fetchAll($select);
        if( !empty($result) )
        {
            $optionsForUpdate = array();
            foreach($result as $option)
            {
                $optionValue = Teamwork_Service_Model_Confattrmapprop::getMappedAttributeValue($option['values_mapping'], $option['attribute_value'], $option['attribute_alias'], $option['attribute_alias2']);
                $optionsForUpdate[$option['attribute_internal_id']][] = array(
                    'value'     => $optionValue,
                    'data'      => $option,
                );
            }
            
            foreach($optionsForUpdate as $attributeId => $attributeOptions)
            {
                $this->addAttributeValues($attributeId, $attributeOptions, true);
                $this->checkLastUpdateTime();
            }
        }
    }

    /**
     * Get/ititiate creation of magento attribute by attribute name (attribute code)
     * From 4.7.0 we ONLY create Magento attributes by which styles are configured (CHQ attributes from service_attribute_set_table)
     *
     * @param string $attributeName
     * @param string $type
     * @param string $attributeLabel
     *
     * @return int
     */
    public function getAttributeId($attributeName, $type = 'select', $attributeLabel = '', $createAttributeIfNotExists = false)
    {
        if(trim($attributeLabel) === "")
        {
            $attributeLabel = $attributeName;
        }

        $attributeCode = $this->getAttributeCodeByName($attributeName);
        $attribute = $this->getAttributeByCode($attributeCode);

        $attributeId = $attribute->getId();

        if(empty($attributeId) && $createAttributeIfNotExists)
        {
            $attributeId = $this->_createAttribute($attributeCode, $attributeLabel, $type);
        }

        return $attributeId;
    }

    /**
     * Add magento attribute to magento attribute set
     * From 4.7.0 we don't add Magento attributes to attribute sets! This function is left for compatibility
     *
     * @param int $attributeId
     */
    public function attributeToAttributeSet($attributeId)
    {
        $attributeExistInAttributeSet = false;

        foreach(Mage::getModel('catalog/product_attribute_api')->items($this->_attributeSetId) as $attribute)
        {
            if($attribute['attribute_id'] == $attributeId)
            {
                $attributeExistInAttributeSet = true;
                break;
            }
        }
        if(!$attributeExistInAttributeSet)
        {
            $model = new Mage_Eav_Model_Entity_Setup('transfer_setup');
            try
            {
                $attributeGroupId = $model->getAttributeGroup('catalog_product', $this->_attributeSetId, 'General');
                $model->addAttributeToSet('catalog_product', $this->_attributeSetId, $attributeGroupId['attribute_group_id'], $attributeId);
            }
            catch(Exception $e)
            {
                $msg = "Error occured while attaching attribute to set: attribute set id:{$this->_attributeSetId}; attribute group id:" . $attributeGroupId['attribute_group_id'] ? $attributeGroupId['attribute_group_id'] : '' . "; attribute id:" . $attributeId . "(" . $e->getMessage() . ")";
                $this->_addErrorMsg($msg, true);
                $this->_getLogger()->addException($e);
            }
        }
    }

    /**
     * Populate magento attribute option id using option value
     *
     * @param string $attributeName
     * @param string $value
     *
     * @return string|null
     */
    public function populateAttibuteOption($attributeName, $value)
    {
        $attrId = $this->getAttributeId($attributeName);

        if( empty($this->_attributeOptions[$attrId]) )
        {
            $this->_attributeOptions[$attrId] = $this->_getAttributeOptions($attrId);
        }

        if(!in_array($value, $this->_attributeOptions[$attrId]))
        {
            $addOptions = array(
                array('value' => $value)
            );
            $this->addAttributeValues($attrId, $addOptions);
            $this->_attributeOptions[$attrId] = $this->_getAttributeOptions($attrId);
        }
    }

    /**
     * Get magento attribute frontend type
     *
     * @param string $attrName
     *
     * @return string|false
     */
    public function getAttributeType($attrName)
    {
        $attributeCode =  $this->getAttributeCodeByName($attrName);
        $attribute = $this->getAttributeByCode($attributeCode);

        if(!empty($attribute))
        {
            return $attribute->getFrontendInput();
        }

        return false;
    }

    /**
     * Get/construct magento attribute simple info
     *
     * @param string $name
     *
     * @return array
     */
    public function getAttributeData($name, $fieldName = '', $returnAttributeInstance = false)
    {
        if(!empty($this->_attributeData[$name]))
        {
            return $this->_attributeData[$name];
        }

        $attributeCode = $this->getAttributeCodeByName($name);
        $attribute = $this->getAttributeByCode($attributeCode);

        if($attribute->getId())
        {
            $data = array(
                'id'           => $attribute->getId(),
                'code'         => $attributeCode,
                'source_model' => $attribute->getSourceModel(),
                'type'         => $attribute->getFrontendInput()
            );
            if ($returnAttributeInstance)
            {
                $data['instance'] = $attribute;
            }
        }
        else
        {
            $data = array('code' => $attributeCode);

            if ($this->_getSpecialAttributeClass($attributeCode) == 'collection')
            {
                $data['type'] = 'multiselect';
            }
            elseif((strpos($fieldName, 'customlookup') !== false) || ($this->_getSpecialAttributeClass($attributeCode) == 'select'))
            {
                $data['type'] = 'select';
            }
            elseif(strpos($fieldName, 'customdate') !== false)
            {
                $data['type'] = 'date';
            }
            elseif(strpos($fieldName, 'customflag') !== false)
            {
                $data['type'] = 'boolean';
            }
            else
            {
                $data['type'] = 'text';
            }

            $data['id'] = $this->getAttributeId($name, $data['type']);
        }

        $this->_attributeData[$name] = $data;

        return $data;
    }

    /**
     * Create magento attribute
     *
     * @param string $attributeCode
     * @param string $labelText
     * @param string $type
     * @param array $productTypes
     *
     * @return false|int
     */
    protected function _createAttribute($attributeCode, $labelText, $type = 'select',  $productTypes = array(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE, Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE, Mage_Catalog_Model_Product_Type::TYPE_BUNDLE))
    {
        $labelText     = trim($labelText);
        $attributeCode = trim($attributeCode);
        // $setInfo = array(
        //     'SetID'     => $this->_attributeSetId,
        //     'GroupID'   => $this->_groupId
        // );
        if($type == 'date')
        {
            $backendType = 'datetime';
        }
        elseif (in_array($type, array('text', 'multiselect')))
        {
            $backendType = 'varchar';
        }
        else //select or boolean
        {
            $backendType = 'int';
        }
        $data = array(
            'is_global'                         => '1',
            'frontend_input'                    => $type,
            'default_value_text'                => '',
            'default_value_yesno'               => '0',
            'default_value_date'                => '',
            'default_value_textarea'            => '',
            'is_unique'                         => '0',
            'is_required'                       => '0',
            'frontend_class'                    => '',
            'is_searchable'                     => '1',
            'is_visible_in_advanced_search'     => '1',
            'is_comparable'                     => '1',
            'is_used_for_promo_rules'           => '0',
            'is_html_allowed_on_front'          => '1',
            'is_visible_on_front'               => '0',
            'used_in_product_listing'           => '0',
            'used_for_sort_by'                  => '0',
            'is_configurable'                   => ($type == 'select' ? '1' : '0'),
            'is_filterable'                     => '0',
            'is_filterable_in_search'           => '0',
            'backend_type'                      => $backendType,
            'default_value'                     => ''
        );
        $data['backend_model']  = ($type == 'multiselect') ? 'eav/entity_attribute_backend_array' : null;
        $data['apply_to']       = $productTypes;
        $data['attribute_code'] = $attributeCode;
        $data['frontend_label'] = array(
            0 => $labelText,
            1 => ''
        );
        $model = Mage::getModel('catalog/resource_eav_attribute');
        $model->addData($data);
        // $model->setAttributeSetId($setInfo['SetID']);
        // $model->setAttributeGroupId($setInfo['GroupID']);
        $entityTypeID = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
        $model->setEntityTypeId($entityTypeID);
        $model->setIsUserDefined(1);
        try
        {
            $model->save();
            return $model->getId();
        }
        catch(Exception $e)
        {
            $this->_addErrorMsg(sprintf("Error occured while attribute creating: \"%s\" (%s)", $labelText, $e->getMessage()), true);
            $this->_getLogger()->addException($e);
        }

        return false;
    }

    /**
     * Update label of magento attribute
     *
     * @param int $attributeId
     * @param string $labelText
     */
    protected function _updateAttributeLabel($attributeId, $labelText)
    {
        $model = Mage::getModel('catalog/resource_eav_attribute');
        $model->load($attributeId);
        $model->setFrontendLabel(array(
            0 => $labelText,
            1 => ''
        ));

        try
        {
            $model->save();
        }
        catch(Exception $e)
        {
            $this->_addErrorMsg(sprintf("Error occured while updating attribute label (tried to set \"%s\"): attribute_id: %s (%s)", $labelText, $attributeId, $e->getMessage()), true);
            $this->_getLogger()->addException($e);
        }
    }

    /**
     * SAVE magento attribute option
     *
     * @param int $attributeId
     * @param string $labelText
     */
    public function addAttributeValues($attributeId, $optionsForUpdate = array(), $isConfigurableAttribute=false)
    {
        if( $attributeId && !empty($optionsForUpdate) )
        {
            $attribute = Mage::getModel('eav/entity_attribute')->load($attributeId);
            
            if( !$attribute->getSourceModel() ||
                $attribute->getSourceModel() == Mage::getModel('catalog/resource_eav_attribute')->_getDefaultSourceModel()
            )
            {
                $i=0;
                $magentoAttributeOptions = $this->_getAttributeOptions($attributeId, false);
                foreach($optionsForUpdate as $option)
                {
                    if( !empty($option['value']) )
                    {
                        $optionId = $this->_getAttributeOptionIdByValue($option['value'], $magentoAttributeOptions);
                        $order = !empty($magentoAttributeOptions[$optionId]['sort_order']) ? $magentoAttributeOptions[$optionId]['sort_order'] : 0;
                        $createNewOption = false;
                        if(!$optionId)
                        {
                            $optionId = "option_" . $i++;
                            $order = !empty($option['data']['option_order']) ? $option['data']['option_order'] : 0;
                            $createNewOption = true;
                        }
                        elseif($isConfigurableAttribute && !$this->isMergedAttributeOption($optionId))
                        {
                            $order = $option['data']['option_order'];
                        }
                        
                        $attribute->setData('option', array(
                                'order' => array($optionId => $order),
                                'value' => array(
                                    $optionId => array(
                                        $option['value'], //NEVER CHANGE THIS VALUE, YOU ONLY CAN SIMPLY REGULATE NEXT POSITIONS
                                        $option['value'],
                                    )
                                )
                            )
                        );
                        
                        try
                        {
                            $attribute->save();
                            if($isConfigurableAttribute)
                            {
                                if($createNewOption)
                                {
                                    $magentoAttributeOptions = $this->_getAttributeOptions($attributeId, false);
                                    $optionId = $this->_getAttributeOptionIdByValue($option['value'], $magentoAttributeOptions);
                                }
                                $this->_db->update(Mage::getSingleton('core/resource')->getTableName('service_attribute_value'), array('internal_id' => $optionId), "attribute_value_id = '{$option['data']['attribute_value_id']}'");
                            }
                        }
                        catch (Exception $e)
                        {
                            $this->_addErrorMsg(sprintf("Sorry, error occured while trying to save the value \"%s\" for attribute \"%s\". Error: %s", $val, $attribute->getFrontendLabel(), $e->getMessage()), true);
                            $this->_getLogger()->addException($e);
                        }
                    }
                }
            }
        }
    }

    /**
     * Get all available attribute options from magento tables
     *
     * @param int $attrId
     * @param bool $isList
     *
     * @return array
     */
    protected function _getAttributeOptions($attrId, $isList = true)
    {
        $attributeOptions = array();
        
        $attribute = $this->getAttributeById($attrId);
        $sourceModel = $attribute->getSourceModel();
        
        if( !$sourceModel || $sourceModel == $attribute->_getDefaultSourceModel() )
        {
            $optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->setAttributeFilter($attrId)
                ->setStoreFilter(Mage_Core_Model_App::ADMIN_STORE_ID, false)
            ->load();

            foreach ($optionCollection as $option)
            {
                $attributeOptions[$option->getId()] = $option->getValue();
                if(!$isList)
                {
                    $attributeOptions[$option->getId()] = array(
                        'id'             => $option->getId(),
                        'sort_order'     => $option->getSortOrder(),
                        'label'          => $option->getValue(),
                    );
                }
            }
        }
        else
        {
            $model = Mage::getModel($sourceModel)->setAttribute($attribute);
            if ( method_exists($model, 'getAllOptions') )
            {
                foreach( $model->getAllOptions() as $option)
                {
                    if( isset($option['label']) )
                    {
                        $attributeOptions[$option['value']] = $option['label']; // $option['value'] is an option_id
                        if(!$isList)
                        {
                            $attributeOptions[$option['value']] = array(
                                'id'             => $option['value'],
                                'sort_order'     => '0',
                                'label'          => $option['label'],
                            );
                        }
                    }
                }
            }
        }
        
        ksort($attributeOptions);
        return $attributeOptions;
    }

    /**
     * Get catalog attribute by its code
     *
     * @param string $attribute
     *
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    public function getAttributeByCode($attributeCode)
    {
        return Mage::getModel('catalog/resource_eav_attribute')->loadByCode($this->_entityTypeId, $attributeCode);
    }

    /**
     * Get catalog attribute by id
     *
     * @param int|string $attributeId
     *
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    public function getAttributeById($attributeId)
    {
        return Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
    }

    /**
     * Checks whether given map attribute is included in special attribute list
     *
     * @param string $mapAttrName
     *
     * @return bool
     */
    public function isSpecialAttribute($mapAttrName)
    {
        return (isset($this->_specialAttributes[$mapAttrName]));
    }

    /**
     * Returns special attribute or null if not exists
     *
     * @param string $mapAttrName
     * @param array  $specAttrModelParams (usualy contains "class_item_object" => Teamwork_Transfer_Model_Class_Item)
     *
     * @return Teamwork_Transfer_Model_Class_Specialattribute_{attribute class}|null
     */
    public function getSpecialAttribute($mapAttrName, $specAttrModelParams = NULL)
    {
        return ($this->isSpecialAttribute($mapAttrName)) ? $this->_getSpecialAttributeObject($this->_specialAttributes[$mapAttrName], $specAttrModelParams) : null;
    }

    /**
     * Returns special attribute object
     *
     * @param string $className
     * @param array  $specAttrModelParams (usualy contain "class_item_object" => Teamwork_Transfer_Model_Class_Item)
     *
     * @return Teamwork_Transfer_Model_Class_Specialattribute_{attribute class}
     */
    protected function _getSpecialAttributeObject($className, $specAttrModelParams = NULL)
    {
        return $this->_getClassObject('specialattribute_'.$className, $specAttrModelParams);
    }

    /**
     * Returns values of given special attribute for object with given objectData (style or item)
     * For special attributes, call 'getValues' function
     * For other ones, return $objectData[$mapAttrName]
     *
     * It's worth noting that this function returns 'raw' value of special attribute. This value DOES NOT depend on mapping settings.
     *
     * @param string $mapAttrName
     * @param array  $objectData
     * @param array  $auxiliaryParams
     * @param array  $specAttrModelParams (usualy contain "class_item_object" => Teamwork_Transfer_Model_Class_Item)
     *
     * @return array
     */
    public function getSpecialAttributeValues($mapAttrName, $objectData, $auxiliaryParams, $specAttrModelParams = NULL)
    {
        if ($attribute = $this->getSpecialAttribute($mapAttrName, $specAttrModelParams))
        {
            $values = $attribute->getValues($mapAttrName, $objectData, $auxiliaryParams);
            return (is_array($values)) ? $values : array($values);
        }
        return $objectData[$mapAttrName];
    }

    /**
     * Returns class that serves given special attribute
     *
     * @param  string $mapAttrName
     *
     * @return string
     */
    protected function _getSpecialAttributeClass($mapAttrName)
    {
       return ($this->isSpecialAttribute($mapAttrName)) ? $this->_specialAttributes[$mapAttrName] : null;
    }

    /**
     * Method returns attribute name/label by priority
     *
     * @param array $attribute
     *
     * @return string
     */
    public function getAttributeName($attribute)
    {
        if( !empty($attribute['alias']) )
        {
            $name = $attribute['alias'];
        }
        elseif( !empty($attribute['description']) )
        {
            $name = $attribute['description'];
        }
        else
        {
            $name = $attribute['code'];
        }
        return trim($name);
    }

    /**
     * Uses to attache products to attribute sets while creating
     *
     * @param string $attributeSetNameArr
     *
     */
    public function setCurrentAttributeSet($attributeSetName = '')
    {
        $attributeSetId = $this->getAttributeSetId($attributeSetName);
        if ($attributeSetId === false)
        {
            $attributeSetId = $this->getAttributeSetId($this->getDefaultAttributeSetName());
        }
        $this->_attributeSetId = $attributeSetId;
        $model = new Mage_Eav_Model_Entity_Setup('transfer_setup');
        $this->_groupId = $model->getAttributeGroup('catalog_product', $attributeSetId, 'General');
    }

    /**
     * Get attribute set id by name
     *
     * @param string $name
     *
     * @return int | false
     */
    public function getAttributeSetId($name, $useDefaultIfNotExists = true)
    {
        if (empty($name))
        {
            if (!$useDefaultIfNotExists) return false;
            $name = $this->getDefaultAttributeSetName();
        }

        if (!isset($this->_attributeSetCache['ids'][$name]))
        {
            $entityTypeID = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
        
            $collection = Mage::getModel("eav/entity_attribute_set")->getCollection();
            $collection->addFieldToFilter("attribute_set_name", $name);
            $collection->addFieldToFilter("entity_type_id", $entityTypeID);
            if (count($collection))
            {
                $this->_attributeSetCache['ids'][$name] = $collection->getFirstItem()->getId();
            }
            else if ($useDefaultIfNotExists)
            {
                $name = $this->getDefaultAttributeSetName();
            }
        }

        if (isset($this->_attributeSetCache['ids'][$name]))
        {
            return $this->_attributeSetCache['ids'][$name];
        }
        return false;
    }

    /**
     * Get default attribute set name and fill $this->_attributeSetCache['ids'] field for it to use from getAttributeSetId
     *
     * @return string
     */
    public function getDefaultAttributeSetName()
    {
        if (empty($this->_attributeSetCache['default']) || empty($this->_attributeSetCache['ids'][$this->_attributeSetCache['default']]))
        {
            $attributeSetId = 0;
            if (Mage::getStoreConfig(Teamwork_Transfer_Helper_Config::XML_PATH_DEFAULT_ATTRIBUTE_SET))
            {
                $attributeSetId = Mage::getStoreConfig(Teamwork_Transfer_Helper_Config::XML_PATH_DEFAULT_ATTRIBUTE_SET);
            }

            if (!$attributeSetId) {
                $attributeSetId = Mage::getModel('catalog/product')->getResource()->getEntityType()->getDefaultAttributeSetId();
            }

            $model = Mage::getModel('eav/entity_attribute_set')->load($attributeSetId);
            if (!$model->getId())
            {
                Mage::throwException("Error. Can't load default attribute set.");
            }
            $this->_attributeSetCache['default'] = $model->getData('attribute_set_name');
            $this->_attributeSetCache['ids'][$this->_attributeSetCache['default']] = $attributeSetId;
        }
        return $this->_attributeSetCache['default'];
    }
    
    protected function _getAttributeOptionIdByValue($value,$options)
    {
        foreach($options as $optionId => $option)
        {
            if( is_array($option) && strtolower(trim($value)) === strtolower(trim($option['label'])) )
            {
                return $optionId;
            }
            elseif( !is_array($option) && strtolower(trim($value)) === strtolower(trim($option)) )
            {
                return $optionId;
            }
        }     
    }
    
    public function isMergedAttribute($attributeId)
    {
        $table = Mage::getSingleton('core/resource')->getTableName('service_attribute_set');
        $select = $this->_db->select()->from($table,array('count(*)'))->where('internal_id = ?', $attributeId);
        $count = $this->_db->fetchOne($select);
        return ($count>1) ? 1 : 0;
    }
    
    public function isMergedAttributeOption($optionId)
    {
        $table = Mage::getSingleton('core/resource')->getTableName('service_attribute_value');
        $select = $this->_db->select()->from($table,array('count(*)'))->where('internal_id = ?', $optionId);
        $count = $this->_db->fetchOne($select);
        return ($count>1) ? 1 : 0;
    }
}