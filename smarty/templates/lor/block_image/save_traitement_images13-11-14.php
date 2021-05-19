<?php
/**
 * Traitement du module image
 * Inseré dans le traitement général quand le module est activé
 * 
 * @author  Aurelie Lopes pour Millesima
 */


echo "<br /><br />TRAITEMENT DES IMAGES<br /><br />";
// Variables déclarée dans index
// $tracking

/**
 * Ajout d'un bandeau unique
 *
 */
if(isset($_POST["bandeau_unique"])){
	$oSmarty->assign('bdunq', "1");
	
	if(isset($_POST["bdunq_height"])){
		$oSmarty->assign('bdunq_height',$_POST["bdunq_height"]);	
	}
	$url = "";
	if(!$_POST["bdunq_nourl"]){
		$url = getUrl('bdunq_url', $tracking);
	}
	if(DEBUG){
		var_dump($_POST['bdunq_exceptions_pays']);
		echo'<br />';
	}
	if($_POST['bdunq_exceptions'] and in_array($country, $_POST['bdunq_exceptions_pays'])){
		if(DEBUG){
			echo 'pays exception : '.$country.'<br />'; 
		}
		$oSmarty->assign('exception', true);
	}else{
		$oSmarty->assign('exception', false);
	}
	
	$oSmarty->assign('bdunq_url', $url);
	
	$oSmarty->assign('bdunq_extension',$_POST["bdunq_type_image"]);
	
	if(DEBUG)
		echo "Ajout d'un bandeau unique dans l'email<br />";
}else{
	if(DEBUG)
		echo "l'image ne nécessite pas de bandeau unique<br />";
}

/**
 * Traitement d'une image multi-tranches horizontales 
 *
 */
if(isset($_POST["bandeau_tranches"])){
	$oSmarty->assign('bdtrch', "1");
	
	// Creation d'un tableau contenant les pairs hauteur - url 
	$proprietesImages = array();
	$height=0;
	$url="";
	$bandeau="";
	$nb="00";
	$extension="";
	$exception="";
	
	for ($i = 1; $i <= intval($_POST["bandeau_tranches_nb"]); $i++){
		echo "Ajout de l'image ".$i."<br />";
		$bandeau="bd".$i;
		$height=intval($_POST[$bandeau."_height"]);
		echo "Hauteur de ".$bandeau." : ".$height."<br />";
		$url = "";
		if(!$_POST[$bandeau."_nourl"]){
			$url=getUrl($bandeau.'_url', $tracking);
		}
		
		echo "Url de ".$bandeau." : ".$url."<br />";
		if(i<10){
			$nb="0".$i;
		}else{
			$nb=$i;
		};
		if($_POST[$bandeau.'_exceptions'] and in_array($country, $_POST[$bandeau.'_exceptions_pays'])){
			if(DEBUG){
				echo 'pays exception : '.$country.'<br />'; 
			}
			$exception=true;
		}else{
			$exception=false;
		}
		$extension=$_POST[$bandeau."_type_image"];
		$proprietesImages[$bandeau] = array("url" => $url,
												"height" => $height,
												"bdnb" => $nb,
												"extension" => $extension,
												"exception" => $exception);
	}
	//var_dump($proprietesImages);
	$oSmarty->assign('bandeauxArray', $proprietesImages);
	
	if(DEBUG)
		echo "Ajout d'une image multi-tranches horizontales<br />";
}else{
	if(DEBUG)
		echo "l'image ne nécessite pas d'une image multi-tranches horizontale<br />";
}

/**
 * Ajout d'un bandeau bas
 *
 */
if(isset($_POST["bandeau_bas"])){
	$oSmarty->assign('bdbas', "1");
	
	if(isset($_POST["bdbas_height"])){
		$oSmarty->assign('bdbas_height',$_POST["bdbas_height"]);	
	}
	$url = "";
	if(!$_POST["bdbas_nourl"]){
		$url = getUrl('bdbas_url', $tracking);
	}
	$oSmarty->assign('bdbas_url', $url);
	
	if($_POST['bdbas_exceptions'] and in_array($country, $_POST['bdbas_exceptions_pays'])){
		if(DEBUG){
			echo 'pays exception : '.$country.'<br />'; 
		}
		$oSmarty->assign('bdbas_exception', true);
	}else{
		$oSmarty->assign('bdbas_exception', false);
	}
	
	$oSmarty->assign('bdbas_extension',$_POST["bdbas_type_image"]);
	
	if(DEBUG)
		echo "Ajout d'un bandeau bas dans l'email<br />";
	
}else{
	if(DEBUG)
		echo "l'image ne nécessite pas de bandeau bas<br />";
}

?>