<?php

require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');


function setScript($skuString,$script)
{
    $skuArr = explode(",", $skuString);
    $updateSuccess = array();
    $updateFail = array();

    foreach ($skuArr as $sku) {
        $product_id = Mage::getModel('catalog/product')->getIdBySku(trim($sku));
        if ($product_id) {
            $product = Mage::getModel('catalog/product')->load($product_id);
            $product->setSearchspringScript($script);
            try {
                $product->save();
                addLog("product updated sku:" . $sku);
                array_push($updateSuccess, $sku);
            } catch (Exception $e) {
                addLog($sku . ":Exception:" . $e->getMessage());
            }
        } else {
            addLog("product not found for sku:" . $sku);
            array_push($updateFail, $sku);
        }
    }

    addLog("final result:");
    addLog("sku updated successfully:");
    addLog($updateSuccess);
    addLog("sku update failed:");
    addLog($updateFail);
}



function addLog($string)
{
    echo "\n".$string;
    Mage::log($string,7,"search_spring_set_script.log",true);
}



$sku3mmString="XCHFR2FP53D,XCHFR3FP75D,XCHMQ73D,XCHP64D,XCHP75D,XCHP425D,XCHMQ63D,XCHP53D,XCHFR2FP53D";
$script3mm='<div class="row"><div class="ss-complimentary-container"><div class="ss-recs-wrapper"><script type="searchspring/recommend" profile="wear-it-with-3-0mm">seed = ""</script></div></div></div>';
addLog("SKU's: ".$sku3mmString);
setScript($sku3mmString,$script3mm);



$sku1_9mmString="XCHFL45D,XCHFL55D,XCHSTAR45D,XCHSTAR55D,XCHBAD,XCHSQD,XCHTRD,XCHSCMQ4D,XCHLB11D,XCHBAR7PAD";
$script1_9mm='<div class="row"><div class="ss-complimentary-container"><div class="ss-recs-wrapper"><script type="searchspring/recommend" profile="wear-it-with-1-9mm">seed = ""</script></div></div></div>';
addLog("SKU's: ".$sku1_9mmString);
setScript($sku1_9mmString,$script1_9mm);




$sku1_4mmString="J2J1C16,J2J1C22,J2J3C76,JPE53SC31C32D,J2J1CH76,J2J2CH76,J2J2SC1C76D";
$script1_4mm='<div class="row"><div class="ss-complimentary-container"><div class="ss-recs-wrapper"><script type="searchspring/recommend" profile="wear-it-with">seed = ""</script></div></div></div>';
addLog("SKU's: ".$sku1_4mmString);
setScript($sku1_4mmString,$script1_4mm);