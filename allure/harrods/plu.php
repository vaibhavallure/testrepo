<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');



$ioo = new Varien_Io_File();
$path = Mage::getBaseDir('var') . DS . 'teamwork' ;
$name = "70000369_20180806_PLU";
$file = $path . DS . $name . '.txt';
$ioo->setAllowCreateFolders(true);
$ioo->open(array('path' => $path));
$ioo->streamOpen($file, 'w+');
$ioo->streamLock(true);

$header=array('recid'=>'RecID','description'=> 'Description ', 'purch_grp'=>'Purch Grp', 'bmc'=>'BMC',
    'article_type'=> 'Article Type','art_cat'=>'Art. Cat.','size_matrix'=>'Size Matrix','GTIN_number'=>'GTIN number',
    'cost'=>'Cost','store_retail'=>'Store Retail','airports_retail'=>'Airports Retail','wholes_selling'=>'Wholes. Selling',
    'ctry_of_origi'=>'Ctry ofOrigi','import_code'=>'Import Code','tax_cls'=>'Tax Cls','seas_code'=>'Seas. Code',
    'seas_year'=>'Seas. Year','store'=>'Store','airports'=>'Airports','wholesale'=>'Wholesale','consign'=>'Consign',
    'vendor'=>'Vendor','vendor_subrange'=>'Vendor Subrange','vendors_art_no'=>'Vendors Art. No','tax_code'=>'Tax Code','brand'=>'Brand',
    'Range'=>'range','harrods_mainenance_structure'=>'Harrods Mainenance Structure (Style)','shape'=>'Shape',
    'design'=>'Design','comp'=>'Comp','sport'=>'Sport','gender'=>'Gender','harrods_colour'=>'Harrods Colour',
    'pack_size'=>'Pack Size','prod_hierarchy'=>'Prod. Hierarchy','contents'=>'Contents','content_unit'=>'Content Unit',
    'vendor_colour'=>'Vendor Colour','order_units'=>'Order Units','single_size'=>'Single Size','total_cost'=>'Total Cost',
    'var_tax_rate'=>'Var Tax Rate','POS_description'=>'POS Description','direct_mail'=>'Direct Mail','spare3'=>'Spare3','spare4'=>'Spare4','spare5'=>'Spare5',
    'backdate_article'=>'Backdate Article','error_message'=>'Error Message','Record Status','article_number'=>'Article Number',
    'site listings'=>'Site Listings','siteDelimited'=>'SiteDelimited','string_for_generic_lines'=>'String for Generic lines');



$ioo->streamWriteCsv($header);
$data = array();

$collection=Mage::getModel("catalog/product")->getCollection();
$collection->addAttributeToFilter('status', array('eq' => 1));
$collection->addAttributeToFilter('type_id', array('eq' => 'configurable'));
$collection->setOrder('sku', 'asc');

$some_attr_code = "metal";
$attribute = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $some_attr_code);

foreach ($collection as $_product){
    
    $_product=Mage::getModel("catalog/product")->load($_product->getId());
    $data=array();
    $optionId='';
    if(!is_null($_product->getMetal()))
        $optionId = $_product->getMetal();
    $optionLabel='';
    if(!empty($optionId))
        $optionLabel = $attribute->getFrontend()->getOption($optionId);
    
    $skuConfig=$_product->getSku();
    $data['recid']=$_product->getSku();
    $data['description']=strtoupper($_product->getName());
    $data['purch_grp']='907';
    $data['bmc']='LW36900';
    $data['article_type']='ZDMC';
    $data['art_cat']='Generic'; //Single for simple
    $data['size_matrix']='SIZE-LADIESWEAR';
    $data['GTIN_number']=$_product->getGtinNumber();   // Need to add later
    $data['cost']='0.00';
    $data['store_retail']=$_product->getHarrodsPrice();
    $data['airports_retail']='';
    $data['wholes_selling']='';
    $data['ctry_of_origi']='';
    $data['import_code']='';
    $data['tax_cls']=$_product->getTaxClassId()?1:0;
    $data['seas_code']='0';
    $data['seas_year']='2018';
    $data['store']=TRUE;
    $data['airports']=FALSE;
    $data['wholesale']=FALSE;
    $data['consign']=FALSE;
    $data['vendor']='70000369'; //check with Todd
    $data['vendor_subrange']='CON';
    $data['vendors_art_no']=$skuConfig;   //Config SKU 
    $data['tax_code']='C0';
    $data['brand']='MARIA TASH';
    $data['range']='';
    $data['harrods_mainenance_structure']='AC WOMENS EARRINGS';
    $data['shape']='';
    $data['design']='';
    $data['comp']='';
    $data['sport']='';
    $data['gender']='UNISEX';
    $data['harrods_colour']=str_replace("GOLD","",$optionLabel);;  //COlor
    $data['pack_size']='';
    $data['prod_hierarchy']='FA';
    $data['contents']='';
    $data['content_unit']='';
    $data['vendor_colour']=$optionLabel;  //Color ROSE GOLd
    $data['order_units']='';
    $data['single_size']='';
    $data['total_cost']='';
    $data['var_tax_rate']='';
    $data['POS_description']=$skuConfig;  //Add COnfigurable SKU
    $data['direct_mail']=TRUE; 
    $data['spare3']=''; 
    $data['spare4']=''; 
    $data['spare5']=''; 
    $data['backdate_article']=''; 
    $data['error_message']='';
    $data['record_status']='';
    $data['article_number']='';
    $data['site_listings']='D369';
    $data['siteDelimited']='SiteDelim';
    $data['string_for_generic_lines']='';
    
    $ioo->streamWriteCsv($data);
    
    
    
    
    
    
    $conf = Mage::getModel('catalog/product_type_configurable')->setProduct($_product);
    $simple_collection = $conf->getUsedProductCollection()->addAttributeToSelect('*')->addFilterByRequiredOptions();
    foreach ($simple_collection as $simpleProd){
        $_product=Mage::getModel("catalog/product")->load($simpleProd->getId());
        
       
        $optionId='';
        if(!is_null($_product->getMetal()))
            $optionId = $_product->getMetal();
        $optionLabel='';
        if(!empty($optionId))
            $optionLabel = $attribute->getFrontend()->getOption($optionId);
        $data=array();
       
        $data['recid']=$_product->getSku();
        $data['description']=strtoupper($_product->getName());
        $data['purch_grp']='907';
        $data['bmc']='LW36900';
        $data['article_type']='ZDMC';
        $data['art_cat']='Single'; //Single for simple
        $data['size_matrix']='SIZE-LADIESWEAR';
        $data['GTIN_number']=$_product->getGtinNumber();   // Need to add later
        $data['cost']='0.00';
        $data['store_retail']=$_product->getHarrodsPrice();
        $data['airports_retail']='';
        $data['wholes_selling']='';
        $data['ctry_of_origi']='';
        $data['import_code']='';
        $data['tax_cls']=$_product->getTaxClassId()?1:0;
        $data['seas_code']='0';
        $data['seas_year']='2018';
        $data['store']=TRUE;
        $data['airports']=FALSE;
        $data['wholesale']=FALSE;
        $data['consign']=FALSE;
        $data['vendor']='70000369'; //check with Todd
        $data['vendor_subrange']='CON';
        $data['vendors_art_no']=$skuConfig;   //Config SKU
        $data['tax_code']='C0';
        $data['brand']='MARIA TASH';
        $data['range']='';
        $data['harrods_mainenance_structure']='AC WOMENS EARRINGS';
        $data['shape']='';
        $data['design']='';
        $data['comp']='';
        $data['sport']='';
        $data['gender']='UNISEX';
        $data['harrods_colour']=str_replace("GOLD"," ",$optionLabel);;  //COlor
        $data['pack_size']='';
        
        $data['prod_hierarchy']='FA';
        $data['contents']='';
        $data['content_unit']='';
        $data['vendor_colour']=$optionLabel;  //Color ROSE GOLd
        $data['order_units']='';
        $data['single_size']='';
        $data['total_cost']='';
        $data['var_tax_rate']='';
        $data['POS_description']=$skuConfig;  //Add COnfigurable SKU
        $data['direct_mail']=TRUE;
        $data['spare3']='';
        $data['spare4']='';
        $data['spare5']='';
        $data['backdate_article']='';
        $data['error_message']='';
        $data['record_status']='';
        $data['article_number']='';
        $data['site_listings']='D369';
        $data['siteDelimited']='SiteDelim';
        $data['string_for_generic_lines']='';
        
        $ioo->streamWriteCsv($data);
        
    }
    
   
}

die("Finished");

//die("Finish");
