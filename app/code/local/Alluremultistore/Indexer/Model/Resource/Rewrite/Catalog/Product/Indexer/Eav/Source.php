<?php

class Alluremultistore_Indexer_Model_Resource_Rewrite_Catalog_Product_Indexer_Eav_Source
    extends Mage_Catalog_Model_Resource_Product_Indexer_Eav_Source
{
   

    /**
     * Prepare data index for indexable select attributes
     *
     * @param array $entityIds      the entity ids limitation
     * @param int $attributeId      the attribute id limitation
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Eav_Source
     */
    protected function _prepareSelectIndex($entityIds = null, $attributeId = null)
    {
        $adapter    = $this->_getWriteAdapter();
        $idxTable   = $this->getIdxTable();
        // prepare select attributes
        if (is_null($attributeId)) {
            $attrIds    = $this->_getIndexableAttributes(false);
        } else {
            $attrIds    = array($attributeId);
        }

        if (!$attrIds) {
            return $this;
        }

        /**@var $subSelect Varien_Db_Select*/
        $subSelect = $adapter->select()
            ->from(
                array('s' => $this->getTable('core/store')),
                array('store_id', 'website_id')
            )
            ->joinLeft(
                array('d' => $this->getValueTable('catalog/product', 'int')),
                '1 = 1 AND d.store_id = 0',
                array('entity_id', 'attribute_id', 'value')
            )
            ->where('s.store_id != 0');

        $statusCond = $adapter->quoteInto(' = ?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $this->_addAttributeToSelect($subSelect, 'status', 'd.entity_id', 's.store_id', $statusCond);

        if (!is_null($entityIds)) {
            $subSelect->where('d.entity_id IN(?)', $entityIds);
        }

        /**@var $select Varien_Db_Select*/
        $select = $adapter->select()
            ->from(
                array('pid' => new Zend_Db_Expr(sprintf('(%s)',$subSelect->assemble()))),
                array()
            )
            ->joinLeft(
                array('pis' => $this->getValueTable('catalog/product', 'int')),
                'pis.entity_id = pid.entity_id AND pis.attribute_id = pid.attribute_id AND pis.store_id = pid.store_id',
                array()
            )
            ->columns(
                array(
                    'pid.entity_id',
                    'pid.attribute_id',
                    'pid.store_id',
                    'value' => $adapter->getIfNullSql('pis.value', 'pid.value')
                )
            )
            ->where('pid.attribute_id IN(?)', $attrIds);

        $select->where(Mage::getResourceHelper('catalog')->getIsNullNotNullCondition('pis.value', 'pid.value'));

        /**
         * Add additional external limitation
         */
        Mage::dispatchEvent('prepare_catalog_product_index_select', array(
            'select'        => $select,
            'entity_field'  => new Zend_Db_Expr('pid.entity_id'),
            'website_field' => new Zend_Db_Expr('pid.website_id'),
            'store_field'   => new Zend_Db_Expr('pid.store_id')
        ));

        $query = $select->distinct()->insertFromSelect($idxTable);
        //Mage::log($query,Zend_log::DEBUG,'product_indexer',true);
        $adapter->query($query);

        return $this;
    }

    /**
     * Prepare data index for indexable multiply select attributes
     *
     * @param array $entityIds      the entity ids limitation
     * @param int $attributeId      the attribute id limitation
     * @return Mage_Catalog_Model_Resource_Product_Indexer_Eav_Source
     */
    protected function _prepareMultiselectIndex($entityIds = null, $attributeId = null)
    {
        $adapter    = $this->_getWriteAdapter();

        // prepare multiselect attributes
        if (is_null($attributeId)) {
            $attrIds    = $this->_getIndexableAttributes(true);
        } else {
            $attrIds    = array($attributeId);
        }

        if (!$attrIds) {
            return $this;
        }

        // load attribute options
        $options = array();
        $select  = $adapter->select()
            ->from($this->getTable('eav/attribute_option'), array('attribute_id', 'option_id'))
            ->where('attribute_id IN(?)', $attrIds);
        $query = $select->query();
        while ($row = $query->fetch()) {
            $options[$row['attribute_id']][$row['option_id']] = true;
        }

        // prepare get multiselect values query
        $productValueExpression = $adapter->getCheckSql('pvs.value_id > 0', 'pvs.value', 'pvd.value');
        $select = $adapter->select()
            ->from(
                array('pvd' => $this->getValueTable('catalog/product', 'text')),
                array('entity_id', 'attribute_id'))
            ->join(
                array('cs' => $this->getTable('core/store')),
                '',
                array('store_id'))
            ->joinLeft(
                array('pvs' => $this->getValueTable('catalog/product', 'text')),
                'pvs.entity_id = pvd.entity_id AND pvs.attribute_id = pvd.attribute_id'
                    . ' AND pvs.store_id=cs.store_id',
                array('value' => $productValueExpression))
            ->where('pvd.store_id=?',
                $adapter->getIfNullSql('pvs.store_id', Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID))
            ->where('cs.store_id!=?', Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
            ->where('pvd.attribute_id IN(?)', $attrIds);

        $statusCond = $adapter->quoteInto('=?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $this->_addAttributeToSelect($select, 'status', 'pvd.entity_id', 'cs.store_id', $statusCond);

        if (!is_null($entityIds)) {
            $select->where('pvd.entity_id IN(?)', $entityIds);
        }

        /**
         * Add additional external limitation
         */
        Mage::dispatchEvent('prepare_catalog_product_index_select', array(
            'select'        => $select,
            'entity_field'  => new Zend_Db_Expr('pvd.entity_id'),
            'website_field' => new Zend_Db_Expr('cs.website_id'),
            'store_field'   => new Zend_Db_Expr('cs.store_id')
        ));

        $i     = 0;
        $data  = array();
        $query = $select->distinct()->query();
        while ($row = $query->fetch()) {
            $values = array_unique(explode(',', $row['value']));
            foreach ($values as $valueId) {
                if (isset($options[$row['attribute_id']][$valueId])) {
                    $data[] = array(
                        $row['entity_id'],
                        $row['attribute_id'],
                        $row['store_id'],
                        $valueId
                    );
                    $i ++;
                    if ($i % 10000 == 0) {
                        $this->_saveIndexData($data);
                        $data = array();
                    }
                }
            }
        }

        $this->_saveIndexData($data);
        unset($options);
        unset($data);

        return $this;
    }
}
