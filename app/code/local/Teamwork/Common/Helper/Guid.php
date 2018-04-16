<?php

class Teamwork_Common_Helper_Guid extends Mage_Core_Helper_Abstract
{
    public function generateGuid($namespace='')
    {
        static $guid = '';
        $uid = uniqid("", true);
        $data = $namespace;
        $data .= !empty($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : '';
        $data .= !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $data .= !empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
        $data .= !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        $data .= !empty($_SERVER['REMOTE_PORT']) ? $_SERVER['REMOTE_PORT'] : '';
        $hash = strtolower(hash('ripemd128', $uid . $guid . md5($data)));

        return $this->getGuidFromString($hash, true);
    }
    
    public function getGuidFromString($string, $dataIsHashed = false)
    {
        $string = $dataIsHashed ? $string : md5($string);
        return implode( '-',
            array(
                substr($string, 0, 8),
                substr($string, 8, 4),
                substr($string, 12, 4),
                substr($string, 16, 4),
                substr($string, 20, 12),
            )
        );
    }
    
    public function isGuidString($string, $caseless=true)
    {
        $flag = '';
        if($caseless)
        {
            $flag = 'i';
        }
        preg_match("/^[A-F0-9]{8}\-[A-F0-9]{4}\-4[A-F0-9]{3}\-(8|9|A|B)[A-F0-9]{3}\-[A-F0-9]{12}$/{$flag}", $string, $matches);
        return !empty($matches) ? true : false;
    }
}