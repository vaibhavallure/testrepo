<?php
/**
 * Traitement du module image
 * Inseré dans le traitement général quand le module est activé
 * 
 * @author  Aurelie Lopes pour Millesima
 */


echo "<br /><br />TRAITEMENT DES IMAGES<br /><br />";

/**
 * Ajout d'un bandeau unique
 *
 */
if(isset($_POST["bandeau_unique"])){
	$oSmarty->assign('bdunq', "1");
	
	if(isset($_POST["bdunq_height"])){
		$oSmarty->assign('bdunq_height',$_POST["bdunq_height"]);	
	}
	
	$url = getUrl('bdunq_url');
	$oSmarty->assign('bdunq_url', $url);
	
	echo "Ajout d'un bandeau unique dans l'email<br />";
}else{
	echo "l'image ne nécessite pas de bandeau unique<br />";
}

/**
 * Traitement d'une image multi-tranches horizontales 
 *
 */
/*if(isset($_POST["bandeau_tranches"])){
	$oSmarty->assign('bdtrch', "1");
	
	if(isset($_POST["bdunq_height"])){
		$oSmarty->assign('bdunq_height',$_POST["bdunq_height"]);	
	}
	
	$url = getUrl('bdunq_url');
	$oSmarty->assign('bdunq_url', $url);
	
	echo "Ajout d'un bandeau unique dans l'email<br />";
}else{
	echo "l'image ne nécessite pas de bandeau unique<br />";
}*/

/**
 * Ajout d'un bandeau bas
 *
 */
if(isset($_POST["bandeau_bas"])){
	$oSmarty->assign('bdbas', "1");
	
	if(isset($_POST["bdbas_height"])){
		$oSmarty->assign('bdbas_height',$_POST["bdbas_height"]);	
	}
	
	$url = getUrl('bdbas_url');
	$oSmarty->assign('bdbas_url', $url);
	
	echo "Ajout d'un bandeau bas dans l'email<br />";
	
}else{
	echo "l'image ne nécessite pas de bandeau bas<br />";
}

?>