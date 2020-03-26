<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');

var_dump(generatePPCReport());

function generatePPCReport()
{

    try {

        $ioo = new Varien_Io_File();
        $path = Mage::getBaseDir('var') . DS . 'harrodsFiles'.DS. 'all';

        $date =date("Ymd",now());
        $filenm="70000369_".$date."_PPC.txt";
        $file = $path . DS . $filenm;
        $ioo->setAllowCreateFolders(true);
        $ioo->open(array('path' => $path));
        $ioo->streamOpen($file, 'w+');
        $ioo->streamLock(true);


        $sr_no=1;


        $date = new Zend_Date(Mage::getModel('core/date')->timestamp());
        $date->addDay('3');
        $activeDate= $date->toString('YYYYMMdd');


        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToSelect('harrods_online_flag');

        $collection->addFieldToFilter(array(
            array('attribute'=>'harrods_online_flag','eq'=>'1'),
        ));

/*
        echo ($collection->getSelect()->__toString());
        die();*/

        foreach ($collection as $product) {

            $_product = Mage::getSingleton("catalog/product")->load($product->getId());

            if(!is_numeric($_product->getHarrodsInventory()))
            {
                continue;
            }

            $data = array();


            $data['GTIN_number'] = charEncode($_product->getBarcode());
            $data['harrods_price'] = charEncode(number_format((float)$_product->getHarrodsPrice(), 2, '.', ''));
            $data['Active Date'] = charEncode($activeDate);
            $data['End Date'] = charEncode("99991231");

            $ioo->streamWriteCsv($data,"\t");



            $sr_no++;
        }

        $date =date("Ymd", now());
        $filenm="70000369_".$date."_PPC.OK";
        $file2 = $path . DS . $filenm;
        $ioo->streamOpen($file2, 'w+');
        $ioo->streamLock(true);
//            if($sr_no!=1)
        $ioo->streamWrite(mb_convert_encoding(($sr_no-1),"ASCII","UTF-8"));

        $files['txt']=$file;
        $files['ok']=$file2;

        return $files;

    }catch (Exception $e)
    {
        echo $e->getMessage();
    }
    return false;
}
function  charEncode($str)
{
    if(!empty($str))
        return mb_convert_encoding($str,"Windows-1252","UTF-8");
}

die("Finished");
