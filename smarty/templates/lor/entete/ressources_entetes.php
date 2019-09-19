<?php
/**
 * Traitement du module réseaux sociaux
 * Inseré dans le traitement général quand le module est activé
 * Faire attention à une création de l'objet $oSmarty préalablement dans le traitement général
 * 
 * @author  Aurelie Lopes pour Millesima
 */


//echo "Ajout du header du message<br />";

$url="http://cdn.millesima.com.s3.amazonaws.com/ios/".$codemessage."/".$country.$codemessage.".html";

$vsligne="~PROBE(0)~";
$vssmartphone="http://cdn.millesima.com.s3.amazonaws.com/ios/".$codemessage."/M".$country.$codemessage.".html";
$vcard="http://cdn.millesima.com.s3.amazonaws.com/templates/vcard/MillesimaSA.vcf";


$oSmarty->assign('vsligne', $vsligne);
$oSmarty->assign('vssmartphone', $vssmartphone);
$oSmarty->assign('vcard', $vcard);

?>