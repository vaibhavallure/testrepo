<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');



$ioo = new Varien_Io_File();
$path = Mage::getBaseDir('var') . DS . 'teamwork' ;
$name = "harrods_plu";
$file = $path . DS . $name . '.csv';
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
    'site listings'=>'Site Listings','siteDelimited'=>'SiteDelimited','string_for_generic_lines'=>'String for Generic lines','gtin_1'=>'Gtin Number 1','gtin_2'=>'Gtin Number 2','gtin_3'=>'Gtin Number 3','gtin_4'=>'Gtin Number 4');



$ioo->streamWriteCsv($header);
$data = array();

$collection=Mage::getModel("catalog/product")->getCollection();
$collection->addAttributeToFilter('status', array('eq' => 1));
$collection->addAttributeToFilter('harrods_inventory', array('gt' => 0));
//$collection->addAttributeToFilter('type_id', array('eq' => 'simple'));
$collection->setOrder('sku', 'asc');

$some_attr_code = "metal";
$attribute = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $some_attr_code);

$NoOfProductQTYGreaterThanZero=0;
$NoOfProductQTYLessThanZero=0;
foreach ($collection as $_product){
    
    $_product=Mage::getModel("catalog/product")->load($_product->getId());


    $data=array();
    $optionId='';
    if(!is_null($_product->getMetal()))
        $optionId = $_product->getMetal();
    $optionLabel='';
    if(!empty($optionId))
        $optionLabel = $attribute->getFrontend()->getOption($optionId);
    
    if ($_product->getTypeId() == 'configurable') {
        $art_cat ='Generic';
    }else{
        $art_cat ='Single';
    }
    
    $skuConfig=$_product->getSku();
    $data['recid']=$_product->getSku();
    $data['description']=strtoupper(substr($_product->getName(),0,40));
    $data['purch_grp']='907';
    $data['bmc']='LW36900';
    $data['article_type']='ZDMC';
    $data['art_cat']=$art_cat; //Single for simple
    $data['size_matrix']='SIZE-LADIESWEAR';
    $data['GTIN_number']=$_product->getGtinNumber();   // Need to add later
    $data['cost']='0.00';
    $data['store_retail']=$_product->getHarrodsPrice();
    $data['airports_retail']='';
    $data['wholes_selling']='';
    $data['ctry_of_origi']='';
    $data['import_code']='';
    $data['tax_cls']='1'; //$_product->getTaxClassId()
    $data['seas_code']='0';
    $data['seas_year']='2018';
    $data['store']='TRUE';
    $data['airports']='FALSE';
    $data['wholesale']='FALSE';
    $data['consign']='FALSE';
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
    $data['harrods_colour']=str_replace("GOLD","",$optionLabel);  //COlor
    $data['pack_size']='';
    $data['prod_hierarchy']='FA';
    $data['contents']='';
    $data['content_unit']='';
    $data['vendor_colour']=$_product->getHarrodsColor();  //Color ROSE GOLd
    $data['order_units']='';
    $data['single_size']='';
    $data['total_cost']='';
    $data['var_tax_rate']='';
    $data['POS_description']=substr ( $skuConfig , 1,15 );  //Add COnfigurable SKU
    $data['direct_mail']='TRUE'; 
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

    if ($_product->getTypeId() == 'configurable') {
        $conf = Mage::getModel('catalog/product_type_configurable')->setProduct($_product);
        $simple_collection = $conf->getUsedProductCollection()->addAttributeToSelect('*')->addFilterByRequiredOptions();
        $indexCount = 1;
        foreach ($simple_collection as $k => $simpleProd){
            $simple_product=Mage::getModel("catalog/product")->load($simpleProd->getId());
            $gtin_index = 'gtin_'.$indexCount;
            $data[$gtin_index] = $simple_product->getGtinNumber();
            $indexCount++;
        }
    }
    $stockParent = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);

    /*Condition to Avoid Products that has less than or Equal to Zero Harrods QTY */

    /*if((float)$_product->getData("harrods_inventory")>0)
    {*/
        $ioo->streamWriteCsv($data);
        $NoOfProductQTYGreaterThanZero++;
    /*}
    else{
        $NoOfProductQTYLessThanZero++;
    }*/




    /*$conf = Mage::getModel('catalog/product_type_configurable')->setProduct($_product);
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
        $data['description']=strtoupper(substr($_product->getName(),0,40));
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
        $data['tax_cls']=$_product->getTaxClassId();
        $data['seas_code']='0';
        $data['seas_year']='2018';
        $data['store']='1';
        $data['airports']='0';
        $data['wholesale']='0';
        $data['consign']='0';
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
        $data['direct_mail']='1';
        $data['spare3']='';
        $data['spare4']='';
        $data['spare5']='';
        $data['backdate_article']='';
        $data['error_message']='';
        $data['record_status']='';
        $data['article_number']='';
        $data['site_listings']='D369';
        $data['siteDelimited']='SiteDelim';
        $data['string_for_generic_lines']='';*/




       /* if((float)$_product->getData("harrods_inventory")>0)
        {*/
            /*$ioo->streamWriteCsv($data);
            $NoOfProductQTYGreaterThanZero++;*/
       /* }
        else{
            $NoOfProductQTYLessThanZero++;
        }*/


    //}


}

echo "NO OF PRODUCTS QTY GREATER THAN ZERO = {$NoOfProductQTYGreaterThanZero} <BR> NO OF PRODUCTS QTY ZERO AND BLANK = {$NoOfProductQTYLessThanZero} <hr>";
die("Finished");

//die("Finish");