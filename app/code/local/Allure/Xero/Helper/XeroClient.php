<?php

class Allure_Xero_Helper_XeroClient extends Allure_Xero_Helper_Data {

    private $token;

    private $refresh_token;

    public $bank_accounts;

    /**
     * Allure_Xero_Helper_XeroClient constructor.
     * @param $token
     */
    public function __construct() {
        $tokens = $this->generateAccessToken();
        if($tokens) {
            $this->token = $tokens['access_token'];
            $this->refresh_token = $tokens['refresh_token'];
            $this->bank_accounts = $this->getBankAccounts();
        }else {
            $this->log('Can not generate token');
        }
    }

    function sendRequest($url,$requestMethod,$requestArgs,$requestHeaders,$json=false) {

        if(!$this->getXeroStatus()) {
            $this->log('Xero API Disabled!');
            return;
        }

        $$this->sendRequest = curl_init($url);
        curl_setopt($$this->sendRequest, CURLOPT_CUSTOMREQUEST, $requestMethod);
        curl_setopt($$this->sendRequest,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($$this->sendRequest, CURLOPT_HTTPHEADER,$requestHeaders);
        if(!empty($requestArgs) && $requestMethod == "PUT"){
            $this->log('URL'.$url);
            $this->log(json_encode($requestArgs));
            curl_setopt($$this->sendRequest, CURLOPT_POSTFIELDS, json_encode($requestArgs));
        }

        if(!empty($requestArgs) && $requestMethod == "POST" && !$json) {
            curl_setopt($$this->sendRequest, CURLOPT_POSTFIELDS, http_build_query($requestArgs));
        }

        if($requestMethod == "POST" && $json) {
            curl_setopt($$this->sendRequest, CURLOPT_POSTFIELDS, json_encode($requestArgs,true));
        }


        $response = curl_exec($$this->sendRequest);
        echo $url;

        //if(strpos($url,'INV-')) print_r($response);die;

        $responseArr = json_decode($response,true);
        if(strpos('TokenExpired',$responseArr['detail'])){
            //TODO LOG
            $this->refreshAccessToken();
            $this->sendRequest($url,$requestMethod,$requestArgs,$requestHeaders);
        }
        return $responseArr;
    }

    function getBasicAPIRequestHeaders() {
        $request_headers = array();
        //echo "Basic Access".$token;
        $request_headers[] = 'Authorization: '. "Bearer {$this->token}";
        $request_headers[] = 'Accept: application/json';
        $request_headers[] = 'xero-tenant-id: cd6a5c16-0a0b-49b6-bd27-144b40ac08c1';

        return $request_headers;
    }

    public function getBankAccounts() {
        $bankAccountURL = $this->getXeroBaseApiUrl().'Accounts?where=type=="BANK"';
        $bankAccountsResponseArr = $this->sendRequest($bankAccountURL,"GET",null,$this->getBasicAPIRequestHeaders());
        $accountsArray =$bankAccountsResponseArr['Accounts'];
        $acc = array();
        foreach ($accountsArray as $account) {
            //array_push($acc,array("Name" => $account['Name'],"Id" => $account['AccountID']));
            $acc[$account['Name']] = $account['AccountID'];
        }
        return $acc;
    }

    private function generateAccessToken() {
        if(!empty($this->getRefreshToken())) {
            $this->log("Refresh token exists");
            $tokens = $this->refreshAccessToken();
            return $tokens;
        }

        $code = $this->getAuthCode();
        $this->log('Auth code'.$code);
        $tokenRequestHeaders = $this->getTokenHeaders();
        $refreshTokenReqArgs = array(
            "grant_type" => "authorization_code",
            "redirect_uri" => $this->getRedirectUri(),
            "code" => $this->getAuthCode()
        );

        $tokenResponse = $this->sendRequest($this->getXeroTokenUrl(),"POST",$refreshTokenReqArgs,$tokenRequestHeaders);
        $accessToken = $tokenResponse['access_token'];
        $refreshToken = $tokenResponse['refresh_token'];
        $date = new DateTime();
        $dateString = $date->format('Y-m-d');
        if($accessToken && $refreshToken){
            $this->log("Access token and Refresh token generated at {$dateString}");
            $this->setRefreshToken($refreshToken);
            return array("access_token" => $accessToken, "refresh_token" => $refreshToken);
        } else {
          $this->log('Error while generating token'.json_encode($tokenResponse));
          return false;
        }
    }

    private function refreshAccessToken() {
        $this->log("Refreshing access token");
        $tokenRequestHeaders = $this->getTokenHeaders();
        $refreshTokenReqArgs = array(
            "grant_type" => "refresh_token",
            "refresh_token" => $this->getRefreshToken(),
        );

        $tokenResponse = $this->sendRequest($this->getXeroTokenUrl(),"POST",$refreshTokenReqArgs,$tokenRequestHeaders);
        $accessToken = $tokenResponse['access_token'];
        $refreshToken = $tokenResponse['refresh_token'];
        $this->token = $accessToken;
        $this->refresh_token = $refreshToken;
        $this->setRefreshToken($refreshToken);
        return array("access_token" => $accessToken, "refresh_token" => $refreshToken);
    }

    private function getTokenHeaders() {
        $tokenRequestHeaders =  array(
            "Content-Type: application/x-www-form-urlencoded",
            "Authorization: Basic OTE1NjkyQTc2MTNCNDFDMzkyNjQ0NjM4REZDNTkzMzc6WjlaYi0tNWhpTXFieXlOUVRlTllkWV9xbHFfQVpfRjRacF9xZlVmMXRIcmNIdE1s"
        );
        return $tokenRequestHeaders;
    }

    public function deletePayment($paymentId) {
        $requestArgs = array("Status" => "DELETED");

        $deletePaymentResponse = $this->sendRequest($this->getXeroBaseApiUrl()."Payments/{$paymentId}",POST,$requestArgs,$this->getBasicAPIRequestHeaders(),true);
        return $deletePaymentResponse;
    }

    public function getInvoiceByOrderReference($orderReference) {
        $invoiceUrl = $this->getXeroBaseApiUrl()."Invoices/$orderReference?";

        $requestArgs = array(
            "Statuses" => "AUTHORISED",
            //"where" => 'Reference=="'. $orderReference .' - Synced by Webgility"'
        );

        $invoiceRespone = $this->sendRequest($invoiceUrl.http_build_query($requestArgs),GET,null,$this->getBasicAPIRequestHeaders());
        return $invoiceRespone;
    }

    public function getInvoices() {
        $url = $this->getXeroBaseApiUrl()."Invoices?";

        $requestArgs = array(
            "Statuses" => "AUTHORISED",
            "where" => 'InvoiceNumber.Contains("TWI")',
        );

        $invoiceRespone = $this->sendRequest($url.http_build_query($requestArgs),GET,null,$this->getBasicAPIRequestHeaders());
//        print_r($invoiceRespone);die;
        //print_r(json_encode($invoiceRespone));die;
        return $invoiceRespone;
    }

    public function deleteInvoice($invoiceNo,$status,$isBulk=false,$bulkArgs=null) {
        if(!$isBulk) {
            $url = $this->getXeroBaseApiUrl()."Invoices/".$invoiceNo;
            $requestArgs = array(
                //"InvoiceNumber" => $invoiceNo,
                "Status" => $status
            );
            $deleteInvoiceRespone = $this->sendRequest($url,POST,$requestArgs,$this->getBasicAPIRequestHeaders(),true);

            if($deleteInvoiceRespone['Status'] == "OK") {
                $this->log("Invoice {$invoiceNo} - {$status}");
            }else if($deleteInvoiceRespone['ErrorNumber']) {
                $errorNo = $deleteInvoiceRespone['ErrorNumber'];
                $message = $deleteInvoiceRespone['Message'];
                $errorMessage = $deleteInvoiceRespone['Elements'][0]['ValidationErrors'][0]['Message'];
                $this->log("Error({$errorNo}) - {$message} for Invoice {$invoiceNo} - {$status} / {$errorMessage}");
            }
        }else {
            $url = $this->getXeroBaseApiUrl()."Invoices";
            $deleteInvoiceRespone = $this->sendRequest($url,POST,$bulkArgs,$this->getBasicAPIRequestHeaders(),true);
        }

        return $deleteInvoiceRespone;
    }

    public function getCreditNotes() {
        $url = $this->getXeroBaseApiUrl().'CreditNotes?';

        $requestArgs = array(
            "where" => 'status=="AUTHORISED"&&CreditNoteNumber.Contains("TWC")',
        );

        $CreditNoteRespone = $this->sendRequest($url.http_build_query($requestArgs),GET,null,$this->getBasicAPIRequestHeaders());
        //print_r($CreditNoteRespone);die;
        return $CreditNoteRespone;
    }

    public function deleteCreditNotes($CreditNoteNo,$status,$isBulk=false,$bulkArgs=null) {
        if(!$isBulk) {
            $url = $this->getXeroBaseApiUrl()."CreditNotes/".$CreditNoteNo;

            $requestArgs = array(
                //"CreditNoteNumber" => $invoiceNo,
                "Status" => $status
            );

            $deleteCreditNoteRespone = $this->sendRequest($url,POST,$requestArgs,$this->getBasicAPIRequestHeaders(),true);
            if($deleteCreditNoteRespone['Status'] == "OK") {
                $this->log("Invoice {$CreditNoteNo} - {$status}");
            }else if($deleteCreditNoteRespone['ErrorNumber']) {
                $errorNo = $deleteCreditNoteRespone['ErrorNumber'];
                $message = $deleteCreditNoteRespone['Message'];
                $errorMessage = $deleteCreditNoteRespone['Elements'][0]['ValidationErrors'][0]['Message'];
                $this->log("Error({$errorNo}) - {$message} for Invoice {$CreditNoteNo} - {$status} / {$errorMessage}");
            }
        }else {
            $url = $this->getXeroBaseApiUrl()."CreditNotes";
            $deleteCreditNoteRespone = $this->sendRequest($url,POST,$bulkArgs,$this->getBasicAPIRequestHeaders(),true);
        }

        return $deleteCreditNoteRespone;
    }

    public function getCreditNoteByOrderReference($creditNoteReference=null) {
        if($creditNoteReference!=null)
            $creditNoteUrl = $this->getXeroBaseApiUrl()."CreditNotes/{$creditNoteReference}?";
        else
            $creditNoteUrl = $this->getXeroBaseApiUrl()."CreditNotes?";
        $requestArgs = array(
            "Statuses" => "AUTHORISED",
        );

        $creditNoteRespone = $this->sendRequest($creditNoteUrl.http_build_query($requestArgs),GET,null,$this->getBasicAPIRequestHeaders());
        $this->log(json_decode($creditNoteRespone));
        return $creditNoteRespone;
    }



    public function createPaymentForInvoice($paymentRequestArgs) {
        $paymentResponse = $this->sendRequest($this->getXeroBaseApiUrl()."Payments","PUT",$paymentRequestArgs,$this->getBasicAPIRequestHeaders());
        return $paymentResponse;
    }

    public function createPaymentForCreditNote($creditNoteArgs) {
        $creditNoteResponse = $this->sendRequest($this->getXeroBaseApiUrl()."Payments","PUT",$creditNoteArgs,$this->getBasicAPIRequestHeaders());
        $this->log(json_decode($creditNoteResponse));
        return $creditNoteResponse;
    }

    public function createBankTransaction($type) {
        $bankTransactionArgs = array(
            "Type" => $type,
            "Contact" => array("ContactID" => "b8f292c2-7905-4f38-97c5-e9a38f9a05e6"),
            "BankAccount" => array("AccountID" => "5f91a430-98bc-4ed8-bca6-7383b0941549"),
            "LineAmountTypes" => "NoTax",
            "LineItems" => array(
                array(
                    "Description" => "Return Change Amount",
                    "LineAmount" => 100
                )
            )
        );

        $bankTransactionResponse  = $this->sendRequest($this->getXeroBaseApiUrl()."BankTransactions","POST",$bankTransactionArgs,$this->getBasicAPIRequestHeaders(),true);
        $this->log(json_encode($bankTransactionResponse));

    }

    public function allocateOverpaymentToInvoice($overPaymentId, $invoiceId) {
        $allocateOverpaymentRequestArgs = array(
            "Amount" => 100,
            "Invoice" => array("InvoiceID" => "2ccb33e0-1a24-4317-bfd8-d5b85bd44902")
        );

        $allocateOverpaymentResposne = $this->sendRequest($this->getXeroBaseApiUrl()."Overpayments/015cb737-b7d5-4ee7-8716-86344fa6cadd/Allocations","PUT",$allocateOverpaymentRequestArgs,$this->getBasicAPIRequestHeaders(),true);
        $this->log(json_encode($allocateOverpaymentResposne));
    }

    public function updateInvoice($body) {
        $res = $this->sendRequest($this->getXeroBaseApiUrl()."Invoices","POST",$body,$this->getBasicAPIRequestHeaders(),true);
        return $res;
    }


    public function getXeroBankAccountNameByPaymentTitle($paymentTitle) {
        $paymentTitleMappings = array(
            "£ CASH" => "Cash Pay",
            "AED Cash" => "Cash Pay",
            "AED Credit" => "Credit Card Pay",
            "AED Credit Card" => "Credit Card Pay",
            "AED Refund" => "Cash Pay",
            "Amazon" => "AmazonPay",
            "Apple Pay (Authorize.Net)" => "ApplePay",
            "Authorize" => "Credit Card Pay",
            "AUTHORIZE.NET" => "Credit Card Pay",
            "Bday" => "Cash Pay",
            "BROWN THOMAS TILL" => "Other Pay",
            "Cash" => "Cash Pay",
            "CASHEURO" => "Cash Pay",
            "CASHUK" => "Cash Pay",
            "CASH" => "Cash Pay",
            "CC REFUND" => "Credit Card Pay",
            "Check" => "Other Pay",
            "CHECK US" => "Other Pay",
            "Coupan" => "Other Pay",
            "CREDIT CARD" => "Credit Card Pay",
            "Credit Card (Secure)" => "Credit Card Pay",
            "Default" => "Other Pay",
            "DEPOSIT" => "Deposit Pay",
            "GENIUS CHARGE" => "Credit Card Pay",
            "GENIUS REFUND" => "Credit Card Pay",
            "Gift" => "Giftcard Pay",
            "GIFT CARD" => "Giftcard Pay",
            "HARRODS TILL" => "Other Pay",
            "HOUSE ACCOUNT" => "Other Pay",
            "Internet" => "Other Pay",
            "LIBERTY TILL" => "Other Pay",
            "Login and Pay with Amazon" => "AmazonPay",
            "No Payment Information Required" => "Cash Pay",
            "OFFLINE CREDIT CARD" => "Other Pay",
            "Pay by Wire Transfer" => "Other Pay",
            "PAYPAL" => "Paypal Pay",
            "Paypal" => "Paypal Pay",
            "PayPal Billing Agreement" => "Paypal Pay",
            "PayPal Express Checkout" => "Paypal Pay",
            "POS Cash in Hand"=> "Cash Pay",
            "Pldgc" => "Other Pay",
            "POS Free" => "Other Pay",
            "SQUARE" => "Other Pay",
            "STCR" => "Other Pay",
            "STORE CREDIT" => "Other Pay",
            "Wholesale" => "Cash Pay",
            "WIRE TRANSFER" => "Other Pay",
            "Wired" => "Other Pay",
        );

        return $paymentTitleMappings[$paymentTitle];
    }

}
