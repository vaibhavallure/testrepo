<?php
require_once '../app/Mage.php';
umask(0);

/*$function = $_GET['function'];
$password = $_GET['pass'];*/
Mage::app('admin');

require 'gapi.class.php';
define('ga_email','farooqbellard@allureinc.co');
define('ga_password','farooq123');
define('ga_profile_id','118301941875');


 
$ga = new gapi(ga_email,ga_password);
 
/* We are using the 'source' dimension and the 'visits' metrics */
$dimensions = array('source');
$metrics    = array('visits');
 
/* We will sort the result be desending order of visits, 
    and hence the '-' sign before the 'visits' string */
$ga->requestReportData(ga_profile_id, $dimensions, $metrics,'-visits');
 
$gaResults = $ga->getResults();
 
$i=1;
 
foreach($gaResults as $result)
{
    printf("%-4d %-40s %5d\n",
           $i++,
           $result->getSource(),
           $result->getVisits());
}
 
echo "\n-----------------------------------------\n";
echo "Total Results : {$ga->getTotalResults()}";    