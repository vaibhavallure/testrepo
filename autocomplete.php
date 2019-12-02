<?php
/**
 * @category    Wyomind
 * @package     Wyomind_Elasticsearch
 * @version     4.5.0
 * @copyright   Copyright (c) 2017 Wyomind (https://wyomind.net)
 */

if (version_compare(phpversion(), '5.3.0', '<')===true) {
    echo  '<div style="font:12px/1.35em arial, helvetica, sans-serif;">
<div style="margin:0 0 25px 0; border-bottom:1px solid #ccc;">
<h3 style="margin:0; font-size:1.7em; font-weight:normal; text-transform:none; text-align:left; color:#2f2f2f;">
Whoops, it looks like you have an invalid PHP version.</h3></div><p>Magento supports PHP 5.3.0 or newer.
<a href="http://www.magentocommerce.com/install" target="">Find out</a> how to install</a>
 Magento using PHP-CGI as a work-around.</p></div>';
    exit;
}

/**
 * Compilation includes configuration file
 */
define('MAGENTO_ROOT', getcwd());

$compilerConfig = MAGENTO_ROOT . '/includes/config.php';
if (file_exists($compilerConfig)) {
    include $compilerConfig;
}

$mageFilename = MAGENTO_ROOT . '/app/Mage.php';
$maintenanceFile = 'maintenance.flag';

if (!file_exists($mageFilename)) {
    if (is_dir('downloader')) {
        header("Location: downloader");
    } else {
        echo $mageFilename." was not found";
    }
    exit;
}

if (file_exists($maintenanceFile)) {
    include_once dirname(__FILE__) . '/errors/503.php';
    exit;
}

require MAGENTO_ROOT . '/app/bootstrap.php';
require_once $mageFilename;

#Varien_Profiler::enable();

if (isset($_SERVER['MAGE_IS_DEVELOPER_MODE'])) {
    Mage::setIsDeveloperMode(true);
}

// Register autoload
spl_autoload_register(function($class) {
    $classFile = str_replace('\\', '/', $class, $count);
    if (!$count) {
        $classFile = str_replace(' ', DS, ucwords(str_replace('_', ' ', $class)));
    }
    $classFile .= '.php';
    include $classFile;
});

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
header('Pragma: no-cache');

$html = '';
$q = isset($_GET['q']) ? $_GET['q'] : '';
$found = false;

if ('' !== $q) {
    $store = isset($_GET['store']) ? $_GET['store'] : '';
    $currency = isset($_GET['currency']) ? $_GET['currency'] : '';
    $customerGroup = isset($_GET['cg']) ? $_GET['cg'] : '';
    $config = new Wyomind_Elasticsearch_Config($store);
    $synonyms = new Wyomind_Elasticsearch_Config($store, true);
    $strings = $synonyms->getObject();
    $searchTerm = $q;
    if (is_array($strings) && count($strings)) {
        foreach ($strings as $string) {
            if (trim($q) == trim($string['query_text']) && $store == $string['store']) {
                $q = $string['synonym_for'];
                break;
            }
        }
    }

    try {
        if (!$config->getData()) {
            throw new Exception('Could not find config for autocomplete');
        }

        $client = new Wyomind_Elasticsearch_Client($config->getClientConfig());
        $index = new Wyomind_Elasticsearch_Index($client, $client->getIndexAlias($store));
        $index->setAnalyzers($config->getAnalyzers());

        $autocomplete = new Wyomind_Elasticsearch_Autocomplete($config);
        $html = $autocomplete->search($q, $searchTerm, $currency, $customerGroup, $index);
        $found = true;
    } catch (Exception $e) {
        $fallback = parse_url($_GET['fallback_url']);
        $current = parse_url($_SERVER['HTTP_HOST']);
        if (isset($_GET['fallback_url']) && $fallback['host'] == $current['path']) {
            $url = $_GET['fallback_url'] . '?q=' . $q;
            $html = @file_get_contents($url);
        } else {
            throw new Exception('Warning : Fallback url is not from the same domain !');
        }
    }
}

header('Fast-Autocomplete: ' . ($found ? 'HIT' : 'MISS'));

echo $html;
exit;
