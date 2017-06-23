<?php
require_once('../app/Mage.php'); 
umask(0);
Mage::app();
Mage::getModel('allure_instacatalog/cron')->syncFeeds();
echo "Sync Done";
die;
