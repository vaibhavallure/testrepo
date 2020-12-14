<?php
class Doddle_Returns_Model_Api2_Product_Variants_Rest_Admin_V1 extends Mage_Catalog_Model_Api2_Product_Rest
{
    protected $_parentProduct;
    protected $_superAttributes;
    protected $_superAttributeCodes;
    protected $_attributes = array(
        'entity_id',
        'qty',
        'sku',
        'name',
        'doddle_returns_excluded'
    );

    /**
     * Retrieve list of alternative variant products
     *
     * @return array
     * @throws Mage_Api2_Exception
     */
    protected function _retrieveCollection()
    {
        $product = $this->_getProduct();
        $collection = $this->_getSiblingsCollection($product);

        /** @var Mage_Catalog_Model_Product $product */
        foreach ($collection as $product) {
            $this->_prepareProductForResponse($product);
        }

        return $collection->toArray();
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @throws Mage_Api2_Exception
     */
    protected function _prepareProductForResponse(Mage_Catalog_Model_Product $product)
    {
        $productData = $product->getData();

        // Add stock quantity, or false if stock management disabled
        /** @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
        $stockItem = $product->getStockItem();
        $productData['stock'] = $stockItem->getManageStock() ? $stockItem->getQty() : false;

        // Add image URL
        $productData['image_url'] = (string) Mage::helper('catalog/image')->init($product, 'image');

        // Add super attributes sub array
        $productData['attributes'] = $this->_getAttributes($product);

        // Remove super attributes from top level
        $superAttributes = $this->_getSuperAttributes($product);
        foreach ($superAttributes as $superAttribute) {
            $product->unsetData($superAttribute->getProductAttribute()->getAttributeCode());
            unset($productData[$superAttribute->getProductAttribute()->getAttributeCode()]);
        }

        $product->addData($productData);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Resource_Product_Collection
     * @throws Mage_Api2_Exception
     */
    protected function _getSiblingsCollection(Mage_Catalog_Model_Product $product)
    {
        /** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('catalog/product_collection');

        // Filter collection by sibling product IDs
        $siblingsIds = $this->_getSiblingProductIds($product);
        $collection->addFieldToFilter('entity_id', array('in' => $siblingsIds));

        // Return empty collection if no siblings available
        if (count($siblingsIds) < 1) {
            return $collection;
        }

        // Prepare full array of required product attributes
        $attributes = array_merge(
            $this->_attributes,
            $this->_getSuperAttributeCodes($product)
        );

        // Add required product attributes to collection
        $collection->addAttributeToSelect($attributes);

        // Filter to ensure products are enabled
        $collection->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);

        // Filter to ensure products are in stock
        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);

        // Add stock item data to collection
        Mage::getSingleton('cataloginventory/stock')->addItemsToProducts($collection);

        return $collection;
    }

    /**
     * @param $productId
     * @return Mage_Core_Model_Abstract
     * @throws Mage_Api2_Exception
     */
    protected function _getParentProduct($productId)
    {
        if (!isset($this->_parentProduct)) {
            /** @var Mage_Catalog_Model_Product_Type_Configurable $productType */
            $productType = Mage::getModel('catalog/product_type_configurable');

            $parentIds = $productType->getParentIdsByChild($productId);

            if (count($parentIds)  < 1) {
                $this->_critical('Parent product not found.', Mage_Api2_Model_Server::HTTP_NOT_FOUND);
            }

            // Get most recent parent association
            $parentId = end($parentIds);
            $this->_parentProduct = Mage::getModel('catalog/product')->load($parentId);
        }

        return $this->_parentProduct;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return array
     * @throws Mage_Api2_Exception
     */
    protected function _getSiblingProductIds(Mage_Catalog_Model_Product $product)
    {
        $parentProduct = $this->_getParentProduct($product->getId());
        $siblingIds = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($parentProduct->getId());

        $siblingIds = reset($siblingIds);

        return $siblingIds;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return array
     * @throws Mage_Api2_Exception
     */
    protected function _getAttributes(Mage_Catalog_Model_Product $product)
    {
        $attributes = array();
        $superAttributes = $this->_getSuperAttributes($product);

        foreach ($superAttributes as $attribute) {
            $attributes[] = array(
                'label'       => $attribute->getLabel(),
                'value'       => $product->getAttributeText($attribute->getProductAttribute()->getAttributeCode()),
                'value_index' => $product->getData($attribute->getProductAttribute()->getAttributeCode())
            );
        }

        return $attributes;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return mixed
     * @throws Mage_Api2_Exception
     */
    protected function _getSuperAttributeCodes(Mage_Catalog_Model_Product $product)
    {
        if (!isset($this->_superAttributeCodes)) {
            $superAttributes = $this->_getSuperAttributes($product);
            foreach ($superAttributes as $superAttribute) {
                $this->_superAttributeCodes[] = $superAttribute->getProductAttribute()->getAttributeCode();
            }
        }

        return $this->_superAttributeCodes;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return mixed
     * @throws Mage_Api2_Exception
     */
    protected function _getSuperAttributes(Mage_Catalog_Model_Product $product)
    {
        if (!isset($this->_superAttributes)) {
            $parentProduct = $this->_getParentProduct($product->getId());
            $this->_superAttributes = $parentProduct->getTypeInstance()->getConfigurableAttributes();
        }

        return $this->_superAttributes;
    }
}
