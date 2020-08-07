<?php
/**
 * 
 * @author allure
 *
 */
class Allure_BulkProduct_Model_Catalog_Convert_Parser_Product
extends Mage_Catalog_Model_Convert_Parser_Product
{
    
    /**
     * Unparse (prepare data) loaded products
     *
     * @return Mage_Catalog_Model_Convert_Parser_Product
     */
    public function unparse()
    {
        $entityIds = $this->getData();
        
        //load category collection
        $categoryArray = array();
        $categories = Mage::getModel('catalog/category')->getCollection()
        ->addAttributeToSelect('*');
        foreach ($categories as $category){
            $categoryArray[$category->getId()] = $category->getName();
        }

        foreach ($entityIds as $i => $entityId) {
            $product = $this->getProductModel()
                ->setStoreId($this->getStoreId())
                ->load($entityId);
            $this->setProductTypeInstance($product);
            /* @var $product Mage_Catalog_Model_Product */

            $position = Mage::helper('catalog')->__('Line %d, SKU: %s', ($i+1), $product->getSku());
            $this->setPosition($position);
            
            //Get category names by category id assigned by product
            $categoryIds = $product->getCategoryIds();
            $categoryNames = array();
            foreach ($categoryIds as $catId){
                $categoryNames[] = $categoryArray[$catId];
            }

            $row = array(
                'store'         => $this->getStore()->getCode(),
                'websites'      => '',
                'attribute_set' => $this->getAttributeSetName($product->getEntityTypeId(),
                                        $product->getAttributeSetId()),
                'type'          => $product->getTypeId(),
                'category_ids'  => join(',', $product->getCategoryIds())
            );
            
            //add new csv column as category name
            $row['category_names'] = join(',', $categoryNames);

            if ($this->getStore()->getCode() == Mage_Core_Model_Store::ADMIN_CODE) {
                $websiteCodes = array();
                foreach ($product->getWebsiteIds() as $websiteId) {
                    $websiteCode = Mage::app()->getWebsite($websiteId)->getCode();
                    $websiteCodes[$websiteCode] = $websiteCode;
                }
                $row['websites'] = join(',', $websiteCodes);
            } else {
                $row['websites'] = $this->getStore()->getWebsite()->getCode();
                if ($this->getVar('url_field')) {
                    $row['url'] = $product->getProductUrl(false);
                }
            }

            foreach ($product->getData() as $field => $value) {
                if (in_array($field, $this->_systemFields) || is_object($value)) {
                    continue;
                }

                $attribute = $this->getAttribute($field);
                if (!$attribute) {
                    continue;
                }

                if ($attribute->usesSource()) {
                    $option = $attribute->getSource()->getOptionText($value);
                    if ($value && empty($option) && $option != '0') {
                        $this->addException(
                            Mage::helper('catalog')->__('Invalid option ID specified for %s (%s), skipping the record.', $field, $value),
                            Mage_Dataflow_Model_Convert_Exception::ERROR
                        );
                        continue;
                    }
                    if (is_array($option)) {
                        $value = join(self::MULTI_DELIMITER, $option);
                    } else {
                        $value = $option;
                    }
                    unset($option);
                } elseif (is_array($value)) {
                    continue;
                }

                $row[$field] = $value;
            }

            if ($stockItem = $product->getStockItem()) {
                foreach ($stockItem->getData() as $field => $value) {
                    if (in_array($field, $this->_systemFields) || is_object($value)) {
                        continue;
                    }
                    $row[$field] = $value;
                }
            }

            $productMediaGallery = $product->getMediaGallery();
            $product->reset();

            $processedImageList = array();
            foreach ($this->_imageFields as $field) {
                if (isset($row[$field])) {
                    if ($row[$field] == 'no_selection') {
                        $row[$field] = null;
                    } else {
                        $processedImageList[] = $row[$field];
                    }
                }
            }
            $processedImageList = array_unique($processedImageList);

            $batchModelId = $this->getBatchModel()->getId();
            $this->getBatchExportModel()
                ->setId(null)
                ->setBatchId($batchModelId)
                ->setBatchData($row)
                ->setStatus(1)
                ->save();

            $baseRowData = array(
                'store'     => $row['store'],
                'website'   => $row['website'],
                'sku'       => $row['sku']
            );
            unset($row);

            foreach ($productMediaGallery['images'] as $image) {
                if (in_array($image['file'], $processedImageList)) {
                    continue;
                }

                $rowMediaGallery = array(
                    '_media_image'          => $image['file'],
                    '_media_lable'          => $image['label'],
                    '_media_position'       => $image['position'],
                    '_media_is_disabled'    => $image['disabled']
                );
                $rowMediaGallery = array_merge($baseRowData, $rowMediaGallery);

                $this->getBatchExportModel()
                    ->setId(null)
                    ->setBatchId($batchModelId)
                    ->setBatchData($rowMediaGallery)
                    ->setStatus(1)
                    ->save();
            }
        }

        return $this;
    }
    
}
