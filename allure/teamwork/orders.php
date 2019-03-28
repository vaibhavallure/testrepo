<?php
require_once('../../app/Mage.php');
umask(0);
Mage::app();
Mage::app()->setCurrentStore(0);
ini_set('memory_limit', '-1');


$csvFile = 'orders';

header('Content-Disposition: attachment; filename=' . $csvFile . '.csv');
header('Content-type: text/csv');
header('Pragma: no-cache');
header('Expires: 0');
$file = fopen('php://output', 'w');

fputcsv($file, array('id','Increment Id','Status','Is Invoiced','Store Id'));
$data = array();
$status = array();

$collection = Mage::getResourceModel("sales/order_grid_collection");
$collection->getSelect()->order('store_id ASC');
$collection->getSelect()->order('status DESC');

$deletedSkus=array("NIV2D|YELLOW GOLD|LEFT SIDE","NIV2D|YELLOW GOLD|RIGHT SIDE","NIV2D|YELLOW GOLD|STRAIGHT","NIV2D|ROSE GOLD|LEFT SIDE","NIV2D|ROSE GOLD|RIGHT SIDE","NIV2D|ROSE GOLD|STRAIGHT","NIV15D|ROSE GOLD|LEFT SIDE","NIV15D|ROSE GOLD|RIGHT SIDE","NIV15D|ROSE GOLD|STRAIGHT","NIV15D|YELLOW GOLD|LEFT SIDE","NIV15D|YELLOW GOLD|RIGHT SIDE","NIV15D|YELLOW GOLD|STRAIGHT","C3OPTRL_B|BLACK RHODIUM","C3OPTRL_B|ROSE GOLD","C3OPTRL_B|WHITE GOLD","C3OPTRL_B|YELLOW GOLD","C3OPTRL_C|BLACK RHODIUM","C3OPTRL_C|ROSE GOLD","C3OPTRL_C|WHITE GOLD","C3OPTRL_C|YELLOW GOLD","C3OPTRL_R|BLACK RHODIUM","C3OPTRL_R|ROSE GOLD","C3OPTRL_R|WHITE GOLD","C3OPTRL_R|YELLOW GOLD","CETH1695PE_D|ROSE GOLD","CETH1695PE_D|WHITE GOLD","CETH1695PE_D|YELLOW GOLD","CBAR11MD3|BLACK GOLD","CBAR11MD3|ROSE GOLD","CBAR11MD3|WHITE GOLD","CBAR11MD3|YELLOW GOLD","CBAR11MD3_B|BLACK GOLD","CBAR11MD3_B|ROSE GOLD","CBAR11MD3_B|WHITE GOLD","CBAR11MD3_B|YELLOW GOLD","CBAR11MD3_C|BLACK GOLD","CBAR11MD3_C|ROSE GOLD","CBAR11MD3_C|WHITE GOLD","CBAR11MD3_C|YELLOW GOLD","CBAR7MD3|BLACK GOLD","CBAR7MD3|ROSE GOLD","CBAR7MD3|WHITE GOLD","CBAR7MD3|YELLOW GOLD","CBAR7MD3_B|BLACK GOLD","CBAR7MD3_B|ROSE GOLD","CBAR7MD3_B|WHITE GOLD","CBAR7MD3_B|YELLOW GOLD","CBAR7MD3_C|BLACK GOLD","CBAR7MD3_C|ROSE GOLD","CBAR7MD3_C|WHITE GOLD","CBAR7MD3_C|YELLOW GOLD","CBAR7MD3_E|BLACK GOLD","CBAR7MD3_E|ROSE GOLD","CBAR7MD3_E|WHITE GOLD","CBAR7MD3_E|YELLOW GOLD","CBAR7MD3_R|BLACK GOLD","CBAR7MD3_R|ROSE GOLD","CBAR7MD3_R|WHITE GOLD","CBAR7MD3_R|YELLOW GOLD","CBAR7MD3_T|BLACK GOLD","CBAR7MD3_T|ROSE GOLD","CBAR7MD3_T|WHITE GOLD","CBAR7MD3_T|YELLOW GOLD","CBAR11MD3_E|BLACK GOLD","CBAR11MD3_E|ROSE GOLD","CBAR11MD3_E|WHITE GOLD","CBAR11MD3_E|YELLOW GOLD","CBAR11MD3_R|BLACK GOLD","CBAR11MD3_R|ROSE GOLD","CBAR11MD3_R|WHITE GOLD","CBAR11MD3_R|YELLOW GOLD","CBAR11MD3_T|BLACK GOLD","CBAR11MD3_T|ROSE GOLD","CBAR11MD3_T|WHITE GOLD","CBAR11MD3_T|YELLOW GOLD","CETMQSC65OP|BLACK GOLD","CETMQSC65OP|ROSE GOLD","CETMQSC65OP|WHITE GOLD","CETMQSC65OP|YELLOW GOLD","CETMQSC65OP_B|BLACK GOLD","CETMQSC65OP_B|ROSE GOLD","CETMQSC65OP_B|WHITE GOLD","CETMQSC65OP_B|YELLOW GOLD","CETMQSC65OP_E|BLACK GOLD","CETMQSC65OP_E|ROSE GOLD","CETMQSC65OP_E|WHITE GOLD","CETMQSC65OP_E|YELLOW GOLD","CETMQSC65OP_R|BLACK GOLD","CETMQSC65OP_R|ROSE GOLD","CETMQSC65OP_R|WHITE GOLD","CETMQSC65OP_R|YELLOW GOLD","CETMQSC65OP_T|BLACK GOLD","CETMQSC65OP_T|ROSE GOLD","CETMQSC65OP_T|WHITE GOLD","CETMQSC65OP_T|YELLOW GOLD","NIV2BD|ROSE GOLD|LEFT","NIV2BD|ROSE GOLD|RIGHT","NIV2BD|ROSE GOLD|STRAIGHT","BMTBS","C3BTRL|ROSE GOLD","C3BTRL|WHITE GOLD","C3BTRL|YELLOW GOLD","C3BTRL_B|ROSE GOLD","C3BTRL_B|WHITE GOLD","C3BTRL_B|YELLOW GOLD","C3BTRL_C|ROSE GOLD","C3BTRL_C|WHITE GOLD","C3BTRL_C|YELLOW GOLD","C3BTRL_R|ROSE GOLD","C3BTRL_R|WHITE GOLD","C3BTRL_R|YELLOW GOLD","C3BTRL_T|ROSE GOLD","C3BTRL_T|WHITE GOLD","C3BTRL_T|YELLOW GOLD","C3DTRDL|ROSE GOLD","C3DTRDL|WHITE GOLD","C3DTRDL|YELLOW GOLD","C3DTRDL_B|ROSE GOLD","C3DTRDL_B|WHITE GOLD","C3DTRDL_B|YELLOW GOLD","C3DTRDL_C|ROSE GOLD","C3DTRDL_C|WHITE GOLD","C3DTRDL_C|YELLOW GOLD","C3DTRDL_E|ROSE GOLD","C3DTRDL_E|WHITE GOLD","C3DTRDL_E|YELLOW GOLD","C3DTRDL_R|ROSE GOLD","C3DTRDL_R|WHITE GOLD","C3DTRDL_R|YELLOW GOLD","C3DTRDL_T|ROSE GOLD","C3DTRDL_T|WHITE GOLD","C3DTRDL_T|YELLOW GOLD","C3PAIV65D|BLACK GOLD","C3PAIV65D|ROSE GOLD","C3PAIV65D|WHITE GOLD","C3PAIV65D|YELLOW GOLD","C3PAIV65D_B|BLACK GOLD","C3PAIV65D_B|ROSE GOLD","C3PAIV65D_B|WHITE GOLD","C3PAIV65D_B|YELLOW GOLD","CDH8D_D|BLACK GOLD","CDH8D_D|ROSE GOLD","CDH8D_D|WHITE GOLD","CDH8D_D|YELLOW GOLD","CDSL22DOP|BLACK GOLD","CDSL22DOP|ROSE GOLD","CDSL22DOP|WHITE GOLD","CDSL22DOP|YELLOW GOLD","CDSL22DOP_B|BLACK GOLD","CDSL22DOP_B|ROSE GOLD","CDSL22DOP_B|WHITE GOLD","CDSL22DOP_B|YELLOW GOLD","CDSL22DOP_C|BLACK GOLD","CDSL22DOP_C|ROSE GOLD","CDSL22DOP_C|WHITE GOLD","CDSL22DOP_C|YELLOW GOLD","CETIVBA65D|BLACK RHODIUM","CETIVBA65D|ROSE GOLD","CETIVBA65D|WHITE GOLD","CETIVBA65D|YELLOW GOLD","CETIVBA65D_B|BLACK RHODIUM","CETIVBA65D_B|ROSE GOLD","CETIVBA65D_B|WHITE GOLD","CETIVBA65D_B|YELLOW GOLD","CETIVBA65D_R|BLACK RHODIUM","CETIVBA65D_R|ROSE GOLD","CETIVBA65D_R|WHITE GOLD","CETIVBA65D_R|YELLOW GOLD","CETIVBA65D_T|BLACK RHODIUM","CETIVBA65D_T|ROSE GOLD","CETIVBA65D_T|WHITE GOLD","CETIVBA65D_T|YELLOW GOLD","CETIVBA8D|BLACK RHODIUM","CETIVBA8D|ROSE GOLD","CETIVBA8D|WHITE GOLD","CETIVBA8D|YELLOW GOLD","CETIVBA8D_B|BLACK RHODIUM","CETIVBA8D_B|ROSE GOLD","CETIVBA8D_B|WHITE GOLD","CETIVBA8D_B|YELLOW GOLD","CETIVBA95D|BLACK RHODIUM","CETIVBA95D|ROSE GOLD","CETIVBA95D|WHITE GOLD","CETIVBA95D|YELLOW GOLD","CETIVBA95D_B|BLACK RHODIUM","CETIVBA95D_B|ROSE GOLD","CETIVBA95D_B|WHITE GOLD","CETIVBA95D_B|YELLOW GOLD","CETMQIV11D|BLACK GOLD","CETMQIV11D|ROSE GOLD","CETMQIV11D|WHITE GOLD","CETMQIV11D|YELLOW GOLD","CETMQIV11D_B|BLACK GOLD","CETMQIV11D_B|ROSE GOLD","CETMQIV11D_B|WHITE GOLD","CETMQIV11D_B|YELLOW GOLD","CETMQIV11D_C|BLACK GOLD","CETMQIV11D_C|ROSE GOLD","CETMQIV11D_C|WHITE GOLD","CETMQIV11D_C|YELLOW GOLD","CETMQIV65D|BLACK GOLD","CETMQIV65D|ROSE GOLD","CETMQIV65D|WHITE GOLD","CETMQIV65D|YELLOW GOLD","CETMQIV65D_B|BLACK GOLD","CETMQIV65D_B|ROSE GOLD","CETMQIV65D_B|WHITE GOLD","CETMQIV65D_B|YELLOW GOLD","CETMQIV65D_R|BLACK GOLD","CETMQIV65D_R|ROSE GOLD","CETMQIV65D_R|WHITE GOLD","CETMQIV65D_R|YELLOW GOLD","CETMQIV65D_T|BLACK GOLD","CETMQIV65D_T|ROSE GOLD","CETMQIV65D_T|WHITE GOLD","CETMQIV65D_T|YELLOW GOLD","CETMQIV8D|BLACK GOLD","CETMQIV8D|ROSE GOLD","CETMQIV8D|WHITE GOLD","CETMQIV8D|YELLOW GOLD","CETMQIV8D_B|BLACK GOLD","CETMQIV8D_B|ROSE GOLD","CETMQIV8D_B|WHITE GOLD","CETMQIV8D_B|YELLOW GOLD","CETMQIV95D|BLACK GOLD","CETMQIV95D|ROSE GOLD","CETMQIV95D|WHITE GOLD","CETMQIV95D|YELLOW GOLD","CETMQIV95D_B|BLACK GOLD","CETMQIV95D_B|ROSE GOLD","CETMQIV95D_B|WHITE GOLD","CETMQIV95D_B|YELLOW GOLD","CRCOBD95RB_B|BLACK GOLD|BLACK DIA HOOD","CRCOBD95RB_B|BLACK GOLD|OPAL HOOD","CRCOBD95RB_B|BLACK GOLD|WHITE DIA HOOD","CRCOBD95RB_B|ROSE GOLD|BLACK DIA HOOD","CRCOBD95RB_B|ROSE GOLD|OPAL HOOD","CRCOBD95RB_B|ROSE GOLD|WHITE DIA HOOD","CRCOBD95RB_B|WHITE GOLD|BLACK DIA HOOD","CRCOBD95RB_B|WHITE GOLD|OPAL HOOD","CRCOBD95RB_B|WHITE GOLD|WHITE DIA HOOD","CRCOBD95RB_B|YELLOW GOLD|BLACK DIA HOOD","CRCOBD95RB_B|YELLOW GOLD|OPAL HOOD","CRCOBD95RB_B|YELLOW GOLD|WHITE DIA HOOD","SAMPLERINGS","ZLOTEG615D|ROSE GOLD|14 GA THREAD|BACK","ZLOTEG615D|ROSE GOLD|14 GA THREAD|SIDE","ZLOTEG615D|ROSE GOLD|16 GA PUSHIN|BACK","ZLOTEG615D|ROSE GOLD|16 GA PUSHIN|SIDE","ZLOTEG615D|ROSE GOLD|18 GA PUSHIN|BACK","ZLOTEG615D|ROSE GOLD|18 GA PUSHIN|SIDE","ZLOTEG615D|ROSE GOLD|18G-16GA THREAD|BACK","ZLOTEG615D|ROSE GOLD|18G-16GA THREAD|SIDE","ZLOTEG615D|ROSE GOLD|19 GA TASH|BACK","ZLOTEG615D|ROSE GOLD|19 GA TASH|SIDE","ZLOTEG615D|WHITE GOLD|14 GA THREAD|BACK","ZLOTEG615D|WHITE GOLD|14 GA THREAD|SIDE","ZLOTEG615D|WHITE GOLD|16 GA PUSHIN|BACK","ZLOTEG615D|WHITE GOLD|16 GA PUSHIN|SIDE","ZLOTEG615D|WHITE GOLD|18 GA PUSHIN|BACK","ZLOTEG615D|WHITE GOLD|18 GA PUSHIN|SIDE","ZLOTEG615D|WHITE GOLD|18G-16GA THREAD|BACK","ZLOTEG615D|WHITE GOLD|18G-16GA THREAD|SIDE","ZLOTEG615D|WHITE GOLD|19 GA TASH|BACK","ZLOTEG615D|WHITE GOLD|19 GA TASH|SIDE","ZLOTEG615D|YELLOW GOLD|14 GA THREAD|BACK","ZLOTEG615D|YELLOW GOLD|14 GA THREAD|SIDE","ZLOTEG615D|YELLOW GOLD|16 GA PUSHIN|BACK","ZLOTEG615D|YELLOW GOLD|16 GA PUSHIN|SIDE","ZLOTEG615D|YELLOW GOLD|18 GA PUSHIN|BACK","ZLOTEG615D|YELLOW GOLD|18 GA PUSHIN|SIDE","ZLOTEG615D|YELLOW GOLD|18G-16GA THREAD|BACK","ZLOTEG615D|YELLOW GOLD|18G-16GA THREAD|SIDE","ZLOTEG615D|YELLOW GOLD|19 GA TASH|BACK","ZLOTEG615D|YELLOW GOLD|19 GA TASH|SIDE","ZLOTEGD625D|ROSE GOLD|14 GA THREAD|BACK","ZLOTEGD625D|ROSE GOLD|14 GA THREAD|SIDE","ZLOTEGD625D|ROSE GOLD|16 GA PUSHIN|BACK","ZLOTEGD625D|ROSE GOLD|16 GA PUSHIN|SIDE","ZLOTEGD625D|ROSE GOLD|18 GA PUSHIN|BACK","ZLOTEGD625D|ROSE GOLD|18 GA PUSHIN|SIDE","ZLOTEGD625D|ROSE GOLD|18G-16GA THREAD|BACK","ZLOTEGD625D|ROSE GOLD|18G-16GA THREAD|SIDE","ZLOTEGD625D|ROSE GOLD|19 GA TASH|BACK","ZLOTEGD625D|ROSE GOLD|19 GA TASH|SIDE","ZLOTEGD625D|WHITE GOLD|14 GA THREAD|BACK","ZLOTEGD625D|WHITE GOLD|14 GA THREAD|SIDE","ZLOTEGD625D|WHITE GOLD|16 GA PUSHIN|BACK","ZLOTEGD625D|WHITE GOLD|16 GA PUSHIN|SIDE","ZLOTEGD625D|WHITE GOLD|18 GA PUSHIN|BACK","ZLOTEGD625D|WHITE GOLD|18 GA PUSHIN|SIDE","ZLOTEGD625D|WHITE GOLD|18G-16GA THREAD|BACK","ZLOTEGD625D|WHITE GOLD|18G-16GA THREAD|SIDE","ZLOTEGD625D|WHITE GOLD|19 GA TASH|BACK","ZLOTEGD625D|WHITE GOLD|19 GA TASH|SIDE","ZLOTEGD625D|YELLOW GOLD|14 GA THREAD|BACK","ZLOTEGD625D|YELLOW GOLD|14 GA THREAD|SIDE","ZLOTEGD625D|YELLOW GOLD|16 GA PUSHIN|BACK","ZLOTEGD625D|YELLOW GOLD|16 GA PUSHIN|SIDE","ZLOTEGD625D|YELLOW GOLD|18 GA PUSHIN|BACK","ZLOTEGD625D|YELLOW GOLD|18 GA PUSHIN|SIDE","ZLOTEGD625D|YELLOW GOLD|18G-16GA THREAD|BACK","ZLOTEGD625D|YELLOW GOLD|18G-16GA THREAD|SIDE","ZLOTEGD625D|YELLOW GOLD|19 GA TASH|BACK","ZLOTEGD625D|YELLOW GOLD|19 GA TASH|SIDE","ZDG5D|ROSE GOLD|14 GA THREAD|LEFT","ZDG5D|ROSE GOLD|14 GA THREAD|RIGHT","ZDG5D|ROSE GOLD|16 GA PUSHIN|LEFT","ZDG5D|ROSE GOLD|16 GA PUSHIN|RIGHT","ZDG5D|ROSE GOLD|18 GA PUSHIN|LEFT","ZDG5D|ROSE GOLD|18 GA PUSHIN|RIGHT","ZDG5D|ROSE GOLD|18G-16GA THREAD|LEFT","ZDG5D|ROSE GOLD|18G-16GA THREAD|RIGHT","ZDG5D|ROSE GOLD|19 GA TASH|LEFT","ZDG5D|ROSE GOLD|19 GA TASH|RIGHT","ZDG5D|WHITE GOLD|14 GA THREAD|LEFT","ZDG5D|WHITE GOLD|14 GA THREAD|RIGHT","ZDG5D|WHITE GOLD|16 GA PUSHIN|LEFT","ZDG5D|WHITE GOLD|16 GA PUSHIN|RIGHT","ZDG5D|WHITE GOLD|18 GA PUSHIN|LEFT","ZDG5D|WHITE GOLD|18 GA PUSHIN|RIGHT","ZDG5D|WHITE GOLD|18G-16GA THREAD|LEFT","ZDG5D|WHITE GOLD|18G-16GA THREAD|RIGHT","ZDG5D|WHITE GOLD|19 GA TASH|LEFT","ZDG5D|WHITE GOLD|19 GA TASH|RIGHT","ZDG5D|YELLOW GOLD|14 GA THREAD|LEFT","ZDG5D|YELLOW GOLD|14 GA THREAD|RIGHT","ZDG5D|YELLOW GOLD|16 GA PUSHIN|LEFT","ZDG5D|YELLOW GOLD|16 GA PUSHIN|RIGHT","ZDG5D|YELLOW GOLD|18 GA PUSHIN|LEFT","ZDG5D|YELLOW GOLD|18 GA PUSHIN|RIGHT","ZDG5D|YELLOW GOLD|18G-16GA THREAD|LEFT","ZDG5D|YELLOW GOLD|18G-16GA THREAD|RIGHT","ZDG5D|YELLOW GOLD|19 GA TASH|LEFT","ZDG5D|YELLOW GOLD|19 GA TASH|RIGHT","X3DTRBKD|BLACK GOLD","ZSN10D|BLACK GOLD|14 GA THREAD|LEFT","ZSN10D|BLACK GOLD|14 GA THREAD|RIGHT","ZSN10D|BLACK GOLD|16 GA PUSHIN|LEFT","ZSN10D|BLACK GOLD|16 GA PUSHIN|RIGHT","ZSN10D|BLACK GOLD|18 GA PUSHIN|LEFT","ZSN10D|BLACK GOLD|18 GA PUSHIN|RIGHT","ZSN10D|BLACK GOLD|18G-16G THREAD|LEFT","ZSN10D|BLACK GOLD|18G-16G THREAD|RIGHT","ZSN10D|BLACK GOLD|19 GA TASH|LEFT","ZSN10D|BLACK GOLD|19 GA TASH|RIGHT","ZSN10D|ROSE GOLD|14 GA THREAD|LEFT","ZSN10D|ROSE GOLD|14 GA THREAD|RIGHT","ZSN10D|ROSE GOLD|16 GA PUSHIN|LEFT","ZSN10D|ROSE GOLD|16 GA PUSHIN|RIGHT","ZSN10D|ROSE GOLD|18 GA PUSHIN|LEFT","ZSN10D|ROSE GOLD|18 GA PUSHIN|RIGHT","ZSN10D|ROSE GOLD|18G-16G THREAD|LEFT","ZSN10D|ROSE GOLD|18G-16G THREAD|RIGHT","ZSN10D|ROSE GOLD|19 GA TASH|LEFT","ZSN10D|ROSE GOLD|19 GA TASH|RIGHT","ZSN10D|WHITE GOLD|14 GA THREAD|LEFT","ZSN10D|WHITE GOLD|14 GA THREAD|RIGHT","ZSN10D|WHITE GOLD|16 GA PUSHIN|LEFT","ZSN10D|WHITE GOLD|16 GA PUSHIN|RIGHT","ZSN10D|WHITE GOLD|18 GA PUSHIN|LEFT","ZSN10D|WHITE GOLD|18 GA PUSHIN|RIGHT","ZSN10D|WHITE GOLD|18G-16G THREAD|LEFT","ZSN10D|WHITE GOLD|18G-16G THREAD|RIGHT","ZSN10D|WHITE GOLD|19 GA TASH|LEFT","ZSN10D|WHITE GOLD|19 GA TASH|RIGHT","ZSN10D|YELLOW GOLD|14 GA THREAD|LEFT","ZSN10D|YELLOW GOLD|14 GA THREAD|RIGHT","ZSN10D|YELLOW GOLD|16 GA PUSHIN|LEFT","ZSN10D|YELLOW GOLD|16 GA PUSHIN|RIGHT","ZSN10D|YELLOW GOLD|18 GA PUSHIN|LEFT","ZSN10D|YELLOW GOLD|18 GA PUSHIN|RIGHT","ZSN10D|YELLOW GOLD|18G-16G THREAD|LEFT","ZSN10D|YELLOW GOLD|18G-16G THREAD|RIGHT","ZSN10D|YELLOW GOLD|19 GA TASH|LEFT","ZSN10D|YELLOW GOLD|19 GA TASH|RIGHT","X5PA8BKD2|BLACK","X5PA8BKD2|ROSE GOLD","X5PA8BKD2|WHITE GOLD","X5PA8BKD2|YELLOW GOLD","SRET14|BLACK","SRET14|ROSE GOLD","SRET14|WHITE GOLD","SRET14|YELLOW GOLD","SRET16|BLACK","SRET16|ROSE GOLD","SRET16|WHITE GOLD","SRET16|YELLOW GOLD","SRET18|BLACK","SRET18|ROSE GOLD","SRET18|WHITE GOLD","SRET18|YELLOW GOLD","C3OPTRL_B|BLACK GOLD","C3OPTRL_C|BLACK GOLD","C3OPTRL_R|BLACK GOLD","ESC3D|BLACK GOLD","ZLOTEG615D|BLACK GOLD|18G-16GA THREAD|SIDE","ZLOTEG615D|BLACK GOLD|18 GA PUSHIN|BACK","ZLOTEG615D|BLACK GOLD|19 GA TASH|SIDE","ZLOTEG615D|BLACK GOLD|16 GA PUSHIN|SIDE","ZLOTEG615D|BLACK GOLD|19 GA TASH|BACK","ZLOTEG615D|BLACK GOLD|14 GA THREAD|BACK","ZLOTEG615D|BLACK GOLD|18G-16GA THREAD|BACK","ZLOTEG615D|BLACK GOLD|16 GA PUSHIN|BACK","ZLOTEG615D|BLACK GOLD|14 GA THREAD|SIDE","ZLOTEG615D|BLACK GOLD|18 GA PUSHIN|SIDE","ZLOTEGD625D|BLACK GOLD|16 GA PUSHIN|BACK","ZLOTEGD625D|BLACK GOLD|16 GA PUSHIN|SIDE","ZLOTEGD625D|BLACK GOLD|18G-16GA THREAD|BACK","ZLOTEGD625D|BLACK GOLD|18 GA PUSHIN|SIDE","ZLOTEGD625D|BLACK GOLD|14 GA THREAD|BACK","ZLOTEGD625D|BLACK GOLD|19 GA TASH|BACK","ZLOTEGD625D|BLACK GOLD|14 GA THREAD|SIDE","ZLOTEGD625D|BLACK GOLD|18G-16GA THREAD|SIDE","ZLOTEGD625D|BLACK GOLD|19 GA TASH|SIDE","ZLOTEGD625D|BLACK GOLD|18 GA PUSHIN|BACK","ZFLD75OP|BLACK GOLD|19 GA TASH|SIDE","ZFLD75OP|BLACK GOLD|14 GA THREAD|SIDE","ZFLD75OP|BLACK GOLD|18G-16GA THREAD|SIDE","ZFLD75OP|BLACK GOLD|19 GA TASH|BACK","ZFLD75OP|BLACK GOLD|18 GA PUSHIN|BACK","ZFLD75OP|BLACK GOLD|18G-16GA THREAD|BACK","ZFLD75OP|BLACK GOLD|16 GA PUSHIN|SIDE","ZFLD75OP|BLACK GOLD|18 GA PUSHIN|SIDE","ZFLD75OP|BLACK GOLD|14 GA THREAD|BACK","ZFLD75OP|BLACK GOLD|16 GA PUSHIN|BACK","ZSNEG10RB|ROSE GOLD|LEFT|14 GA THREAD","ZSNEG10RB|ROSE GOLD|LEFT|18G-16GA THREAD","ZSNEG10RB|WHITE GOLD|LEFT|16 GA PUSHIN","ZSNEG10RB|YELLOW GOLD|LEFT|18G-16GA THREAD","ZSNEG10RB|WHITE GOLD|RIGHT|18 GA PUSHIN","ZSNEG10RB|YELLOW GOLD|LEFT|14 GA THREAD","ZSNEG10RB|BLACK GOLD|RIGHT|19 GA THREAD","ZSNEG10RB|BLACK GOLD|RIGHT|18G-16GA THREAD","ZSNEG10RB|ROSE GOLD|RIGHT|19 GA THREAD","ZSNEG10RB|ROSE GOLD|LEFT|19 GA THREAD","ZSNEG10RB|WHITE GOLD|RIGHT|14 GA THREAD","ZSNEG10RB|YELLOW GOLD|RIGHT|14 GA THREAD","ZSNEG10RB|BLACK GOLD|LEFT|18 GA PUSHIN","ZSNEG10RB|BLACK GOLD|LEFT|18G-16GA THREAD","ZSNEG10RB|YELLOW GOLD|RIGHT|19 GA THREAD","ZSNEG10RB|YELLOW GOLD|RIGHT|18G-16GA THREAD","ZSNEG10RB|WHITE GOLD|RIGHT|18G-16GA THREAD","ZSNEG10RB|YELLOW GOLD|RIGHT|18 GA PUSHIN","ZSNEG10RB|ROSE GOLD|LEFT|18 GA PUSHIN","ZSNEG10RB|BLACK GOLD|LEFT|14 GA THREAD","ZSNEG10RB|YELLOW GOLD|RIGHT|16 GA PUSHIN","ZSNEG10RB|ROSE GOLD|LEFT|16 GA PUSHIN","ZSNEG10RB|WHITE GOLD|RIGHT|19 GA THREAD","ZSNEG10RB|ROSE GOLD|RIGHT|14 GA THREAD","ZSNEG10RB|BLACK GOLD|LEFT|16 GA PUSHIN","ZSNEG10RB|YELLOW GOLD|LEFT|18 GA PUSHIN","ZSNEG10RB|BLACK GOLD|RIGHT|14 GA THREAD","ZSNEG10RB|WHITE GOLD|LEFT|14 GA THREAD","ZSNEG10RB|BLACK GOLD|RIGHT|16 GA PUSHIN","ZSNEG10RB|ROSE GOLD|RIGHT|18 GA PUSHIN","ZSNEG10RB|WHITE GOLD|LEFT|18G-16GA THREAD","ZSNEG10RB|WHITE GOLD|LEFT|18 GA PUSHIN","ZSNEG10RB|ROSE GOLD|RIGHT|18G-16GA THREAD","ZSNEG10RB|ROSE GOLD|RIGHT|16 GA PUSHIN","ZSNEG10RB|YELLOW GOLD|LEFT|19 GA THREAD","ZSNEG10RB|BLACK GOLD|RIGHT|18 GA PUSHIN","ZSNEG10RB|BLACK GOLD|LEFT|19 GA THREAD","ZSNEG10RB|WHITE GOLD|LEFT|19 GA THREAD","ZSNEG10RB|WHITE GOLD|RIGHT|16 GA PUSHIN","ZSNEG10RB|YELLOW GOLD|LEFT|16 GA PUSHIN","ZSNEG5BKD|WHITE GOLD|LEFT|18 GA PUSHIN","ZSNEG5BKD|WHITE GOLD|RIGHT|19 GA THREAD","ZSNEG5BKD|BLACK GOLD|RIGHT|16 GA PUSHIN","ZSNEG5BKD|YELLOW GOLD|RIGHT|16 GA PUSHIN","ZSNEG5BKD|ROSE GOLD|RIGHT|18G-16GA THREAD","ZSNEG5BKD|YELLOW GOLD|LEFT|14 GA THREAD","ZSNEG5BKD|WHITE GOLD|LEFT|19 GA THREAD","ZSNEG5BKD|ROSE GOLD|LEFT|16 GA PUSHIN","ZSNEG5BKD|WHITE GOLD|LEFT|16 GA PUSHIN","ZSNEG5BKD|ROSE GOLD|RIGHT|19 GA THREAD","ZSNEG5BKD|BLACK GOLD|LEFT|18 GA PUSHIN","ZSNEG5BKD|BLACK GOLD|LEFT|18G-16GA THREAD","ZSNEG5BKD|BLACK GOLD|LEFT|16 GA PUSHIN","ZSNEG5BKD|ROSE GOLD|RIGHT|18 GA PUSHIN","ZSNEG5BKD|BLACK GOLD|LEFT|14 GA THREAD","ZSNEG5BKD|YELLOW GOLD|LEFT|16 GA PUSHIN","ZSNEG5BKD|YELLOW GOLD|LEFT|19 GA THREAD","ZSNEG5BKD|WHITE GOLD|RIGHT|18 GA PUSHIN","ZSNEG5BKD|ROSE GOLD|RIGHT|14 GA THREAD","ZSNEG5BKD|YELLOW GOLD|RIGHT|19 GA THREAD","ZSNEG5BKD|WHITE GOLD|LEFT|14 GA THREAD","ZSNEG5BKD|ROSE GOLD|LEFT|18 GA PUSHIN","ZSNEG5BKD|YELLOW GOLD|RIGHT|18 GA PUSHIN","ZSNEG5BKD|BLACK GOLD|LEFT|19 GA THREAD","ZSNEG5BKD|WHITE GOLD|RIGHT|16 GA PUSHIN","ZSNEG5BKD|BLACK GOLD|RIGHT|18G-16GA THREAD","ZSNEG5BKD|WHITE GOLD|LEFT|18G-16GA THREAD","ZSNEG5BKD|ROSE GOLD|LEFT|18G-16GA THREAD","ZSNEG5BKD|BLACK GOLD|RIGHT|19 GA THREAD","ZSNEG5BKD|YELLOW GOLD|RIGHT|18G-16GA THREAD","ZSNEG5BKD|YELLOW GOLD|LEFT|18G-16GA THREAD","ZSNEG5BKD|ROSE GOLD|RIGHT|16 GA PUSHIN","ZSNEG5BKD|BLACK GOLD|RIGHT|14 GA THREAD","ZSNEG5BKD|ROSE GOLD|LEFT|14 GA THREAD","ZSNEG5BKD|YELLOW GOLD|LEFT|18 GA PUSHIN","ZSNEG5BKD|WHITE GOLD|RIGHT|14 GA THREAD","ZSNEG5BKD|WHITE GOLD|RIGHT|18G-16GA THREAD","ZSNEG5BKD|ROSE GOLD|LEFT|19 GA THREAD","ZSNEG5BKD|BLACK GOLD|RIGHT|18 GA PUSHIN","ZSNEG5BKD|YELLOW GOLD|RIGHT|14 GA THREAD");

foreach ($collection as $order){
    $order=Mage::getModel('sales/order')->load($order->getId());
    $flag=FALSE;
    foreach ($order->getAllItems() as $item) {
        if(in_array($item->getSku(), $deletedSkus)){
            $flag=TRUE;
            break;
        }
    }
    if($flag)
        continue;
    $invoiceIds = $order->getInvoiceCollection()->getAllIds();
    $invoiceFlag="No";
    if(count($invoiceIds) >=1){
        $invoiceFlag="Yes";
    }
    if($order->getStoreId()==1){
         $data[]=array($order->getId(),$order->getIncrementId(),$order->getStatus(),$invoiceFlag,$order->getStoreId());
    }
}
foreach ($data as $row)
{
    fputcsv($file, $row);
}

exit();
