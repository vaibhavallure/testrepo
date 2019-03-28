<?php

class Teamwork_CEGiftcards_Model_Resource_Catalog_Product_Type_Giftcard_Indexer_Price
    extends Mage_Catalog_Model_Resource_Product_Indexer_Price_Default
{

    public function registerEvent(Mage_Index_Model_Event $event)
    {
        $attributes = array(
            'giftcard_amount',
            'giftcard_amount_min',
            'giftcard_amount_max',
            'giftcard_open_amount',
        );

        $entity = $event->getEntity();
        if ($entity == Mage_Catalog_Model_Product::ENTITY) {
            switch ($event->getType()) {
                case Mage_Index_Model_Event::TYPE_SAVE:
                    /* @var $product Mage_Catalog_Model_Product */
                    $product      = $event->getDataObject();
                    $reindexPrice = false;
                    foreach ($attributes as $code) {
                        if ($product->dataHasChangedFor($code)) {
                            $reindexPrice = true;
                            break;
                        }
                    }

                    if ($reindexPrice) {
                        $event->addNewData('product_type_id', $product->getTypeId());
                        $event->addNewData('reindex_price', 1);
                    }

                    break;

                case Mage_Index_Model_Event::TYPE_MASS_ACTION:
                    /* @var $actionObject Varien_Object */
                    $actionObject = $event->getDataObject();
                    $reindexPrice = false;

                    // check if attributes changed
                    $attrData = $actionObject->getAttributesData();
                    if (is_array($attrData)) {
                        foreach ($attributes as $code) {
                            if (array_key_exists($code, $attrData)) {
                                $reindexPrice = true;
                                break;
                            }
                        }
                    }

                    if ($reindexPrice) {
                        $event->addNewData('reindex_price_product_ids', $actionObject->getProductIds());
                    }

                    break;
            }
        }
    }

    protected function _prepareFinalPriceData($entityIds = null)
    {
        $this->_prepareDefaultFinalPriceTable();

        $write  = $this->_getWriteAdapter();
        $select = $write->select()
            ->from(array('e' => $this->getTable('catalog/product')), array('entity_id'))
            ->join(
                array('cg' => $this->getTable('customer/customer_group')),
                '',
                array('customer_group_id')
            );
        $this->_addWebsiteJoinToSelect($select, true);
        $this->_addProductWebsiteJoinToSelect($select, 'cw.website_id', 'e.entity_id');
        $select->columns(array('website_id'), 'cw')
            ->columns(array('tax_class_id'  => new Zend_Db_Expr('0')))
            ->where('e.type_id = ?', $this->getTypeId());

        // add enable products limitation
        $statusCond = $write->quoteInto('=?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $this->_addAttributeToSelect($select, 'status', 'e.entity_id', 'cs.store_id', $statusCond, true);

        $select->joinLeft(
            array('twgcamount' => $this->getTable('teamwork_cegiftcards/amount')),
            'twgcamount.product_id = e.entity_id',
            array()
        );


        $openAmount = $this->_addAttributeToSelect($select, 'giftcard_open_amount', 'e.entity_id', 'cs.store_id');
        $minAmount = $this->_addAttributeToSelect($select, 'giftcard_amount_min', 'e.entity_id', 'cs.store_id');

        $amountOrMinAmount = $write->getCheckSql($minAmount . ' < ' . new Zend_Db_Expr('MIN(twgcamount.amount)'), $minAmount, new Zend_Db_Expr('MIN(twgcamount.amount)'));

        $minPrice = $write->getCheckSql($openAmount . ' = 1', $amountOrMinAmount, new Zend_Db_Expr('MIN(twgcamount.amount)'));

        $select->group(array('e.entity_id', 'cg.customer_group_id', 'cw.website_id'))
            ->columns(array(
                'price'            => new Zend_Db_Expr('NULL'),
                'final_price'      => new Zend_Db_Expr('MIN(twgcamount.amount)'),
                'min_price'        => $minPrice,
                'max_price'        => new Zend_Db_Expr('NULL'),
                'tier_price'       => new Zend_Db_Expr('NULL'),
                'base_tier'        => new Zend_Db_Expr('NULL'),
                'group_price'      => new Zend_Db_Expr('NULL'),
                'base_group_price' => new Zend_Db_Expr('NULL'),
            ));

        if (!is_null($entityIds)) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        /**
         * Add additional external limitation
         */
        Mage::dispatchEvent('prepare_catalog_product_index_select', array(
            'select'        => $select,
            'entity_field'  => new Zend_Db_Expr('e.entity_id'),
            'website_field' => new Zend_Db_Expr('cw.website_id'),
            'store_field'   => new Zend_Db_Expr('cs.store_id')
        ));

        $query = $select->insertFromSelect($this->_getDefaultFinalPriceTable());
        $write->query($query);
        return $this;
    }
}
