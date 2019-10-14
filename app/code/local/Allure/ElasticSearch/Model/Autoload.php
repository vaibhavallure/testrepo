<?php
/**
 * @category    Allure
 * @package     Allure_ElasticSearch
 * @version     1.0.0
 * @copyright   Copyright (c) 2019 Allure Software, Inc. (https://www.allureinc.co)
 */
class Allure_ElasticSearch_Model_Autoload
{
    /**
     * Try autoloading namespace classes before regular Magento classes
     *
     * @param string $class
     */
    public static function load($class)
    {
        $classFile = BP . DS . 'lib' . DS . str_replace('\\', DS, $class, $count) . '.php';
        if ($count > 0 && is_file($classFile)) {
            include $classFile;
        }
    }
}
