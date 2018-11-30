<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();

if(!Mage::helper("harrodsinventory/config")->getModuleStatus())
{

    echo "<h1 style='text-align: center;color: red'><strong>OOPS!!! </strong>Module Disabled, Enable From System Config</h1>";
    die();
}



 $update=(isset($_GET['update']))?1:0;
 $email=(isset($_GET['email']))?1:0;
 $download=(isset($_GET['download']))?$_GET['download']:0;

if(!$download)
echo "<p style='color: blueviolet'>to update use <strong>runUpdate.php?update</strong>, to update+download use <strong>runUpdate.php?update&download</strong> to update+download+email use  <strong>runUpdate.php?update&download&email</strong></p><hr>";



Mage::helper("harrodsinventory")->add_log("Manual call to update inventory function");


if($update)
{
    Mage::getModel('harrodsinventory/cron')->updateHarrodsInventory();
    Mage::helper("harrodsinventory")->add_log("Harrods Inventory Updated");

}

if($email)
{
    Mage::helper("harrodsinventory")->sendEmail();
    Mage::helper("harrodsinventory")->add_log("Harrods Inventory Emailed");

}
if($download)
{

    $files=Mage::helper("harrodsinventory")->generateReport();



    //$csv = iconv("UTF-8", "Windows-1252", $csv);


    if (file_exists($files[$download])) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($files[$download]) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($files[$download]));
        readfile($files[$download]);
       exit;
    }





    Mage::helper("harrodsinventory")->add_log("report generated and downloaded");

}

echo "DONE";
die;
