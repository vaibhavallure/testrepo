<?php
/**
 * Traitement du module image
 * Inseré dans le traitement général quand le module est activé
 * 
 * @author  Aurelie Lopes pour Millesima
 */


//echo "<br /><br />TRAITEMENT DES IMAGES<br /><br />";
// Variables déclarée dans index
// $tracking

/**
 * Ajout d'un bandeau unique
 *
 */

$urlgen='';
if(isset($_POST["bandeau_unique"])){
	$oSmarty->assign('bdunq', "1");
	
	if(isset($_POST["bdunq_height"])){
		$oSmarty->assign('bdunq_height',$_POST["bdunq_height"]);	
	}
	$url = "";
	if(!isset($_POST["bdunq_nourl"]) || !$_POST["bdunq_nourl"]){
		$url = $this->getUrl('bdunq_url', $tracking,$siteweb, $country);
	}
	if(self::DEBUG){
		var_dump($_POST['bdunq_exceptions_pays']);
		echo'<br />';
	}
	if(isset($_POST['bdunq_exceptions']) && $_POST['bdunq_exceptions'] and in_array($country, $_POST['bdunq_exceptions_pays'])){
		if(self::DEBUG){
			echo 'pays exception : '.$country.'<br />'; 
		}
		$oSmarty->assign('exception', true);
	}else{
		$oSmarty->assign('exception', false);
	}
	
	$oSmarty->assign('bdunq_url', $url);
	
	$oSmarty->assign('bdunq_extension',$_POST["bdunq_type_image"]);
	$urlgen=$url;
	if(self::DEBUG)
		echo "Ajout d'un bandeau unique dans l'email<br />";
}else{
	if(self::DEBUG)
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
	
	$urlgen="";
	if(!isset($_POST[$bandeau."_nourl"]) || !$_POST[$bandeau."_nourl"]){
		$urlgen=$this->getUrl("bd1_url", $tracking, $siteweb, $country);
	}
	
	for ($i = 1; $i <= intval($_POST["bandeau_tranches_nb"]); $i++){
		//echo "Ajout de l'image ".$i."<br />";
		$bandeau="bd".$i;
		$height=intval($_POST[$bandeau."_height"]);
		//echo "Hauteur de ".$bandeau." : ".$height."<br />";
		$url = "";
		if(!isset($_POST[$bandeau."_nourl"]) || !$_POST[$bandeau."_nourl"]){
			$url=$this->getUrl($bandeau.'_url', $tracking, $siteweb, $country);
		}
		
		//echo "Url de ".$bandeau." : ".$url."<br />";
		if($i<10){
			$nb="0".$i;
		}else{
			$nb=$i;
		};
		if(isset($_POST[$bandeau.'_exceptions']) && $_POST[$bandeau.'_exceptions'] and in_array($country, $_POST[$bandeau.'_exceptions_pays'])){
			if(self::DEBUG){
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
	if(self::DEBUG)
		echo "Ajout d'une image multi-tranches horizontales<br />";
}else{
	if(self::DEBUG)
		echo "l'image ne nécessite pas d'une image multi-tranches horizontale<br />";
}

/**
 * Traitement d'une image avec une tranche horizontales, un carré 2x2 et une tranche horizontale 
 *
 */
if(isset($_POST["bandeau_1-2x2-1"])){
	$oSmarty->assign('bd12x21', "1");
	
	// Creation d'un tableau contenant les pairs hauteur - url 
	$proprietesImages = array();
	$height=0;
	$url="";
	$bandeau="";
	$nb="00";
	$extension="";
	$exception="";
	
	$urlgen="";
	if(!isset($_POST[$bandeau."_nourl"]) || !$_POST[$bandeau."_nourl"]){
		$urlgen=$this->getUrl("bd1_url", $tracking, $siteweb, $country);
	}
	
	for ($i = 1; $i <= intval($_POST["bandeau_1-2x2-1_nb"]); $i++){
		//echo "Ajout de l'image ".$i."<br />";
		$bandeau="bd".$i;
		$height=intval($_POST[$bandeau."_height"]);
		//echo "Hauteur de ".$bandeau." : ".$height."<br />";
		$url = "";
		if(!isset($_POST[$bandeau."_nourl"]) || !$_POST[$bandeau."_nourl"]){
			$url=$this->getUrl($bandeau.'_url', $tracking, $siteweb, $country);
		}
		
		//echo "Url de ".$bandeau." : ".$url."<br />";
		if(i<10){
			$nb="0".$i;
		}else{
			$nb=$i;
		};
		if(isset($_POST[$bandeau.'_exceptions']) && $_POST[$bandeau.'_exceptions'] and in_array($country, $_POST[$bandeau.'_exceptions_pays'])){
			if(self::DEBUG){
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
	
	if(self::DEBUG)
		echo "Ajout d'une image 1-2x2-1<br />";
}else{
	if(self::DEBUG)
		echo "l'image ne nécessite pas une image 1-2x2-1<br />";
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
	if(!isset($_POST["bdbas_nourl"]) || !$_POST["bdbas_nourl"]){
		$url = $this->getUrl('bdbas_url', $tracking, $siteweb, $country);
	}
	$oSmarty->assign('bdbas_url', $url);
	
	if($_POST['bdbas_exceptions'] and in_array($country, $_POST['bdbas_exceptions_pays'])){
		if(self::DEBUG){
			echo 'pays exception : '.$country.'<br />'; 
		}
		$oSmarty->assign('bdbas_exception', true);
	}else{
		$oSmarty->assign('bdbas_exception', false);
	}
	
	$oSmarty->assign('bdbas_extension',$_POST["bdbas_type_image"]);
	if(self::DEBUG)
		echo "Ajout d'un bandeau bas dans l'email<br />";
	
}else{
	if(self::DEBUG)
		echo "l'image ne nécessite pas de bandeau bas<br />";
}


if(isset($_POST["bandeau_primeurs"])){
	$oSmarty->assign('bdprim', "1");
	
	$url = $this->getUrl('bdprim_url', $tracking,$siteweb, $country);
	$oSmarty->assign('bdprim_url', $url);
	
	$urlgen=$url;
}

?>