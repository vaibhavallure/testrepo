<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();




$date=(isset($_GET['date']))? $_GET['date'] : NULL;



if(isset($_GET['sendemail']))
Mage::getModel('ecp_reporttoemail/observer')->sendReport();
else
Mage::getModel('ecp_reporttoemail/observer')->sendReportNew($date);


//echo "Done";
die;
