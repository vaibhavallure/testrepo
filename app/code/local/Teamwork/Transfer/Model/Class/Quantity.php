<?php
/**
 * Inventory updating model
 *
 * @category    Teamwork
 * @package     Teamwork_Transfer
 * @author      Teamwork
 */

class Teamwork_Transfer_Model_Class_Quantity extends Teamwork_Transfer_Model_Transfer
{
    public $productFactory = array();

    public $mode = 0;
    const SCHEDULER_MODE = 1;
    const FULL_INVENTORY_RECOUNT_MODE = 2;

    /**
     * Prepare working objects
     *
     * @param array $globalVars
     */
    public function init($globalVars)
    {
        $this->_globalVars = $globalVars;
        $this->_db = Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    /**
     * Entry point
     */
    public function execute()
    {
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        try
        {
            $this->updateProducts();
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
     * Update products' stock data
     *
     * @param array $itemIds
     */
    public function updateProducts()
    {
        $select = $this->_db->select()
            ->from(array('inv' => Mage::getSingleton('core/resource')->getTableName('service_inventory')), array('inv.item_id', $this->_getTotalQuantity()))
            ->join(array('it' => Mage::getSingleton('core/resource')->getTableName('service_items')), 'inv.item_id = it.item_id and inv.channel_id = it.channel_id', array('it.internal_id'))
            ->join(array('loc' => Mage::getSingleton('core/resource')->getTableName('service_location_status')), 'inv.location_id = loc.location_id and inv.channel_id = loc.channel_id', array())
            ->where('inv.channel_id = ?', $this->_globalVars['channel_id'])
            ->where('it.internal_id is not null')
            ->group('inv.item_id')
        ->order('it.style_id');

        switch($this->mode)
        {
            case self::SCHEDULER_MODE:
                $select->where(
                    'inv.item_id in ?',
                    $this->_db->select()->distinct()
                        ->from(Mage::getSingleton('core/resource')->getTableName('service_inventory'), array('item_id'))
                    ->where('request_id = ?', $this->_globalVars['request_id'])
                );
            break;
            case !self::FULL_INVENTORY_RECOUNT_MODE:
                $select
                   ->where('inv.request_id = ?', $this->_globalVars['request_id'])
                ->where('it.request_id != ?', $this->_globalVars['request_id']);
            break;
        }

        $items = $this->_db->fetchAll($select);
        if(!empty($items))
        {
            $stockItemChanged = 0;
            $this->productFactory = Mage::helper('teamwork_transfer')->getProductFactory();

            $stockItem = Mage::getModel('cataloginventory/stock_item');
            $parentStockItem = Mage::getModel('cataloginventory/stock_item');
            foreach($items as $item)
            {
                if(!empty($item['internal_id']) && is_numeric($item['internal_id']))
                {
                    try
                    {
                        $newQty = $this->getNewQty($item['internal_id'], (int)$item['quantity']);
                        $this->updateProductQty($item, $newQty, $stockItem, $stockItemChanged);
                        $this->updateParentProductQty($stockItem, $parentStockItem, $stockItemChanged);
                    }
                    catch(Exception $e)
                    {
                        $product = Mage::getModel('catalog/product')->load($item['internal_id']);
                        $sku = "";
                        if ($product->getId())
                        {
                            $sku = $product->getSku();
                        }
                        $this->_addErrorMsg(sprintf("Error occured while stock info updating for \"%s\" product (internal id: %s): %s", $sku, $item['internal_id'], $e->getMessage()), true);
                        $this->_getLogger()->addException($e);
                    }

                    if($this->mode != self::SCHEDULER_MODE)
                    {
                        $this->checkLastUpdateTime();
                    }
                }
            }

            /*if method called not from different class object*/
            if ($stockItemChanged)
            {
                try
                {
                    Mage::getSingleton('index/indexer')->indexEvents(
                        Mage_CatalogInventory_Model_Stock_Item::ENTITY,
                        Mage_Index_Model_Event::TYPE_SAVE
                    );
                }
                catch(Exception $e)
                {
                    $this->_lastErrorMsg = $e->getMessage();
                    $this->_addErrorMsg(sprintf("Error occured while reindexing after stock updating: %s", $e->getMessage()), true);
                    $this->_getLogger()->addException($e);
                }
            }
        }
    }

    /**
     * Correct qty value using order's data about ordered but not delivered products
     *
     * @param int $internalId
     * @param int $qty
     */
    public function getNewQty($internalId, $qty)
    {
        if( Mage::helper('teamwork_service')->useRealtimeavailability() )
        {
            return $qty;
        }

        $select = "SELECT q.internal_id, SUM(q.total) qty FROM
        (
            SELECT ord.entity_id, ent.entity_id internal_id, GREATEST((it.qty_ordered - it.qty_canceled - it.qty_refunded - it.qty_shipped), 0) total
                FROM " . Mage::getSingleton('core/resource')->getTableName('sales_flat_order') . " ord
            JOIN  " . Mage::getSingleton('core/resource')->getTableName('sales_flat_order_item') . "  it
                ON it.order_id=ord.entity_id
            JOIN  " . Mage::getSingleton('core/resource')->getTableName('catalog_product_entity') . "  ent
                ON ent.sku=it.sku
            WHERE ord.state in ('processing', 'new', 'pending')
                GROUP by entity_id
        ) q
        WHERE q.internal_id = '{$internalId}'
        GROUP by q.internal_id";

        $reserved = $this->_db->fetchRow($select);
        if(!empty($reserved['qty']))
        {
            return ((($qty - $reserved['qty']) > 0) ? ($qty - $reserved['qty']) : 0);
        }
        return $qty;
    }

    /**
     * Update magento product stock data
     *
     * @param array $item
     * @param int $qty
     * @param null|Mage_CatalogInventory_Model_Stock_Item  $_stockItem
     * @return array|int
     */
    public function updateProductQty($item, $quantity, $stockItem, &$stockItemChanged)
    {
		if( !empty($item['internal_id']) )
        {
            $stockItem->setData(array())->setProcessIndexEvents(false);
            $stockItem->loadByProduct($item['internal_id']);

            if($stockItem->getItemId())
            {
                $stockItem->setProductId($item['internal_id']);

                $inventoryData = $this->_getInventoryData($item['item_id'], $quantity);
                if(!$stockItemChanged)
                {
                    foreach($inventoryData as $attribute => $value)
                    {
                        if($stockItem->getData($attribute) != $value)
                        {
                            $stockItemChanged = 1;
                        }
                    }
                }

                $stockItem->addData($inventoryData);
                $stockItem->save();
            }
        }
    }

    /**
     * Prepare stock data for product object (for external calling)
     *
     * @param int $itemId
     * @param int $productId
     * @param array $children
     *
     * @return array
     */
    public function getProductStockData($itemId, $productId, $children=array())
    {
        $select = $this->_db->select()
            ->from(array('inv' => Mage::getSingleton('core/resource')->getTableName('service_inventory')), array($this->_getTotalQuantity()))
            ->join(array('loc' => Mage::getSingleton('core/resource')->getTableName('service_location_status')), 'inv.location_id = loc.location_id and inv.channel_id = loc.channel_id', array())
            ->where('loc.channel_id = ?', $this->_globalVars['channel_id'])
        ->where('inv.item_id = ?', $itemId);
        $quantity = $this->_db->fetchOne($select);

        if (!$children && $productId)
        {
            $quantity = $this->getNewQty($productId, (int)$quantity);
        }

        $inventoryData = $this->_getInventoryData($itemId, (float)$quantity, $children);
        if(!$productId)
        {
            $inventoryData['item_id'] = null;
        }
        return $inventoryData;
    }

    protected function _getTotalQuantity()
    {
        $total = 'SUM(if(loc.enabled,inv.quantity,0)) as quantity';
        if(Mage::helper('teamwork_service')->useRealtimeavailability() && !Mage::getStoreConfig(Teamwork_Realtimeavailability_Model_Realtimeavailability::RTA_NEGATIVE_INVENTORY))
        {
            $total = 'SUM(GREATEST(if(loc.enabled,inv.quantity,0),0)) as quantity';
        }
        return $total;
    }

    protected function _getInventoryData($itemId, $quantity, $children=array())
    {
        $manageStock = 1;
        if(!$children)
        {
            $manageStock = $this->checkManageStock($itemId);
        }

        $inventoryData = array(
            'use_config_manage_stock'   => $manageStock,
            'qty'                       => $quantity,
        );

        if (Mage::getStoreConfigFlag(Teamwork_Transfer_Helper_Config::XML_PATH_UPDATE_STOCK_AVALIABILITY))
        {
            $inventoryData['is_in_stock'] = ( $quantity > 0 || $this->_getChildrenDependedStock($children) ) ?
                Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK :
            Mage_CatalogInventory_Model_Stock_Status::STATUS_OUT_OF_STOCK;
        }

        if( !$manageStock )
        {
            $inventoryData['manage_stock'] = $manageStock;
        }
        return $inventoryData;
    }

    public function checkManageStock($itemId)
	{
        $select = $this->_db->select()
            ->from( array('sty' => Mage::getSingleton('core/resource')->getTableName('service_style')), array('sty.inventype') )
        ->join( array('it' => Mage::getSingleton('core/resource')->getTableName('service_items')), "sty.style_id = it.style_id and sty.channel_id = it.channel_id and it.item_id='{$itemId}'", array() );
        $invenType = $this->_db->fetchOne($select);

        if($invenType == Teamwork_Transfer_Model_Class_Item::CHQ_PRODUCT_TYPE_SERVICEITEM)
        {
            return 0;
        }
        return 1;
	}

    protected function updateParentProductQty($stockItem, $parentStockItem, &$stockItemSaved)
	{
        if($productId = $stockItem->getProductId())
        {
            $parentIds = array();
            foreach($this->productFactory as $typeInstance) {
                $parentIds = array_merge($parentIds, $typeInstance->getParentIdsByChild($productId));
            }

            if( !empty($parentIds) )
            {
                foreach($parentIds as $parentId)
                {
                    if($parentId != $parentStockItem->getProductId())
                    {
                        $parentStockItem->setData(array())->setProcessIndexEvents(false);
                        $parentStockItem->loadByProduct($parentId);
                    }
                    if($parentStockItem->getIsInStock() == Mage_CatalogInventory_Model_Stock_Status::STATUS_OUT_OF_STOCK
                        && $stockItem->getIsInStock() == Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK )
                    {
                        $parentStockItem->setIsInStock( Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK );
                        $parentStockItem->save();
                        $stockItemSaved = true;
                    }
                }
            }
        }
    }
    
    protected function _getChildrenDependedStock($children)
    {
        foreach($children as $childId => $childAttributes)
        {
            foreach($childAttributes as $childAttribute)
            {
                if($childAttribute['product_stock'] == Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK && $childAttribute['product_status'] == Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                {
                    return true;
                }
                break;
            }
        }
    }

    /*
        * since schedulerMode is deprecated in the quantity model, support the RTA versions which are using that flag
    */
    public function __set($property, $statement)
    {
        if($property == 'schedulerMode' && $statement === true)
        {
            $this->mode = self::SCHEDULER_MODE;
        }
    }
}