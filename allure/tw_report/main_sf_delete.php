<?php
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
    header('Access-Control-Allow-Headers: token, Content-Type');
    header('Access-Control-Max-Age: 1728000');
    header('Content-Length: 0');
    die();
  }

  header('Access-Control-Allow-Origin: *');
  header('Content-Type: *');

$fileName = $_GET['filename'];
//var_dump($fileName);die;

//$dir = Mage::getBaseDir("var") . DS ."tw-mag-report/";
$dir = "";

const BASE_URL = "https://login.salesforce.com";
const OAUTH_URL = "/services/oauth2/token?";
const QUERY_URL = "/services/data/v43.0/query?q=SELECT+Id,Title+FROM+ContentDocument+WHERE+";


$filterIncrementIds = rtrim(buildIncrentIdForQuery($dir.$fileName), ',');
//var_dump($filterIncrementIds);
//echo var_dump(refreshToken());die;
$responseArr = refreshToken();

$date = basename($fileName, '.csv');

$queryUrl = QUERY_URL . $filterIncrementIds;
//var_dump($queryUrl);die;
$res = sendRequest($queryUrl, "GET", null, $responseArr);
//var_dump($res);die;
$sfFilename = $date . '-sf' . '.csv';
$fp = fopen($dir.$sfFilename, 'w');

foreach ($res['records'] as $r) {
    //echo '<pre>';
    //print_r($r);
    $date = new DateTime(substr($r['Created_At__c'], 0, sizeof($r['Created_At__c']) - 9));
    $date->setTimezone(new DateTimeZone('America/New_York')); // +04
    $convertedDate = $date->format('Y-m-d H:i:s');
    fwrite($fp, $convertedDate . "," . $r['Increment_Id__c'] . "," . $r['Grant_Total__c'] . PHP_EOL);
}

fclose($fp);
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . basename($sfFilename) . "\"");
readfile($dir.$sfFilename);

/**
 * make api request call to salesforce through curl
 * @param - urlPath - contains which api object call
 * @param - requestMethod - contains GET|POST|DELETE|PUT|PATCH|OPTIONS|HEAD
 * @param - requestArgs - contains input parameters of request
 */
function sendRequest($urlPath, $requestMethod = "GET", $requestArgs, $responseArr)
{
    if ($responseArr["access_token"]) {
        $oauthToken = $responseArr["access_token"];
        $instaceUrl = $responseArr["instance_url"];
    }

    if ($oauthToken && $instaceUrl) {
        $requestURL = $instaceUrl . $urlPath;
//            var_dump($requestURL);
        $sendRequest = curl_init($requestURL);
        curl_setopt($sendRequest, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($sendRequest, CURLOPT_HEADER, false);
        curl_setopt($sendRequest, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($sendRequest, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($sendRequest, CURLOPT_CUSTOMREQUEST, $requestMethod);
        curl_setopt($sendRequest, CURLOPT_FOLLOWLOCATION, 0);

        curl_setopt($sendRequest, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer {$oauthToken}"
        ));

        // convert requestArgs to json
        if ($requestArgs != null) {
            $json_arguments = json_encode($requestArgs);
            curl_setopt($sendRequest, CURLOPT_POSTFIELDS, $json_arguments);
        }
        // execute sendRequest
        $response = curl_exec($sendRequest);
        $responseArr = json_decode($response, true);
        //$this->salesforceLog("count = ".$this->_retry_count);
        return $responseArr;
    }
    return json_encode(array("success" => false, "message" => "Unkwon error."));
}

function refreshToken()
{
    $grantType = "password";
    $clientId = "3MVG9CEn_O3jvv0xFGmB78Bd4pSsSTm2n7Q.ifFizt29IJDVSzTb0e5UtkQ2VeCOapw.rbvbNhyjga2X2Mgid";
    $clientSecret = "3819778418826826068";
    $username = "indrajeetlatthe@allureinc.co";
    $password = 'mt@$h183MzHobusdJGqcdHbOSdy4xTAKv';

    $tokenUrl = BASE_URL . OAUTH_URL . "grant_type={$grantType}&client_id={$clientId}&" .
        "client_secret={$clientSecret}&username={$username}&password={$password}";
    //  var_dump($tokenUrl);
    $tokenRequest = curl_init($tokenUrl);
    curl_setopt($tokenRequest, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($tokenRequest, CURLOPT_HEADER, false);
    curl_setopt($tokenRequest, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($tokenRequest, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($tokenRequest, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($tokenRequest, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($tokenRequest, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json"
    ));

    // execute $tokenRequest
    $tokenResponse = curl_exec($tokenRequest);
    $tokenResponseArr = json_decode($tokenResponse, true);
//    var_dump($tokenResponse);
    if ($tokenResponseArr["access_token"]) { //successfully generate access token
        return $tokenResponseArr;
    } else { //error - access token not generated
        return null;
    }
}

/**
 * @param fileName
 * @desc Returns increment ids in String comma seperated
 */
function buildIncrentIdForQuery($fileName)
{
    $lines = file($fileName);
    $incrementIds = "";
    foreach ($lines as $lineNumber => $line) {
        $myvalue = $lines[$lineNumber];
        $lineArray = explode(',', trim($myvalue));
        if ($lineNumber == sizeof($lines) - 1)
            $incrementIds .= "Increment_Id__c+LIKE+'TW-" . $lineArray[0] . "'";
        else
            $incrementIds .= "Increment_Id__c+LIKE+'TW-" . $lineArray[0] . "'+OR+";
    }
    return $incrementIds;
}

/**
 * @param fileName
 * @desc Returns increment ids in String comma seperated
 */
function buildPDFNameForQuery($fileName)
{
    $lines = file($fileName);
    $incrementIds = "";
    foreach ($lines as $lineNumber => $line) {
        $myvalue = $lines[$lineNumber];
        $lineArray = explode(',', trim($myvalue));
        if ($lineNumber == sizeof($lines) - 1)
            $incrementIds .= "Title+LIKE+'" . $lineArray[0] . "'";
        else
            $incrementIds .= "Title+LIKE+'" . $lineArray[0] . "'+OR+";
    }
    return $incrementIds;
}