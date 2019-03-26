
<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();



if(isset($_GET['stk']))
    $file=Mage::helper("brownthomas/data")->generateStockFile();
elseif(isset($_GET['enrich']))
  Mage::helper("brownthomas/data")->generateEnrichFile();
else
    $file=Mage::helper("brownthomas/data")->generateFoundationFile();




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

echo "DONE";


