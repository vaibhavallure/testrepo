<?php

class Allure_BrownThomas_Model_Data
{

    const SUPPLIER = '506080';
    const DEPARTMENT = '6321';

    private function data() {
        return Mage::helper("brownthomas/data");
    }

    private function readConnection()
    {
        $resource = Mage::getSingleton('core/resource');
        return $resource->getConnection('core_read');
    }

    public function formatString($str,$pad_length,$pad_string=' ',$pad_type=STR_PAD_RIGHT)
    {
       return  substr(str_pad($str, $pad_length,$pad_string, $pad_type), 0, $pad_length);
    }

    public function getFITEM()
    {
            $readConnection=$this->readConnection();
            $attrbute_id=$this->data()->getAttributeId('brown_thomas_inventory');

            $whr='WHERE cpe.type_id="simple" AND cpv.attribute_id=' . $attrbute_id . ' AND cpv.value IS NOT NULL AND cpv.value >0';
            $sql='SELECT cpv.entity_id from catalog_product_entity cpe JOIN catalog_product_entity_varchar cpv on cpv.entity_id=cpe.entity_id '.$whr;
            $products = $readConnection->fetchCol($sql);
            $data=array();

            foreach ($products as $product_id) {
                 $_product = Mage::getSingleton("catalog/product")->load($product_id);

                 $data[$product_id]['record_type']='FITEM';
                 $data[$product_id]['action_type']='N';
                 $data[$product_id]['UPC']=$this->formatString($_product->getBarcode(),13);
                 $data[$product_id]['UPC_TYPE']='EAN13';

                 $formatedSKU = strtolower(preg_replace("/[^a-zA-Z]+/", "", $_product->getSKU()));
                 $data[$product_id]['WC_Product_ID']=$this->formatString(SELF::SUPPLIER."x".SELF::DEPARTMENT."x".$formatedSKU,45);

                 $data[$product_id]['VPN']=$this->formatString($_product->getSKU(),30);
                 $data[$product_id]['department']=self::DEPARTMENT;
                 $data[$product_id]['description']=$this->formatString($_product->getDescription(),40);
                 $data[$product_id]['short_description']=$this->formatString($_product->getShortDescription(),20);


            }

            echo "<pre>";

            var_dump($data);




    }

}