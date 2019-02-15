<?php
class Allure_HarrodsInventory_Helper_Data extends Mage_Core_Helper_Abstract
{
    private function harrodsConfig() {
        return Mage::helper("harrodsinventory/config");
    }

    public function add_log($message) {
		if (!$this->harrodsConfig()->getDebugStatus()) {
            return;
    	}
        Mage::log($message,Zend_log::DEBUG,"update_harrods_inventory.log",true);
    }

    public function sendEmail()
    {
        if(!$this->harrodsConfig()->getModuleStatus()) {
            $this->add_log("Module Disabled----");
            return;
        }

        if ($this->harrodsConfig()->getEmailStatus()) {

            $templateId = $this->harrodsConfig()->getEmailTemplate();

            $mailTemplate = Mage::getModel('core/email_template');
            $storeId = Mage::app()->getStore()->getId();
            $senderName = $this->harrodsConfig()->getSenderName();
            $senderEmail = $this->harrodsConfig()->getSenderEmail();

            $sender = array('name' => $senderName, 'email' => $senderEmail);
            $recieverEmails = $this->harrodsConfig()->getEmailsGroup();
            $recieverNames = $this->harrodsConfig()->getEmailGroupNames();

            $recipientEmails = explode(',',$recieverEmails);
            $recipientNames = explode(',',$recieverNames);

            //$emailTemplateVariables['collection'] = $collection;
            $emailTemplateVariables['store_name'] = Mage::app()->getStore()->getName();
            $emailTemplateVariables['store_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);



            $files=$this->generateReport();
            $file=$files['txt'];

                if($file){
                    $date = Mage::getModel('core/date')->date('Y_m_d');
                    $name = "70000369_".$date.".".$this->harrodsConfig()->getFileType();
                    $mailTemplate->getMail()->createAttachment(
                        file_get_contents($file),
                        Zend_Mime::TYPE_OCTETSTREAM,
                        Zend_Mime::DISPOSITION_ATTACHMENT,
                        Zend_Mime::ENCODING_BASE64,
                        $name
                    );
                }


            try {
                $mailTemplate
                    ->sendTransactional(
                        $templateId,
                        $sender,
                        $recipientEmails, //here comes recipient emails
                        $recipientNames, // here comes recipient names
                        $emailTemplateVariables,
                        $storeId
                    );

                if (!$mailTemplate->getSentSuccess()) {
                      $this->add_log('mail sending failed');
                }
                else {
                        $this->add_log('mail sending done');
                }
            } catch(Exception $e) {
                $this->add_log('mail sending exception = > '.$e->getMessage());
            }
        }
    }

    public function  charEncode($str)
    {
        if(!empty($str))
        return mb_convert_encoding($str,"Windows-1252","UTF-8");
    }

    public function generateReport()
    {
        if (!$this->harrodsConfig()->getModuleStatus()) {
            $this->add_log("Module Disabled----");
            return;
        }

        try {

            $ioo = new Varien_Io_File();
            $path = Mage::getBaseDir('var') . DS . 'teamwork';

            $date = Mage::getModel('core/date')->date('Ymd');
            $filenm="70000369_".$date."_PLU.".$this->harrodsConfig()->getFileType();
            $file = $path . DS . $filenm;
            $ioo->setAllowCreateFolders(true);
            $ioo->open(array('path' => $path));
            $ioo->streamOpen($file, 'w+');
            $ioo->streamLock(true);

//            $header = array('recid' => 'RecID', 'description' => 'Description ', 'purch_grp' => 'Purch Grp', 'bmc' => 'BMC',
//                'article_type' => 'Article Type', 'art_cat' => 'Art. Cat.', 'size_matrix' => 'Size Matrix', 'GTIN_number' => 'GTIN number',
//                'cost' => 'Cost', 'store_retail' => 'Store Retail', 'airports_retail' => 'Airports Retail', 'wholes_selling' => 'Wholes. Selling',
//                'ctry_of_origi' => 'Ctry ofOrigi', 'import_code' => 'Import Code', 'tax_cls' => 'Tax Cls', 'seas_code' => 'Seas. Code',
//                'seas_year' => 'Seas. Year', 'store' => 'Store', 'airports' => 'Airports', 'wholesale' => 'Wholesale', 'consign' => 'Consign',
//                'vendor' => 'Vendor', 'vendor_subrange' => 'Vendor Subrange', 'vendors_art_no' => 'Vendors Art. No', 'tax_code' => 'Tax Code', 'brand' => 'Brand',
//                'Range' => 'range', 'harrods_mainenance_structure' => 'Harrods Mainenance Structure (Style)', 'shape' => 'Shape',
//                'design' => 'Design', 'comp' => 'Comp', 'sport' => 'Sport', 'gender' => 'Gender', 'harrods_colour' => 'Harrods Colour',
//                'pack_size' => 'Pack Size', 'prod_hierarchy' => 'Prod. Hierarchy', 'contents' => 'Contents', 'content_unit' => 'Content Unit',
//                'vendor_colour' => 'Vendor Colour', 'order_units' => 'Order Units', 'single_size' => 'Single Size', 'total_cost' => 'Total Cost',
//                'var_tax_rate' => 'Var Tax Rate', 'POS_description' => 'POS Description', 'direct_mail' => 'Direct Mail', 'spare3' => 'Spare3', 'spare4' => 'Spare4', 'spare5' => 'Spare5',
//                'backdate_article' => 'Backdate Article', 'error_message' => 'Error Message', 'Record Status', 'article_number' => 'Article Number',
//                'site listings' => 'Site Listings', 'siteDelimited' => 'SiteDelimited', 'string_for_generic_lines' => 'String for Generic lines','gtin_1'=>'Gtin Number 1','gtin_2'=>'Gtin Number 2','gtin_3'=>'Gtin Number 3','gtin_4'=>'Gtin Number 4','gtin_5'=>'Gtin Number 5','gtin_6'=>'Gtin Number 6','gtin_7'=>'Gtin Number 7','gtin_8'=>'Gtin Number 8','gtin_9'=>'Gtin Number 9','gtin_10'=>'Gtin Number 10','gtin_11'=>'Gtin Number 11','gtin_12'=>'Gtin Number 12','gtin_13'=>'Gtin Number 13','gtin_14'=>'Gtin Number 14','gtin_15'=>'Gtin Number 15');
//
//
//            $ioo->streamWriteCsv($header);

//            $header =   array('recid' => $this->charEncode('MSS V2.10'), 'description' => $this->charEncode('FALSE'), 'purch_grp' => '', 'bmc' => '',
//                'article_type' => '', 'art_cat' => '', 'size_matrix' => '', 'GTIN_number' => '','cost' => $this->charEncode('FALSE'));


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

            $parentPro = $readConnection->fetchCol('SELECT cpv.entity_id from catalog_product_entity cpe JOIN catalog_product_entity_varchar cpv on cpv.entity_id=cpe.entity_id WHERE cpe.type_id="simple" AND cpv.attribute_id=' . $attrbute_id . ' AND cpv.value IS NOT NULL AND cpv.value >0');

            $some_attr_code = "metal";
            $attribute = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $some_attr_code);

            $harr_color = "harrods_color";
            $attributeHarrodsColor = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $harr_color);



            foreach ($parentPro as $parentProductId) {

//                $this->add_log("inside config product---------");
//
//                $_product = Mage::getSingleton("catalog/product")->load($parentProductId);
//
//                $data = array();
//                $optionId = '';
//
//                if (!is_null($_product->getMetal()))
//                    $optionId = $_product->getMetal();
//                $optionLabel = '';
//
//                if (!empty($optionId))
//                    $optionLabel = $attribute->getFrontend()->getOption($optionId);
//
//                $skuConfig = $this->charEncode($_product->getSku());
//                $data['recid'] = $this->charEncode($sr_no); //$_product->getSku();
//                $data['description'] = $this->charEncode(strtoupper(substr($_product->getName(), 0, 30)));
//                $data['purch_grp'] = $this->charEncode('907');
//                $data['bmc'] = $this->charEncode('LW36900');
//                $data['article_type'] = $this->charEncode('ZDMC');
//                $data['art_cat'] = $this->charEncode('Generic'); //Single for simple
//                $data['size_matrix'] = $this->charEncode('SIZE-LADIESWEAR');
//                $data['GTIN_number'] = ''; //$_product->getGtinNumber();   // Need to add later
//                $data['cost'] = $this->charEncode('0.00');
//                $data['store_retail'] = $this->charEncode(number_format((float)$_product->getHarrodsPrice(), 2, '.', ''));
//                $data['airports_retail'] = '';
//                $data['wholes_selling'] = '';
//                $data['ctry_of_origi'] = '';
//                $data['import_code'] = '';
//                $data['tax_cls'] = $this->charEncode($_product->getTaxClassId() ? 1 : 0);
//                $data['seas_code'] = $this->charEncode('0000');
//                $data['seas_year'] = $this->charEncode('2018');
//                $data['store'] = $this->charEncode('TRUE');
//                $data['airports'] = $this->charEncode('FALSE');
//                $data['wholesale'] = $this->charEncode('FALSE');
//                $data['consign'] = $this->charEncode('FALSE');
//                $data['vendor'] = $this->charEncode('70000369'); //check with Todd
//                $data['vendor_subrange'] = $this->charEncode('CON');
//                $data['vendors_art_no'] = $skuConfig;   //Config SKU
//                $data['tax_code'] = $this->charEncode('C0');
//                $data['brand'] = $this->charEncode('MARIA TASH');
//                $data['range'] = '';
//                $data['harrods_mainenance_structure'] = $this->charEncode('AC WOMENS EARRINGS');
//                $data['shape'] = '';
//                $data['design'] = '';
//                $data['comp'] = '';
//                $data['sport'] = '';
//                $data['gender'] = $this->charEncode('UNISEX');
//                $data['harrods_colour'] = $this->charEncode(strtoupper(str_replace("GOLD", "", $optionLabel)));  //COlor
//                $data['pack_size'] = '';
//                $data['prod_hierarchy'] = $this->charEncode('FA');
//                $data['contents'] = '';
//                $data['content_unit'] = '';
//                $data['vendor_colour'] = $this->charEncode(strtoupper($_product->getHarrodsColor()));  //Color ROSE GOLd
//                $data['order_units'] = '';
//                $data['single_size'] = '';
//                $data['total_cost'] = '';
//                $data['var_tax_rate'] = '';
//                $data['POS_description'] = substr($skuConfig, 0, 15);  //Add COnfigurable SKU
//                $data['direct_mail'] = $this->charEncode('TRUE');
//                $data['spare3'] = '';
//                $data['spare4'] = '';
//                $data['spare5'] = '';
//                $data['backdate_article'] = '';
//                $data['error_message'] = '';
//                $data['record_status'] = '';
//                $data['article_number'] = '';
//                $data['site_listings'] = $this->charEncode('D369');
//                $data['siteDelimited'] = $this->charEncode('SiteDelim');
////                $data['string_for_generic_lines'] = '';
//
//
//
//                    $conf = Mage::getModel('catalog/product_type_configurable')->setProduct($_product);
//                    $simple_collection = $conf->getUsedProductCollection()->addAttributeToSelect('*')->addFilterByRequiredOptions();
//                    $indexCount = 1;
//                    foreach ($simple_collection as $k => $simpleProd){
//                        $simple_product=Mage::getSingleton("catalog/product")->load($simpleProd->getId());
//                        $gtin_index = 'gtin_'.$indexCount;
//                        $data[$gtin_index] = $this->charEncode("(ONE_SIZE;".$simple_product->getGtinNumber().";;;;)");
//                        $indexCount++;
//                    }
//                $data['siteDelimitedend'] = $this->charEncode('SiteDelim');
//
//                $ioo->streamWriteCsv($data,"\t");
//                $sr_no++;
//
//				//$conf = Mage::getModel('catalog/product_type_configurable')->setProduct($_product);
//				//$simple_collection = $conf->getUsedProductCollection()->addAttributeToSelect('*')->addFilterByRequiredOptions();
//
//                foreach ($simple_collection as $simpleProd) {

                    $_product = Mage::getSingleton("catalog/product")->load($parentProductId);

                    $this->add_log("inside simple product---------");
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
                    $data['vendor_colour'] = $this->charEncode(strtoupper($optionLabel));  //Color ROSE GOLd
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




//            }



            $date = Mage::getModel('core/date')->date('Ymd');
            $filenm="70000369_".$date."_PLU.OK";
            $file2 = $path . DS . $filenm;
            $ioo->streamOpen($file2, 'w+');
            $ioo->streamLock(true);
            $ioo->streamWrite(mb_convert_encoding(($sr_no-1),"ASCII","UTF-8"));





               $files['txt']=$file;
               $files['ok']=$file2;

            return $files;


        }catch (Exception $e)
        {
            $this->add_log($e->getMessage());
        }
    }




    public function generateSTKReport($type="txt")
    {
        if (!$this->harrodsConfig()->getModuleStatus()) {
            $this->add_log("Module Disabled----");
            return;
        }

        try {

            $ioo = new Varien_Io_File();
            $path = Mage::getBaseDir('var') . DS . 'teamwork';

            $date = Mage::getModel('core/date')->date('Ymd');
            $filenm="70000369_".$date."_STK.".$this->harrodsConfig()->getFileType();
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

                $data['GTIN_number'] = $this->charEncode($_product->getGtinNumber());
                $data['harrods_inventory'] = $this->charEncode($_product->getHarrodsInventory());
                $data['site_listings'] = $this->charEncode('D369');

                $ioo->streamWriteCsv($data,"\t");

                $sr_no++;
            }

            $date = Mage::getModel('core/date')->date('Ymd');
            $filenm="70000369_".$date."_STK.OK";
            $file2 = $path . DS . $filenm;
            $ioo->streamOpen($file2, 'w+');
            $ioo->streamLock(true);
            $ioo->streamWrite(mb_convert_encoding(($sr_no-1),"ASCII","UTF-8"));

            if($type=="OK")
                return $file2;
            else
                return $file;

        } catch (Exception $e) {
            $this->add_log($e->getMessage());
        }
    }

    public function generatePPCReport($type="txt")
    {
        if (!$this->harrodsConfig()->getModuleStatus()) {
            $this->add_log("Module Disabled----");
            return;
        }

        try {

            $ioo = new Varien_Io_File();
            $path = Mage::getBaseDir('var') . DS . 'teamwork';

            $date = Mage::getModel('core/date')->date('Ymd');
            $filenm="70000369_".$date."_PPC.".$this->harrodsConfig()->getFileType();
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

            $products = $readConnection->fetchCol("SELECT `productid` FROM `allure_harrodsinventory_price` WHERE `updated_date` LIKE '".$curruntDate."%'");


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

            $date = Mage::getModel('core/date')->date('Ymd');
            $filenm="70000369_".$date."_PPC.OK";
            $file2 = $path . DS . $filenm;
            $ioo->streamOpen($file2, 'w+');
            $ioo->streamLock(true);
//            if($sr_no!=1)
            $ioo->streamWrite(mb_convert_encoding(($sr_no-1),"ASCII","UTF-8"));

            if($type=="OK")
                return $file2;
            else
                return $file;

        }catch (Exception $e)
        {
            $this->add_log($e->getMessage());
        }
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


    public function getDiffTimezone()
    {

         /* -- utc and backend set timezone -- */

        $local_tz = new DateTimeZone('UTC');
        $local = new DateTime('now', $local_tz);


        $user_tz = new DateTimeZone($this->harrodsConfig()->getTimeZone());
        $user = new DateTime('now', $user_tz);

        $usersTime = new DateTime($user->format('Y-m-d H:i:s'));
        $localsTime = new DateTime($local->format('Y-m-d H:i:s'));
        $offset = $local_tz->getOffset($local) - $user_tz->getOffset($user);
        $interval = $usersTime->diff($localsTime);

        if($offset > 0)
            return  $diffZone=$interval->h .' hours'.' '. $interval->i .' minutes';
        else
            return  $diffZone= '-'.$interval->h .' hours'.' '. $interval->i .' minutes';

    }

    public function getCurrentDatetime()
    {
        $user_tz = new DateTimeZone($this->harrodsConfig()->getTimeZone());
        $user = new DateTime('now', $user_tz);
        $usersTime = new DateTime($user->format('Y-m-d H:i:s'));
        $ar=(array)$usersTime;
        $date = $ar['date'];
        return $date = strtotime($date);

    }

    public function getCurrentHour()
    {
       return date('H',  $this->getCurrentDatetime());
    }

}
