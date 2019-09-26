
<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dgorski
 * Date: 18/12/13
 * Time: 17:27
 * To change this template use File | Settings | File Templates.
 */
include_once 'millesima_abstract.php';
include_once 'millesima_bdd.php';

class Millesima_Brief extends Millesima_Abstract
{

    /**
     * Function to get list of brief
     * @return mixed
     */
    public function getBriefAllList(){
        $bddClass = new Millesima_Bdd();
        $res= $bddClass->selectAll("SELECT * FROM brief ORDER BY id DESC");
        return $res;
    }

    /**
     * Function to get list of brief can update
     * @return mixed
     */
    public function getBriefModifList(){
        $bddClass = new Millesima_Bdd();
        $res= $bddClass->selectAll("SELECT * FROM brief WHERE statut in (1,2,3,4) ORDER BY id DESC");
        return $res;
    }

    /**
     * Function to get list of brief can update
     * @return mixed
     */
    public function getBriefMessageList(){
        $bddClass = new Millesima_Bdd();
        $res= $bddClass->selectAll("SELECT * FROM brief WHERE statut >= 2 ORDER BY id DESC");
        return $res;
    }

    /**
     * Function to get list of brief can update
     * @param int statut
     * @return mixed
     */
    public function getBriefStatutList($statut){
        $bddClass = new Millesima_Bdd();
        $res= $bddClass->selectAll('SELECT * FROM brief WHERE statut in ('.$statut.') ORDER BY id DESC');
        return $res;
    }

    /**
     * Function to get status off one brief
     * @param int $briefId
     * @return int
     */
    public function getStatutBrief($briefId){
        $bddClass = new Millesima_Bdd();
        $res = $bddClass->selectone("SELECT statut FROM brief WHERE id = (?)",array($briefId),'statut');
        if( is_array($res) && count($res)>0){
            return $res[0];
        } else {
            return false;
        }
    }

    /**
     * Function to get status off one brief
     * @param int $briefId
     * @return int
     */
    public function getNbChampTradBrief($briefId){
        $bddClass = new Millesima_Bdd();
        $res = $bddClass->selectAll('SELECT nbChampTrad FROM brief WHERE id ='.$briefId);
        return $res[0];
    }

    /**
     * Function to get one brief
     * @param int $briefId
     * @return mixed
     */
    public function getBrief($briefId){
        $bddClass = new Millesima_Bdd();
        $res= $bddClass->selectAll('SELECT * FROM brief WHERE id = '.$briefId.' ORDER BY id DESC');
        if(count($res)>0){
            return $res[0];
        } else {
            return false;
        }
    }

    /**
     * Function to uptdate status off one brief
     * @param int $status
     * @param int $idBrief
     * @return mixed
     */
    public function updateStatus($status,$idBrief){
        $bddClass = new Millesima_Bdd();
        $bddClass->update("UPDATE brief SET statut = (?) where id = (?)",array($status,$idBrief));
    }

    /**
     * Function to delete one brief
     * @param int $idBrief
     * @return mixed
     */
    public function delete($idBrief){
        $bddClass = new Millesima_Bdd();
        $bddClass->delete("Delete from brief WHERE id = (?)",array($idBrief));
    }

    public function getBriefByCodeBriefAndTypeBrief($typeBrief, $codeBrief){
        $bddClass = new Millesima_Bdd();
        $res= $bddClass->selectAll("SELECT * FROM brief WHERE typebrief = '".$typeBrief."' and code = '".$codeBrief."'");
        if(count($res)>0){
            return true;
        } else {
            return false;
        }
    }

    public function getBriefIdByStatut($statut){
        $bddClass = new Millesima_Bdd();
        $res= $bddClass->selectAll("SELECT id FROM brief WHERE statut >= '".$statut."' AND created_at >= '2017-05-28'");
        return $res;
    }

    public function getBriefIdByStatutNotComplete($statut){
        $bddClass = new Millesima_Bdd();
        $res= $bddClass->selectAll("SELECT id FROM brief WHERE statut >= '".$statut."' AND statut < 9 AND created_at >= '2017-05-28'");
        return $res;
    }

    public function getBriefMessageMounted($id){
        $bddClass = new Millesima_Bdd();
        $message_mounted = $bddClass->selectone("SELECT * FROM brief where id = ?", array($id),'messagemounted');
        return $message_mounted;
    }

    public function getNbPaysByBriefId($idBrief){
        $bddClass = new Millesima_Bdd();
        $pays = $bddClass->selectone("SELECT * FROM brief WHERE id = ?",array($idBrief),'pays');
        $pays = explode("|", $pays);
        $nbPays = 0;
        foreach ($pays as $titi){
            $nbPays++;
        }

        return $nbPays;
    }

    /**
     * Function do update brief
     * @param $data
     * @param $id
     * @param null $statut
     * @return string
     */
    public function update($data,$id, $statut=null){
        $bddClass = new Millesima_Bdd();

        //fonction to concat pays and offre sup
        $pays = '';
        $nbPays = 0;
        $nbChampATraduire =0;
        foreach ($data as $key => $value) {
            if(preg_match('/^pays/',$key)){
                $value = str_replace('pays_','',$key);
                $nbPays ++;
                if($pays == '' ){
                    $pays = $value;
                } else {
                    $pays .= '|'.$value;
                }
                unset($data[$key]);
            }
        }
        $data['pays'] = $pays;

        if($data['objfr'] !=""){
            $nbChampATraduire ++;
        }
        if($data['subobj'] !=""){
            $nbChampATraduire ++;
        }
        if($data['wording'] !=""){
            $nbChampATraduire ++;
        }
        if($data['titredescsousimg'] !=""){
            $nbChampATraduire ++;
        }
        if($data['descsousimg'] !=""){
            $nbChampATraduire ++;
        }
        if($data['bpinfo'] !=""){
            $nbChampATraduire ++;
        }

        $data['nboffsup'] = (int) $data['nboffsup'];
        if($data['nboffsup'] > 0){
            for ($i=1; $i<$data['nboffsup']+1; $i++) {
                $tabOsTitre[] = $data['article'.$i.'ostitre'];
                if($data['article'.$i.'ostitre'] != ""){
                    $nbChampATraduire ++;
                }
                $trad['article'.$i.'ostitretrad'] = $data['article'.$i.'ostitre'];
                unset($data['article'.$i.'ostitre']);
                $tabOsUrl[] = $data['article'.$i.'osurl'];
                unset($data['article'.$i.'osurl']);
                $tabOsDesc[] = $data['article'.$i.'osdesc'];
                if($data['article'.$i.'osdesc'] != ""){
                    $nbChampATraduire ++;
                }
                $trad['article'.$i.'osdesctrad'] = $data['article'.$i.'osdesc'];
                unset($data['article'.$i.'osdesc']);
            }
            $data['ostitre'] = serialize($tabOsTitre);
            $data['osurl'] = serialize($tabOsUrl);
            $data['osdesc'] = serialize($tabOsDesc);
        }

        $data['nbChampTrad'] = $nbChampATraduire;

        foreach ($data as $key=>$value){
            if(!preg_match('/^article/',$key)){
                if($key == 'dateenvoi'){
                    $value = str_replace('/', '-', $value);
                    $value = date('Y-m-d 00:00:00', strtotime($value));
                }
                if($key == 'validite'){
                    $value = str_replace('/', '-', $value);
                    $value = date('Y-m-d 00:00:00', strtotime($value));
                }
                if($key == 'blockpush' || $key == 'offsup' || $key == 'slide' || $key == 'visuemail' || $key == 'blcimg' || $key == 'visuface' || $key == 'blccom'){
                    $value = (int) $value;
                }
                if($data['bpinfo'] != '') {
                    $trad['bpinfotrad'] = $data['bpinfo'];
                }
                $bddClass->update("UPDATE brief SET ".$key."= (?) where  id = (?)",array($value,$id));
                //$bddClass->update("UPDATE config SET  value=value+1 where  name = (?)",array($cle));
            }
        }
        $date = date("Y-m-d H:i:s");
        $brief = $this->getBrief($id);
        $dataType = null;
        if($statut == 2){
            $dataType = 'validmarket_at';
        }else if($statut == 3){
            $dataType = 'validmarianne_at';
        }else if($statut == 4){
            $dataType = 'validtrad_at';
            if ($brief['validmarianne_at'] == null) {
                $bddClass->update("UPDATE brief SET validmarianne_at = (?) where  id = (?)", array($date, $id));
            }
        }
        if($dataType != null) {
            if ($brief[$dataType] == null) {
                $bddClass->update("UPDATE brief SET ".$dataType." = (?) where  id = (?)", array($date, $id));
            }
        }
        $bddClass->update("UPDATE brief SET updated_at = (?) where  id = (?)", array($date, $id));

        $tradClass = new Millesima_Traduction();
        if($nbPays == 1){
            $trad['lang_id'] = $pays;
            $trad['brief_id'] = $id;
            $trad['descsousimgtrad'] = $data['descsousimg'];
            $trad['titredescsousimgtrad'] = $data['titredescsousimg'];
            $trad['wordingtrad'] = $data['wording'];
            $trad['subobjtrad'] = $data['subobj'];
            $trad['objtrad'] = $data['objfr'];

            $tradClass->saveTraduction($trad);
        }


        //update config identifiant du message
        if($data['typebrief'] == 'livrable_eu'){
            $cle = 'iosliv';
        }else if($data['typebrief'] == 'primeur_eu'){
            $cle = 'iosprim';
        }else if($data['typebrief'] == 'livrable_us'){
            $cle = 'uiosliv';
        }else if($data['typebrief'] == 'primeur_us'){
            $cle = 'uiosprim';
        }else if($data['typebrief'] == 'edv'){
            $cle = 'edv';
        }else if($data['typebrief'] == 'staff_pick'){
            $cle = 'uiospick';
        }else if($data['typebrief'] == 'partenaire'){
            $cle = 'iospart';
        }
        //text de retour de la modification du brief
        $html = '';
        //$html .="Modification du brief pris en compte. Identifiant brief : ".$cle.$data['code'];
        return $html;
    }

    /**
     * Function to create brief
     * @param $data
     * @param bool $briefcopie
     * @return mixed
     */
    public function create($data, $briefcopie = false){

        $bddClass = new Millesima_Bdd();
        $nbChampATraduire =0;
        //fonction to concat pays and offre sup
        $pays = '';
        $nbPays = 0;
        foreach ($data as $key => $value) {
            if(preg_match('/^pays/',$key)){
                $nbPays ++;
                $value = str_replace('pays_','',$key);
                if($pays == '' ){
                    $pays = $value;
                } else {
                    $pays .= '|'.$value;
                }
                unset($data[$key]);
            }
        }
        $data['pays'] = $pays;

        if($data['objfr'] !=""){
            $nbChampATraduire ++;
        }
        if($data['subobj'] !=""){
            $nbChampATraduire ++;
        }
        if($data['wording'] !=""){
            $nbChampATraduire ++;
        }
        if($data['titredescsousimg'] !=""){
            $nbChampATraduire ++;
        }
        if($data['descsousimg'] !=""){
            $nbChampATraduire ++;
        }
        if($data['bpinfo'] !=""){
            $nbChampATraduire ++;
        }

        if($data['nboffsup'] > 0){
            for ($i=1; $i<$data['nboffsup']+1; $i++) {
                $tabOsTitre[] = $data['article'.$i.'ostitre'];
                if($data['article'.$i.'ostitre'] != ""){
                    $nbChampATraduire ++;
                }
                $trad['article'.$i.'ostitretrad'] = $data['article'.$i.'ostitre'];
                unset($data['article'.$i.'ostitre']);
                $tabOsUrl[] = $data['article'.$i.'osurl'];
                unset($data['article'.$i.'osurl']);
                $tabOsDesc[] = $data['article'.$i.'osdesc'];
                if($data['article'.$i.'osdesc'] != ""){
                    $nbChampATraduire ++;
                }
                $trad['article'.$i.'osdesctrad'] = $data['article'.$i.'osdesc'];
                unset($data['article'.$i.'osdesc']);
            }
            $data['ostitre'] = serialize($tabOsTitre);
            $data['osurl'] = serialize($tabOsUrl);
            $data['osdesc'] = serialize($tabOsDesc);
        } else {
            $data['ostitre'] = '';
            $data['osurl'] = '';
            $data['osdesc'] = '';
        }

        $data['nbChampTrad'] = $nbChampATraduire;

        //rempli les attribut si vide création des attributs de la requete
        $attributs = '';
        $pointInterogation = '';
        $values = array();
        foreach ($data as $key => $value){
            if(!preg_match('/^article/',$key)){
                if($attributs == '' ){
                    $attributs = $key;
                } else {
                    $attributs .= ','.$key;
                }
                if($pointInterogation == '' ){
                    $pointInterogation = '(?';
                } else {
                    $pointInterogation .= ',?';
                }
                if($key == 'dateenvoi'){
                    $value = str_replace('/', '-', $value);
                    $value = date('Y-m-d 00:00:00', strtotime($value));
                }
                if($key == 'validite'){
                    $value = str_replace('/', '-', $value);
                    $value = date('Y-m-d 00:00:00', strtotime($value));
                }
                
                if($key == 'blockpush' || $key == 'offsup' || $key == 'slide' || $key == 'visuemail' || $key == 'blcimg' || $key == 'visuface' || $key == 'blccom' || $key =='nbChampTrad'){
                    $values[] = (int) $value;
                } else {
                    $values[] = $value;
                }
                if($data['bpinfo'] != '') {
                    $trad['bpinfotrad'] = $data['bpinfo'];
                }
            }
        }
        $dateBrief = date("Y-m-d H:i:s");
        $values[] = $dateBrief;
        $attributs .= ',created_at';
        $pointInterogation .= ',?)';

        //insert in bdd du brief
        $requete =  "INSERT INTO brief (".$attributs.")
                    VALUES ".$pointInterogation;
        $result = $bddClass->insert($requete,$values);
        if ($result == "0") {
            //text de retour de la non création du brief
            $html = '';
            $html .="Le brief n'a pas été créé, une erreur est survenue.";
            $return['html'] = $html;
            $return['id'] = '0';
            return $return;
        }

        //update config identifiant du message
        if($data['typebrief'] == 'livrable_eu'){
            $cle = 'iosliv';
        }else if($data['typebrief'] == 'primeur_eu'){
            $cle = 'iosprim';
        }else if($data['typebrief'] == 'livrable_us'){
            $cle = 'uiosliv';
        }else if($data['typebrief'] == 'primeur_us'){
            $cle = 'uiosprim';
        }else if($data['typebrief'] == 'edv'){
            $cle = 'edv';
        }else if($data['typebrief'] == 'staff_pick'){
            $cle = 'uiospick';
        }else if($data['typebrief'] == 'partenaire'){
            $cle = 'iospart';
        }
        $bddClass->update("UPDATE config SET  value=value+1 where  name = (?)",array($cle));

        //text de retour de la création du brief
        $html = '';
        $html .="Le brief a été créé. Identifiant brief : ".$cle.$data['code'];

        $return['html'] = $html;
        $return['id'] = $result;


        $tradClass = new Millesima_Traduction();
        if($nbPays == 1 && $briefcopie == false){
            $trad['lang_id'] = $pays;
            $trad['brief_id'] = $result;
            $trad['pays'] = $pays;
            $trad['descsousimgtrad'] = $data['descsousimg'];
            $trad['titredescsousimgtrad'] = $data['titredescsousimg'];
            $trad['wordingtrad'] = $data['wording'];
            $trad['subobjtrad'] = $data['subobj'];
            $trad['objtrad'] = $data['objfr'];

            $tradClass->saveTraduction($trad);
        }
        return $return;
    }
}