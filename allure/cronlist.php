<?php 
require_once '../app/Mage.php';
umask(0);
Mage::app('admin');

//Allure Method to get cron jobs
function getCronJobs(){
	$cronJobs = Mage::app()->getConfig()->getNode('crontab/jobs');
	foreach ($cronJobs as $cronJob){
		$cronList = $cronJob;
		break;
	}
	return $cronList;
}

echo '
<html>
<head>
<title=Magento Cron List>
<style type="text/css">
html {
    width: 100%;
    font-family: Helvetica, Arial, sans-serif;
}
body {
    background-color:#00AEEF;
    color:#FFFFFF;
    line-height:1.0em;
    font-size: 125%;
}
b {
    color: #FFFFFF;
}
table{
    border-spacing: 1px;
    border-collapse: collapse;
}
th {
    text-align: center;
    font-size: 125%;
    font-weight: bold;
    padding: 5px;
    border: 2px solid #FFFFFF;
    background: #00AEEF;
    color: #FFFFFF;
}
td {
    text-align: left;
    padding: 4px;
    border: 2px solid #FFFFFF;
    color: #FFFFFF;
    background: #666;
}
</style>
</head>';

// DB info for user to see
echo '
<body>';

// Set up the table
echo "<table border='1' width='100%'>
        <thread>
        <tr>
        <th>Cron Job name</th>
           <th>Time</th>
           <th>Model Name</th>
           </tr>
           </thread>
           <tbody>";

// Display the data from the query
$cronList = getCronJobs();
foreach ($cronList as $name=>$cronJob){
	echo "<tr>";
	echo "<td>" . $name . "</td>";
	echo "<td>" . (string)$cronJob->schedule->cron_expr . "</td>";
	echo "<td>" . (string)$cronJob->run->model . "</td>";
	echo "</tr>";
}

// Close table and last few tags
echo "</tbody></table></body></html>";


?>