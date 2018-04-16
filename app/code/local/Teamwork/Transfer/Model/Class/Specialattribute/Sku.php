<?php
/**
 * DCSS special attribute class
 *
 * @category    Teamwork
 * @package     Teamwork_Transfer
 * @author      Teamwork
 */

class Teamwork_Transfer_Model_Class_Specialattribute_Sku extends Teamwork_Transfer_Model_Class_Specialattribute_Abstract
{
    /**
     * All attributes processed by a class
     *
     * @var array
     */
    protected $_mapAttributes = array('no' => array(), 'item.plu' => array());

    /**
     * Prefix added at the beginning of the configurable product SKU
     *
     * @var array
     */
    protected $_styleSkuPrefix = 'style-';

    /**
     * Special function for getting style sku
     * We add prefix to product's sku, if following conditions satisfied:
     * 1) (Obvious) we do mapping for sku attribute
     * 2) We are working with a style (not item). We check this by looking whether $objectData has 'item_id' field
     * 3) Map attribute contains integer converted to string
     *
     * @param string $mapAttrName
     * @param array  $objectData
     * @param array  $auxiliaryParams
     *
     * @return string
     */
    public function getValues($mapAttrName, $objectData, $auxiliaryParams)
    {
        $itemIdField = Teamwork_Service_Model_Mapping::FIELD_ITEM_ID;
        $magentoAttrName = $auxiliaryParams['magento_attribute_name'];

        $specialSkuForSimpleProducts = (Mage::getStoreConfig(Teamwork_Transfer_Helper_Config::XML_PATH_SKU_FOR_SIMPLE_PRODUCTS) == Teamwork_Transfer_Helper_Config::ITEMSKU_STYLENO_PLU);

        if ($magentoAttrName == 'sku')
        {
            if (empty($objectData[$itemIdField])) //if style data
            {
                if ($specialSkuForSimpleProducts && array_key_exists(Teamwork_Service_Model_Mapping::FIELD_STYLE_INVETTYPE, $objectData))
                {
                    //if simple product
                    if ($this->_classItemObject->getProductTypeByInventype($objectData[Teamwork_Service_Model_Mapping::FIELD_STYLE_INVETTYPE]) == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
                    {
                        //get plu
                        $items = $this->_classItemObject->getItems(false, $objectData[Teamwork_Service_Model_Mapping::FIELD_STYLE_ID]);
                        if (!empty($items))
                        {
                            $item = current($items);
                            if (array_key_exists(Teamwork_Service_Model_Mapping::FIELD_ITEM_PLU, $item))
                            {
                                $plu = $item[Teamwork_Service_Model_Mapping::FIELD_ITEM_PLU];
                                return $objectData[$mapAttrName] . "-" . $plu; 
                            }
                        }
                    }
                }
                if ($this->_stringContainsInteger($objectData[$mapAttrName]))
                {
                    return $this->_styleSkuPrefix . $objectData[$mapAttrName];
                }
            }
            else
            {
                //get plu
                $plu = $objectData[Teamwork_Service_Model_Mapping::FIELD_ITEM_PLU];
                if ($specialSkuForSimpleProducts)
                {
                    //get style object
                    $styleData = $this->_classItemObject->getStyle($objectData[Teamwork_Service_Model_Mapping::FIELD_STYLE_ID]);
                    if (!empty($styleData) && array_key_exists(Teamwork_Service_Model_Mapping::FIELD_STYLE_NO, $styleData))
                    {
                        return $styleData[Teamwork_Service_Model_Mapping::FIELD_STYLE_NO] . '-' . $plu; 
                    }
                }
                return $plu; 
            }
        }

        return $objectData[$mapAttrName];
    }

    /**
     * Checks whether given string contains integer
     *
     * @param  string  $string
     *
     * @return boolean
     */
    protected function _stringContainsInteger($string)
    { 
        return preg_match("/^\d+$/", $string);
    }
}