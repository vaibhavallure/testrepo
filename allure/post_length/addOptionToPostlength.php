<?php

require_once '../../app/Mage.php';

$_cate_id=$_GET['cid'];

if(empty($_cate_id))
{
    die("error");
}

umask(0);
Mage::app('default');


const OPTION="9.5MM";


$resource = Mage::getSingleton('core/resource');
$writeAdapter = $resource->getConnection('core_write');
$readConnection = $resource->getConnection('core_read');


$products = Mage::getModel('catalog/category')->load($_cate_id); //put your category id here
$productslist = $products->getProductCollection()->addAttributeToSelect('*');


$count=0;

addLog("Loop Started-----------cat_id=".$_cate_id);

$productIds=array();

foreach($productslist as $product)
{
    addLog("Product Id = ".$product->getId());

    $query="SELECT * FROM `catalog_product_option` po JOIN `catalog_product_option_title` pot ON po.option_id=pot.option_id WHERE `product_id` =".$product->getId();
    $results = $readConnection->fetchAll($query);


    foreach ($results as $rs)
    {
        if(trim(strtolower($rs['title']))=='post length')
        {
            addLog("post length found----");

            try
            {

                $query1="SELECT * FROM `catalog_product_option_type_value` potv JOIN `catalog_product_option_type_title` pott ON potv.option_type_id=pott.option_type_id WHERE potv.option_id = {$rs['option_id']} AND pott.title='".OPTION."'";
                $results1 = $readConnection->fetchAll($query1);


                if(!count($results1))
                {
                    addLog(OPTION." Not Found For This Product");


                    $writeAdapter->query('INSERT INTO `catalog_product_option_type_value`(`option_id`,`sort_order`) VALUES ('.$rs["option_id"].',5)');
                    $results2 = $readConnection->fetchAll('SELECT * FROM `catalog_product_option_type_value` WHERE option_id='.$rs['option_id'].' ORDER BY  option_type_id DESC ');
                    $option_type_id=current($results2)['option_type_id'];
                    addLog("new row created in catalog_product_option_type_value option_type_id=".$option_type_id);

                    $writeAdapter->query('INSERT INTO `catalog_product_option_type_title`(`option_type_id`, `store_id`, `title`) VALUES ('.$option_type_id.',0,"'.OPTION.'")');
                    addLog("new row created in catalog_product_option_type_title");

                    $writeAdapter->query('INSERT INTO `catalog_product_option_type_price`(`option_type_id`, `store_id`, `price`, `price_type`) VALUES ('.$option_type_id.',0,0,"fixed")');
                    addLog("new row created in catalog_product_option_type_price");
                 $count++;
                    $productIds[]=$product->getId();
                }


            }catch(Exception $e)
            {
                addLog($e->getMessage());
            }
        }
    }


//break;

}



addLog("Total Products = ".count($productslist)." Updated Products =".$count);

addLog("--------------------------------------------------------Updated product ids--------------");
addLog(implode(",",$productIds));


echo "DONE";

function addLog($data)
{
    Mage::log($data,Zend_Log::DEBUG,"add_option_to_postlength.log",true);
}




