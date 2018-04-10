<?php
$transRequestXmlStr= file_get_contents('request.xml');

$transRequestXml=new SimpleXMLElement($transRequestXmlStr);

$refId = 'ref' . time();
//$transRequestXml->addChild('refId', $refId);

//$transRequestXml->merchantAuthentication->addChild('name','venus12');
//$transRequestXml->merchantAuthentication->addChild('transactionKey','5s8UVJ42HUhj6u9k');
//$transRequestXml->merchantAuthentication->name = "5KP3u95bQpv";
//$transRequestXml->merchantAuthentication->transactionKey = "4Ktq966gC55GAX7S";

$transRequestXml->transactionRequest->amount='360.00';
//$transRequestXml->transactionRequest->payment->opaqueData->dataDescriptor=$_POST['dataDesc'];

$data = '{"version":"EC_v1","data":"PMIzvJxm2R0otSeRAR3Jsha48RFMSgG6O+wbp1Czw5dXZjoX3RVyWGRc4gv3mPHy1rE6etZWCtMcvUcPqfLqj7n3GCLJNLwpih4clSVIJQPFwSO0mhT35z2GpK415K/Hpgh0anFzqqOykAzJuT+NmAJEGyI/OHXTtENkCvAdyewI40JIWdziQOty5qBdhf+cSv91JWCAUjIBZiYqkrcMoJXJiWYrSXqYEONrf1CeckXbvbxGUU472sf06WWu50/Szw1N/TqueOzJQtutx7lCCPAiJ+0piq5WgUC5bwccZh6RAjZVYoHgu+OQvio6ou5Sbo4mvKwrpDDqwUBhmUZLJK49m7FZ5SavpW/p9gFlYyWKJGgjIXLpbYxEq3RbMy7iAZ402VFJ9NOJ1fw42KHosHb/0B//O1IuMvoPPMA1bPGQ","signature":"MIAGCSqGSIb3DQEHAqCAMIACAQExDzANBglghkgBZQMEAgEFADCABgkqhkiG9w0BBwEAAKCAMIID4jCCA4igAwIBAgIIJEPyqAad9XcwCgYIKoZIzj0EAwIwejEuMCwGA1UEAwwlQXBwbGUgQXBwbGljYXRpb24gSW50ZWdyYXRpb24gQ0EgLSBHMzEmMCQGA1UECwwdQXBwbGUgQ2VydGlmaWNhdGlvbiBBdXRob3JpdHkxEzARBgNVBAoMCkFwcGxlIEluYy4xCzAJBgNVBAYTAlVTMB4XDTE0MDkyNTIyMDYxMVoXDTE5MDkyNDIyMDYxMVowXzElMCMGA1UEAwwcZWNjLXNtcC1icm9rZXItc2lnbl9VQzQtUFJPRDEUMBIGA1UECwwLaU9TIFN5c3RlbXMxEzARBgNVBAoMCkFwcGxlIEluYy4xCzAJBgNVBAYTAlVTMFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEwhV37evWx7Ihj2jdcJChIY3HsL1vLCg9hGCV2Ur0pUEbg0IO2BHzQH6DMx8cVMP36zIg1rrV1O/0komJPnwPE6OCAhEwggINMEUGCCsGAQUFBwEBBDkwNzA1BggrBgEFBQcwAYYpaHR0cDovL29jc3AuYXBwbGUuY29tL29jc3AwNC1hcHBsZWFpY2EzMDEwHQYDVR0OBBYEFJRX22/VdIGGiYl2L35XhQfnm1gkMAwGA1UdEwEB/wQCMAAwHwYDVR0jBBgwFoAUI/JJxE+T5O8n5sT2KGw/orv9LkswggEdBgNVHSAEggEUMIIBEDCCAQwGCSqGSIb3Y2QFATCB/jCBwwYIKwYBBQUHAgIwgbYMgbNSZWxpYW5jZSBvbiB0aGlzIGNlcnRpZmljYXRlIGJ5IGFueSBwYXJ0eSBhc3N1bWVzIGFjY2VwdGFuY2Ugb2YgdGhlIHRoZW4gYXBwbGljYWJsZSBzdGFuZGFyZCB0ZXJtcyBhbmQgY29uZGl0aW9ucyBvZiB1c2UsIGNlcnRpZmljYXRlIHBvbGljeSBhbmQgY2VydGlmaWNhdGlvbiBwcmFjdGljZSBzdGF0ZW1lbnRzLjA2BggrBgEFBQcCARYqaHR0cDovL3d3dy5hcHBsZS5jb20vY2VydGlmaWNhdGVhdXRob3JpdHkvMDQGA1UdHwQtMCswKaAnoCWGI2h0dHA6Ly9jcmwuYXBwbGUuY29tL2FwcGxlYWljYTMuY3JsMA4GA1UdDwEB/wQEAwIHgDAPBgkqhkiG92NkBh0EAgUAMAoGCCqGSM49BAMCA0gAMEUCIHKKnw+Soyq5mXQr1V62c0BXKpaHodYu9TWXEPUWPpbpAiEAkTecfW6+W5l0r0ADfzTCPq2YtbS39w01XIayqBNy8bEwggLuMIICdaADAgECAghJbS+/OpjalzAKBggqhkjOPQQDAjBnMRswGQYDVQQDDBJBcHBsZSBSb290IENBIC0gRzMxJjAkBgNVBAsMHUFwcGxlIENlcnRpZmljYXRpb24gQXV0aG9yaXR5MRMwEQYDVQQKDApBcHBsZSBJbmMuMQswCQYDVQQGEwJVUzAeFw0xNDA1MDYyMzQ2MzBaFw0yOTA1MDYyMzQ2MzBaMHoxLjAsBgNVBAMMJUFwcGxlIEFwcGxpY2F0aW9uIEludGVncmF0aW9uIENBIC0gRzMxJjAkBgNVBAsMHUFwcGxlIENlcnRpZmljYXRpb24gQXV0aG9yaXR5MRMwEQYDVQQKDApBcHBsZSBJbmMuMQswCQYDVQQGEwJVUzBZMBMGByqGSM49AgEGCCqGSM49AwEHA0IABPAXEYQZ12SF1RpeJYEHduiAou/ee65N4I38S5PhM1bVZls1riLQl3YNIk57ugj9dhfOiMt2u2ZwvsjoKYT/VEWjgfcwgfQwRgYIKwYBBQUHAQEEOjA4MDYGCCsGAQUFBzABhipodHRwOi8vb2NzcC5hcHBsZS5jb20vb2NzcDA0LWFwcGxlcm9vdGNhZzMwHQYDVR0OBBYEFCPyScRPk+TvJ+bE9ihsP6K7/S5LMA8GA1UdEwEB/wQFMAMBAf8wHwYDVR0jBBgwFoAUu7DeoVgziJqkipnevr3rr9rLJKswNwYDVR0fBDAwLjAsoCqgKIYmaHR0cDovL2NybC5hcHBsZS5jb20vYXBwbGVyb290Y2FnMy5jcmwwDgYDVR0PAQH/BAQDAgEGMBAGCiqGSIb3Y2QGAg4EAgUAMAoGCCqGSM49BAMCA2cAMGQCMDrPcoNRFpmxhvs1w1bKYr/0F+3ZD3VNoo6+8ZyBXkK3ifiY95tZn5jVQQ2PnenC/gIwMi3VRCGwowV3bF3zODuQZ/0XfCwhbZZPxnJpghJvVPh6fRuZy5sJiSFhBpkPCZIdAAAxggGMMIIBiAIBATCBhjB6MS4wLAYDVQQDDCVBcHBsZSBBcHBsaWNhdGlvbiBJbnRlZ3JhdGlvbiBDQSAtIEczMSYwJAYDVQQLDB1BcHBsZSBDZXJ0aWZpY2F0aW9uIEF1dGhvcml0eTETMBEGA1UECgwKQXBwbGUgSW5jLjELMAkGA1UEBhMCVVMCCCRD8qgGnfV3MA0GCWCGSAFlAwQCAQUAoIGVMBgGCSqGSIb3DQEJAzELBgkqhkiG9w0BBwEwHAYJKoZIhvcNAQkFMQ8XDTE4MDMxNjA3MTU1MVowKgYJKoZIhvcNAQk0MR0wGzANBglghkgBZQMEAgEFAKEKBggqhkjOPQQDAjAvBgkqhkiG9w0BCQQxIgQgEPxcUFiKSDcUMGCP46MbzBl9KtZffleclFoBiesVD7IwCgYIKoZIzj0EAwIERzBFAiEAwX1jgEklAmqnxTN6U5QB9PBrhctaPU++SpRLMal9S30CIFZZgIeHwKD4alkAKvQYMAz15Va3Xcr1NFKf3I+guqBoAAAAAAAA","header":{"ephemeralPublicKey":"MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEGVRaekf5mhim8/ZrIOBV56PWuKf4wklJ6Fn1lZQA2mKkHN/JoOY4+KwQE++HwRZirRnSXmr4Tx0+9bY/sipTsw==","publicKeyHash":"ie6G12DBkvqUqQ9BtxOoN9FVBxC8/gololx6j6+S2F4=","transactionId":"9481e4532f75822885304b57657a996cc5fa9165a60214a637eaf96f76bb27d6"}}';
//$data = '{"data":"BDPNWStMmGewQUWGg4o7E\/j+1cq1T78qyU84b67itjcYI8wPYAOhshjhZPrqdUr4XwPMbj4zcGMdy++1H2VkPOY+BOMF25ub19cX4nCvkXUUOTjDllB1TgSr8JHZxgp9rCgsSUgbBgKf60XKutXf6aj\/o8ZIbKnrKQ8Sh0ouLAKloUMn+vPu4+A7WKrqrauz9JvOQp6vhIq+HKjUcUNCITPyFhmOEtq+H+w0vRa1CE6WhFBNnCHqzzWKckB\/0nqLZRTYbF0p+vyBiVaWHeghERfHxRtbzpeczRPPuFsfpHVs48oPLC\/k\/1MNd47kz\/pHDcR\/Dy6aUM+lNfoily\/QJN+tS3m0HfOtISAPqOmXemvr6xJCjCZlCuw0C9mXz\/obHpofuIES8r9cqGGsUAPDpw7g642m4PzwKF+HBuYUneWDBNSD2u6jbAG3","version":"EC_v1","header":{"applicationData":"94ee059335e587e501cc4bf90613e0814f00a7b08bc7c648fd865a2af6a22cc2","transactionId":"c1caf5ae72f0039a82bad92b828363734f85bf2f9cadf193d1bad9ddcb60a795","ephemeralPublicKey":"MIIBSzCCAQMGByqGSM49AgEwgfcCAQEwLAYHKoZIzj0BAQIhAP\/\/\/\/8AAAABAAAAAAAAAAAAAAAA\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/MFsEIP\/\/\/\/8AAAABAAAAAAAAAAAAAAAA\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/8BCBaxjXYqjqT57PrvVV2mIa8ZR0GsMxTsPY7zjw+J9JgSwMVAMSdNgiG5wSTamZ44ROdJreBn36QBEEEaxfR8uEsQkf4vOblY6RA8ncDfYEt6zOg9KE5RdiYwpZP40Li\/hp\/m47n60p8D54WK84zV2sxXs7LtkBoN79R9QIhAP\/\/\/\/8AAAAA\/\/\/\/\/\/\/\/\/\/+85vqtpxeehPO5ysL8YyVRAgEBA0IABGm+gsl0PZFT\/kDdUSkxwyfo8JpwTQQzBm9lJJnmTl4DGUvAD4GseGj\/pshBZ0K3TeuqDt\/tDLbE+8\/m0yCmoxw=","publicKeyHash":"\/bb9CNC36uBheHFPbmohB7Oo1OsX2J+kJqv48zOVViQ="},"signature":"MIIDQgYJKoZIhvcNAQcCoIIDMzCCAy8CAQExCzAJBgUrDgMCGgUAMAsGCSqGSIb3DQEHAaCCAiswggInMIIBlKADAgECAhBcl+Pf3+U4pk13nVD9nwQQMAkGBSsOAwIdBQAwJzElMCMGA1UEAx4cAGMAaABtAGEAaQBAAHYAaQBzAGEALgBjAG8AbTAeFw0xNDAxMDEwNjAwMDBaFw0yNDAxMDEwNjAwMDBaMCcxJTAjBgNVBAMeHABjAGgAbQBhAGkAQAB2AGkAcwBhAC4AYwBvAG0wgZ8wDQYJKoZIhvcNAQEBBQADgY0AMIGJAoGBANC8+kgtgmvWF1OzjgDNrjTEBRuo\/5MKvlM146pAf7Gx41blE9w4fIXJAD7FfO7QKjIXYNt39rLyy7xDwb\/5IkZM60TZ2iI1pj55Uc8fd4fzOpk3ftZaQGXNLYptG1d9V7IS82Oup9MMo1BPVrXTPHNcsM99EPUnPqdbeGc87m0rAgMBAAGjXDBaMFgGA1UdAQRRME+AEHZWPrWtJd7YZ431hCg7YFShKTAnMSUwIwYDVQQDHhwAYwBoAG0AYQBpAEAAdgBpAHMAYQAuAGMAbwBtghBcl+Pf3+U4pk13nVD9nwQQMAkGBSsOAwIdBQADgYEAbUKYCkuIKS9QQ2mFcMYREIm2l+Xg8\/JXv+GBVQJkOKoscY4iNDFA\/bQlogf9LLU84THwNRnsvV3Prv7RTY81gq0dtC8zYcAaAkCHII3yqMnJ4AOu6EOW9kJk232gSE7WlCtHbfLSKfuSgQX8KXQYuZLk2Rr63N8ApXsXwBL3cJ0xgeAwgd0CAQEwOzAnMSUwIwYDVQQDHhwAYwBoAG0AYQBpAEAAdgBpAHMAYQAuAGMAbwBtAhBcl+Pf3+U4pk13nVD9nwQQMAkGBSsOAwIaBQAwDQYJKoZIhvcNAQEBBQAEgYBaK3ElOstbH8WooseDABf+Jg\/129JcIawm7c6Vxn7ZasNbAq3tAt8Pty+uQCgssXqZkLA7kz2GzMolNtv9wYmu9Ujwar1PHYS+B\/oGnoz591wjagXWRz0nMo5y3O1KzX0d8CRHAVa88SrV1a5JIiRev3oStIqwv5xuZldag6Tr8w=="}';

$dataArray = json_decode($data, true);

$newDataArray = array(
        'data' => $dataArray['data'],
        'version' => $dataArray['version'],
        'header' => $dataArray['header'],
        'signature' => $dataArray['signature']
);

$dataValue = hash('sha256',json_encode($newDataArray));

$transRequestXml->transactionRequest->payment->opaqueData->dataValue = $dataValue;

if('COMMON.APPLE.INAPP.PAYMENT' === 'COMMON.VCO.ONLINE.PAYMENT')
{
    $transRequestXml->transactionRequest->addChild('callId',$_POST['callId']);
}


if(isset($_POST['paIndicator'])){
    $transRequestXml->transactionRequest->addChild('cardholderAuthentication');
    $transRequestXml->transactionRequest->addChild('authenticationIndicator',$_POST['paIndicator']);
    $transRequestXml->transactionRequest->addChild('cardholderAuthenticationValue',$_POST['paValue']);
}

$url="https://api.authorize.net/xml/v1/request.api";
header("Content-Type: text/plain");

print_r($newDataArray);
print_r($transRequestXml->asXML());

try{	//setting the curl parameters.
    $ch = curl_init();
    if (FALSE === $ch)
        throw new Exception('failed to initialize');
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $transRequestXml->asXML());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
        // The following two curl SSL options are set to "false" for ease of development/debug purposes only.
        // Any code used in production should either remove these lines or set them to the appropriate
        // values to properly use secure connections for PCI-DSS compliance.
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);	//for production, set value to true or 1
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);	//for production, set value to 2
        curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false );
        $content = curl_exec($ch);
        if (FALSE === $content)
            throw new Exception(curl_error($ch), curl_errno($ch));
            curl_close($ch);
            
            $xmlResult=simplexml_load_string($content);
            
            $jsonResult=json_encode($xmlResult);
            
            echo $jsonResult;
            
}catch(Exception $e) {
    trigger_error(sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()), E_USER_ERROR);
}