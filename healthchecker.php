<?php
register_shutdown_function(function () {
    if (error_get_last()) {
        serverIsSick(print_r(error_get_last(),true));
    }
});
try {
    require_once 'app/Mage.php';
    //Check require extensions
    $memcache = new Memcache;

    if (Mage::app()->getCache()->getBackend() instanceof Zend_Cache_Backend_File) {
        serverIsSick('Cache object is instance of Zend_Cache_Backend_File, please check local.xml file');
    }
    $healthCheckerKey = 'testKey_' . rand();
    if (!Mage::app()->getCache()->save('1', $healthCheckerKey)) {
        serverIsSick('Could not save data to cache with key ' . $healthCheckerKey);
    }
    if (!(Mage::app()->getCache()->load($healthCheckerKey) == 1)) {
        serverIsSick('Could not load data from cache with key ' . $healthCheckerKey);
    }
    Mage::app()->getCache()->remove($healthCheckerKey);

    if (!Mage::getConfig()->getResourceModel('core')->getReadConnection()->query('select 1;')) {
        serverIsSick('Could not esteblish DB connection');
    }
    ;
} catch (Exception $e) {
    serverIsSick($e->getMessage());
}

function serverIsSick($symptom = 'Unknown symptoms')
{
    error_log($symptom, E_USER_ERROR);
    header('HTTP/1.1 503 Service Temporarily Unavailable');
    die;
}
