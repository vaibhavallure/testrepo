<?php

class Teamwork_CEGiftcards_Transfer_Model_Class_Item extends Teamwork_Transfer_Model_Class_Item
{
    protected function _importSimpleProduct(&$style)
    {
        parent::_importSimpleProduct($style);

        if ($style['inventype'] == Teamwork_Transfer_Model_Class_Item::CHQ_PRODUCT_TYPE_SERVICEITEM) {

            $item = current($this->_items[$style['style_id']]);

            $gcType = $this->_convertCHQGCType($item);

            if ($gcType !== Teamwork_CEGiftcards_Model_Catalog_Product_Type_Giftcard::TYPE_UNKNOWN) {


                $productId = $style['internal_id'];
                if (!$productId){
                    $tableStyle = Mage::getSingleton('core/resource')->getTableName('service_style');
                    $select = $this->_db->select()
                        ->from(array('i' => $tableStyle), array('internal_id' => 'i.internal_id'))
                    ->where('i.style_id = ?', $style['style_id']);

                    $productId = $this->_db->fetchOne($select);
                }

                $product = $this->_loadProduct($productId);
                if (!$product->getId()) return;

                $amount = $product->getData('price');

                $product->setTypeId(Teamwork_CEGiftcards_Model_Catalog_Product_Type_Giftcard::TYPE_GIFTCARD);
                $product->save();

                $product = $this->_loadProduct($productId);

                //if first creation in multistore mode
                if ($product->getStoreId() !== Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID
                    && is_null($product->getData('giftcard_type'))) {
                        $product = Mage::getModel('catalog/product')->load($productId, array('name'));
                }

                $product->setData('giftcard_type', $gcType);

                $amount = floatval($amount);
                if ($amount <= 0.0001) {
                    $amount = array();
                    $giftcardOpenAmount = 1;
                } else {
                    $amount = array($amount);
                    $giftcardOpenAmount = 0;
                }

                $product->setData('giftcard_open_amount', $giftcardOpenAmount);
                if ($giftcardOpenAmount) {
                    $product->setData('giftcard_amount_min', Mage::getStoreConfig(Teamwork_CEGiftcards_Helper_Data::XML_PATH_MIN_OPEN_AMOUNT));
                    $product->setData('giftcard_amount_max', Mage::getStoreConfig(Teamwork_CEGiftcards_Helper_Data::XML_PATH_MAX_OPEN_AMOUNT));
                }
                $product->setData('giftcard_amount', $amount);


                $product->setStockData(array(
                    'use_config_manage_stock' => 0,
                    'is_in_stock' => 1,
                    'qty' => 9999,
                    'manage_stock' => 0,
                    'use_config_notify_stock_qty' => 0,
                ));

                $this->_saveProduct($product);
                $this->_saveNonDefaultStoreValues($product);

            }
        }

    }


    protected function _convertCHQGCType($item)
    {
        $mType = Teamwork_CEGiftcards_Model_Catalog_Product_Type_Giftcard::TYPE_UNKNOWN;
        if ($item['IsChargeItem']) {
            switch ($item['ChargeItemType']) {
                case 'VirtualGiftCard':
                    $mType = Teamwork_CEGiftcards_Model_Catalog_Product_Type_Giftcard::TYPE_VIRTUAL;
                    break;
                case 'PhysicalGiftCard':
                    $mType = Teamwork_CEGiftcards_Model_Catalog_Product_Type_Giftcard::TYPE_PHYSICAL;
                    break;
                default:
                    break;
            }
        }
        return $mType;
    }

}
