
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
include_once 'apitoselligente.php';
require_once 'library/PHPExcel.php';
require_once 'library/PHPExcel/IOFactory.php';

class Millesima_Segment extends Millesima_Abstract
{
    const REPOSITORY_SEGMENT = "fichiers/segment-file";

    /**
     * Function to create message in dolist
     *
     * @param string $leseg
     * @return string
     */
    public function createInBdd($leseg){
        $bddClass = new Millesima_Bdd();
        $res = $this->getSegmentByName($leseg);
        if(!$res){
            $bddClass->insert("INSERT INTO segment_selligente (name,status,created_at) VALUES (?,?,?)",array($leseg,'local',date('Y-m-d H:i:s')));
        } else {
            $bddClass->update("UPDATE segment_selligente SET created_at = ? WHERE name = ?",array(date('Y-m-d H:i:s'),$leseg));
        }

    }

    /**
     * Function to get list of segment
     * @return mixed
     */
    public function getSegmentList(){
        $bddClass = new Millesima_Bdd();
        $res= $bddClass->selectAll("SELECT * FROM segment_selligente ORDER BY created_at;");
        return $res;
    }

    /**function to get info by id segment
     * @param $id
     * @param $info
     * @return mixed
     */
    public function getInfoById ($id,$info){
        $bddClass = new Millesima_Bdd();
        $res = $bddClass->selectone("SELECT * FROM segment_selligente WHERE segment_id = ?",array($id),$info);
        return $res;
    }

    /**
     * Function to get one segment by name
     * @param string $name
     * @return mixed
     */
    public function getSegmentByName($name){
        $bddClass = new Millesima_Bdd();
        $res= $bddClass->selectAll("SELECT * FROM segment_selligente WHERE name = '".$name."' ORDER BY id DESC");
        if(count($res)>0){
            $segment = $res[0];
            return $res[0];
        } else {
            return false;
        }
    }

    /**
     * Function to get one segment by name and status
     * @param string $name
     * @param string $status
     * @return mixed
     */
    public function getSegmentByNameAndStatus($name,$status){
        $bddClass = new Millesima_Bdd();
        $res= $bddClass->selectAll("SELECT * FROM segment_selligente WHERE name = '".$name."' AND status = '".$status."'  ORDER BY id DESC");
        if(count($res)>0){
            $segment = $res[0];
            return $res[0];
        } else {
            return false;
        }
    }

    /**
     * Function to import segment file  in db
     */
    public function chargeEmailBdd($fileName,$tinyclues){
        $bddClass = new Millesima_Bdd();

        //truncate bdd dolistsegment
        $bddClass->truncate("TRUNCATE TABLE dolistsegment");

        $cellRef = 0;
        $cellPC = 1;
        $cellLangue = 2;

        //parse file csv and get row, insert data in dolistsegment
        $objReader = PHPExcel_IOFactory::createReader('CSV');
        $objReader->setDelimiter(';');
        $objReader->setSheetIndex(0);
        $objPHPExcel = $objReader->load(self::REPOSITORY_SEGMENT."/".$fileName);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $highestRow = $objWorksheet->getHighestRow();
        for ($row = 2; $row <= $highestRow; ++$row) {
            $refpick = $objWorksheet->getCellByColumnAndRow($cellRef, $row)->getValue();
            $paysCom = $objWorksheet->getCellByColumnAndRow($cellPC, $row)->getValue();
            $lalangue = $objWorksheet->getCellByColumnAndRow($cellLangue, $row)->getValue();
            $bddClass->insert("INSERT INTO dolistsegment(email,payscom,langue) VALUES (?,?,?)",array($refpick,$paysCom,$lalangue));
        }

        //TODO LIBERER mémoire et detruire objet qui servent plus
        $objReader='';
        $objPHPExcel='';
        $objWorksheet='';
        $highestRow='';
    }



    /**
     * Function to create Dm File for create segment
     * @param $country
     * @param $nameSegment
     * @return string
     */
    public function createPickFile($country,$nameSegment){
        $bddClass = new Millesima_Bdd();
        $entete="Refpick\r\n";

        //write line in file
        $dossier = self::REPOSITORY_SEGMENT."/".$nameSegment;
        if(!file_exists($dossier)){
            mkdir($dossier);
        }

        $language2 = '';
        if ($country == "F") {
            $paysCom = "FRANCE";
        } elseif ($country == "B") {
            $paysCom = "BELGIQUE";
        } elseif ($country == "L") {
            $paysCom = "LUXEMBOURG";
        } elseif ($country == "D") {
            $paysCom = "ALLEMAGNE";
        } elseif ($country == "O") {
            $paysCom = "AUTRICHE";
        } elseif ($country == "SA") {
            $paysCom = "SUISSE";
            $language2="ALLEMAND";
        } elseif ($country == "SF") {
            $paysCom = "SUISSE";
            $language2="FRANCAIS";
        } elseif ($country == "G") {
            $paysCom = "GRANDE BRETAGNE";
        } elseif ($country == "I") {
            $paysCom = "IRLANDE";
        } elseif ($country == "Y") {
            $paysCom = "ITALIE";
        } elseif ($country == "E") {
            $paysCom = "ESPAGNE";
        } elseif ($country == "P") {
            $paysCom = "PORTUGAL";
        } elseif ($country == "H") {
            $paysCom = "HONG KONG";
        } elseif ($country == "SG") {
            $paysCom = "SINGAPOUR";
        } elseif ($country == "U") {
            $paysCom = "USA";
        } elseif ($country == "U") {
            $paysCom = "USA";
        }

        //get prospect in bdd from payscom
        if ($language2 == '') {
            $res= $bddClass->selectAll("SELECT email FROM dolistsegment WHERE payscom = ?",$paysCom);
        } else {
            $res= $bddClass->selectAll("SELECT email FROM dolistsegment WHERE payscom = ? AND langue = ? ",array($paysCom, $language2));
            unset($language2);
        }

        //create line file
        $line='';
        foreach($res as $proscpect) {
            $line.=$proscpect["email"]."\r\n";
        }

        if ($country == "F") {
            $line.="1116188"."\r\n";//"lanton@millesima.com"."\r\n";
            $line.="1009006"."\r\n";//"mdutoya@millesima.com"."\r\n";
            $line.="1409739"."\r\n";//"smonneau@millesima.com"."\r\n";
            //Si fichier DML USA
        }elseif ($country == "U") {
            $line.="U14044"."\r\n";//"hobernard@millesima.com"."\r\n";
            $line.="U44291"."\r\n";//"imiossec@millesima.com"."\r\n";
            $line.="1116188"."\r\n";//"lanton@millesima.com"."\r\n";
            $line.="1009006"."\r\n";//"mdutoya@millesima.com"."\r\n";
            $line.="1455164"."\r\n";//"braphanel@millesima.com"."\r\n";
        }

        $fileName = $country.$nameSegment.'.csv'; //nom du fichier de commande .csv
        $file = fopen ($dossier.'/'.$fileName, "w+" ); //on l'ouvre en ecriture
        fputs ( $file, $entete.$line); //on ecrit la ligne dedans
        fclose ( $file );

        //vide cache
        $liste='';
        $resultlist='';
        $res='';

        return $country.$nameSegment;
    }

    public function sendFileSegmentFtp($nameSegment,$nomFile){
        $ftp_server = "sftp.avanci.fr"; /* IP ne fonctionne plus : 195.114.115.9 */
        $connect = ssh2_connect($ftp_server,'2222');
        $login="millesima";
        $password="HmnhixkCJikKYI32tucp";
        $dossier_destination="/Tmp/Selligent/";
        $return = false;

        if (ssh2_auth_password($connect, $login, $password)) {
            $myFile = self::REPOSITORY_SEGMENT."/".$nameSegment.'/'.$nomFile.'.csv';
            $retour_ftp = ssh2_scp_send($connect, $myFile, $dossier_destination.$nomFile.'.csv', 0777);
            $sftp = ssh2_sftp($connect);
            $stat_ftp = ssh2_sftp_stat($sftp, $dossier_destination.$nomFile.'.csv');
            if($retour_ftp && $stat_ftp['size'] > 0 ){
               $return = true;
            }
        } else {
            $retour_ftp = "Connexion impossible en tant que ".$login."<br>";
            $return = false;
        }

        return $return;
    }


    /**
     * Function to get segment info in selligente
     *
     * @return string
     */
    public function updateSegment(){
        $html = '';
        $return = array();
        $apiToSell = new Millesima_Api_To_Selligente();
        $client =$apiToSell->getClientIndividual();
        $data['listId'] = 657; //base tout clients
        $results = $client->GetSegments($data);

        if($results->GetSegmentsResult == 0){
            $segmentList = $results->segments->SegmentInfo;
            foreach($segmentList as $segment){
                $bddClass = new Millesima_Bdd();
                $segmentBdd = $this->getSegmentByNameAndStatus($segment->Name,'local');
                if($segmentBdd){
                  $bddClass->update("UPDATE segment_selligente SET status = ?, selligente_id = ?, type = ? WHERE id = ?",array('selligente',(int) $segment->ID,$segment->Type,$segmentBdd['id']));
                  $return[] = $segment->Name;
                  $memberCount = $this->segmentCount($segment->ID);
                  $html .= "Le segment ".$segment->Name." a ete mis a jour, nb contact ".$memberCount."<br />";
               }

            }
        }
        return $html;
    }

    /**
     * Function to get count id segment
     * @param $segmentId
     * @return array
     */
    public function segmentCount($segmentId){
        $apiToSell = new Millesima_Api_To_Selligente();
        $client =$apiToSell->getClientIndividual();
        $results = $client->GetSegmentRecordCount(array('segmentId'=>$segmentId));
        if($results->GetSegmentRecordCountResult == 0){
            $memberCount = $results->segmentCount;
            $bddClass = new Millesima_Bdd();
            $bddClass->update("UPDATE segment_selligente SET nb_contact = ? WHERE selligente_id = ?",array((int) $memberCount,(int) $segmentId));
            return (int) $memberCount;
        }
        return 0;
    }



}