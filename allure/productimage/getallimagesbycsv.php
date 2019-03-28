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


$sku=null;

$all=null;

if (defined('STDIN')) {
    $sku = $argv[1];
} else {

    if(isset($_GET['sku']) && !empty($_GET['sku']))
        $sku=$_GET['sku'];
    else
        die("plz mention first letter of sku");

}

if($sku==null)
    die("plz mention first letter of sku");



$source  = Mage::getBaseDir('media'). DS . 'catalog' . DS . 'product';
$dest  = Mage::getBaseDir('media'). DS . 'catalog' . DS . 'product'; //Mage::getBaseDir('var') . DS . 'export' . DS . 'renamedImages';



$io = new Varien_Io_File();
if (!$io->fileExists($dest, false)) {
    $io->mkdir($dest);
}


$csvFile=Mage::getBaseDir('var') . DS . 'export'."/rename_images_".$sku.".csv";




if(!file_exists($csvFile))
      die("csv file not found");


try{


$csv = new Varien_File_Csv();
$data = $csv->getData($csvFile);


    if (defined('STDIN')) {
        if(trim($argv[2])=="all")
        {
            $lowerlimit = 1;
            $upperlimit = count($data);
        }
        else if($argv[2] && $argv[3])
        {
            $lowerlimit = $argv[2];
            $upperlimit = $argv[3];
        }
    } else {
        if (isset($_GET['all'])) {
            $lowerlimit = 1;
            $upperlimit = count($data);
        } else {
            $lowerlimit = (isset($_GET['lower'])) ? $_GET['lower'] : 0;
            $upperlimit = (isset($_GET['upper'])) ? $_GET['upper'] : 0;
        }
    }


for ($i=$lowerlimit;$i<=$upperlimit;$i++)
{
     $filesource=$source."".$data[$i]['3'];
    if(!file_exists($filesource))
    {
        Mage::log("COUNT=> " .$i." image not found => ".$filesource,Zend_Log::DEBUG,'copy_product_images.log',true);
          continue;
    }


    $pathArray=explode("/",$data[$i]['6']);
    $pathArray[count($pathArray)-1]="";
    $destPath=implode("/",$pathArray);
    $newFilePath=$dest.$destPath;
    if (!$io->fileExists($newFilePath, false)) {
        $io->mkdir($newFilePath);
    }



     $filedest=$dest."".$data[$i]['6'];


    if(file_exists($filedest))
    {
        Mage::log("COUNT=> " .$i." image already present => ".$filedest,Zend_Log::DEBUG,'copy_product_images.log',true);
        continue;
    }

    if (!copy($filesource, $filedest))
        Mage::log("COUNT=> " .$i. "image not copied => " . $filesource, Zend_Log::DEBUG, 'copy_product_images.log', true);


    Mage::log("COUNT=> " .$i, Zend_Log::DEBUG, 'copy_product_images.log', true);

}

}catch (Exception $e)
{
    echo $e->getMessage();
}

die("Done");

