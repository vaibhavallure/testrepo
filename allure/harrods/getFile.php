<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();

if(!Mage::helper("harrodsinventory/config")->getModuleStatus())
{

    echo "<h1 style='text-align: center;color: red'><strong>OOPS!!! </strong>Module Disabled, Enable From System Config</h1>";
    die();
}


$download=(isset($_GET['download']))?$_GET['download']:0;

if($download)
{

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
