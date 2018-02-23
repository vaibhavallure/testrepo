<?php
/**
 * Product price updating model
 *
 * @category    Teamwork
 * @package     Teamwork_Transfer
 * @author      Teamwork
 */

class Teamwork_Transfer_Model_Class_Price extends Teamwork_Transfer_Model_Transfer
{
    /**
     * Product price code -> ec level mapping array
     *
     * @var array (<price attribute code> => ecprice.<level> (Teamwork_Service_Model_Mapping::CONST_ECPRICE == "ecprice") )
     */
    protected $_priceMapping;

    /**
     * Item model
     *
     * @var Teamwork_Transfer_Model_Class_Item
     */
    protected $_itemModel;

    /**
     * Working product object
     *
     * @var Mage_Catalog_Model_Product
     */
    protected $_product;

    /**
     * Prepare working objects
     *
     * @param array $globalVars
     */
    public function init($globalVars)
    {
        $this->_globalVars = $globalVars;
        $this->_db = Mage::getSingleton('core/resource')->getConnection('core_write');
        $this->_product = Mage::getModel('catalog/product');

        $this->_mapModel = Mage::getModel('teamwork_service/mapping');
        $this->_mapModel->getMappingFields($globalVars['channel_id']);
        $this->_priceMapping = $this->_mapModel->getPrice();

        $this->_itemModel = Mage::getModel('teamwork_transfer/class_item');
        $this->_itemModel->init($globalVars);
        $this->_itemModel->initAttributeData();
    }

    /**
     * Entry point.
     */
    public function execute()
    {
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        try
        {
            Mage::helper('teamwork_transfer/reindex')->registerReindex();
            $this->_getStagingItems();
        }
        catch(Exception $e)
        {
            $this->_getLogger()->addMessage(sprintf("Should not be here: file: %s; line: %s", __FILE__, __LINE__), Zend_Log::DEBUG);
            $this->_getLogger()->addException($e);
            $this->_addErrorMsg("Internal error (exception): " . $e->getMessage(), false);
        }
        Mage::helper('teamwork_transfer/reindex')->runIndexerByCode('catalog_product_price');

        $this->updateEcm('Prices');
        return $this;
    }

    /**
     * Get data for updating from staging tables and initiate update process.
     */
    protected function _getStagingItems()
    {
        /*get product id list*/
        $select = $this->_db->select()
            ->from(array('pr' => Mage::getSingleton('core/resource')->getTableName('service_price')), array())
            ->join(array('it' => Mage::getSingleton('core/resource')->getTableName('service_items')), "pr.item_id = it.item_id AND it.internal_id IS NOT NULL AND it.request_id != '{$this->_globalVars['request_id']}'", array())
            ->join(array('st' => Mage::getSingleton('core/resource')->getTableName('service_style')), 'st.style_id = it.style_id AND st.internal_id IS NOT NULL', array('st.internal_id'))
            ->where('pr.request_id = ?', $this->_globalVars['request_id'])
        ->group('st.internal_id');

        $styles = $this->_db->fetchCol($select);

        if(!empty($styles))
        {
            /*processing products' price data using 100 pcs chunks*/
            $step = 100;
            for($i=0,$j=count($styles); $i<=$j; $i=$i+$step)
            {
                $style_ids = implode(',', array_slice($styles, $i, $step));
                if(!empty($style_ids))
                {
                    $select = $this->_db->select()
                        ->from(array('pr' => Mage::getSingleton('core/resource')->getTableName('service_price')), array('price', 'price_level'))
                        ->join(array('it' => Mage::getSingleton('core/resource')->getTableName('service_items')), "pr.item_id = it.item_id AND it.internal_id IS NOT NULL AND it.request_id != '{$this->_globalVars['request_id']}'", array('plu', 'internal_id', 'attribute1_id', 'attribute2_id', 'attribute3_id'))
                        ->join(array('st' => Mage::getSingleton('core/resource')->getTableName('service_style')), "st.style_id = it.style_id AND st.internal_id in ({$style_ids})", array('style_internal_id' => 'st.internal_id', 'attributeset1', 'attributeset2', 'attributeset3', 'inventype'))
                        ->where('pr.request_id = ?', $this->_globalVars['request_id'])
                    ->order(array('st.internal_id desc', 'it.internal_id asc', 'price_level asc'));

                    $this->_updateProducts($this->_db->fetchAll($select));
                }
            }
        }
    }

    /**
     * Prepare magento products
     *
     * @param array $items
     */
    protected function _updateProducts($items)
    {
        if(!empty($items) && is_array($items))
        {
            $data = array();
            foreach($items as $id => $item)
            {
                $data[$item['style_internal_id']]['items'][$item['internal_id']][$item['price_level']] = $item['price'];
                $data[$item['style_internal_id']]['attributes'][$item['internal_id']]['attribute1_id'] = $item['attribute1_id'];
                $data[$item['style_internal_id']]['attributes'][$item['internal_id']]['attribute2_id'] = $item['attribute2_id'];
                $data[$item['style_internal_id']]['attributes'][$item['internal_id']]['attribute3_id'] = $item['attribute3_id'];
                $data[$item['style_internal_id']]['attributes'][$item['internal_id']]['plu'] = $item['plu'];
                $data[$item['style_internal_id']]['style']['attributeset1'] = $item['attributeset1'];
                $data[$item['style_internal_id']]['style']['attributeset2'] = $item['attributeset2'];
                $data[$item['style_internal_id']]['style']['attributeset3'] = $item['attributeset3'];
                $data[$item['style_internal_id']]['inventype'] = $item['inventype'];
                unset($items[$id]);
            }
            unset($items);
            
            $priceAttributeCodes = $this->_itemModel->getPriceAttributeCodes();
            
            $storeId = null;
            if (Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_SEVERAL_STORES)
                    && array_key_exists('store_id', $this->_globalVars))
            {
                $storeId = $this->_globalVars['store_id'];
            }
            
            $parentAttributesData = array();
            foreach($data as $parentId => $info)
            {
                $childCollection = Mage::getModel('catalog/product')->getCollection();
                if (!is_null($storeId))
                {
                    $childCollection->setStore($storeId);
                }
                $childCollection->addIdFilter(array_keys($info['items']));
                $childCollection->addAttributeToSelect($priceAttributeCodes);

                $parentProductType = $this->_itemModel->getProductTypeByInventype($info['inventype']);
                foreach($childCollection as $child)
                {
                    $childId = $child->getId();
                    if (array_key_exists($childId, $info['items']))
                    {
                        foreach($this->_priceMapping as $attributeCode => $mappingPriceLevel)
                        {
                            $level = (int)substr($mappingPriceLevel, -1);
                            $amount = !empty( $info['items'][$childId][$level] ) ? floatval($info['items'][$childId][$level]) : '';
                            if( $attributeCode == 'price' && empty($amount) )
                            {
                                $amount = floatval(0);
                            }
                            $child->setData($attributeCode, $amount);
                        }
                        $this->_itemModel->removeEqualPriceData($child);
                        
                        $doSave = $this->saveChecker($child);
                        if($doSave)
                        {
                            $obj = new Varien_Object();
                            $this->_itemModel->copyPriceData($child, $obj);
                            if (count($obj->getData()))
                            {
                                try
                                {
                                    Mage::getSingleton('catalog/product_api')->update($childId, $obj->getData(), $storeId);
                                }
                                catch(Exception $e)
                                {
                                    $this->_addErrorMsg(sprintf("Error occured while price save for simple product trought API: simple product %s - %s", $childId, $e->getMessage()), true);
                                    $this->_getLogger()->addException($e);
                                }
                            }
                            unset($obj);
                            $this->checkLastUpdateTime();
                        }
                        if ($parentProductType == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
                        {
                            if (!array_key_exists($parentId, $parentAttributesData))
                            {
                                $parentAttributesData[$parentId] = array(
                                    'children'          => array(),
                                    'children_saved'    => false,
                                );
                            } 
                            $parentAttributesData[$parentId]['children_saved'] |= $doSave;
                            $attrArray = $this->_itemModel->addAttributeFromSets($child, $info['attributes'][$childId], true, $info['style']);
                            if(!empty($attrArray))
                            {
                                $parentAttributesData[$parentId]['children'][$childId] = $attrArray;
                            }
                        }
                    }
                }
                if (array_key_exists($parentId, $parentAttributesData) && (!$parentAttributesData[$parentId]['children_saved']))
                {
                    unset($parentAttributesData[$parentId]);
                }
            }
            
            if (count($parentAttributesData))
            {
                $parentIds = array_keys($parentAttributesData);
                foreach($parentIds as $productId)
                {
                    try
                    {
                        $configurableProduct = $this->_itemModel->_loadProduct($productId, Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);
                        if( !$configurableProduct->isConfigurable() )
                        {
                            continue;
                        }
                        
                        $style = array('internal_id' => $productId);
                        $attributesData = $this->_itemModel->getConfigurableAttributesData($configurableProduct, $parentAttributesData[$productId]['children'], $style);
                        if ($this->_itemModel->hasErrorMsgs())
                        {
                            $this->_addErrorMsgs($this->_itemModel->getErrorMsgs());
                            $this->_itemModel->cleanUpErrorMsgs();
                        }
                        $configurableProduct->setConfigurableAttributesData($attributesData);
                        $configurableProduct->setCanSaveConfigurableAttributes(true);
                        $configurableProduct->setAffectConfigurableProductAttributes(true);
                    
                        $configurableProduct->save();
                    }
                    catch(Exception $e)
                    {
                        $this->_addErrorMsg(sprintf("Error occured while super attribute price data saving for configurable product: style %s - %s", $productId, $e->getMessage()), true);
                        $this->_getLogger()->addException($e);
                    }
                    $this->checkLastUpdateTime();
                }
                unset($parentCollection);
            }
            unset($childCollection);
        }
    }
    
    protected function saveChecker($product)
    {
        $doSave = false;
        foreach($this->_priceMapping as $attributeCode => $mappingPriceLevel)
        {
            $origAmount = is_null($product->getOrigData($attributeCode)) ? '' : floatval( $product->getOrigData($attributeCode) );
            if( $product->getData($attributeCode) !== $origAmount  )
            {
                $doSave = true;
                break;
            }
        }
        return $doSave;
    }
}
