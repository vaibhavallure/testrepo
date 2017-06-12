<?php

class Ecp_Color_Model_Observer
{

    public function updateColors($observer)
    {
        $attribute = $observer->getAttribute();
        $colours = Mage::getStoreconfig('ecp_color/color_attr');
        $colours = explode(',', $colours);
        if (in_array($attribute->getAttributeCode(), $colours)) {
            $optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->setAttributeFilter($attribute->getId())
                ->setPositionOrder('desc', true);

            $currentOptions = array();
            foreach ($optionCollection as $option)
                $currentOptions[$option->getValue()] = $option->getOptionId();

            $options = $observer->getAttribute()->getOption();


            /* echo '<pre>';
              var_dump($options['value']);
              die; */

            $post = Mage::app()->getRequest()->getPost();
            $deleteimage = $post['option']['deleteimage'];

            foreach ($options['value'] as $key => $values) {
                $oldKey = $key;
                if (!is_numeric($key)) {
                    $key = $currentOptions[$values[0]];
                }

                $color = Mage::getModel('ecp_color/color')->getCollection()->addFieldToFilter('eav_id', $key);
                $color = ($color->getSize() == 1) ? $color->getFirstItem() : Mage::getModel('ecp_color/color');

                $color->setHex($options['hex'][$oldKey]);

                $imgs = $_FILES['option']['name'];
                $tmpImgs = $_FILES['option']['tmp_name'];


                if ($imgs['image'][$oldKey] != '') {
                    $file = $oldKey . substr($imgs['image'][$oldKey], strrpos($imgs['image'][$oldKey], '.'), (strlen($imgs['image'][$oldKey]) - 1));
                    $pathMedia = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'color' . DS . $file;

                    //echo $pathMedia."<br />";
                    if (move_uploaded_file($tmpImgs['image'][$oldKey], $pathMedia)) {
                        $color->setImage($file);
                        //echo "guardado<br />";
                    }
                }

                if( (int)$deleteimage[$oldKey] == 1 ) {
                    $color->setImage('');
                }
                $color->setEavId($key)
                    ->setOrder($options['order'][$oldKey])
                    ->save();
            }
        }
    }

    public function reorderAttributes($observer)
    {
        $sizeId = Mage::getResourceModel('eav/entity_attribute')
            ->getIdByCode('catalog_product', 'size');
        $colorId = Mage::getResourceModel('eav/entity_attribute')
            ->getIdByCode('catalog_product', 'color');

        $resource = Mage::getSingleton('core/resource');
        $DBConnection = $resource->getConnection('core_write');
        $table = $resource->getTableName('catalog/product_super_attribute');
        if (empty($table) || empty($sizeId) || empty($colorId)) return;
        $query = 'select * from ' . $table . ' where attribute_id = ' . $sizeId . ' or attribute_id = ' . $colorId;
        $attributes = $DBConnection->fetchAll($query);
        $toUpdate = array();
        foreach ($attributes as $key => $attribute) {
            $toUpdate[$attribute['product_id']][$attribute['attribute_id']] = $attribute['position'];
        }

        foreach ($toUpdate as $productId => $attribute) {
            if (isset($attribute[$sizeId]) && isset($attribute[$colorId])) {
                if ($attribute[$sizeId] != 1 || $attribute[$colorId] != 0) {
                    Mage::log('Reordenando atributos del producto ' . $productId);
                    $DBConnection->query('update ' . $table . ' set position = 0 where product_id = ' . $productId . ' and attribute_id = ' . $colorId);
                    $DBConnection->query('update ' . $table . ' set position = 1 where product_id = ' . $productId . ' and attribute_id = ' . $sizeId);
                }
            }
        }
    }

}

