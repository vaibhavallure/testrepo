<?php

/**
 * Created by PhpStorm.
 * User: ybelledent
 * Date: 05/12/2016
 * Time: 14:42
 */
include_once 'millesima_abstract.php';
include_once 'millesima_bdd.php';
include_once 'millesima_image.php';
include_once 'millesima_traduction.php';
class Millesima_Messagedata extends Millesima_Abstract
{
    public function getMessageDataByIdBrief($briefId)
    {
        $bddClass = new Millesima_Bdd();
        $res = $bddClass->selectAll('SELECT * FROM messagedata WHERE brief_id = ' . $briefId . ' ORDER BY id DESC');
        if (count($res) > 0) {
            return $res[0];
        } else {
            return false;
        }
    }

    public function getMessageDataList()
    {
        $bddClass = new Millesima_Bdd();
        $res = $bddClass->selectAll("SELECT * FROM messagedata ORDER BY id DESC");
        return $res;
    }

    public function saveMessageData($data)
    {
        $bddClass = new Millesima_Bdd();
        $imgClass = new Millesima_Image();
        $tradClass = new Millesima_Traduction();

        $allPays = array("F", "B", "L", "D", "O", "SA", "SF", "G", "I", "Y", "E", "P", "H", "SG", "U");
        $langue = array();
        $image = array();

        $pays = '';
        $payspush = '';
        $payscgv = '';
        foreach ($data as $key => $value) {
            //Récupère les pays de sélectionné partie "Gestion Pays"
            if ($key == 'pays') {
                $pays = implode("|", $value);
            }
            //Si exceptions est coché tout en bas, alors récupération des pays sélectionné s'il y en a
            if ($key == 'push_exceptions_pays') {
                if (isset($data["push_exceptions_pays"])) {
                    $payspush = implode("|", $value);
                }
            }
            //Si "CGV particulières" est coché, alors récupération des pays sélectionné s'il y en a
            if ($key == 'cgv_exceptions') {
                if (isset($data["cgv_exceptions"])) {
                    $payscgv = implode("|", $value);
                }
            }
        }
        unset($data["pays"]);
        $data['pays'] = $pays;
        unset($data["push_exceptions_pays"]);
        $data["push_exceptions_pays"] = $payspush;
        unset($data["cgv_exceptions"]);
        $data["cgv_exceptions"] = $payscgv;

        foreach ($allPays as $lettre) {
            //traitement partie "Gestion Sous-objets"
            if ($data["objet-" . $lettre] != "") {
                $langue["objet-" . $lettre] = $data["objet-" . $lettre];
            } else {
                $langue["objet-" . $lettre] = null;
            }
            unset($data["objet-" . $lettre]);

            //traitement partie "Titre de l'offre principale"
            if ($data["titre_" . $lettre] != "") {
                $langue["titre_" . $lettre] = $data["titre_" . $lettre];
            } else {
                $langue["titre_" . $lettre] = null;
            }
            unset($data["titre_" . $lettre]);

            //traitement partie "Description sous l'image principale"
            if ($data["desctitre" . $lettre] != "") {
                $langue["desctitre" . $lettre] = $data["desctitre" . $lettre];
            } else {
                $langue["desctitre" . $lettre] = null;
            }
            if ($data["desctext" . $lettre] != "") {
                $langue["desctext" . $lettre] = $data["desctext" . $lettre];
            } else {
                $langue["desctext" . $lettre] = null;
            }
            unset($data["desctitre" . $lettre]);
            unset($data["desctext" . $lettre]);
        }

        //traitement partie "Section articles supplemantaires"
        if (($data["section_article"] != 0) && ($data["articles_nb"] > 1)) {
            for ($i = 1; $i <= $data["articles_nb"]; $i++) {
                foreach ($allPays as $lettre) {
                    if ($data["article" . $i . "text" . $lettre] != "") {
                        $langue["article" . $i . "text" . $lettre] = $data["article" . $i . "text" . $lettre];
                    } else {
                        $langue["article" . $i . "text" . $lettre] = null;
                    }
                    if ($data["article" . $i . "titre" . $lettre] != "") {
                        $langue["article" . $i . "titre" . $lettre] = $data["article" . $i . "titre" . $lettre];
                    } else {
                        $langue["article" . $i . "titre" . $lettre] = null;
                    }
                    unset($data["article" . $i . "text" . $lettre]);
                    unset($data["article" . $i . "titre" . $lettre]);
                }
                $langue["article" . $i . "_url"] = $data["article" . $i . "_url"];
                unset($data["article" . $i . "_url"]);
                if (isset($data["article" . $i . "_url_content"])) {
                    $langue["article" . $i . "_url_content"] = $data["article" . $i . "_url_content"];
                    unset($data["article" . $i . "_url_content"]);
                } else {
                    $langue["article" . $i . "_url_content"] = null;
                }
                if (isset($data["article" . $i . "_nourl"])) {
                    $langue["article" . $i . "_nourl"] = $data["article" . $i . "_nourl"];
                    unset($data["article" . $i . "_nourl"]);
                } else {
                    $langue["article" . $i . "_nourl"] = null;
                }
                if (isset($data["article" . $i . "titreupper"])) {
                    $langue["article" . $i . "titreupper"] = $data["article" . $i . "titreupper"];
                    unset($data["article" . $i . "titreupper"]);
                } else {
                    $langue["article" . $i . "titreupper"] = null;
                }
				if (isset($data["article" . $i . "_astart"])) {
                    $langue["article" . $i . "_astart"] = $data["article" . $i . "_astart"];
                    unset($data["article" . $i . "_astart"]);
                } else {
                    $langue["article" . $i . "_astart"] = null;
                }
				if (isset($data["article" . $i . "_artimgprim"])) {
                    $langue["article" . $i . "_artimgprim"] = $data["article" . $i . "_artimgprim"];
                    unset($data["article" . $i . "_artimgprim"]);
                } else {
                    $langue["article" . $i . "_artimgprim"] = null;
                }
                if (isset($data["article" . $i . "_exceptions"])) {
                    $langue["article" . $i . "_exceptions"] = $data["article" . $i . "_exceptions"];
                    unset($data["article" . $i . "_exceptions"]);
                } else {
                    $langue["article" . $i . "_exceptions"] = null;
                }
                if (isset($data["article" . $i . "_exceptions_pays"])) {
                    $articleExceptionsPays = implode("|", $data["article" . $i . "_exceptions_pays"]);
                    $langue["article" . $i . "_exceptions_pays"] = $articleExceptionsPays;
                    unset($data["article" . $i . "_exceptions_pays"]);
                } else {
                    $langue["article" . $i . "_exceptions_pays"] = null;
                }
                $langue["article" . $i . "typebtn"] = $data["article" . $i . "typebtn"];
                unset($data["article" . $i . "typebtn"]);
            }
        }
        elseif (($data["section_article"] != 0) && ($data["articles_nb"] == 1)) {
            foreach ($allPays as $lettre) {
                if ($data["article1text" . $lettre] != "") {
                    $langue["article1text" . $lettre] = $data["article1text" . $lettre];
                } else {
                    $langue["article1text" . $lettre] = null;
                }
                if ($data["article1titre" . $lettre] != "") {
                    $langue["article1titre" . $lettre] = $data["article1titre" . $lettre];
                } else {
                    $langue["article1titre" . $lettre] = null;
                }
                unset($data["article1text" . $lettre]);
                unset($data["article1titre" . $lettre]);
            }
            $langue["article1_url"] = $data["article1_url"];
            unset($data["article1_url"]);
            if (isset($data["article1_url_content"])) {
                $langue["article1_url_content"] = $data["article1_url_content"];
                unset($data["article1_url_content"]);
            } else {
                $langue["article1_url_content"] = null;
            }
            if (isset($data["article1_nourl"])) {
                $langue["article1_nourl"] = $data["article1_nourl"];
                unset($data["article1_nourl"]);
            } else {
                $langue["article1_nourl"] = null;
            }
            if (isset($data["article1titreupper"])) {
                $langue["article1titreupper"] = $data["article1titreupper"];
                unset($data["article1titreupper"]);
            } else {
                $langue["article1titreupper"] = null;
            }
            if (isset($data["article1_astart"])) {
                $langue["article1_astart"] = $data["article1_astart"];
                unset($data["article1_astart"]);
            } else {
                $langue["article1_astart"] = null;
            }
            if (isset($data["article1_artimgprim"])) {
                $langue["article1_artimgprim"] = $data["article1_artimgprim"];
                unset($data["article1_artimgprim"]);
            } else {
                $langue["article1_artimgprim"] = null;
            }
            if (isset($data["article1_exceptions"])) {
                $langue["article1_exceptions"] = $data["article1_exceptions"];
                unset($data["article1_exceptions"]);
            } else {
                $langue["article1_exceptions"] = null;
            }
            if (isset($data["article1_exceptions_pays"])) {
                $article1ExceptionsPays = implode("|", $data["article1_exceptions_pays"]);
                $langue["article1_exceptions_pays"] = $article1ExceptionsPays;
                unset($data["article1_exceptions_pays"]);
            } else {
                $langue["article1_exceptions_pays"] = null;
            }
            $langue["article1typebtn"] = $data["article1typebtn"];
            unset($data["article1typebtn"]);
        }
        else {
            foreach ($allPays as $lettre) {
                unset($data["article1text" . $lettre]);
                unset($data["article1titre" . $lettre]);
                unset($data["article1_url"]);
                unset($data["article1_nourl"]);
                unset($data["article1_exceptions"]);
                unset($data["article1titreupper"]);
                unset($data["article1typebtn"]);
            }
        }



        //traitement partie "Image principale"
        if ($data["block_image"] == 1) {
            // Parti "1 seule image"
            if (isset($data["bandeau_unique"])) {
                $return = $this->saveImageTab('bdunq', $data, 'bandeau_unique', null);
                $data = $return['data'];
                unset($data["bandeau_unique"]);
                $image[] = $return['image'];
            }
            // Parti "Plusieurs images sur une seule colonne"
            if (isset($data["bandeau_tranches"])) {
                $nb = $data["bandeau_tranches_nb"];
                unset($data["bandeau_tranches_nb"]);
                for ($i = 1; $i <= $nb; $i++) {
                    $return = $this->saveImageTab('bd' . $i, $data, 'bandeau-tranches', $nb);
                    $data = $return['data'];
                    $image[] = $return['image'];
                }
                unset($data["bandeau_tranches"]);
            }

            // Parti "1-2x2-1"
            if (isset($data["bandeau_1-2x2-1"])) {
                $nb = $data["bandeau_1-2x2-1_nb"];
                unset($data["bandeau_1-2x2-1_nb"]);
                for ($i = 1; $i <= $nb; $i++) {
                    $return = $this->saveImageTab('bd' . $i, $data, 'bandeau_1-2x2-1', $nb);
                    $data = $return['data'];
                    $image[] = $return['image'];
                }
                unset($data["bandeau_1-2x2-1"]);
            }
            if (isset($data["bandeau_primeurs"])) {
                $return = $this->saveImageTab(null, $data, 'bandeau_primeurs', null);
                $data = $return['data'];
                $image[] = $return['image'];
                unset($data["bandeau_primeurs"]);
            }

            if (isset($data["bdunq_height"])) {
                unset($data["bdunq_height"]);
            }
            if (isset($data["bdunq_type_image"])) {
                unset($data["bdunq_type_image"]);
            }
            if (isset($data["bdunq_url"])) {
                unset($data["bdunq_url"]);
            }
            if (isset($data["bdprim_url"])) {
                unset($data["bdprim_url"]);
            }
            if (isset($data["bandeau_1-2x2-1_nb"])) {
                unset($data["bandeau_1-2x2-1_nb"]);
            }
            if (isset($data["bandeau_tranches_nb"])) {
                unset($data["bandeau_tranches_nb"]);
            }
            if (isset($data["bdunq_nourl"])) {
                unset($data["bdunq_nourl"]);
            }
            if (isset($data["bdunq_exceptions"])) {
                unset($data["bdunq_exceptions"]);
            }
            if (isset($data["bdunq_exceptions_pays"])) {
                unset($data["bdunq_exceptions_pays"]);
            }
        }
        else {
            unset($data["bdunq_height"]);
            unset($data["bdunq_type_image"]);
            unset($data["bdunq_url"]);
            unset($data["bdprim_url"]);
        }


        //rempli les attributs si vide création des attributs de la requete
        $attributs = '';
        $pointInterogation = '';
        $values = array();
        foreach ($data as $value => $val){
            if(preg_match("/bd/",$value)){
                unset($data[$value]);
            }
        }

        if (!isset($data["cgv_prim_actuelle"])) {
            $data["cgv_prim_actuelle"] = null;
        }
        if (!isset($data["cgv_prim_prec"])) {
            $data["cgv_prim_prec"] = null;
        }
        if (!isset($data["menu_sans_primeurs"])) {
            $data["menu_sans_primeurs"] = null;
        }
        if (!isset($data["fdpo_bandeau"])) {
            $data["fdpo_bandeau"] = null;
        }
        if (!isset($data["fdpo_conditions"])) {
            $data["fdpo_conditions"] = null;
        }
        if (!isset($data["other_cgv"])) {
            $data["other_cgv"] = null;
        }
        if (!isset($data["desctitreupper"])) {
            $data["desctitreupper"] = null;
        }
        if (!isset($data["iscodepromo"])) {
            $data["iscodepromo"] = null;
        }
        if (!isset($data["astdesc"])) {
            $data["astdesc"] = null;
        }
        if (!isset($data["push_url_content"])) {
            $data["push_url_content"] = null;
        }
        if (!isset($data["push_exceptions"])) {
            $data["push_exceptions"] = null;
        }

        foreach ($data as $key => $value) {
            if ($attributs == '') {
                $attributs = $key;
            } else {
                $attributs .= ',' . $key;
            }
            if ($pointInterogation == '') {
                $pointInterogation = '(?';
            } else {
                $pointInterogation .= ',?';
            }
            if ($key == 'dateenvoi' || $key == 'datevalide' || $key == 'datefdpo') {
                $value = str_replace('/', '-', $value);
                $value = date('Y-m-d 00:00:00', strtotime($value));
            }
            if ($key == 'articles_nb') {
                $value = (int)$value;
            }
            $values[] = $value;
        }
        $pointInterogation .= ')';

        $messageDataExist = $this->getMessageDataByIdBrief($data['brief_id']);
        if ($messageDataExist) {
            $imgClass->deleteImgBdd($messageDataExist['id']);
            $tradClass->deleteTradByIdMessageData($messageDataExist['id']);
            $attr = explode(",",$attributs);
            $i = 0;
            foreach ($values as $value => $val) {
                $bddClass->update("UPDATE messagedata SET ".$attr[$i]."= (?) where id = (?)", array($val, $messageDataExist['id']));
                $i++;
            }

            $result = $messageDataExist['id'];
            $html = 'Message modifié';
        }else{
            //insert in bdd du message
            $requete = "INSERT INTO messagedata (" . $attributs . ")
                    VALUES " . $pointInterogation;
            $result = $bddClass->insert($requete, $values);

            if ($result != "0") {
                $html = 'Message sauvegardé';
            } else {
                $html = 'Problème sauvegarde message';
            }
        }

        // insert in bdd des langues du message
        $attributsTrad = "brief_id, idmessagedata, type, value";
        $pointInterogationlangue = "(?,?,?,?)";
        $requeteTrad = "INSERT INTO traduction (" . $attributsTrad . ")
                    VALUES " . $pointInterogationlangue;
        foreach ($langue as $key => $value) {
            $valuesTrad = array();
            $valuesTrad[] = null;
            $valuesTrad[] = $result;
            $valuesTrad[] = $key;
            $valuesTrad[] = $value;

            $resultlangue = $bddClass->insert($requeteTrad, $valuesTrad);
            if ($resultlangue == 0) {
                die("Les différentes traductions ne sont pas enregistrés - Problème de sauvegarde");
            }
        }

        // insert in bdd des images du message
        foreach ($image as $key => $value) {
            $return = $imgClass->saveImgBdd($value, $result);
            $html .= $return;
        }
        return $html;
    }

    public function saveImageTab($nom, $data, $type = null, $nbTranche = null)
    {
        $image["type"] = $type;
        $image["nbtranche"] = $nbTranche;

        if (isset($data[$nom."_height"])) {
            $image["hauteur"] = $data[$nom."_height"];
            unset($data[$nom."_height"]);
        }
        if (isset($data[$nom."_type_image"])) {
            $image["extension"] = $data[$nom."_type_image"];
            unset($data[$nom."_type_image"]);
        }
        if (isset($data[$nom."_url"])) {
            $image["typeurl"] = $data[$nom."_url"];
            unset($data[$nom."_url"]);
        }
        if (isset($data[$nom."_url_content"])) {
            $image["contenturl"] = $data[$nom."_url_content"];
            unset($data[$nom."_url_content"]);
        }
        if (isset($data[$nom."_nourl"])) {
            $image["sansurl"] = $data[$nom."_nourl"];
            unset($data[$nom."_nourl"]);
        }
        if (isset($data[$nom."_exceptions"])) {
            $image["exception"] = $data[$nom."_exceptions"];
            unset($data[$nom."_exceptions"]);
        }
        if (isset($data[$nom."_exceptions_pays"])) {
            $paysException = implode("|", $data[$nom."_exceptions_pays"]);
            $image["exceptionpays"] = $paysException;
            unset($data[$nom."_exceptions_pays"]);
        }

        if($nom == null){
            if (isset($data["bdprim_url"]) && $type == "bandeau_primeurs") {
            $image["typeurl"] = $data["bdprim_url"];
            unset($data["bdprim_url"]);
            }
            if (isset($data["bdprim_url_content"])) {
                $image["contenturl"] = $data["bdprim_url_content"];
                unset($data["bdprim_url_content"]);
            }
        }

        $return['image'] = $image;
        $return['data'] = $data;

        return $return;
    }
}