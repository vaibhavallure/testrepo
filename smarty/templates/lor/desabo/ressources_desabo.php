<?php
/**
 * Traitement du module desabo
 * Inseré dans le traitement général quand le module est activé /TO DO/
 * Faire attention à une création de l'objet $oSmarty préalablement dans le traitement général
 * 
 * @author  Aurelie Lopes pour Millesima
 */


//echo "Ajout du bloc de desabo<br />";


switch ($country) {
	case "F" :
		$desabo = array ("title" => "&ecirc;tre retir&eacute; de notre liste de diffusion",
						"lien" => "https://avanci.emsecure.net/optiext/optiextension.dll?ID=gOxS5kFKAynkEadQkfHohwk96R2bRBYdcDWaR2H1aQQVr9HKMC7d2u033U9kHdlhk3rsfkDHj2g4gt&MAIL=~MAIL~"
						);
		break;
						
	case "B" :
		$desabo = array ("title" => "&ecirc;tre retir&eacute; de notre liste de diffusion",
						"lien" => "https://avanci.emsecure.net/optiext/optiextension.dll?ID=gOxS5kFKAynkEadQkfHohwk96R2bRBYdcDWaR2H1aQQVr9HKMC7d2u033U9kHdlhk3rsfkDHj2g4gt&MAIL=~MAIL~"
						);
		break;
						
	case "L" :
		$desabo = array ("title" => "&ecirc;tre retir&eacute; de notre liste de diffusion",
						"lien" => "https://avanci.emsecure.net/optiext/optiextension.dll?ID=gOxS5kFKAynkEadQkfHohwk96R2bRBYdcDWaR2H1aQQVr9HKMC7d2u033U9kHdlhk3rsfkDHj2g4gt&MAIL=~MAIL~"
						);
		break;
						
	case "SF" :
		$desabo = array ("title" => "&ecirc;tre retir&eacute; de notre liste de diffusion",
						"lien" => "https://avanci.emsecure.net/optiext/optiextension.dll?ID=gOxS5kFKAynkEadQkfHohwk96R2bRBYdcDWaR2H1aQQVr9HKMC7d2u033U9kHdlhk3rsfkDHj2g4gt&MAIL=~MAIL~"
						);
		break;

	case "D" :
		$desabo = array ("title" => "Streichung aus Verteilerliste - hier",
						"lien" => "https://avanci.emsecure.net/optiext/optiextension.dll?ID=woFw8PynirSGtqG0G790pqb3tBA43wqaJ6QBE7DI5FQD9PdgnaH6NACv%2BZMiCYHjBgpbFoOmdf9EwU&MAIL=~MAIL~"
						);
		break;

	case "O" :
		$desabo = array ("title" => "Streichung aus Verteilerliste - hier",
						"lien" => "https://avanci.emsecure.net/optiext/optiextension.dll?ID=woFw8PynirSGtqG0G790pqb3tBA43wqaJ6QBE7DI5FQD9PdgnaH6NACv%2BZMiCYHjBgpbFoOmdf9EwU&MAIL=~MAIL~"
						);
		break;
		
	case "SA" :
		$desabo = array ("title" => "Streichung aus Verteilerliste - hier",
						"lien" => "https://avanci.emsecure.net/optiext/optiextension.dll?ID=woFw8PynirSGtqG0G790pqb3tBA43wqaJ6QBE7DI5FQD9PdgnaH6NACv%2BZMiCYHjBgpbFoOmdf9EwU&MAIL=~MAIL~"
						);
		break;
		
	case "G" :
		$desabo = array ("title" => "I want to be withdrawn from the mailing list",
						"lien" => "https://avanci.emsecure.net/optiext/optiextension.dll?ID=TknmomkYGHvFildOkwjzajS3r49qqLJpPiXFtK34b8_ommNkQldk_5y8U0nVnM8UC8wj_9sXrrivTJ&MAIL=~MAIL~"
						);
		break;

	case "I" :
		$desabo = array ("title" => "I want to be withdrawn from the mailing list",
						"lien" => "https://avanci.emsecure.net/optiext/optiextension.dll?ID=TknmomkYGHvFildOkwjzajS3r49qqLJpPiXFtK34b8_ommNkQldk_5y8U0nVnM8UC8wj_9sXrrivTJ&MAIL=~MAIL~"
						);
		break;

	case "Y" :
		$desabo = array ("title" => "Essere cancellato dal nostro elenco",
						"lien" => "https://avanci.emsecure.net/optiext/optiextension.dll?ID=KLvdVRFYGiDomFWTdr1gKpQfzCs2d75ptZmZ2Pw%2B8Ter1%2BuSxwo7e4nnysaODrwNtcVpbgPPcUunKT&MAIL=~MAIL~"
						);
		break;

	case "E" :
		$desabo = array ("title" => "Ser eliminado de la lista de difusi&oacute;n",
						"lien" => "https://avanci.emsecure.net/optiext/optiextension.dll?ID=vHKtSmvSmZPe2XivaR6YGr8sGvslzvo_2RpJiH%2ByQb8mMKuCyhWI4yuAdn3eHXTC8FBwjZU%2B9xxrvU&MAIL=~MAIL~"
						);
		break;

	case "P" :
		$desabo = array ("title" => "Desejo ser retirado da lista de difus&atilde;o",
						"lien" => "https://avanci.emsecure.net/optiext/optiextension.dll?ID=Y78YVHVulfWNMy5fNA3OATc2pV0vFHpUqXDmt_yPd1o9mh_7sw9CYV_PkWh4blLo1ADD2ZUS76ueYU&MAIL=~MAIL~"
						);
		break;
	
	case "H" :
		$desabo = array ("title" => "I want to be withdrawn from the mailing list",
						"lien" => "https://avanci.emsecure.net/optiext/optiextension.dll?ID=TknmomkYGHvFildOkwjzajS3r49qqLJpPiXFtK34b8_ommNkQldk_5y8U0nVnM8UC8wj_9sXrrivTJ&MAIL=~MAIL~"
						);
		break;

	case "SG" :
		$desabo = array ("title" => "I want to be withdrawn from the mailing list",
						"lien" => "https://avanci.emsecure.net/optiext/optiextension.dll?ID=TknmomkYGHvFildOkwjzajS3r49qqLJpPiXFtK34b8_ommNkQldk_5y8U0nVnM8UC8wj_9sXrrivTJ&MAIL=~MAIL~"
						);
		break;

	case "U" :
		$desabo = array ("title" => "I want to be withdrawn from the mailing list",
						"lien" => "https://avanci.emsecure.net/optiext/optiextension.dll?ID=Za52i10PWfEwLKERRCV8JsjCfRGJHXJg2QZTpCAMzHCo8XS6G5jwUcnwmAZ%2B%2BldY%2BaUKEzQ0iFybZt&MAIL=~MAIL~"
						);
		break;

	default :
		$desabo = array ("title" => "&ecirc;tre retir&eacute; de notre liste de diffusion",
						"lien" => "https://avanci.emsecure.net/optiext/optiextension.dll?ID=Za52i10PWfEwLKERRCV8JsjCfRGJHXJg2QZTpCAMzHCo8XS6G5jwUcnwmAZ%2B%2BldY%2BaUKEzQ0iFybZt&MAIL=~MAIL~"
						);
		break;
	
}

$oSmarty->assign('desabo', $desabo);

?>