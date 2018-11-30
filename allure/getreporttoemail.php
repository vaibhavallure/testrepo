<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();

if(isset($_GET['sendemail']))
Mage::getModel('ecp_reporttoemail/observer')->sendReport();
else
Mage::getModel('ecp_reporttoemail/observer')->sendReportNew();


//echo "Done";
die;
