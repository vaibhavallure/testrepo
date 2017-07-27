<?php
/*
 *  Get List of customer's from sugarsrm &
 *  add into magento.
 *
 */

die;

function sendRequest ($request_url, $request_arguments, $is_auth,
    $reqType = null){
   // if ($reqType == null) {
        $reqType = "GET";
    //}
    
    $is_auth = true;
        
    $oauth_token = "";
    $send_request = curl_init($request_url);
    curl_setopt($send_request, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($send_request, CURLOPT_HEADER, false);
    curl_setopt($send_request, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($send_request, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($send_request, CURLOPT_CUSTOMREQUEST, $reqType);
    curl_setopt($send_request, CURLOPT_FOLLOWLOCATION, 0);
    if ($is_auth) {
        $oauth_token = login();
        curl_setopt($send_request, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "oauth-token: {$oauth_token}"
        ));
    } else {
        curl_setopt($send_request, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json"
        ));
    }
    
    // convert arguments to json
    if ($request_arguments != null) {
        $json_arguments = json_encode($request_arguments);
        curl_setopt($send_request, CURLOPT_POSTFIELDS, $json_arguments);
    }
    
    // execute request
    $response = curl_exec($send_request);
    
    $response_obj = json_decode($response, true);
    
    return $response;
}


function login (){
    $loginUrl = getLoginUrl();
    $loginParams = getLoginParams();
    $token = loginPost($loginUrl, $loginParams);
    return $token;
}


function getLoginParams ()
{
    return array(
        "grant_type" => "password",
        "client_id" => "maria_consumer_key",
        "client_secret" => "allure_secret_key",
        "username" => "allure_api",
        "password" => "jjt4SS",
        "platform" => "magento"
    );
}


function loginPost ($loginUrl, $login_params)
{
    $login_request = curl_init($loginUrl);
    curl_setopt($login_request, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($login_request, CURLOPT_HEADER, false);
    curl_setopt($login_request, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($login_request, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($login_request, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($login_request, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json"
    ));
    
    // convert arguments to json
    $json_arguments = json_encode($login_params);
    curl_setopt($login_request, CURLOPT_POSTFIELDS, $json_arguments);
    
    // execute request
    $login_response = curl_exec($login_request);
    
    // decode oauth2 response to get token
    $login_response_obj = json_decode($login_response, true);
    $login_access_token = $login_response_obj['access_token'];
    return $login_access_token;
}


function getLoginUrl(){
    return "https://mariatash.sugarondemand.com/rest/v10/oauth2/token";
}

function createCustomer(){
    
}

//$params = array('max_results'=>'2');
$ac_url = "https://mariatash.sugarondemand.com/rest/v10/Contacts";
$filter_response = sendRequest($ac_url, $params, $is_auth, "GET");
echo "<pre>";
$arr = json_decode($filter_response,true);

print_r(($arr['records'])); 


die;

$url = "https://mariatash.sugarondemand.com/service/v4_1/rest.php";
$username = "allure_api";
$password = "jjt4SS";

//function to make cURL request
function call($method, $parameters, $url)
{
    ob_start();
    $curl_request = curl_init();
    
    curl_setopt($curl_request, CURLOPT_URL, $url);
    curl_setopt($curl_request, CURLOPT_POST, 1);
    curl_setopt($curl_request, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($curl_request, CURLOPT_HEADER, 1);
    curl_setopt($curl_request, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl_request, CURLOPT_FOLLOWLOCATION, 0);
    
    $jsonEncodedData = json_encode($parameters);
    
    $post = array(
        "method" => $method,
        "input_type" => "JSON",
        "response_type" => "JSON",
        "rest_data" => $jsonEncodedData
    );
    
    curl_setopt($curl_request, CURLOPT_POSTFIELDS, $post);
    $result = curl_exec($curl_request);
    curl_close($curl_request);
    
    $result = explode("\r\n\r\n", $result, 2);
    $response = json_decode($result[1]);
    ob_end_flush();
    
    return $response;
}

//login -----------------------------------------
$login_parameters = array(
    "user_auth" => array(
        "user_name" => $username,
        "password" => md5($password),
        "version" => "1"
    ),
    "application_name" => "RestTest",
    "name_value_list" => array(),
);

$login_result = call("login", $login_parameters, $url);

/*
 echo "<pre>";
 print_r($login_result);
 echo "</pre>";
 */

//get session id
$session_id = $login_result->id;

//get list of records --------------------------------

$get_entry_list_parameters = array(
    
    //session id
    'session' => $session_id,
    
    //The name of the module from which to retrieve records
    'module_name' => 'Contacts',
    
    //The SQL WHERE clause without the word "where".
    'query' => "",
    
    //The SQL ORDER BY clause without the phrase "order by".
    'order_by' => "",
    
    //The record offset from which to start.
    'offset' => '0',
    
    //Optional. A list of fields to include in the results.
    'select_fields' => array(
        'id',
        'name',
        'title',
    ),
    
    /*
     A list of link names and the fields to be returned for each link name.
     Example: 'link_name_to_fields_array' => array(array('name' => 'email_addresses', 'value' => array('id', 'email_address', 'opt_out', 'primary_address')))
     */
    'link_name_to_fields_array' => array(
    ),
    
    //The maximum number of results to return.
    'max_results' => '2',
    
    //To exclude deleted records
    //'deleted' => '0',
    
    //If only records marked as favorites should be returned.
    'Favorites' => false,
);

$get_entry_list_result = call('get_entry_list', $get_entry_list_parameters, $url);

echo '<pre>';
print_r($get_entry_list_result);
echo '</pre>';
