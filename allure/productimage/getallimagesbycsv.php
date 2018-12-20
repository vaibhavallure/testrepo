<?php
/**
 * Created by PhpStorm.
 * User: adityagatare
 * Date: 12/11/18
 * Time: 7:19 PM
 */



require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');




$source  = Mage::getBaseDir('media'). DS . 'catalog' . DS . 'product';
$dest  = Mage::getBaseDir('var') . DS . 'export' . DS . 'renamedImages';



$io = new Varien_Io_File();
if (!$io->fileExists($dest, false)) {
    $io->mkdir($dest);
}


$csvFile=Mage::getBaseDir('var') . DS . 'export'."/rename_images.csv";

$lowerlimit=(isset($_GET['lower']))?$_GET['lower']:0;
$upperlimit=(isset($_GET['upper']))?$_GET['upper']:0;

if(!file_exists($csvFile))
      die("csv file not found");


try{


$csv = new Varien_File_Csv();
$data = $csv->getData($csvFile);

for ($i=$lowerlimit;$i<=$upperlimit;$i++)
{
     $filesource=$source."".$data[$i]['3'];
    if(!file_exists($source))
    {
        Mage::log("image not found => ".$filesource,Zend_Log::DEBUG,'copy_product_images.log',true);
          continue;
    }

     $filedest=$dest."/".$data[$i]['6'];

    if(!copy($filesource, $filedest))
        Mage::log("image not copied => ".$filesource,Zend_Log::DEBUG,'copy_product_images.log',true);

}

}catch (Exception $e)
{
    echo $e->getMessage();
}

die("Done");

