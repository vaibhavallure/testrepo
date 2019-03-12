<?php

include_once 'library/PHPExcel.php';
include_once 'library/PHPExcel/Reader/CSV.php';
include_once 'library/PHPExcel/Writer/CSV.php';
include_once 'library/PHPExcel/IOFactory.php';
include_once 'millesima_brief.php';

class Millesima_Tinyclues{
    const REPOSITORY_SEGMENT = "fichiers/segment-file";

    function ListeFichierSFTP($dir, $date) {
        $dateDay = $date;
        $tempArray = array();
        $handle = opendir($dir);
        // List all the files
        while (false !== ($file = readdir($handle))) {

            if(strpos($file,$dateDay)) {
                if (substr("$file", 0, 1) != ".") {
                    if (is_dir($file)) {
                        $tempArray[$file] = ListeFichierSFTP("$dir/$file, $date");
                    } else {
                        $tempArray[] = $file;
                    }
                }
            }
        }
        closedir($handle);
        return $tempArray;
    }

    public function recupFichier($type){
        $i = 0;
        $tabFichier = array();

        if($type == "dedoublon"){
            $dossier = opendir('tinyclues/dedoublon');
        }else{
            $dossier = opendir('tinyclues');
        }

        if($dossier){
            while(false !== ($fichier = readdir($dossier))) {
                if($fichier != '.' && $fichier != '..' && $fichier != 'index.php' && $fichier != "dedoublon") {
                    if($type == "non_us"){
                        $caract=substr($fichier,0,1);
                        if($caract != "u"){
                            $i ++;
                            $tabFichier[$i] = "tinyclues/".$fichier;
                        }
                    }else if($type =="us"){
                        $caract=substr($fichier,0,1);
                        if($caract == "u"){
                            $i ++;
                            $tabFichier[$i] = "tinyclues/".$fichier;
                        }
                    }else if($type == "dedoublon"){
                        $i ++;
                        $tabFichier[$i] = "tinyclues/dedoublon/".$fichier;
                    }
                }
            }
        }
        return $tabFichier;
    }

    //Fonction qui permet de trier les fichiers par taille
    //pour garder les doublons dans le plus gros fichier qui
    //se trouvera en position 1
    public function sortBySize($array){
        $nbTab = count($array);
        if($nbTab > 1){
            for($i = 1 ; $i<=$nbTab ; $i++){
                for($j = $i+1 ; $j<=$nbTab ; $j++){
                    if(filesize($array[$i]) < filesize($array[$j])){
                        $ici = $array[$i];
                        $array[$i] = $array[$j];
                        $array[$j] = $ici;
                    }
                }
            }
        }
        return $array;
    }

    function dedoublonneEtOrganise($tabFichier){
        if(!is_dir("tinyclues/dedoublon")){
            mkdir("tinyclues/dedoublon");
        }
        $listeRef = array();
        for ($i = 1; $i <= count($tabFichier); $i++) {
            $nameFichier = explode("/",$tabFichier[$i]);
            $handle = fopen($tabFichier[$i], 'r');
            //Si on a réussi à ouvrir le fichier
            if ($handle) {
                $new_file = "tinyclues/dedoublon/".$nameFichier[1];
                $f = fopen($new_file, "x+");
                while (!feof($handle)) {
                //On lit la ligne courante
                    $ligne = fgets($handle);
                    if ($ligne) {
                        $celule = explode(",", $ligne);
                        $user_id = $celule[0];
                        $pays = $celule[3];
                        $langue = $celule[5];
                            if ($i == 1 || !isset($listeRef[$user_id])) {
                                //$listeRef[] = $user_id;
                                $listeRef[$user_id] = 1;
                                $tabSorti = array("$user_id","$pays","$langue");
                                fwrite($f, implode(";", $tabSorti));
                            }
                    }
                }
                //On ferme le fichier
                fclose($handle);
                fclose($f);
            }
        }
    }

    function archivageEtEnvoiTinyclues($fichier) {
        $nameFichier = explode("/", $fichier);
        $name = explode("_",$nameFichier[2]);

        $html = '';
        if(preg_match("/^iosliv/", $nameFichier[2])){

            $typeBrief = "livrable_eu";
            $codeBrief = substr($name[0], 6, 5);
            $dossier = substr($nameFichier[2], 0, -15);
            $year = substr($name[0], 6, 2);
            $year = "20".$year;
            $this->deplaceFichier($nameFichier[2], $year, $dossier, $name[0], "2-LIVRABLES");
            $html .= $this->envoiDolist($typeBrief, $codeBrief, $name[0], $year, $dossier, "2-LIVRABLES", $fichier);

        }else if(preg_match("/^iosprim/", $nameFichier[2])) {
            $typeBrief = "primeur_eu";
            $codeBrief = substr($name[0], 7, 5);
            $dossier = substr($nameFichier[2], 0, -15);
            $year = substr($name[0], 7, 2);
            $year = "20" . $year;
            $this->deplaceFichier($nameFichier[2], $year, $dossier, $name[0], "1-PRIMEURS");
            $html .= $this->envoiDolist($typeBrief, $codeBrief, $name[0], $year, $dossier, "1-PRIMEURS", $fichier);
        }else if(preg_match("/^uiosliv/", $nameFichier[2])) {

            $typeBrief = "livrable_us";
            $verif2 = "livrable_eu";
            $codeBrief = substr($name[0], 7, 5);
            $dossier = substr($nameFichier[2], 0, -15);
            $year = substr($name[0], 7, 2);
            $year = "20" . $year;
            $this->deplaceFichier($nameFichier[2], $year, $dossier, $name[0], "2-LIVRABLES","USA/");
            $html .= $this->envoiDolist($typeBrief, $codeBrief, $name[0], $year, $dossier, "2-LIVRABLES", $fichier, $usa = true, $verif2);

        }else if(preg_match("/^uiosprim/", $nameFichier[2])) {
            $typeBrief = "primeur_us";
            $verif2 = "primeur_eu";
            $codeBrief = substr($name[0], 8, 5);
            $dossier = substr($nameFichier[2], 0, -15);
            $year = substr($name[0], 8, 2);
            $year = "20" . $year;
            $this->deplaceFichier($nameFichier[2], $year, $dossier, $name[0], "1-PRIMEURS", "USA/");
            $html .= $this->envoiDolist($typeBrief, $codeBrief, $name[0], $year, $dossier, "1-PRIMEURS", $fichier, $usa = true, $verif2);
        }
        return $html;
    }

    function deplaceFichier($fichier, $year, $dossier, $nameNewFichier, $type, $pays="") {
        if(!file_exists("/mnt/sf_P_DRIVE/Millésima/Marketing/Messages/$type/$year")){
            mkdir("/mnt/sf_P_DRIVE/Millésima/Marketing/Messages/$type/$year");
        }
        if(!file_exists("/mnt/sf_P_DRIVE/Millésima/Marketing/Messages/$type/$year/emailing")){
            mkdir("/mnt/sf_P_DRIVE/Millésima/Marketing/Messages/$type/$year/emailing");
        }
        if($pays != "" && !file_exists("/mnt/sf_P_DRIVE/Millésima/Marketing/Messages/$type/$year/emailing/$pays")){
            mkdir("/mnt/sf_P_DRIVE/Millésima/Marketing/Messages/$type/$year/emailing/$pays");
        }
        if($pays != "" && !file_exists("/mnt/sf_P_DRIVE/Millésima/Marketing/Messages/$type/$year/emailing/$pays")){
            mkdir("/mnt/sf_P_DRIVE/Millésima/Marketing/Messages/$type/$year/emailing/$pays");
        }
        if(!file_exists("/mnt/sf_P_DRIVE/Millésima/Marketing/Messages/$type/$year/emailing/$pays$dossier")){
            mkdir("/mnt/sf_P_DRIVE/Millésima/Marketing/Messages/$type/$year/emailing/$pays$dossier");
        }
        if(!file_exists("/mnt/sf_P_DRIVE/Millésima/Marketing/Messages/$type/$year/emailing/$pays$dossier/extraction")){
            mkdir("/mnt/sf_P_DRIVE/Millésima/Marketing/Messages/$type/$year/emailing/$pays$dossier/extraction");
        }
        copy("/var/www/emailing/tinyclues/dedoublon/$fichier","/mnt/sf_P_DRIVE/Millésima/Marketing/Messages/$type/$year/emailing/$pays$dossier/extraction/$fichier");
        rename("/mnt/sf_P_DRIVE/Millésima/Marketing/Messages/$type/$year/emailing/$pays$dossier/extraction/$fichier","/mnt/sf_P_DRIVE/Millésima/Marketing/Messages/$type/$year/emailing/$pays$dossier/extraction/extraction_$nameNewFichier.csv");

    }
    
    function envoiDolist($typeBrief, $codeBrief, $nomFichier, $year, $dossier, $type, $fichier, $usa = false, $verif2 =""){
        $handle = fopen($fichier, 'r');
        //Si on a réussi à ouvrir le fichier
        $countries = array();
        $briefClass = new Millesima_Brief();

        if ($handle) {
            //Tant que l'on est pas à la fin du fichier
            while (!feof($handle)) {
                //On lit la ligne courante
                $ligne = fgets($handle);
                if ($ligne) {
                    $celule = explode(";", $ligne);
                    $paysCom = $celule[1];
                    if($paysCom == "FRANCE"){
                        if(!in_array("F", $countries)) {
                            $countries[] = "F";
                        }
                    }elseif ($paysCom == "BELGIQUE"){
                        if(!in_array("B", $countries)) {
                            $countries[] = "B";
                        }
                    }elseif ($paysCom == "LUXEMBOURG"){
                        if(!in_array("L", $countries)) {
                            $countries[] = "L";
                        }
                    }elseif ($paysCom == "ALLEMAGNE"){
                        if(!in_array("D", $countries)) {
                            $countries[] = "D";
                        }
                    }elseif ($paysCom == "AUTRICHE"){
                        if(!in_array("O", $countries)) {
                            $countries[] = "O";
                        }
                    }elseif ($paysCom == "SUISSE"){
                        $langue = trim($celule[2]);
                        if($langue == "ALLEMAND"){
                            if(!in_array("SA", $countries)) {
                                $countries[] = "SA";
                            }
                        }else if($langue == "FRANCAIS"){
                            if(!in_array("SF", $countries)) {
                                $countries[] = "SF";
                            }
                        }
                    }elseif ($paysCom == "GRANDE BRETAGNE"){
                        if(!in_array("G", $countries)) {
                            $countries[] = "G";
                        }
                    }elseif ($paysCom == "IRLANDE"){
                        if(!in_array("I", $countries)) {
                            $countries[] = "I";
                        }
                    }elseif ($paysCom == "ITALIE"){
                        if(!in_array("Y", $countries)) {
                            $countries[] = "Y";
                        }
                    }elseif ($paysCom == "ESPAGNE"){
                        if(!in_array("E", $countries)) {
                            $countries[] = "E";
                        }
                    }elseif ($paysCom == "PORTUGAL"){
                        if(!in_array("P", $countries)) {
                            $countries[] = "P";
                        }
                    }elseif ($paysCom == "HONG KONG"){
                        if(!in_array("H", $countries)) {
                            $countries[] = "H";
                        }
                    }elseif ($paysCom == "SINGAPOUR"){
                        if(!in_array("SG", $countries)) {
                            $countries[] = "SG";
                        }
                    }elseif ($paysCom == "USA"){
                        if(!in_array("U", $countries)) {
                            $countries[] = "U";
                        }
                    }
                }
            }
            //On ferme le fichier
            fclose($handle);
        }

        $segmentClass = new Millesima_Segment();
        $nomfichiersdolist=array();
        $html = '';

        //ETAPE 2 : Pour chaque pays, creer un import Dolist et memoriser le nom de fichier
        $brief = $briefClass->getBriefByCodeBriefAndTypeBrief($typeBrief, $codeBrief);
        $erreur = false;
        if($usa){
            if(!$brief){
                $brief = $briefClass->getBriefByCodeBriefAndTypeBrief($verif2, $codeBrief);
                if(!$brief){
                    $html .= "Aucun brief créé pour l'extraction $dossier. L'envoi de segment est donc annulé !";
                    $erreur = true;
                }else{
                    $caract = substr($nomFichier,0,1);
                    if($caract == "u"){
                        $nomFichier2 = substr($nomFichier, 1, strlen($nomFichier));
                        copy("/mnt/sf_P_DRIVE/Millésima/Marketing/Messages/$type/$year/emailing/USA/$dossier/extraction/extraction_$nomFichier.csv","fichiers/segment-file/extraction_$nomFichier2.csv");
                        $nomFichier = $nomFichier2;
                    }else{
                        copy("/mnt/sf_P_DRIVE/Millésima/Marketing/Messages/$type/$year/emailing/USA/$dossier/extraction/extraction_$nomFichier.csv","fichiers/segment-file/extraction_$nomFichier.csv");
                    }
                }
            }else{
                copy("/mnt/sf_P_DRIVE/Millésima/Marketing/Messages/$type/$year/emailing/USA/$dossier/extraction/extraction_$nomFichier.csv","fichiers/segment-file/extraction_$nomFichier.csv");
            }
        }else{
            copy("/mnt/sf_P_DRIVE/Millésima/Marketing/Messages/$type/$year/emailing/$dossier/extraction/extraction_$nomFichier.csv","fichiers/segment-file/extraction_$nomFichier.csv");
            if(!$brief){
                $html .= "Aucun brief créé pour l'extraction $dossier. L'envoi de segment est donc annulé !";
                $erreur = true;
            }
        }


        //charger en base les emails
        $segmentClass->chargeEmailBdd("extraction_$nomFichier.csv",true);
        if(!$erreur){
            //ETAPE 3 CREER UN FICHIER AU FORMAT DML CONTENANT EMAIL + PAYSCOM pour chaque pays
            $html .= "<b>La demande de création d'import a été prise en compte.</b><br/>";
            foreach($countries as $country) {
                $nomFile = $segmentClass->createPickFile($country,$nomFichier);
                $return = $segmentClass->sendFileSegmentFtp($nomFichier,$nomFile);
                if($return){
                    $segmentClass->createInBdd($nomFile);
                    $html .= "<b>".$nomFile."</b><br/>";
                } else {
                    $html .= "<b>Erreur envoi ftp".$nomFile."</b><br/>";
                }

            }
        }
        return $html;
    }
}