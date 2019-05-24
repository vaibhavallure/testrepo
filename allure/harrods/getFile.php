<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();

Mage::helper("harrodsinventory/cron")->sendDailySales();


echo "done";

//Mage::helper("harrodsinventory/data")->generateReport();
//Mage::helper("harrodsinventory/data")->generateSTKReport();
//Mage::helper("harrodsinventory/data")->generatePPCReport();



echo "DONE";

























/*

echo $from = date("Y-m-d H:i:s", strtotime('-24 hours', Mage::helper("harrodsinventory/cron")->getCurrentDatetime()));
echo "<br>";
echo $to = date("Y-m-d H:i:s", Mage::helper("harrodsinventory/cron")->getCurrentDatetime());
echo "<br>";

echo $diff=Mage::helper("harrodsinventory/cron")->getDiffUtc();
echo "<br>";

echo $from = date("Y-m-d H:i:s", strtotime($diff, strtotime($from)));
echo "<br>";

echo $to = date("Y-m-d H:i:s", strtotime($diff, strtotime($to)));*/




die();

var_dump(Mage::helper("harrodsinventory/cron")->getMinute(Mage::helper("harrodsinventory/cron")->getCurrentDatetime()));


var_dump(Mage::helper("harrodsinventory/config")->getHourProductCron());





var_dump(Mage::helper("harrodsinventory/cron")->getHour(Mage::helper("harrodsinventory/cron")->getCurrentDatetime())==Mage::helper("harrodsinventory/config")->getHourProductCron());





die();








if(!Mage::helper("harrodsinventory/config")->getModuleStatus())
{
    echo "<h1 style='text-align: center;color: red'><strong>OOPS!!! </strong>Module Disabled, Enable From System Config</h1>";
    die();
}


$download=(isset($_GET['download']))?$_GET['download']:0;
$file=(isset($_GET['file']))?$_GET['file']:"STK";


if($download)
{

    if($file=="PPC")
        $file=Mage::helper("harrodsinventory")->generatePPCReport($download);
    else
        $file=Mage::helper("harrodsinventory")->generateSTKReport($download);


    if (file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }

    Mage::helper("harrodsinventory")->add_log("report generated and downloaded");

}

echo "DONE";
die;
