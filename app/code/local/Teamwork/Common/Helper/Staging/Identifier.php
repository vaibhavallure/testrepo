<?php
class Teamwork_Common_Helper_Staging_Identifier extends Mage_Core_Helper_Abstract
{
    const CHQ_IDENTIFIER_UPC = 'UPC';
    const CHQ_IDENTIFIER_CLU = 'CLU';
    const CHQ_IDENTIFIER_EID = 'EID';
    
    public function getIdentifier($identifier)
    {
        switch($identifier)
        {
            case self::CHQ_IDENTIFIER_UPC:
            {
                return 0;
            }
            case self::CHQ_IDENTIFIER_CLU:
            {
                return 1;
            }
            case self::CHQ_IDENTIFIER_EID:
            {
                return 2;
            }
        }
    }
}