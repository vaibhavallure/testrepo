<?php
class Allure_HarrodsInventory_Helper_Data extends Mage_Core_Helper_Abstract
{
    private function harrodsConfig() {
        return Mage::helper("harrodsinventory/config");
    }
    private function cron() {
        return Mage::helper("harrodsinventory/cron");
    }

    public function add_log($message) {
		if (!$this->harrodsConfig()->getDebugStatus()) {
            return;
    	}
        Mage::log($message,Zend_log::DEBUG,"harrods_files.log",true);
    }


    public function  charEncode($str)
    {
        if(!empty($str))
        return mb_convert_encoding($str,"Windows-1252","UTF-8");
    }

    public function generateReport()
    {

        try {


            $ioo = new Varien_Io_File();
            $path = Mage::getBaseDir('var') . DS . 'harrodsFiles';

            $date =date("Ymd", $this->cron()->getCurrentDatetime());
            $filenm="70000369_".$date."_PLU.txt";
            $file = $path . DS . $filenm;
            $ioo->setAllowCreateFolders(true);
            $ioo->open(array('path' => $path));
            $ioo->streamOpen($file, 'w+');
            $ioo->streamLock(true);


            $header =   array('recid' => $this->charEncode('MSS V2.10'),''=>'','1'=>'','2'=>'','3'=>'','4'=>'','5'=>'','GTIN_number'=>$this->charEncode('FALSE'));

            $headerStr="";$count=1;foreach ($header as $hd){$headerStr.=$hd;if($count<count($header)){$count++; $headerStr.="\t";}else{$headerStr.="\n";}}

            $ioo->streamWrite($headerStr);

            $data = array();

            $sr_no=1;

            $resource = Mage::getSingleton('core/resource');

            $readConnection = $resource->getConnection('core_read');

            $attribute_code = "harrods_inventory";
            $attribute_details = Mage::getSingleton("eav/config")->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attribute_code);

            $attribute = $attribute_details->getData();
            $attrbute_id = $attribute['attribute_id'];


            /*------------ get dates --------------------*/
            $from = date("Y-m-d H:i:s", strtotime('-24 hours', $this->cron()->getCurrentDatetime()));
            $to = date("Y-m-d H:i:s", $this->cron()->getCurrentDatetime());

            $diff=$this->cron()->getDiffUtc();
            $from = date("Y-m-d H:i:s", strtotime($diff, strtotime($from)));
            $to = date("Y-m-d H:i:s", strtotime($diff, strtotime($to)));
            /*-------------------------------------------*/

            $whr='WHERE cpe.type_id="simple" AND cpv.attribute_id=' . $attrbute_id . ' AND cpv.value IS NOT NULL AND cpv.value >0';

            if($this->harrodsConfig()->getContentTypePLU()=="update")
            $whr.=' AND (cpe.created_at >= "'.$from.'" AND cpe.created_at <= "'.$to.'")';


            $parentPro = $readConnection->fetchCol('SELECT cpv.entity_id from catalog_product_entity cpe JOIN catalog_product_entity_varchar cpv on cpv.entity_id=cpe.entity_id '.$whr);

            $some_attr_code = "metal";
            $attribute = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $some_attr_code);

            $harr_color = "harrods_color";
            $attributeHarrodsColor = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $harr_color);



            foreach ($parentPro as $parentProductId) {

                    $_product = Mage::getSingleton("catalog/product")->load($parentProductId);

                    //$this->add_log("inside simple product---------");
                    $optionId = '';

                    if (!is_null($_product->getMetal()))
                        $optionId = $_product->getMetal();

                    $optionLabel = '';

                    if (!empty($optionId))
                        $optionLabel = $attribute->getFrontend()->getOption($optionId);

                   /* $harrodsColorOptionsArray=array("White Gold"=>"White","Yellow Gold"=>"Gold","Black Gold"=>"Black","Rose Gold"=>"Rose Gold");

                    if($_product->getHarrodsColor())
                        $harrodsColor= $attributeHarrodsColor->getFrontend()->getOption($_product->getHarrodsColor());
                    else
                        $harrodsColor=$harrodsColorOptionsArray[$optionLabel];*/

                    $newHarrodsColorArray=array("YELLOW GOLD"=>"GOLD","WHITE GOLD"=>"WHITE","ROSE GOLD"=>"ROSE GOLD","BLACK RHODIUM"=>"BLACK","BLACK GOLD"=>"BLACK");

                    $splitsku=$this->splitSku($_product->getSku());

                    $data = array();
                    $skuConfig = $this->charEncode(explode("|",$_product->getSku())[0]);
                    $data['recid'] = $this->charEncode($sr_no); //$_product->getSku();
                    $data['description'] = str_replace(array(':', '-', '/', '*','"'), ' ', $this->charEncode(strtoupper(substr($_product->getName(), 0, 30))));
                    $data['purch_grp'] = $this->charEncode('907');
                    $data['bmc'] = $this->charEncode('LW36900');
                    $data['article_type'] = $this->charEncode('ZDMC');
                    $data['art_cat'] = $this->charEncode('Generic'); //Single for simple
                    $data['size_matrix'] = $this->charEncode('SIZE-LADIESWEAR');
                    $data['GTIN_number'] = ''; //$this->charEncode($_product->getGtinNumber());   // Need to add later
                    $data['cost'] = $this->charEncode('0.00');
                    $data['store_retail'] = $this->charEncode(number_format((float)$_product->getHarrodsPrice(), 2, '.', ''));
                    $data['airports_retail'] = '';
                    $data['wholes_selling'] = '';
                    $data['ctry_of_origi'] = 'US';
                    $data['import_code'] = '';
                    $data['tax_cls'] = $this->charEncode($_product->getTaxClassId() ? 1 : 0);
                    $data['seas_code'] = $this->charEncode('0000');
                    $data['seas_year'] = $this->charEncode('2018');
                    $data['store'] = $this->charEncode('TRUE');
                    $data['airports'] = $this->charEncode('FALSE');
                    $data['wholesale'] = $this->charEncode('FALSE');
                    $data['consign'] = $this->charEncode('FALSE');
                    $data['vendor'] = $this->charEncode('70000369'); //check with Todd
                    $data['vendor_subrange'] = $this->charEncode('CON');
                    $data['vendors_art_no'] = $splitsku['p_sku'];//substr($_product->getSku(), 0, 35);   //Config SKU
                    $data['tax_code'] = $this->charEncode('C0');
                    $data['brand'] = $this->charEncode('MARIA TASH');
                    $data['range'] = '';
                    $data['harrods_mainenance_structure'] = $this->charEncode('AC WOMENS EARRINGS');
                    $data['shape'] = '';
                    $data['design'] = '';
                    $data['comp'] = '';
                    $data['sport'] = '';
                    $data['gender'] = $this->charEncode('UNISEX');
                    $data['harrods_colour'] = $this->charEncode($newHarrodsColorArray[strtoupper($optionLabel)]);  //COlor  $harrodsColor
                    $data['pack_size'] = '';

                    $data['prod_hierarchy'] = $this->charEncode('BA');
                    $data['contents'] = '';
                    $data['content_unit'] = '';
                    $vendor_color=($optionLabel=="BLACK RHODIUM")? "BLACK GOLD" : $optionLabel;
                    $data['vendor_colour'] = $this->charEncode(strtoupper($vendor_color));  //Color ROSE GOLd
                    $data['order_units'] = '';
                    $data['single_size'] = '';
                    $data['total_cost'] = '';
                    $data['var_tax_rate'] = '';
                    $data['POS_description'] = substr($skuConfig, 0, 15);  //Add COnfigurable SKU
                    $data['direct_mail'] = $this->charEncode('TRUE');
                    $data['spare3'] = '';
                    $data['spare4'] = '';
                    $data['spare5'] = '';
                    $data['backdate_article'] = '';
                    $data['error_message'] = '';
                    $data['record_status'] = '';
                    $data['article_number'] = '';
                    $data['space_added'] = '';
                    $data['site_listings'] = $this->charEncode('D369');
                    $data['siteDelimited'] = $this->charEncode('SiteDelim');
//                    $data['string_for_generic_lines'] = '';

                if($splitsku['p_size'])
                $data['gtin'] = $this->charEncode("(".$splitsku['p_size'].";".$_product->getGtinNumber().";;;;)");
                else

                $data['gtin'] = $this->charEncode("(O/S;".$_product->getGtinNumber().";;;;)");
                

                $data['sizeDelimited'] = $this->charEncode('SizeDelim');


                $dataStr="";$count=1;foreach ($data as $dt){$dataStr.=$dt;if($count<count($data)){$count++; $dataStr.="\t";}else{$dataStr.="\n";}}

                $ioo->streamWrite($dataStr);

                $sr_no++;
                }


            $this->add_log("PLU.txt file generated");

            $date =date("Ymd", $this->cron()->getCurrentDatetime());
            $filenm="70000369_".$date."_PLU.OK";
            $file2 = $path . DS . $filenm;
            $ioo->streamOpen($file2, 'w+');
            $ioo->streamLock(true);
            $ioo->streamWrite(mb_convert_encoding(($sr_no-1),"ASCII","UTF-8"));

            $this->add_log("PLU.ok file generated");


            if(count($parentPro)) {
                $files['txt'] = $file;
                $files['ok'] = $file2;
                return $files;
            }
            else
            {
                $this->add_log("empty PLU file so return False");
                return false;
            }

        }catch (Exception $e)
        {
            $this->add_log($e->getMessage());
        }

        return false;
    }




    public function generateSTKReport()
    {

        try {

            $ioo = new Varien_Io_File();
            $path = Mage::getBaseDir('var') . DS . 'harrodsFiles';

            $date =date("Ymd", $this->cron()->getCurrentDatetime());
            $filenm="70000369_".$date."_STK.txt";
            $file = $path . DS . $filenm;
            $ioo->setAllowCreateFolders(true);
            $ioo->open(array('path' => $path));
            $ioo->streamOpen($file, 'w+');
            $ioo->streamLock(true);



            $data = array();

            $sr_no=1;

            $resource = Mage::getSingleton('core/resource');

            $readConnection = $resource->getConnection('core_read');

            $attribute_code = "harrods_inventory";
            $attribute_details = Mage::getSingleton("eav/config")->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attribute_code);

            $attribute = $attribute_details->getData();
            $attrbute_id = $attribute['attribute_id'];

            $parentPro = $readConnection->fetchCol('SELECT cpv.entity_id from catalog_product_entity cpe JOIN catalog_product_entity_varchar cpv on cpv.entity_id=cpe.entity_id WHERE cpe.type_id="simple" AND cpv.attribute_id=' . $attrbute_id . ' AND cpv.value IS NOT NULL AND cpv.value >=0');


            foreach ($parentPro as $parentProductId) {

                $_product = Mage::getSingleton("catalog/product")->load($parentProductId);

                $data = array();

                if($_product->getHarrodsInventory()=="0")
                {
                    if($this->lastStockZero($parentProductId))
                    {
                        continue;
                    }
                }else
                {
                    $this->removeStockZero($parentProductId);
                }

                $data['GTIN_number'] = $this->charEncode($_product->getGtinNumber());
                $data['harrods_inventory'] = $_product->getHarrodsInventory();
                $data['site_listings'] = $this->charEncode('D369');

                $ioo->streamWriteCsv($data,"\t");

                $sr_no++;
            }
            $this->add_log("STK.txt file generated");

            $date =date("Ymd", $this->cron()->getCurrentDatetime());
            $filenm="70000369_".$date."_STK.OK";
            $file2 = $path . DS . $filenm;
            $ioo->streamOpen($file2, 'w+');
            $ioo->streamLock(true);
            $ioo->streamWrite(mb_convert_encoding(($sr_no-1),"ASCII","UTF-8"));
            $this->add_log("STK.ok file generated");

            $files['txt']=$file;
            $files['ok']=$file2;

            return $files;

        } catch (Exception $e) {
            $this->add_log($e->getMessage());
        }
        return false;
    }

    public function generatePPCReport()
    {

        try {

            $ioo = new Varien_Io_File();
            $path = Mage::getBaseDir('var') . DS . 'harrodsFiles';

            $date =date("Ymd", $this->cron()->getCurrentDatetime());
            $filenm="70000369_".$date."_PPC.txt";
            $file = $path . DS . $filenm;
            $ioo->setAllowCreateFolders(true);
            $ioo->open(array('path' => $path));
            $ioo->streamOpen($file, 'w+');
            $ioo->streamLock(true);



            $data = array();

            $sr_no=1;

            $resource = Mage::getSingleton('core/resource');

            $readConnection = $resource->getConnection('core_read');

            $curruntDate = Mage::getModel('core/date')->gmtDate('Y-m-d');

            $date = new Zend_Date(Mage::getModel('core/date')->timestamp());
            $date->addDay('3');
            $activeDate= $date->toString('YYYYMMdd');



            $attribute_code = "harrods_inventory";
            $attribute_details = Mage::getSingleton("eav/config")->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attribute_code);

            $attribute = $attribute_details->getData();
            $attrbute_id = $attribute['attribute_id'];



            if($this->harrodsConfig()->getContentTypePPC()=="full") {
                $whr = 'WHERE cpe.type_id="simple" AND cpv.attribute_id=' . $attrbute_id . ' AND cpv.value IS NOT NULL AND cpv.value >0';
                $products = $readConnection->fetchCol('SELECT cpv.entity_id from catalog_product_entity cpe JOIN catalog_product_entity_varchar cpv on cpv.entity_id=cpe.entity_id ' . $whr);
            }else{
                $products = $readConnection->fetchCol("SELECT `productid` FROM `allure_harrodsinventory_price` WHERE `updated_date` LIKE '".$curruntDate."%'");

            }


            foreach ($products as $productId) {

                $_product = Mage::getSingleton("catalog/product")->load($productId);

                $data = array();

                $data['GTIN_number'] = $this->charEncode($_product->getGtinNumber());
                $data['harrods_price'] = $this->charEncode(number_format((float)$_product->getHarrodsPrice(), 2, '.', ''));
                $data['Active Date'] = $this->charEncode($activeDate);
                $data['End Date'] = $this->charEncode("99991231");

                $ioo->streamWriteCsv($data,"\t");

                $sr_no++;
            }
            $this->add_log("PPC.txt file generated");

            $date =date("Ymd", $this->cron()->getCurrentDatetime());
            $filenm="70000369_".$date."_PPC.OK";
            $file2 = $path . DS . $filenm;
            $ioo->streamOpen($file2, 'w+');
            $ioo->streamLock(true);
//            if($sr_no!=1)
            $ioo->streamWrite(mb_convert_encoding(($sr_no-1),"ASCII","UTF-8"));
            $this->add_log("PPC.ok file generated");

            $files['txt']=$file;
            $files['ok']=$file2;

            return $files;

        }catch (Exception $e)
        {
            $this->add_log($e->getMessage());
        }
        return false;
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
            $data['p_size']=false;
        }

        return $data;
    }

    public function lastStockZero($productid)
    {
        $resource = Mage::getSingleton('core/resource');

        $readConnection = $resource->getConnection('core_read');
        $writeAdapter = $resource->getConnection('core_write');


        $row= $readConnection->fetchCol("SELECT * FROM `allure_harrodsinventory_zero_stock` WHERE `productid`=".$productid);

        if(count($row))
        {
            return true;
        }
        else
        {
            $insertQuery = "INSERT INTO `allure_harrodsinventory_zero_stock`(`productid`, `stock`, `updated_date`) VALUES (".$productid.",0,(now()))";
            try {
                $writeAdapter->query($insertQuery);
                $writeAdapter->commit();
            }catch (Exception $e)
            {
                $this->add_log("lastStockZero=> Exception:".$e->getMessage());
            }
            return false;
        }
    }
    public function removeStockZero($product_id)
    {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $writeAdapter = $resource->getConnection('core_write');

        $row= $readConnection->fetchCol("SELECT * FROM `allure_harrodsinventory_zero_stock` WHERE `productid`=".$product_id);
         if(count($row))
         {
             $removeQuery = "DELETE FROM `allure_harrodsinventory_zero_stock` WHERE `productid`=".$product_id;
             try {
                 $writeAdapter->query($removeQuery);
                 $writeAdapter->commit();
             }catch (Exception $e)
             {
                 $this->add_log("removeStockZero=> Exception:".$e->getMessage());
             }
         }
    }


}
