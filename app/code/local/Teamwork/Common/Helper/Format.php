<?php
class Teamwork_Common_Helper_Format extends Mage_Core_Helper_Abstract
{
    protected static $_format = 'xml';
    
    public static function getFormat()
    {
        return self::$_format;
    }
}