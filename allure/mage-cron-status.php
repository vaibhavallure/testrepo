<?php
 
// Parse magento's local.xml to get db info, if local.xml is found

define('CONFIG_FILE', '../app/etc/local.xml');
 
if (file_exists(CONFIG_FILE)) {
 
$xml = simplexml_load_file(CONFIG_FILE);
 
$tblprefix = $xml->global->resources->db->table_prefix;
$dbhost = $xml->global->resources->default_setup->connection->host;
$dbuser = $xml->global->resources->default_setup->connection->username;
$dbpass = $xml->global->resources->default_setup->connection->password;
$dbname = $xml->global->resources->default_setup->connection->dbname;
 
}
 
else {
    exit('Failed to open app/etc/local.xml');
}
 
// DB Interaction
$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname) or die ('Error connecting to mysql');
 
$result = mysqli_query($conn, "SELECT * FROM " . $tblprefix . "cron_schedule ORDER BY scheduled_at DESC") or die (mysqli_error($conn));
 
 
// CSS for NexStyle
echo '
<html>
<head>
<title>Magento Cron Status</title>
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
echo '<body>';
 
// Set up the table
echo "<table border='1' width='100%'>
        <thead>
        <tr>
        <th>schedule_id</th>
           <th>job_code</th>
           <th>status</th>
           <th>messages</th>
           <th>created_at</th>
           <th>scheduled_at</th>
           <th>executed_at</th>
           <th>finished_at</th>
           </tr>
           </thead>
           <tbody>";
 
// Display the data from the query
while ($row = mysqli_fetch_array($result)) {
           echo "<tr>";
           echo "<td>" . $row['schedule_id'] . "</td>";
           echo "<td>" . $row['job_code'] . "</td>";
           echo "<td>" . $row['status'] . "</td>";
           echo "<td>" . $row['messages'] . "</td>";
           echo "<td>" . $row['created_at'] . "</td>";
           echo "<td>" . $row['scheduled_at'] . "</td>";
           echo "<td>" . $row['executed_at'] . "</td>";
           echo "<td>" . $row['finished_at'] . "</td>";
           echo "</tr>";
}
 
// Close table and last few tags
echo "</tbody></table></body></html>";
 
mysqli_close($conn);
?>