<?php

class Allure_BrownThomas_Model_Data
{

    const SUPPLIER = '506080';
    const DEPARTMENT = '6321';

    var $dataFudas=array();

    private function data() {
        return Mage::helper("brownthomas/data");
    }
    private function cron() {
        return Mage::helper("brownthomas/cron");
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

    public function getFITEM_FUDAS($products=null,$action_type)
    {

        $data = array();
        $dataFudas=array();

        foreach ($products as $product_id) {
            $_product = Mage::getSingleton("catalog/product")->load($product_id);
            $data[$product_id]['record_type'] = 'FITEM';
            $data[$product_id]['action_type'] = $action_type;
            $data[$product_id]['UPC'] = $this->formatString($_product->getBarcode(), 13);
            $data[$product_id]['UPC_TYPE'] = 'EAN13';
            $formatedSKU = $this->formatSKU($_product->getSKU());
            $data[$product_id]['WC_Product_ID'] = $this->formatString(SELF::DEPARTMENT . "x" . SELF::SUPPLIER . "x" . $formatedSKU, 45);
            $data[$product_id]['VPN'] = $this->formatString($_product->getSKU(), 30);
            $data[$product_id]['department'] = self::DEPARTMENT;
            $data[$product_id]['description'] = $this->formatString(str_replace(',', '',$_product->getName()), 40);
            $data[$product_id]['short_description'] = $this->formatString(str_replace(',', '',$_product->getName()), 20);
            $data[$product_id]['packing_method'] = $this->formatString("FLAT", 6);
            $data[$product_id]['unit_of_measure'] = $this->formatString("EA", 4);
            $data[$product_id]['size_group_1'] = $this->formatString("BT215", 10);
            $data[$product_id]['size_group_2'] = $this->formatString("", 10);
            $data[$product_id]['size_system'] = $this->formatString("5", 4);
            $splitsku=$this->splitSku($_product->getSku());
            $data[$product_id]['size_1'] = $this->formatString("1 SIZE", 10);
            $data[$product_id]['size_2'] = $this->formatString("", 10);
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
            $dataFudas[$product_id]['action_type'] = $action_type;
            $dataFudas[$product_id]['UPC'] = $this->formatString($_product->getBarcode(), 13);
            $dataFudas[$product_id]['UDA_name'] = $this->formatString('web_enabled_uda', 16);
            $dataFudas[$product_id]['UDA_value'] = $this->formatString(1, 250);

            $this->productUsed($product_id);
        }

        return array("FITEM"=>$data,"FUDAS"=>$dataFudas);
    }


    public function getStock()
    {
        $collection = Mage::getModel('brownthomas/product')->getCollection();
        $collection->getSelect()->where('created_date <= DATE_SUB(CURDATE(),INTERVAL 14 day)');
        $data = array();
        foreach ($collection as $products) {
            $product_id=$products->getProductId();
            $_product = Mage::getSingleton("catalog/product")->load($product_id);
            $data[$product_id]['BARCODE'] = $_product->getBarcode();
            $data[$product_id]['supplier'] = self::SUPPLIER;
            $data[$product_id]['Concession_Name'] = "VENUS BY MARIA TASH LIMITED";
            $data[$product_id]['Business_Type'] = "CONCESSION";
            $data[$product_id]['Location_Name'] ="BROWN_THOMAS_DUBLIN";

            /*check if negative inventory and change to zero*/
            $inv=$_product->getBrownThomasInventory();
            if((float)$inv<0)
                $inv=0;

            $data[$product_id]['Quantity'] =$this->formatString($inv,9,0,STR_PAD_LEFT);
            $data[$product_id]['blank1'] ="";
            $data[$product_id]['blank2'] ="";
            $data[$product_id]['blank3'] ="";
            $data[$product_id]['blank4'] ="";
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

    public function getEnrichData($products)
    {
        $data = array();

        foreach ($products as $product_id) {
            $_product = Mage::getSingleton("catalog/product")->load($product_id);

            $formatedSKU = strtolower(preg_replace("/[^a-zA-Z0-9 ]+/", "", $_product->getSKU()));
//            $data[$product_id]['WC_Product_ID'] = $this->formatString(SELF::DEPARTMENT . "x" . SELF::SUPPLIER . "x" . $formatedSKU, 45);
            $data[$product_id]['Barcode'] = $this->formatString($_product->getBarcode(), 13);
            $data[$product_id]['department'] = self::DEPARTMENT;
            $data[$product_id]['brand'] = 'Maria Tash';
//            $data[$product_id]['VPN'] = $this->formatString($formatedSKU, 30);
            $data[$product_id]['color_shade'] = $this->formatString(strtoupper($this->getVendorColor($_product)), 24);;
            $data[$product_id]['color_family']='GOLD';
            $data[$product_id]['product_name']=$_product->getName();
            $data[$product_id]['copy']=$_product->getShortDescription();
            $data[$product_id]['product_detail']="";
            $data[$product_id]['product_type']=$this->getCategoryName($_product->getId());

        }

        return $data;
    }
    public function getEnrichTitles()
    {
        return array(
//            1=>"WCID",
            2=>"Barcode",
            3=>"Department",
            4=>"Brand",
//            5=>"VPN",
            6=>"Colour Shade",
            7=>"Colour Family",
            8=>"Product Name",
            9=>"Copy",
            10=>"Product Details",
            11=>"Product Type"
        );
    }

    public function formatSKU($sku)
    {
        $searchVal = array("yellow","rose","black","white","rhodium","gold");
        $replaceVal = array("y","r","b","w","r","g");

        $newsku =strtolower($sku);
        $newsku = str_replace($searchVal, $replaceVal,$newsku);
        $formattedSKU=preg_replace("/[^a-zA-Z0-9]+/", "", $newsku);

        return $formattedSKU;
    }

    public function getPriceData($products=null,$action_type)
    {
        $priceData = array();
        $index = 0;
        foreach ($products as $product)
        {
            $_product = Mage::getSingleton("catalog/product")->load($product);
            $priceData[$index]['record_type'] = $this->formatString('FPCHG',5);
            $priceData[$index]['action_type'] = $this->formatString($action_type,1);
            $priceData[$index]['primary_upc'] = $this->formatString($_product->getBarcode(), 13);
            $priceData[$index]['effective_date'] =$this->formatString(date('Ymd',$this->cron()->getCurrentDatetime()), 13);
            $priceData[$index]['unit_retail'] = $this->formatString(number_format((float)$_product->getDublinPrice(),2,'.',''),21,0, STR_PAD_LEFT);
            $priceData[$index]['clearance_indicator'] = $this->formatString('N',1);
            $productDetails['last_sent_date']=$_product->getUpdatedAt();

            $index++;
        }

        return $priceData;
    }
    public function add_log($message) {
        Mage::helper("brownthomas/data")->add_log($message);
    }

    public function getNewProducts()
    {
        $_products = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToFilter(array(array('attribute' => 'brown_thomas_online', 'eq' => 1)))
            ->addAttributeToFilter(array(array('attribute' => 'type_id', 'eq' => 'simple')))
            ->addAttributeToFilter(array(array('attribute' => 'status', 'eq' => 1)))
            ->addAttributeToFilter(array(array('attribute' => 'barcode', 'neq' => 'NULL')))
            ->addAttributeToFilter(array(array('attribute' => 'brown_thomas_inventory', 'neq' => 'NULL')));

        $_products->getSelect()->joinLeft(array('abp' => 'allure_brownthomas_product'), 'abp.product_id = e.entity_id');
        $_products->getSelect()->where("abp.row_id IS NULL");

        return $_products->getAllIds();
    }
    public function getUpdatedProducts()
    {
        $data=array();
        $_products = Mage::getModel('brownthomas/product')->getCollection();
        $_products->getSelect()->where("updated_date>last_sent_date");
        foreach($_products as $prod)
        {
            $data[]=$prod->getProductId();
        }
        return $data;

    }

    public function getCategoryName($product_id)
    {
        $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product_id);
        $parentProduct=Mage::getSingleton("catalog/product")->load(current($parentIds));
        $categoryIds = $parentProduct->getCategoryIds();
        $category=Mage::getModel('catalog/category')->load(current($categoryIds));
        return $this->checkForEarrings($category->getName());
    }

    public function checkForEarrings($cat_name)
    {
        $EaringsArr=array("Earrings","Ear Cartilage","Helix Jewelry","Tragus Jewelry","Tash Rook Jewelry","Ear Head Jewelry","Conch Jewelry","Rook Jewelry","Daith Jewelry","Large Earrings");

        if(in_array("$cat_name",$EaringsArr))
            return "Earrings";
        else
            return $cat_name;
    }

    public function productUsed($product_id)
    {
        $model = Mage::getModel('brownthomas/product');
        $product=$model->load($product_id, 'product_id');

        try {
            if ($product->getRowId()) {
                $data['last_sent_date'] = Varien_Date::now();
                $product->addData($data)->save();
            } else {
                $data['product_id'] = $product_id;
                $data['last_sent_date'] = Varien_Date::now();
                $model->setData($data)->save();
            }
        }
        catch (Exception $e)
        {
            $this->add_log("Exceptions:". $e->getMessage());
        }
    }

    public function fileTransferred($file)
    {
        try {
            $model = Mage::getModel('brownthomas/filetransfer');
            $model->setFile($file);
            $model->save();
        }catch (Exception $e)
        {
            $this->add_log("Exception=>".$e->getMessage());
        }

    }


}