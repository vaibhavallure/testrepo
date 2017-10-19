<?php 
require_once('../../app/Mage.php');
umask(0);
Mage::app();
$app = Mage::app();
Mage::app()->setCurrentStore(0);

echo "<pre>";

$fixedItems = array("CCLV7DPS_C","CCLV7DPS_R","XCLVD","CCLVD","CCLVD_B","CCLVD_C","CCLVD_E","CCLVD_R","CCLVD_T","XCOBRAD","CCOBRAD","CCOBRAD_B","CCOBRAD_C","CCOBRAD_E","CCOBRAD_R","CCOBRAD_T","XDAL","CDAL","CDAL_B","CDAL_R","XDELD","CDELD","CDELD_B","CDELD_C","CDELD_E","CDELD_R","CDELD_T","XDELDBS","CDELDBS","CDELDBS_B","CDELDBS_C","CDELDBS_E","CDELDBS_R","CDELDBS_T","XDELDPS","CDELDPS","CDELDPS_B","CDELDPS_C","CDELDPS_E","CDELDPS_R","CDELDPS_T","XDSL22DBS","CDSL22DBS_B","XFL3D","CFL3D","CFL3D_B","CFL3D_C","CFL3D_E","CFL3D_R","CFL3D_T","XFL45BD","CFL45BD","CFL45BD_B","CFL45BD_C","CFL45BD_E","CFL45BD_R","CFL45BD_T","XFL45BKD","CFL45BKD","CFL45BKD_B","CFL45BKD_C","CFL45BKD_E","CFL45BKD_R","CFL45BKD_T","XFL45CZ","CFL45CZ","CFL45CZ_B","CFL45CZ_C","CFL45CZ_E","CFL45CZ_R","CFL45CZ_T","XFL45D","CFL45D","CFL45D_B","CFL45D_C","CFL45D_E","CFL45D_R","CFL45D_T","XFL45PD","CFL45PD","CFL45PD_B","CFL45PD_C","CFL45PD_E","CFL45PD_R","CFL45PD_T","XFL55BD","CFL55BD","CFL55BD_B","CFL55BD_C","CFL55BD_E","CFL55BD_R","CFL55BD_T","XFL55BKD","CFL55BKD","CFL55BKD_B","CFL55BKD_C","CFL55BKD_E","CFL55BKD_R","CFL55BKD_T","XFL55CZ","CFL55CZ","CFL55CZ_B","CFL55CZ_C","CFL55CZ_E","CFL55CZ_R","CFL55CZ_T","XFL55D","CFL55D","CFL55D_B","CFL55D_C","CFL55D_E","CFL55D_R","X2TQ","C2TQ","X3CZ","C3CZ","C3CZ_B","C3CZ_C","C3CZ_E","C3CZ_R","C3CZ_T","X3DTRD","C3DTRD","C3DTRD_B","C3DTRD_C","C3DTRD_E","C3DTRD_R","C3DTRD_T","X3OP","C3OP","X3TQ","Z3TQ","X4BTR","C4BTR","C4BTR_B","C4BTR_C","C4BTR_E","C4BTR_R","C4BTR_T","X4BTRD","C4BTRD","C4BTRD_B","C4BTRD_C","C4BTRD_E","C4BTRD_R","C4BTRD_T","X4BTROP","C4BTROP","C4BTROP_B","C4BTROP_C","C4BTROP_E","C4BTROP_M","C4BTROP_R","C4BTROP_T","X4BTRTQ","X4DTRD","C4DTRD","C4DTRD_B","C4DTRD_C","C4DTRD_E","C4DTRD_R","CFL55D_T","XFL55PD","CFL55PD","CFL55PD_B","CFL55PD_C","CFL55PD_E","CFL55PD_R","CFL55PD_T","XFL55PKD","CFL55PKD","CFL55PKD_B","CFL55PKD_C","CFL55PKD_E","CFL55PKD_R","CFL55PKD_T","XFL7CZ","CFL7CZ","CFL7CZ_B","CFL7CZ_C","CFL7CZ_R","XFL7D","CFL7D","CFL7D_B","CFL7D_C","CFL7D_R","XFL7DPE","CFL7DPE","CFL7DPE_B","CFL7DPE_C","CFL7DPE_E","CFL7DPE_R","CFL7DPE_T","XFLD45D","CFLD45D_B","CFLD45D_C","CFLD45D_E","CFLD45D_R","CFLD45D_T","XFLD55D","CFLD55D","CFLD55D_B","CFLD55D_C","CFLD55D_E","CFLD55D_R","CFLD55D_T","XFLP55BKD","CFLP55BKD","CFLP55BKD_B","CFLP55BKD_C","CFLP55BKD_E","CFLP55BKD_R","CFLP55BKD_T","XFLP55D","CFLP55D","CFLP55D_B","CFLP55D_C","CFLP55D_E","CFLP55D_R","CFLP55D_T","XFLP55DRB","CFLP55DRB","CFLP55DRB_B","CFLP55DRB_C","CFLP55DRB_E","CFLP55DRB_R","CFLP55DRB_T","XFLP55OP","CFLP55OP","CFLP55OP_B","CFLP55OP_C","CFLP55OP_E","CFLP55OP_R","CFLP55OP_T","XFLP8BKD","CFLP8BKD","CFLP8BKD_B","CFLP8BKD_C","CFLP8BKD_R","XFLP8D","CFLP8D","CFLP8D_B","CFLP8D_C","CFLP8D_R","XFLP8DRB","CFLP8DRB","CFLP8DRB_B","CFLP8DRB_C","CFLP8DRB_R","XFLP8OP","CFLP8OP","CFLP8OP_B","CFLP8OP_C","CFLP8OP_R","XFLPED","CFLPED","CFLPED_B","CFLPED_C","CFLPED_R","XG3BS","CG3BS","CG3BS_B","CG3BS_C","CG3BS_R","XG3CZ","CG3CZ","CG3CZ_B","CG3CZ_C","CG3CZ_R","XG3OP","CG3OP","CG3OP_B","CG3OP_C","CG3OP_R","XG3PS","CG3PS","CG3PS_B","CG3PS_C","CG3PS_R","XG3RB","CG3RB","CG3RB_B","CG3RB_C","CG3RB_R","CG3TQ_B","CG3TQ_C","CG3TQ_R","XG3TQ","CG3TQ","XHEART","CHEART","CHEART_B","CHEART_C","CHEART_E","CHEART_R","CHEART_T","XIV15BD","CIV15BD","CIV15BD_B","CIV15BD_C","CIV15BD_E","CIV15BD_R","CIV15BD_T","XIV15D","CIV15D","CIV15D_B","CIV15D_C","CIV15D_E","CIV15D_R","CIV15D_T","XIV25D","CIV25D","CIV25D_B","CIV25D_C","CIV25D_E","CIV25D_R","CIV25D_T","XIV2BD","CIV2BD","CIV2BD_B","CIV2BD_C","CIV2BD_E","CIV2BD_R","CIV2BD_T","XIV2DTEST","CIV2DTEST","CIV2DTEST_B","CIV2DTEST_C","CIV2DTEST_E","CIV2DTEST_R","CIV2DTEST_T","XIV2PKD","CIV2PKD","CIV2PKD_B","CIV2PKD_C","CIV2PKD_E","CIV2PKD_R","CIV2PKD_T","XIV35D","CIV35D","CIV35D_B","CIV35D_C","CIV35D_R","CIV35D_T","XIV38D","CIV38D","CIV38D_B","CIV38D_C","CIV38D_E","CIV38D_R","CIV38D_T","XIV3BD","CIV3BD","CIV3BD_B","CIV3BD_C","CIV3BD_R","CIV3BD_T","XIV3D","CIV3D","CIV3D_B","CIV3D_C","CIV3D_E","CIV3D_R","CIV3D_T","XIV3PKD","CIV3PKD","CIV3PKD_B","CIV3PKD_C","CIV3PKD_E","CIV3PKD_R","CIV3PKD_T","XIV47D","CIV47D","CIV47D_B","CIV47D_C","CIV47D_E","CIV47D_R","CIV47D_T","XIV5D","CIV5D","CIV5D_B","CIV5D_C","CIV5D_E","CIV5D_R","CIV5D_T","XIVD215D","CIVD215D","CIVD215D_B","CIVD215D_C","CIVD215D_E","CIVD215D_R","CIVD215D_T","XIVD32D","CIVD32D","CIVD32D_B","CIVD32D_C","CIVD32D_E","CIVD32D_R","CIVD32D_T","XIVFL85D","CIVFL85D","CIVFL85D_B","CIVFL85D_C","CIVFL85D_R","XIVU65D","CIVU65D","CIVU65D_B","CIVU65D_C","CIVU65D_T","XLB11BKD","CLB11BKD","CLB11BKD_B","CLB11BKD_E","CLB11BKD_R","CLB11BKD_T","XLB11D","CLB11D","CLB11D_B","CLB11D_C","CLB11D_E","CLB11D_R","CLB11D_T","XMNPAD","CMNPAD","CMNPAD_B","CMNPAD_C","CMNPAD_E","CMNPAD_R","CMNPAD_T","XMQ725D","CMQ725D","CMQ725D_B","CMQ725D_C","CMQ725D_M","CMQ725D_R","XMTTD","CMTTD","CMTTD_B","CMTTD_C","CMTTD_R","CMTTD_T","XMTTDOP","CMTTDOP","CMTTDOP_B","CMTTDOP_C","CMTTDOP_E","CMTTDOP_R","CMTTDOP_T","XMTTDPS","CMTTDPS","CMTTDPS_B","CMTTDPS_C","CMTTDPS_E","CMTTDPS_R","CMTTDPS_T","XP3CZ","CP3CZ","CP3CZ_B","CP3CZ_C","CP3CZ_E","CP3CZ_R","CP3CZ_T","XPACD","CPACD","CPACD_B","CPACD_C","CPACD_E","CPACD_R","CPACD_T","XPAISLD","CPAISLD","CPAISLD_B","CPAISLD_C","CPAISLD_E","CPAISLD_R","CPAISLD_T","XPAISRD","CPAISRD","CPAISRD_B","CPAISRD_C","CPAISRD_E","CPAISRD_R","CPAISRD_T","XPE625D","CPE625D","CPE625D_B","CPE625D_C","CPE625D_M","CPE625D_R","XPE725D","CPE725D_B","CPE725D_C","CPE725D_R","XPEAR","CPEAR","CPEAR_B","CPEAR_C","CPEAR_E","CPEAR_R","CPEAR_T","XSC12D","CSC12D","CSC12D_B","CSC12D_E","CSC12D_R","CSC12D_T","XSC15BD","CSC15BD","CSC15BD_B","CSC15BD_C","CSC15BD_E","CSC15BD_R","CSC15BD_T","XSC15BKD","CSC15BKD","CSC15BKD_B","CSC15BKD_C","CSC15BKD_E","CSC15BKD_R","CSC15BKD_T","XSC15D","CSC15D","CSC15D_B","CSC15D_C","CSC15D_E","CSC15D_R","CSC15D_T","XSC15PD","CSC15PD","CSC15PD_B","CSC15PD_E","CSC15PD_M","CSC15PD_R","CSC15PD_T","XSC15PKD","CSC15PKD","CSC15PKD_B","CSC15PKD_C","CSC15PKD_E","CSC15PKD_R","CSC15PKD_T","XSC25D","CSC25D","CSC25D_B","CSC25D_C","CSC25D_E","CSC25D_R","CSC25D_T","XSC2BD","CSC2BD","CSC2BD_B","CSC2BD_C","CSC2BD_E","CSC2BD_R","CSC2BD_T","XSC2BKD","CSC2BKD","CSC2BKD_B","CSC2BKD_C","CSC2BKD_E","CSC2BKD_R","CSC2BKD_T","XSC2D","CSC2D","CSC2D_B","CSC2D_C","CSC2D_E","CSC2D_R","CSC2D_T","XSC2PD","CSC2PD","CSC2PD_B","CSC2PD_C","CSC2PD_E","CSC2PD_R","CSC2PD_T","XSC3BD","CSC3BD","CSC3BD_B","CSC3BD_C","CSC3BD_R","CSC3BD_T","XSC3BKD","CSC3BKD","CSC3BKD_B","CSC3BKD_C","CSC3BKD_R","CSC3BKD_T","XSC3D","CSC3D","CSC3D_B","CSC3D_C","CSC3D_R","CSC3D_T","XSC3PKD","CSC3PKD","CSC3PKD_B","CSC3PKD_C","CSC3PKD_T","XSCD32D","CSCD32D","CSCD32D_B","CSCD32D_C","CSCD32D_R","CSCD32D_T","XSCEG3CZ","CSCEG3CZ","CSCEG3CZ_B","CSCEG3CZ_C","CSCEG3CZ_E","CSCEG3CZ_R","CSCEG3CZ_T","XSCEG4CZ","CSCEG4CZ","CSCEG4CZ_B","CSCEG4CZ_C","CSCEG4CZ_R","XSCMQ4D","CSCMQ4D","CSCMQ4D_B","CSCMQ4D_C","CSCMQ4D_E","CSCMQ4D_M","CSCMQ4D_R","CSCMQ4D_T","XSCRB","CSCRB","CSCRB_B","CSCRB_C","XSHSTAR","CSHSTAR","CSHSTAR_B","CSHSTAR_C","CSHSTAR_R","CSHSTAR_T","XSK4BKDM","CSK4BKDM","CSK4BKDM_B","XSK4RB","CSK4RB","CSK4RB_B","XSK5BKDM","CSK5BKDM","CSK5BKDM_B","XSK5DM","CSK5DM","CSK5DM_B","XSK65BKDM","CSK65BKDM","CSK65BKDM_B","XSK65OPM","CSK65OPM","CSK65OPM_B","XSP3525","CSP3525","CSP3525_B","CSP3525_C","CSP3525_E","CSP3525_R","CSP3525_T","XSQ3BTRD","CSQ3BTRD","CSQ3BTRD_B","CSQ3BTRD_C","CSQ3BTRD_E","CSQ3BTRD_R","CSQ3BTRD_T","XSQ4DTRD","CSQ3DTRD","CSQ3DTRD_B","CSQ3DTRD_C","CSQ3DTRD_E","CSQ3DTRD_R","CSQ3DTRD_T","XSQGBS","CSQGBS","CSQGBS_B","CSQGBS_C","CSQGBS_E","CSQGBS_R","CSQGBS_T","XSQGPS","CSQGPS","CSQGPS_B","CSQGPS_C","CSQGPS_E","CSQGPS_R","CSQGPS_T","XSTAR","CSTAR","CSTAR_B","CSTAR_C","CSTAR_E","CSTAR_R","CSTAR_T","XSTAR45D","CSTAR45D","CSTAR45D_B","CSTAR45D_C","CSTAR45D_E","CSTAR45D_R","CSTAR45D_T","XSTAR55D","CSTAR55D","CSTAR55D_B","CSTAR55D_C","CSTAR55D_R","XSTAR7D","CSTAR7D","CSTAR7D_B","CSTAR7D_C","CSTAR7D_R","XSTARD","CSTARD","CSTARD_B","CSTARD_C","CSTARD_E","CSTARD_R","CSTARD_T","XSWCD","CSWCD","CSWCD_B","CSWCD_C","XURPA4D","CURPA4D","CURPA4D_B","CURPA4D_C","CURPA4D_E","CURPA4D_R","CURPA4D_T","XURPA5D","CURPA5D","CURPA5D_B","CURPA5D_C","CURPA5D_E","CURPA5D_R","CURPA5D_T","XURPA5DOP","CURPA5DOP","CURPA5DOP_B","CURPA5DOP_C","CURPA5DOP_E","CURPA5DOP_R","CURPA5DOP_T","XURPAPRS5BKD","CURPAPRS5BKD","CURPAPRS5BKD_B","CURPAPRS5BKD_C","CURPAPRS5BKD_E","CURPAPRS5BKD_R","CURPAPRS5BKD_T","XXMARK","CXMARK","CXMARK_B","CXMARK_C","CXMARK_E","CXMARK_R","CXMARK_T","C4DTRD_T","X4OP","C4OP","C4OP_B","C4OP_C","C4OP_E","C4OP_R","C4OP_T","X5IVD","C5IVD","C5IVD_B","C5IVD_C","C5IVD_R","X5IVD2","Z5IVD2","XAJN","CAJN","CAJN_B","CAJN_E","CAJN_R","CAJN_T","XAJN3B","CAJN3B","CAJN3B_B","CAJN3B_C","CAJN3B_E","CAJN3B_R","CAJN3B_T","XAJNDPE","CAJNDPE","CAJNDPE_B","CAJNDPE_C","CAJNDPE_E","CAJNDPE_R","CAJNDPE_T","XAR10D","CAR10D","CAR10D_B","CAR10D_C","CAR10D_E","CAR10D_R","CAR10D_T","ZAR10D","XAR75D","ZAR75D","XB15","CB15","XB25","CB25","CB25_B","CB25_C","CB25_E","CB25_R","CB25_T","XB3","CB3","CB3_B","CB3_C","CB3_E","CB3_R","CB3_T","XBAD","CBAD","CBAD_B","CBAD_C","CBAD_E","CBAD_R","CBAD_T","XBAR11","CBAR11","CBAR11_B","CBAR11_C","CBAR11_R","XBAR11M","CBAR11M","CBAR11M_B","CBAR11M_C","CBAR11M_R","XBAR18M","CBAR18M","CBAR18M_B","CBAR18M_C","CBAR18M_R","XBAR7","CBAR7","CBAR7_B","CBAR7_C","CBAR7_E","CBAR7_R","CBAR7_T","XBARR11","CBARR11","CBARR11_B","CBARR11_C","CBARR11_R","XBARR11DM","CBARR11DM","CBARR11DM_B","CBARR11DM_C","CBARR11DM_E","CBARR11DM_R","CBARR11DM_T","XBARR7M","CBARR7M","CBARR7M_B","CBARR7M_C","CBARR7M_E","CBARR7M_R","CBARR7M_T","XBAT10D","CBAT10D","CBAT10D_B","CBAT10D_C","CBAT10D_R","ZBAT10D","XBAT75BKD","CBAT75BKD","XBAT75D","CBAT75D","CBAT75D_B","CBAT75D_C","CBAT75D_E","CBAT75D_R","CBAT75D_T","XBAT75RB","CBAT75RB","CBAT75RB_B","CBAT75RB_C","CBAT75RB_E","CBAT75RB_R","CBAT75RB_T","XBF","CBF","CBF_B","CBF_C","CBF_E","CBF_R","CBF_T","XBO7D","CBO7D","CBO7D_B","CBO7D_C","CBO7D_E","CBO7D_R","CBO7D_T","ZBO7D","XBO9D","CBO9D","CBO9D_B","CBO9D_C","CBO9D_R","ZBO9D","XBRKBS","CBRKBS","CBRKBS_B","CBRKBS_C","CBRKBS_E","CBRKBS_R","CBRKBS_T","XBRKCZ","CBRKCZ","CBRKCZ_B","CBRKCZ_C","CBRKCZ_E","CBRKCZ_R","CBRKCZ_T","XBRKPS","CBRKPS","CBRKPS_B","CBRKPS_C","CBRKPS_E","CBRKPS_R","CBRKPS_T","XBRKRB","CBRKRB","CBRKRB_B","CBRKRB_C","CBRKRB_E","CBRKRB_R","CBRKRB_T","ZBRKRB","XBRT3BS","CBRT3BS","CBRT3BS_B","CBRT3BS_C","CBRT3BS_R","XBRT3CZ","CBRT3CZ","CBRT3CZ_B","CBRT3CZ_C","CBRT3CZ_R","XBRT3OP","CBRT3OP","CBRT3OP_B","CBRT3OP_C","CBRT3OP_R","XBRT3PS","CBRT3PS","CBRT3PS_B","CBRT3PS_C","CBRT3PS_R","XBRT3RB","CBRT3RB","CBRT3RB_B","CBRT3RB_C","CBRT3RB_R","XBRT3TQ","CBRT3TQ","CBRT3TQ_B","CBRT3TQ_C","CBRT3TQ_R","XBS18DBS","CBS18DBS_B","XBS18DOP","CBS18DOP_B","XBY4D","CBY4D","CBY4D_B","CBY4D_E","CBY4D_R","CBY4D_T","XBYMQCZ","CBYMQCZ","CBYMQCZ_B","CBYMQCZ_C","CBYMQCZ_R","XBYMQD","CBYMQD","CBYMQD_B","CBYMQD_C","CBYMQD_R","XC3FLD","CC3FLD","CC3FLD_B","CC3FLD_C","CC3FLD_R","XC3FLOP","CC3FLOP","CC3FLOP_B","CC3FLOP_C","CC3FLOP_R","XC3STD","CC3STD","CC3STD_B","CC3STD_C","CC3STD_E","CC3STD_R","XCFL5D","CCFL5D","CCFL5D_B","CCFL5D_C","CCFL5D_R","XCLV55DBS","CCLV55DBS","CCLV55DBS_B","CCLV55DBS_C","CCLV55DBS_R","XCLV55DPS","CCLV55DPS","CCLV55DPS_B","CCLV55DPS_C","CCLV55DPS_R","XCLV7DBS","CCLV7DBS","CCLV7DBS_B","CCLV7DBS_C","CCLV7DBS_R","XCLV7DPS","CCLV7DPS","CCLV7DPS_B","X2OP","C2OP","C2OP_B","C2OP_C","C2OP_E","C2OP_R","C2OP_T","C3OP_B","C3OP_C","C3OP_E","C3OP_R","C3OP_T","X2PE","C2PE");

//$fixedItems = array("CSQGPS_E");//CSQGPS_E XCLVD

$skuByProductId = array();

$customPostLengthOptions = array();
$inventoryUpdates = array();

$postLenths = array();

$skuByProductIdFile = Mage::getBaseDir('var').'/export/skuByProductId.json';
$customPostLengthOptionsFile = Mage::getBaseDir('var').'/export/customPostLengthOptions.json';
$inventoryUpdatesFile = Mage::getBaseDir('var').'/export/inventoryUpdates.json';
$postLenthsFile = Mage::getBaseDir('var').'/export/postLenths.json';


$firstTime = false;


if (!file_exists($skuByProductIdFile)) {
	$firstTime = true;
}

if ($firstTime) {

	foreach ($fixedItems as $fixedSku) {
		Mage::log('Parsing Fixed SKU:: '.$fixedSku, Zend_Log::DEBUG, 'parent_child_migrations_parsing.log', true);
		var_dump("Fixed SKU: ".$fixedSku);
		$productCollection = Mage::getModel('catalog/product')->getCollection()
			->addAttributeToFilter('type_id', 'simple')
			->addAttributeToFilter('sku', array ('like'=> $fixedSku.'|%'))
			->load();

		foreach ($productCollection  as $product) {

			$oldItem = $product->getSku();

			Mage::log('Found Simple SKU :: '.$oldItem, Zend_Log::DEBUG, 'parent_child_migrations_parsing.log', true);
			var_dump("Found Simple SKU: ".$oldItem);

			$oldItemSku = explode('|', $oldItem);

			if (count($oldItemSku) > 2) {

				$parentItem = $oldItemSku[0];

				$post_length = array_pop($oldItemSku);

				$newItem = implode('|', $oldItemSku);

				var_dump("New SKU: ".$newItem);

				//var_dump("Parent Item: ".$parentItem);
				var_dump("Post Length: ".$post_length);

				$oldItemId = Mage::getModel('catalog/product')->getIdBySku($oldItem);
				$newItemId = Mage::getModel('catalog/product')->getIdBySku($newItem);
				$parentItemId = Mage::getModel('catalog/product')->getIdBySku($parentItem);

				Mage::log('Parent SKU :: '.$parentItem, Zend_Log::DEBUG, 'parent_child_migrations_parsing.log', true);
				Mage::log('Original SKU :: '.$oldItem, Zend_Log::DEBUG, 'parent_child_migrations_parsing.log', true);
				Mage::log('New SKU :: '.$newItem, Zend_Log::DEBUG, 'parent_child_migrations_parsing.log', true);

				if ($newItemId && (!isset($customPostLengthOptions[$parentItemId]) || !isset($customPostLengthOptions[$parentItemId][$post_length]))) {

					if (!isset($customPostLengthOptions[$parentItemId])) {
						$customPostLengthOptions[$parentItemId] = array();
					}
				
					Mage::log('New ITEM EXISTS !!', Zend_Log::DEBUG, 'parent_child_migrations_parsing.log', true);

					$skuByProductId[$oldItemId] = $oldItem;
					$skuByProductId[$newItemId] = $newItem;
					$skuByProductId[$parentItemId] = $parentItem;

					$postLenths[$parentItem][] = $post_length;

					if (!empty($parentItemId)) {

						$sort_order = count($customPostLengthOptions[$parentItemId]) + 1;

						$customPostLengthOptions[$parentItemId][$post_length] = array(
				            'title' => $post_length,
				            'price' => 0,
				            'price_type' => 'fixed',
				            'sku' => null,
				            'is_delete' => 0,
				            'sort_order' => $sort_order
			            );

			            $customPostLengthOptionsLog[$parentItem][$post_length] = $post_length;
			        }

			        if (!empty($newItemId)) {
			            $stockItem = Mage::getModel('cataloginventory/stock_item')->getCollection()
							->addProductsFilter(array($oldItemId))
							->addStockFilter(1)
							->getFirstItem();

						$oldStock = $stockItem->getQty();

			            $stockItemLondon = Mage::getModel('cataloginventory/stock_item')->getCollection()
							->addProductsFilter(array($oldItemId))
							->addStockFilter(2)
							->getFirstItem();

						$oldStockLondon = $stockItemLondon->getQty();

						if (!isset($inventoryUpdates[$newItemId])) {
							$inventoryUpdates[$newItemId] = array();
						}

						Mage::log('Main Stock :: '.$oldStock, Zend_Log::DEBUG, 'parent_child_migrations_parsing.log', true);
						Mage::log('London Stock :: '.$oldStockLondon, Zend_Log::DEBUG, 'parent_child_migrations_parsing.log', true);

			            if (isset($inventoryUpdates[$newItemId][1])) {
			            	$inventoryUpdates[$newItemId][1] += $oldStock;
			            } else {
			            	$inventoryUpdates[$newItemId][1] = $oldStock;
			            }

			            $inventoryUpdatesLog[$newItem][1] = $inventoryUpdates[$newItemId];

			            if (isset($inventoryUpdates[$newItemId][2])) {
			            	$inventoryUpdates[$newItemId][2] += $oldStockLondon;
			            } else {
			            	$inventoryUpdates[$newItemId][2] = $oldStockLondon;
			            }

			            $inventoryUpdatesLog[$newItem][2] = $inventoryUpdates[$newItemId][2];
			        }

		            unset($stockItem);
		            unset($stockItemLondon);

				}
			}

			unset($product);
		}

		unset($productCollection);
	}


	file_put_contents($skuByProductIdFile, json_encode($skuByProductId));
	file_put_contents($customPostLengthOptionsFile, json_encode($customPostLengthOptions));
	file_put_contents($inventoryUpdatesFile, json_encode($inventoryUpdates));
	file_put_contents($postLenthsFile, json_encode($postLenths));
} else {
	$skuByProductId = json_decode(file_get_contents($skuByProductIdFile), true);
	$customPostLengthOptions = json_decode(file_get_contents($customPostLengthOptionsFile), true);
	$inventoryUpdates = json_decode(file_get_contents($inventoryUpdatesFile), true);
	$postLenths = json_decode(file_get_contents($postLenthsFile), true);
}

var_dump($skuByProductId);
var_dump($customPostLengthOptions);
var_dump($inventoryUpdates);
var_dump($postLenths);

die;

$post_length_custom_options_file = Mage::getBaseDir('var').'/export/post_length_custom_options.csv';
$post_length_custom_options = fopen($post_length_custom_options_file, 'w');

$skippedSkus = array("C3BTR","C3DTRD","C3OP","C4BTR","C4BTRD","C4DTRD","CDELD","CDELDBS","CDELDPS","CFL3D","CFL45BD","CFL45CZ","CFL45D","CFL45PD","CFL55BD","CFL55CZ","CFL55D","CFL55PD","CIV25D","CIV2D","CIV4D","CIVD215D","CIVD32D","CPACD","CPAISLD","CPAISRD","CPEAR","CSC15D","CSC2D","CSC3D","CSCD32D","CSTAR45D","CSTAR55D","C4BTROP","CMTTDOP","CHEART","CSC15BD","CSC2BD","CFL55PKD","CMTTD","CSC3BD","C3CZ","CB2","CSP3525","CDAL","CFLD55D","CSC12D","CSC15PKD","CSC25D","CSC3PKD","CSHSTAR","CSTARD","C2TQ","CSCEG3CZ","CSCEG4CZ","CCLVD","CCOBRAD","CSQ3BTRD","CG3CZ_B","CG3RB","CMTTDPS","CFL45BKD","CFL55BKD","CSC15BKD","CSC2BKD","CSC3BKD","CFL7D","C3BTR_B","C3BTR_C","C3BTR_E","C3BTR_R","C3BTR_T","CB2_B","CB2_C","CB2_E","CB2_R","CB2_T","CIV25D_B","CIV2D_B","CIV4D_B","CIVD215D_B","CIVD215D_C","CIVD215D_E","CIVD215D_R","CIVD215D_T","CIVD32D_B","CIVD32D_C","CIVD32D_E","CIVD32D_R","CIVD32D_T","CSC12D_B","CSC12D_E","CSC12D_R","CSC12D_T","CSC15D_B","CSC15D_C","CSC15D_E","CSC15D_R","CSC15D_T","CSC25D_B","CSC25D_C","CSC25D_E","CSC25D_R","CSC25D_T","CSC2D_B","CSC2D_C","CSC2D_E","CSC2D_R","CSC2D_T","CSC3D_B","CSC3D_C","CSC3D_T","CSCD32D_B","CSCD32D_C","CSCD32D_R","CSCD32D_T","C3DTRD_B","C4BTR_B","C4BTR_C","C4BTR_E","C4BTR_R","C4BTR_T","CG3CZ","C3DTRD_C","C3DTRD_E","C3DTRD_R","C3DTRD_T","CDELDPS_B","CDELDPS_C","CDELDPS_E","CDELDPS_R","CDELDPS_T","CFL45BD_B","CFL45BKD_B","CFL45BKD_C","CFL45BKD_E","CFL45BKD_R","CFL45BKD_T","CFL45CZ_B","CFL45CZ_C","CFL45CZ_E","CFL45CZ_R","CFL45CZ_T","CFL45D_B","CFL45D_C","CFL45D_E","CFL45D_R","CFL45D_T","CFL55BKD_B","CFL55CZ_B","CFL55CZ_C","CFL55CZ_E","CFL55CZ_R","CFL55CZ_T","CFL55D_B","CFL55D_C","CFL55D_E","CFL55D_R","CFL55D_T","CFL7D_B","CFL7D_C","CFL7D_R","CFLD55D_B","CFLD55D_C","CFLD55D_E","CFLD55D_R","CFLD55D_T","CHEART_B","CHEART_C","CHEART_E","CHEART_R","CHEART_T","CPACD_B","CPACD_C","CPACD_E","CPACD_R","CPACD_T","CPEAR_B","CPEAR_C","CPEAR_E","CPEAR_R","CPEAR_T","CSHSTAR_B","CSHSTAR_C","CSHSTAR_R","CSHSTAR_T","CSP3525_B","CSP3525_C","CSP3525_E","CSP3525_R","CSP3525_T","CSTAR45D_B","CSTAR55D_B","CSTARD_B","CSTARD_C","CSTARD_E","CSTARD_R","CSTARD_T","CFL45PD_B","CFL45PD_C","CFL45PD_E","CFL45PD_R","CFL45PD_T","CFL45PKD_T","CFL55BKD_C","CFL55BKD_E","CFL55BKD_R","CFL55BKD_T","CFL55PD_B","CFL55PD_C","CFL55PD_E","CFL55PD_R","CFL55PD_T","CFL55PKD_B","CFL55PKD_C","CFL55PKD_E","CFL55PKD_R","CFL55PKD_T","CSC15BD_B","CSC2BD_B","CSC15BKD_B","CSC15BKD_C","CSC15BKD_E","CSC15BKD_R","CSC15BKD_T","CSC2BKD_B","CSC2BKD_C","CSC2BKD_E","CSC2BKD_R","CSC2BKD_T","CSC15PKD_B","CSC3BKD_B","CSC3BKD_C","CSC3BKD_R","CSC3BKD_T","CSC3PKD_B","CDELD_B","CDELD_C","CDELD_E","CDELD_R","CDELD_T","CDELDBS_B","CDAL_B","C3CZ_B","C3CZ_C","C3CZ_E","C3CZ_R","C3CZ_T","CSCEG3CZ_B","CSCEG3CZ_C","CSCEG3CZ_E","CSCEG3CZ_R","CSCEG3CZ_T","C4BTROP_B","C4BTROP_C","C4BTROP_E","C4BTROP_R","C4DTRD_B","C4DTRD_C","C4DTRD_E","C4DTRD_R","CSCEG4CZ_B","CSCEG4CZ_C","CSCEG4CZ_R","CSQ3BTRD_B","CSQ3BTRD_C","CSQ3BTRD_E","CSQ3BTRD_R","CSQ3BTRD_T","CCOBRAD_B","CG3CZ_C","CG3CZ_R","CG3RB_B","CG3RB_C","CG3RB_R","CG3TQ","CG3TQ_B","CG3BS","CG3BS_B","CG3BS_C","CG3BS_R","CG3OP","CG3OP_B","CG3OP_C","CG3OP_R","CG3PS","CG3PS_B","CG3PS_C","CG3PS_R","CG3TQ_C","CG3TQ_R","CP3CZ","CP3CZ_B","CP3CZ_C","CP3CZ_E","CP3CZ_R","CP3CZ_T","C4BTRD_B","C4BTRD_C","C4BTRD_E","C4BTRD_R","C4BTRD_T","CPAISLD_B","CPAISLD_C","CPAISLD_E","CPAISLD_R","CPAISLD_T","CPAISRD_B","CPAISRD_C","CPAISRD_E","CPAISRD_R","CPAISRD_T","CXMARK","CXMARK_B","CXMARK_E","CXMARK_C","CXMARK_R","CXMARK_T","CCLVD_B","CCLVD_C","CCLVD_E","CCLVD_R","CCLVD_T","CFL3D_B","CFL3D_C","CFL3D_E","CFL3D_R","CFL3D_T","CMTTD_B","CMTTD_C","CMTTD_R","CMTTD_T","CFLD45D_B","CFLD45D_C","CFLD45D_E","CFLD45D_R","CFLD45D_T","CMTTDOP_B","CMTTDOP_C","CMTTDOP_R","CMTTDOP_T","CMTTDPS_B","CMTTDPS_C","CMTTDPS_R","CMTTDOP_E","CMTTDPS_E","CMTTDPS_T","CSTAR","CSTAR_B","CSTAR_C","CSTAR_E","CSTAR_R","CSC3BD_B","CDAL_R","CSTAR55D_R","CSTAR55D_C","CSTAR_T","CFL55BD_B","C4BTROP_T","CIVU65D_T","CIVU65D_C","CIVU65D_B","CIVU65D","CIV15D_B","CIV15D","CIV47D_T","CIV47D_R","CIV47D_E","CIV47D_C","CIV47D_B","CIV47D","CSQ3DTRD_T","CSQ3DTRD_R","CSQ3DTRD_E","CSQ3DTRD_C","CSQ3DTRD_B","CSQ3DTRD","CFLP8D_R","CFLP8D_C","CFLP8D_B","CFLP8D","CFLP8BKD_R","CFLP8BKD_C","CFLP8BKD_B","CFLP8BKD","CFLP55D_T","CFLP55D_R","CFLP55D_E","CFLP55D_C","CFLP55D_B","CFLP55D","CFLP55BKD_T","CFLP55BKD_R","CFLP55BKD_E","CFLP55BKD_C","CFLP55BKD_B","CFLP55BKD","CCLV7DPS_R","CCLV7DPS_C","CSWCD_C","CSWCD_B","CSWCD","CFLP8DRB_R","CFLP8DRB_C","CFLP8DRB_B","CFLP8DRB","CSCRB_C","CSCRB_B","CSCRB","CSQGPS_T","CSQGPS_R","CSQGPS_E","CSQGPS_C","CSQGPS_B","CSQGPS","CSQGBS_T","CSQGBS_R","CSQGBS_E","CSQGBS_C","CSQGBS_B","CSQGBS","CFLP8OP_R","CFLP8OP_C","CFLP8OP_B","CFLP8OP","CFLP55OP","CFLP55OP_T","CFLP55OP_R","CFLP55OP_E","CFLP55OP_C","CFLP55OP_B","CSCMQ4D_T","CSCMQ4D_R","CSCMQ4D_E","CSCMQ4D_C","CSCMQ4D_B","CSCMQ4D","CIVFL85D_R","CIVFL85D_C","CIVFL85D_B","CIVFL85D","CFLP55DRB_T","CFLP55DRB_R","CFLP55DRB_E","CFLP55DRB_C","CLB11D_T","CMQ725D_R","CMQ725D_B","CMQ725D","CPE625D_R","CPE625D_C","CPE625D_B","CPE625D","CMQ725D_C","CFL45BD_T","CFL45BD_R","CFL45BD_E","CFL45BD_C","CFL55BD_T","CFL55BD_R","CFL55BD_E","CFL55BD_C","CSC2BD_T","CSC2BD_R","CSC2BD_E","CSC2BD_C","CSC15PD_T","CSC15PD_R","CSC15PD_E","CSC15PD_B","CSC15PD","CSC2PD_T","CSC2PD_R","CSC2PD_E","CSC2PD_C","CSC2PD_B","CSC2PD","CLB11BKD_T","CSCMQ4D_M","CPE625D_M","CSC15PD_M","CLB11D","CLB11D_E","CLB11D_C","CLB11D_B","C4BTROP_M","CMQ725D_M","CFL7DPE_T","CFL7DPE_R","CFL7DPE_C","CFL7DPE_B","CFL7DPE","CFL7DPE_E","CSC3BD_T","CSC3BD_R","CSC3BD_C","CURPA5D_B","CURPA5D","CURPA5D_T","CURPA5D_R","CURPA5D_E","CURPA5D_C","CFLPED_R","CFLPED_C","CFLPED_B","CFLPED","CURPAPRS5BKD_B","CURPAPRS5BKD","CURPAPRS5BKD_T","CURPAPRS5BKD_R","CURPAPRS5BKD_E","CURPAPRS5BKD_C","CURPA5DOP_T","CURPA5DOP_R","CURPA5DOP_E","CURPA5DOP_C","CURPA5DOP_B","CURPA5DOP","CSTAR7D_R","CSTAR7D_C","CSTAR7D_B","CSTAR7D","XFL45PD","XBRKPS","X3BTR","XSTAR7D","XSTAR45D","XSQGPS","XSQGBS","XSQ3BTRD","XSP3525","XSCMQ4D","XSC3D","XSC2PD","XSC25D","XSC15PKD","XPE725D","XPE625D","XP3CZ","XMTTDOP","XMQ725D","XIVU65D","XIV15D","XIV15BD","XG3BS","XFLPED","XFL55PKD","XFL55PD","XFL55CZ","XFL55BD","XDELDBS","XCOBRAD","XCLV7DPS","XCLV7DBS","XCLV55DPS","XCLV55DBS","XC3FLD","XBRT3PS","XBAT75D","XBAT10D","XB15","XAJN","X4DTRD","X4BTRTQ","X2TQ","X2PE","X2OP","XBARR7M","XBARR11","XBAR7","XBAR18M","XBAR11M","XBAR11","XXMARK","XURPAPRS5BKD","XURPA5DOP","XURPA5D","XURPA4D","XSWCD","XSTARD","XSTAR55D","XSTAR","XSQ4DTRD","XSHSTAR","XSCRB","XSCEG4CZ","XSCEG3CZ","XSCD32D","XSC3PKD","XSC3BKD","XSC3BD","XSC2D","XSC2BKD","XSC2BD","XSC15PD","XSC15D","XSC15BKD","XSC15BD","XSC12D","XPEAR","XPAISRD","XPAISLD","XPACD","XMTTDPS","XMTTD","XLB11D","XLB11BKD","XIVFL85D","XIVD32D","XIVD215D","XIV5D","XIV4D","XIV47D","XIV3PKD","XIV3D","XIV3BD","XIV38D","XIV35D","XIV2PKD","XIV2D","XIV25D","XHEART","XG3RB","XG3PS","XG3OP","XG3CZ","XFLP8OP","XFLP8D","XFLP8BKD","XFLP55OP","XFLP55D","XFLP55BKD","XFLD55D","XFLD45D","XFL7DPE","XFL7D","XFL55D","XFL55BKD","XFL45D","XFL45CZ","XFL45BKD","XFL45BD","XFL3D","XDSL22DBS","XDELDPS","XDELD","XDAL","XCLVD","XCFL5D","XC3STD","XBYMQD","XBYMQCZ","XBY4D","XBS18DOP","XBS18DBS","XBRT3TQ","XBRT3RB","XBRT3OP","XBRT3CZ","XBRT3BS","XBRKRB","XBRKCZ","XBRKBS","XBF","XBAT75BKD","XBAD","XB3","XB25","XAJNDPE","XAJN3B","X5IVD2","X5IVD","X4OP","X4BTROP","X4BTRD","X4BTR","X3TQ","X3OP","X3DTRD","X3CZ","XFLP8DRB","XFLP55DRB","CFLP55DRB_B","CFLP55DRB","CPE725D_R","CPE725D_C","CPE725D_B","CDSL22DBS_B","CURPA4D_C","CURPA4D_B","CURPA4D","CLB11D_R","CURPA4D_T","CURPA4D_R","CURPA4D_E","XC3FLOP","CIV4D_R","CIV4D_C","CIV2D_T","CIV2D_R","CIV2D_E","CIV2D_C","CIV25D_T","CIV25D_R","CIV25D_E","CIV25D_C","CIV15D_T","CIV15D_R","CIV15D_E","CIV15D_C","CIV3BD_T","CIV3BD_R","CIV3BD_C","CIV3BD_B","CIV3BD","CIV2BD_T","CIV2BD_R","CIV2BD_E","CIV2BD_C","CIV2BD_B","CIV2BD","CIV15BD_T","CIV15BD_R","CIV15BD_E","CIV15BD_C","CIV15BD_B","CIV15BD","CIV5D_T","CIV5D_R","CIV5D_E","CIV5D_C","CIV5D_B","CIV5D","CIV3PKD_T","CIV3PKD_R","CIV3PKD_E","CIV3PKD_C","CIV3PKD_B","CIV3PKD","CIV3D_T","CIV3D_R","CIV3D_E","CIV3D_C","CIV3D_B","CIV3D","CIV38D_T","CIV38D_R","CIV38D_E","CIV38D_C","CIV38D_B","CIV38D","CIV35D_T","CIV35D_R","CIV35D_C","CIV35D_B","CIV35D","CIV2PKD_T","CIV2PKD_R","CIV2PKD_E","CIV2PKD_C","CIV2PKD_B","CIV2PKD","XMNPAD","XAR10D","XBO9D","XBO7D","CLB11BKD_R","CLB11BKD_E","CLB11BKD_B","CLB11BKD","XBARR11DM","XAR75D","XIV2DTEST","CMNPAD_T","CMNPAD_R","CMNPAD_E","CMNPAD_C","CMNPAD_B","CMNPAD","CIV2DTEST_T","CIV2DTEST_R","CIV2DTEST_E","CIV2DTEST_C","CIV2DTEST_B","CIV2DTEST","XSK65BKDM","XSK65OPM","XSK5DM","XSK5BKDM","XSK4RB","XSK4BKDM","Z3TQ","ZBRKRB","ZBO9D","ZBO7D","ZAR75D","ZAR10D","Z5IVD2","ZBAT10D","XETIVBA8D_PC","C3BTR_T|ROSE GOLD","C3BTR_T|WHITE GOLD","C3BTR_T|YELLOW GOLD","CSK4BKDM","CSK4BKDM_B","CSK4RB","CSK4RB_B","CSK5BKDM","CSK5BKDM_B","CSK5DM","CSK5DM_B","CSK65BKDM","CSK65BKDM_B","CSK65OPM","CSK65OPM_B","XBAT75RB");

foreach ($customPostLengthOptions as $product_id => $option_values) {


	$sku = $skuByProductId[$product_id];

	if (empty($sku) || in_array($sku, $skippedSkus)) {
		Mage::log('Skipping Custom Options for SKU:: '.$sku, Zend_Log::DEBUG, 'parent_child_migrations_processing.log', true);
		var_dump('Skipping Custom Options for SKU:: '.$sku);
	}

	Mage::log('Adding Custom Options for SKU:: '.$sku, Zend_Log::DEBUG, 'parent_child_migrations_processing.log', true);
	var_dump('Adding Custom Options for SKU:: '.$sku);

	if (empty($sku)) {
		continue;
	}

	$option = array (
        'title'			=> 'Post Length',
        'type'			=> 'drop_down',
        'is_require'	=> 1,
        'sort_order'	=> 0,
		'is_delete'		=> 0,
        'values'		=> $option_values
    );

    try {

    	/*
	    $_product = Mage::getModel("catalog/product")->load($product_id);
	    
	    if ($_product->getOptions() != ''){
			foreach ($_product->getOptions() as $_option) {
				$_option->delete();
			}

			$_product->setHasOptions(0)->save();
		}

	    $_product->setProductOptions(array($option));
		$_product->setCanSaveCustomOptions(true);

		$_product->save();

		unset($_product);
		*/

		$post_length = implode('|', $postLenths[$sku]);

		Mage::log('Post Length:: '.$post_length, Zend_Log::DEBUG, 'parent_child_migrations_processing.log', true);
		var_dump('Post Length:: '.$post_length);

		var_dump($option_values);

		fputcsv($post_length_custom_options, array(
			$sku,
			$post_length,
			'OK'
		));
	} catch (Exception $e) {
		Mage::log('Failed Adding Custom Options for SKU:: '.$sku, Zend_Log::DEBUG, 'parent_child_migrations_processing.log', true);
		var_dump('Failed Adding Custom Options for SKU:: '.$sku);

		fputcsv($post_length_custom_options, array(
			$sku,
			$post_length,
			'FAIL:'.$e->getMessage()
		));
	}
}

fclose($post_length_custom_options);

die;

$post_length_inventory_file = Mage::getBaseDir('var').'/export/post_length_inventory.csv';

$post_length_inventory = fopen($post_length_inventory_file, 'w');

foreach ($inventoryUpdates as $product_id => $stockQty) {

	foreach ( $stockQty as $stock_id => $qty) {

		$sku = $skuByProductId[$product_id];

		if (empty($sku) || in_array($sku, $skippedSkus)) {
			Mage::log('Skipping Stock for SKU:: '.$sku, Zend_Log::DEBUG, 'parent_child_migrations_processing.log', true);
			var_dump('Skipping Stock for SKU:: '.$sku);
		}

		Mage::log('Updating Stock for SKU:: '.$sku, Zend_Log::DEBUG, 'parent_child_migrations_processing.log', true);
		var_dump('Updating Stock for SKU:: '.$sku);

		try {

		 	$stockItem = Mage::getModel('cataloginventory/stock_item')->getCollection()
				->addProductsFilter(array($product_id))
				->addStockFilter($stock_id)
				->getFirstItem();

	      	$oldStock = $stockItem->getQty();

	        if (!$stockItem->getId()) {
	            $stockItem->setData('product_id', $product_id);
	            $stockItem->setData('stock_id', $stock_id);
	            $stockItem->setData('manage_stock', 1);
	            $stockItem->setData('qty', $qty);
	        } else { // if there is, update it
	            $stockItem->setQty($qty);
	            $stockItem->setManageStock(true);
	        }
	        $stockItem->save();

			unset($stockItem);

			Mage::log('Stock Id:: '.$stock_id, Zend_Log::DEBUG, 'parent_child_migrations_processing.log', true);
			Mage::log('Original Stock:: '.$oldStock, Zend_Log::DEBUG, 'parent_child_migrations_processing.log', true);
			Mage::log('New Stock:: '.$qty, Zend_Log::DEBUG, 'parent_child_migrations_processing.log', true);

			fputcsv($post_length_inventory, array(
				$product_id,
				$sku,
				$stock_id,
				$oldStock,
				$qty,
				'OK'
			));
		} catch (Exception $e) {
			Mage::log('Failed Updating Stock for SKU:: '.$sku, Zend_Log::DEBUG, 'parent_child_migrations_processing.log', true);
			var_dump('Failed Updating Stock for SKU:: '.$sku);

			fputcsv($post_length_inventory, array(
				$product_id,
				$sku,
				$stock_id,
				$oldStock,
				$qty,
				'FAIL:'.$e->getMessage()
			));
		}
	}
}

fclose($post_length_inventory);