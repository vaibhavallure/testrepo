<?php

class Ebizmarts_BakerlooPayment_Helper_Anet extends Mage_Core_Helper_Abstract
{

    /**
     * Generates a fingerprint needed for a hosted order form or DPM.
     *
     * @param string $api_login_id    Login ID.
     * @param string $transaction_key API key.
     * @param string $amount          Amount of transaction.
     * @param string $fp_sequence     An invoice number or random number.
     * @param string $fp_timestamp    Timestamp.
     * @param string $currencyCode    Currency for the transaction.
     *
     * @return string The fingerprint.
     */
    public function getFingerprint($apiLoginId, $transactionKey, $amount, $fpSequence, $fpTimestamp, $currencyCode)
    {

        $toBeHashed = $apiLoginId . "^" . $fpSequence . "^" . $fpTimestamp . "^" . $amount . "^" . $currencyCode;

        if (function_exists('hash_hmac')) {
            $fingerprint = hash_hmac("md5", $toBeHashed, $transactionKey);
        } else {
            $fingerprint = bin2hex(mhash(MHASH_MD5, $toBeHashed, $transactionKey));
        }

        return $fingerprint;
    }

    public function responseStatusForCode($code = null)
    {

        /*x_response_code Value: The overall status of the transaction
        Format:
        1 = Approved
        2 = Declined
        3 = Error
        4 = Held for Review*/

        switch ($code) {
            case 1:
                $status = "Approved";
                break;
            case 2:
                $status = "Declined";
                break;
            case 3:
                $status = "Error";
                break;
            case 4:
                $status = "Held for Review";
                break;
            default:
                $status = "";
                break;
        }

        return $status;
    }
}
