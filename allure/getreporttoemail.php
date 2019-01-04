<?php
require_once('../app/Mage.php');
umask(0);
Mage::app();

if(isset($_GET['refund'])) {
    if(isset($_GET['date']))
        $date=$_GET['date'];
    else
    Mage::getModel('ecp_reporttoemail/refund')->sendReport();
}
else {
    $date = (isset($_GET['date'])) ? $_GET['date'] : NULL;
    $mail = (isset($_GET['sendemail'])) ? 1 : 0;
    $oldreport = (isset($_GET['oldreport'])) ? 1 : 0;

    if ($oldreport)
        Mage::getModel('ecp_reporttoemail/observer')->sendReportOld("manual");
    else
        Mage::getModel('ecp_reporttoemail/observer')->sendReportNew($date, $mail, "manual");
}
//Mage::getModel('ecp_reporttoemail/observer')->sendReport();

//echo "Done";

die("done");
