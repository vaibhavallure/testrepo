<?php
/**
 * Fonctions dédiées à l'insertion d'images
 * Inseré dans le traitement général quand le module est activé
 * 
 * @author  Aurelie Lopes pour Millesima
 */

function insertBandeau($objSmarty, $siteweb, $id=""){
	$objSmarty->assign('bdunq', "1");
	$objSmarty->assign('bdunq_url', $siteweb.$id);
}


function insertBandeauBas($objSmarty, $siteweb, $id=""){
	$objSmarty->assign('bdbas', "1");
	$objSmarty->assign('bdbas_url', $siteweb.$id);
}

?>