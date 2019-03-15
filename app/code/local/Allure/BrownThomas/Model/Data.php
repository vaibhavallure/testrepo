<?php

class Allure_BrownThomas_Model_Data
{

    const SUPPLIER = '506080';
    const DEPARTMENT = '6321';

    var $dataFudas=array();

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

    public function getFoundationHeader()
    {
        $data=array();
        $data['record_type'] = $this->formatString("FHEAD",5);
        $data['supplier'] = $this->formatString(self::SUPPLIER,10,"0",STR_PAD_LEFT);
        return $data;
    }

    public function getFITEM_FUDAS()
    {
        $readConnection = $this->readConnection();
        $attrbute_id = $this->data()->getAttributeId('brown_thomas_inventory');
        $barcode_attr_id=$this->data()->getAttributeId('barcode');

        $whr = 'WHERE cpe.type_id="simple" AND (cpv.attribute_id=' . $attrbute_id . ' AND cpv.value IS NOT NULL AND cpv.value >=0 ) AND (bar.attribute_id=' . $barcode_attr_id . ' AND bar.value IS NOT NULL)';
        $sql = 'SELECT cpv.entity_id from catalog_product_entity cpe JOIN catalog_product_entity_varchar cpv on cpv.entity_id=cpe.entity_id JOIN catalog_product_entity_varchar bar on bar.entity_id=cpe.entity_id ' . $whr;

        $products = $readConnection->fetchCol($sql);
        $data = array();
        $dataFudas=array();

        foreach ($products as $product_id) {
            $_product = Mage::getSingleton("catalog/product")->load($product_id);
            $data[$product_id]['record_type'] = 'FITEM';
            $data[$product_id]['action_type'] = 'N';
            $data[$product_id]['UPC'] = $this->formatString($_product->getBarcode(), 13);
            $data[$product_id]['UPC_TYPE'] = 'EAN13';
            $formatedSKU = strtolower(preg_replace("/[^a-zA-Z0-9]+/", "", $_product->getSKU()));
            $data[$product_id]['WC_Product_ID'] = $this->formatString(SELF::DEPARTMENT . "x" . SELF::SUPPLIER . "x" . $formatedSKU, 45);
            $data[$product_id]['VPN'] = $this->formatString($_product->getSKU(), 30);
            $data[$product_id]['department'] = self::DEPARTMENT;
            $data[$product_id]['description'] = $this->formatString(str_replace(',', '',$_product->getName()), 40);
            $data[$product_id]['short_description'] = $this->formatString(str_replace(',', '',$_product->getName()), 20);
            $data[$product_id]['packing_method'] = $this->formatString("FLAT", 6);
            $data[$product_id]['unit_of_measure'] = $this->formatString("EA", 4);
            $data[$product_id]['size_group_1'] = $this->formatString("", 10);
            $data[$product_id]['size_group_2'] = $this->formatString("", 10);
            $data[$product_id]['size_system'] = $this->formatString("", 4);
            $splitsku=$this->splitSku($_product->getSku());
            $data[$product_id]['size_1'] = $this->formatString($splitsku['p_size'], 10);
            $data[$product_id]['size_2'] = $this->formatString("N/A", 10);
            $data[$product_id]['color'] = $this->formatString("GOLD", 10);
            $data[$product_id]['supplier_color'] = $this->formatString(strtoupper($this->getVendorColor($_product)), 24);
            $data[$product_id]['color_group'] = $this->formatString("ARN_COLORS", 10);
            $data[$product_id]['unit_retail'] = $this->formatString(number_format((float)$_product->getPrice(), 2, '.', ''), 21, 0, STR_PAD_LEFT);
            $data[$product_id]['session_id'] = $this->formatString(45, 3,0,STR_PAD_LEFT);
            $data[$product_id]['phase_id'] = $this->formatString(1, 3,0,STR_PAD_LEFT);
            $data[$product_id]['brand'] = $this->formatString(7259, 5,0,STR_PAD_LEFT);
            $data[$product_id]['class'] = $this->formatString(99,4 ,0,STR_PAD_LEFT);
            $data[$product_id]['subclass'] = $this->formatString(99,4, 0,STR_PAD_LEFT);


            /*---------------------------------fudas data----------------------------------------------*/
            $dataFudas[$product_id]['record_type'] = 'FUDAS';
            $dataFudas[$product_id]['action_type'] = 'N';
            $dataFudas[$product_id]['UPC'] = $this->formatString($_product->getBarcode(), 13);
            $dataFudas[$product_id]['UDA_name'] = $this->formatString('web_enabled_uda', 16);
            $dataFudas[$product_id]['UDA_value'] = $this->formatString(1, 250);


        }

        return array("FITEM"=>$data,"FUDAS"=>$dataFudas);
    }




    public function getStock()
    {
        $readConnection = $this->readConnection();
        $attrbute_id = $this->data()->getAttributeId('brown_thomas_inventory');
        $barcode_attr_id=$this->data()->getAttributeId('barcode');

        $whr = 'WHERE cpe.type_id="simple" AND (cpv.attribute_id=' . $attrbute_id . ' AND cpv.value IS NOT NULL AND cpv.value >=0 ) AND (bar.attribute_id=' . $barcode_attr_id . ' AND bar.value IS NOT NULL)';
        $sql = 'SELECT cpv.entity_id from catalog_product_entity cpe JOIN catalog_product_entity_varchar cpv on cpv.entity_id=cpe.entity_id JOIN catalog_product_entity_varchar bar on bar.entity_id=cpe.entity_id ' . $whr;
        $products = $readConnection->fetchCol($sql);

        foreach ($products as $product_id) {
            $_product = Mage::getSingleton("catalog/product")->load($product_id);
            $data[$product_id]['BARCODE'] = $_product->getBarcode();
            $data[$product_id]['supplier'] = self::SUPPLIER;
            $data[$product_id]['Concession_Name'] = "VENUS BY MARIA TASH";
            $data[$product_id]['Business_Type'] = "CONCESSIONS";
            $data[$product_id]['Location_Name'] ="BROWN_THOMAS_DUBLIN";
            $data[$product_id]['Quantity'] =$this->formatString($_product->getBrownThomasInventory(),9,0,STR_PAD_LEFT);
            $data[$product_id]['blank1'] ="";
            $data[$product_id]['blank2'] ="";
            $data[$product_id]['blank3'] ="";

        }

        return $data;

    }
    public function getVendorColor($_product)
    {
        $some_attr_code = "metal";
        $attribute = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $some_attr_code);
        $optionId = '';
        if (!is_null($_product->getMetal()))
            $optionId = $_product->getMetal();

        $optionLabel = '';
        if (!empty($optionId))
            $optionLabel = $attribute->getFrontend()->getOption($optionId);

        $vendor_color=($optionLabel=="BLACK RHODIUM")? "BLACK GOLD" : $optionLabel;

        return $vendor_color;
    }

    public function splitSku($sku)
    {
         $skuAr=explode("|",$sku);
         $data['p_sku']=$skuAr[0];

        if(count($skuAr)>2)
        {
            $last=end($skuAr);
            if(1 === preg_match('~[0-9]~', $last)){
                #has numbers
                $data['p_size']=trim(str_replace("MM","",$last));
            }
            else
            {
                $data['p_size']=trim(str_replace("EAR","",$last));
            }

        }
        else
        {
            $data['p_size']="";
        }

        return $data;
    }

    public function getEnrichData()
    {
        $readConnection = $this->readConnection();
        $attrbute_id = $this->data()->getAttributeId('brown_thomas_inventory');
        $barcode_attr_id=$this->data()->getAttributeId('barcode');

        $whr = 'WHERE cpe.type_id="simple" AND (cpv.attribute_id=' . $attrbute_id . ' AND cpv.value IS NOT NULL AND cpv.value >=0 ) AND (bar.attribute_id=' . $barcode_attr_id . ' AND bar.value IS NOT NULL)';
        $sql = 'SELECT cpv.entity_id from catalog_product_entity cpe JOIN catalog_product_entity_varchar cpv on cpv.entity_id=cpe.entity_id JOIN catalog_product_entity_varchar bar on bar.entity_id=cpe.entity_id ' . $whr;

        $products = $readConnection->fetchCol($sql);
        $data = array();

        foreach ($products as $product_id) {
            $_product = Mage::getSingleton("catalog/product")->load($product_id);

            $formatedSKU = strtolower(preg_replace("/[^a-zA-Z0-9 ]+/", "", $_product->getSKU()));
            $data[$product_id]['WC_Product_ID'] = $this->formatString(SELF::DEPARTMENT . "x" . SELF::SUPPLIER . "x" . $formatedSKU, 45);
            $data[$product_id]['Barcode'] = $this->formatString($_product->getBarcode(), 13);
            $data[$product_id]['department'] = self::DEPARTMENT;
            $data[$product_id]['brand'] = 'Maria Tash';
            $data[$product_id]['VPN'] = $this->formatString($formatedSKU, 30);
            $data[$product_id]['color_shade'] = 'Multi';
            $data[$product_id]['color_family']='MULTI';
            $data[$product_id]['product_name']=$_product->getName();
            $data[$product_id]['copy']=$_product->getShortDescription();
            $data[$product_id]['copy']=$_product->getShortDescription();

        }

        return $data;
    }


}