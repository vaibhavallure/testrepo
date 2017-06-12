<?php

class Allure_Productshare_Helper_Data extends Mage_Core_Helper_Abstract
{

    /*
     * Product Share to store status code
     */
    const NONE = 0;

    const PENDING = 1;

    const PROCESSING = 2;

    const COMPLETE = 3;

    const NONE_CODE = 'none'; // 0
    const PENDING_CODE = 'pending'; // 1
    const PROCESSING_CODE = 'processing'; // 2
    const COMPLETE_CODE = 'complete'; // 3
    public function getStatusCode ($status)
    {
        $status_code = self::NONE_CODE;
        switch ($status) {
            case 0:
                $status_code = self::NONE_CODE;
                break;
            case 1:
                $status_code = self::PENDING_CODE;
                break;
            case 2:
                $status_code = self::PROCESSING_CODE;
                break;
            case 3:
                $status_code = self::COMPLETE_CODE;
                break;
            default:
                $status_code = self::NONE_CODE;
        }
        return $status_code;
    }
}