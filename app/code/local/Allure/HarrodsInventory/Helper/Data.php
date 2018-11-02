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

            $file=$this->generateReport();

            if ($file) {
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

    public function generateReport()
    {
        if (!$this->harrodsConfig()->getModuleStatus()) {
            $this->add_log("Module Disabled----");
            return;
        }

        try {
			$ioo = new Varien_Io_File();
			$path = Mage::getBaseDir('var') . DS . 'teamwork';

			$date = Mage::getModel('core/date')->date('Y_m_d');
			$filenm="70000369_".$date.".".$this->harrodsConfig()->getFileType();
			$file = $path . DS . $filenm;
			$ioo->setAllowCreateFolders(true);
			$ioo->open(array('path' => $path));
			$ioo->streamOpen($file, 'w+');
			$ioo->streamLock(true);

           // $header = array('recid' => 'RecID', 'description' => 'Description ', 'purch_grp' => 'Purch Grp', 'bmc' => 'BMC',
           //     'article_type' => 'Article Type', 'art_cat' => 'Art. Cat.', 'size_matrix' => 'Size Matrix', 'GTIN_number' => 'GTIN number',
           //     'cost' => 'Cost', 'store_retail' => 'Store Retail', 'airports_retail' => 'Airports Retail', 'wholes_selling' => 'Wholes. Selling',
           //     'ctry_of_origi' => 'Ctry ofOrigi', 'import_code' => 'Import Code', 'tax_cls' => 'Tax Cls', 'seas_code' => 'Seas. Code',
           //     'seas_year' => 'Seas. Year', 'store' => 'Store', 'airports' => 'Airports', 'wholesale' => 'Wholesale', 'consign' => 'Consign',
           //     'vendor' => 'Vendor', 'vendor_subrange' => 'Vendor Subrange', 'vendors_art_no' => 'Vendors Art. No', 'tax_code' => 'Tax Code', 'brand' => 'Brand',
           //     'Range' => 'range', 'harrods_mainenance_structure' => 'Harrods Mainenance Structure (Style)', 'shape' => 'Shape',
           //     'design' => 'Design', 'comp' => 'Comp', 'sport' => 'Sport', 'gender' => 'Gender', 'harrods_colour' => 'Harrods Colour',
           //     'pack_size' => 'Pack Size', 'prod_hierarchy' => 'Prod. Hierarchy', 'contents' => 'Contents', 'content_unit' => 'Content Unit',
           //     'vendor_colour' => 'Vendor Colour', 'order_units' => 'Order Units', 'single_size' => 'Single Size', 'total_cost' => 'Total Cost',
           //     'var_tax_rate' => 'Var Tax Rate', 'POS_description' => 'POS Description', 'direct_mail' => 'Direct Mail', 'spare3' => 'Spare3', 'spare4' => 'Spare4', 'spare5' => 'Spare5',
           //     'backdate_article' => 'Backdate Article', 'error_message' => 'Error Message', 'Record Status', 'article_number' => 'Article Number',
           //     'site listings' => 'Site Listings', 'siteDelimited' => 'SiteDelimited', 'string_for_generic_lines' => 'String for Generic lines','gtin_1'=>'Gtin Number 1','gtin_2'=>'Gtin Number 2','gtin_3'=>'Gtin Number 3','gtin_4'=>'Gtin Number 4','gtin_5'=>'Gtin Number 5','gtin_6'=>'Gtin Number 6','gtin_7'=>'Gtin Number 7','gtin_8'=>'Gtin Number 8','gtin_9'=>'Gtin Number 9','gtin_10'=>'Gtin Number 10','gtin_11'=>'Gtin Number 11','gtin_12'=>'Gtin Number 12','gtin_13'=>'Gtin Number 13','gtin_14'=>'Gtin Number 14','gtin_15'=>'Gtin Number 15');
		   //
		   //
           // $ioo->streamWriteCsv($header);

            $header =   array('recid' => 'MSS V2.10', 'description' => 'FALSE', 'purch_grp' => '', 'bmc' => '', 'article_type' => '', 'art_cat' => '', 'size_matrix' => '', 'GTIN_number' => '','cost' => 'FALSE');
            $ioo->streamWriteCsv($header,"\t");

            $data = array();

            $sr_no=1;

            $resource = Mage::getSingleton('core/resource');

            $readConnection = $resource->getConnection('core_read');

            $attribute_code = "harrods_inventory";
            $attribute_details = Mage::getSingleton("eav/config")->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attribute_code);

            $attribute = $attribute_details->getData();
            $attrbute_id = $attribute['attribute_id'];

            $parentPro = $readConnection->fetchCol('SELECT cpv.entity_id from catalog_product_entity cpe JOIN catalog_product_entity_varchar cpv on cpv.entity_id=cpe.entity_id WHERE cpe.type_id="configurable" AND cpv.attribute_id=' . $attrbute_id . ' AND cpv.value IS NOT NULL AND cpv.value >0');

            $some_attr_code = "metal";
            $attribute = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $some_attr_code);

            foreach ($parentPro as $parentProductId) {

                $this->add_log("inside config product---------");

                $_product = Mage::getSingleton("catalog/product")->load($parentProductId);

                $data = array();
                $optionId = '';

                if (!is_null($_product->getMetal()))
                    $optionId = $_product->getMetal();
                $optionLabel = '';

                if (!empty($optionId))
                    $optionLabel = $attribute->getFrontend()->getOption($optionId);

                $skuConfig = $_product->getSku();
                $data['recid'] = $sr_no; //$_product->getSku();
                $data['description'] = strtoupper(substr($_product->getName(), 0, 30));
                $data['purch_grp'] = '907';
                $data['bmc'] = 'LW36900';
                $data['article_type'] = 'ZDMC';
                $data['art_cat'] = 'Generic'; //Single for simple
                $data['size_matrix'] = 'SIZE-LADIESWEAR';
                $data['GTIN_number'] = ''; //$_product->getGtinNumber();   // Need to add later
                $data['cost'] = '0.00';
                $data['store_retail'] = number_format((float)$_product->getHarrodsPrice(), 2, '.', '');
                $data['airports_retail'] = '';
                $data['wholes_selling'] = '';
                $data['ctry_of_origi'] = '';
                $data['import_code'] = '';
                $data['tax_cls'] = $_product->getTaxClassId() ? 1 : 0;
                $data['seas_code'] = '0000';
                $data['seas_year'] = '2018';
                $data['store'] = 'TRUE';
                $data['airports'] = 'FALSE';
                $data['wholesale'] = 'FALSE';
                $data['consign'] = 'FALSE';
                $data['vendor'] = '70000369'; //check with Todd
                $data['vendor_subrange'] = 'CON';
                $data['vendors_art_no'] = $skuConfig;   //Config SKU
                $data['tax_code'] = 'C0';
                $data['brand'] = 'MARIA TASH';
                $data['range'] = '';
                $data['harrods_mainenance_structure'] = 'AC WOMENS EARRINGS';
                $data['shape'] = '';
                $data['design'] = '';
                $data['comp'] = '';
                $data['sport'] = '';
                $data['gender'] = 'UNISEX';
                $data['harrods_colour'] = strtoupper(str_replace("GOLD", "", $optionLabel));  //COlor
                $data['pack_size'] = '';
                $data['prod_hierarchy'] = 'FA';
                $data['contents'] = '';
                $data['content_unit'] = '';
                $data['vendor_colour'] = strtoupper($_product->getHarrodsColor());  //Color ROSE GOLd
                $data['order_units'] = '';
                $data['single_size'] = '';
                $data['total_cost'] = '';
                $data['var_tax_rate'] = '';
                $data['POS_description'] = substr($skuConfig, 0, 15);  //Add COnfigurable SKU
                $data['direct_mail'] = 'TRUE';
                $data['spare3'] = '';
                $data['spare4'] = '';
                $data['spare5'] = '';
                $data['backdate_article'] = '';
                $data['error_message'] = '';
                $data['record_status'] = '';
                $data['article_number'] = '';
                $data['site_listings'] = 'D369';
                $data['siteDelimited'] = 'SiteDelim';
				//$data['string_for_generic_lines'] = '';

                $conf = Mage::getModel('catalog/product_type_configurable')->setProduct($_product);
                $simple_collection = $conf->getUsedProductCollection()->addAttributeToSelect('*')->addFilterByRequiredOptions();
                $indexCount = 1;
                foreach ($simple_collection as $k => $simpleProd){
                    $simple_product=Mage::getSingleton("catalog/product")->load($simpleProd->getId());
                    $gtin_index = 'gtin_'.$indexCount;
                    $data[$gtin_index] = "(;".$simple_product->getGtinNumber().";;;;)";
                    $indexCount++;
                }

                $data['siteDelimitedend'] = 'SiteDelim';

                $ioo->streamWriteCsv($data,"\t");
                $sr_no++;

				//$conf = Mage::getModel('catalog/product_type_configurable')->setProduct($_product);
				//$simple_collection = $conf->getUsedProductCollection()->addAttributeToSelect('*')->addFilterByRequiredOptions();

                foreach ($simple_collection as $simpleProd) {
                    $_product = Mage::getSingleton("catalog/product")->load($simpleProd->getId());

                    $this->add_log("inside simple product---------");
                    $optionId = '';

                    if (!is_null($_product->getMetal()))
                        $optionId = $_product->getMetal();

                    $optionLabel = '';

                    if (!empty($optionId))
                        $optionLabel = $attribute->getFrontend()->getOption($optionId);

                    $data = array();

                    $data['recid'] = $sr_no; //$_product->getSku();
                    $data['description'] = strtoupper(substr($_product->getName(), 0, 30));
                    $data['purch_grp'] = '907';
                    $data['bmc'] = 'LW36900';
                    $data['article_type'] = 'ZDMC';
                    $data['art_cat'] = 'Single'; //Single for simple
                    $data['size_matrix'] = 'SIZE-LADIESWEAR';
                    $data['GTIN_number'] = $_product->getGtinNumber();   // Need to add later
                    $data['cost'] = '0.00';
                    $data['store_retail'] = number_format((float)$_product->getHarrodsPrice(), 2, '.', '');
                    $data['airports_retail'] = '';
                    $data['wholes_selling'] = '';
                    $data['ctry_of_origi'] = '';
                    $data['import_code'] = '';
                    $data['tax_cls'] = $_product->getTaxClassId() ? 1 : 0;
                    $data['seas_code'] = '0000';
                    $data['seas_year'] = '2018';
                    $data['store'] = 'TRUE';
                    $data['airports'] = 'FALSE';
                    $data['wholesale'] = 'FALSE';
                    $data['consign'] = 'FALSE';
                    $data['vendor'] = '70000369'; //check with Todd
                    $data['vendor_subrange'] = 'CON';
                    $data['vendors_art_no'] = $skuConfig;   //Config SKU
                    $data['tax_code'] = 'C0';
                    $data['brand'] = 'MARIA TASH';
                    $data['range'] = '';
                    $data['harrods_mainenance_structure'] = 'AC WOMENS EARRINGS';
                    $data['shape'] = '';
                    $data['design'] = '';
                    $data['comp'] = '';
                    $data['sport'] = '';
                    $data['gender'] = 'UNISEX';
                    $data['harrods_colour'] = strtoupper(str_replace("GOLD", " ", $optionLabel));  //COlor
                    $data['pack_size'] = '';

                    $data['prod_hierarchy'] = 'FA';
                    $data['contents'] = '';
                    $data['content_unit'] = '';
                    $data['vendor_colour'] = strtoupper($optionLabel);  //Color ROSE GOLd
                    $data['order_units'] = '';
                    $data['single_size'] = '';
                    $data['total_cost'] = '';
                    $data['var_tax_rate'] = '';
                    $data['POS_description'] = substr($skuConfig, 0, 15);  //Add COnfigurable SKU
                    $data['direct_mail'] = 'TRUE';
                    $data['spare3'] = '';
                    $data['spare4'] = '';
                    $data['spare5'] = '';
                    $data['backdate_article'] = '';
                    $data['error_message'] = '';
                    $data['record_status'] = '';
                    $data['article_number'] = '';
                    $data['site_listings'] = 'D369';
                    $data['siteDelimited'] = 'SiteDelim';
					//$data['string_for_generic_lines'] = '';

                    $ioo->streamWriteCsv($data,"\t");

                    $sr_no++;
                }
            };

            return $file;
        } catch (Exception $e) {
            $this->add_log($e->getMessage());
        }
    }
}
