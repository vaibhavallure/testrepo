
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

class Millesima_Campaign extends Millesima_Abstract
{

    /**
     * Function to get list of campaign
     * @return mixed
     */
    public function getCampaignList(){
        $bddClass = new Millesima_Bdd();
        //get all message
        $res= $bddClass->selectAll("SELECT * FROM campaign_selligente ORDER BY selligente_id DESC");

        return $res;
    }

    /**
     * Function to get list of campaign
     * @return mixed
     */
    public function getCampaignListBat(){
        $bddClass = new Millesima_Bdd();
        //get all mesage
        $res= $bddClass->selectAll("SELECT * FROM campaign_selligente WHERE send_date IS NULL ORDER BY selligente_id DESC");
        return $res;
    }

    /**
     * Function to get list of campaign
     * @return mixed
     */
    public function getCampaignListReel(){
        $bddClass = new Millesima_Bdd();
        //get all mesage
        $res= $bddClass->selectAll("SELECT * FROM campaign_selligente WHERE send_date IS NULL AND send_bat_date IS NOT NULL ORDER BY selligente_id DESC");

        return $res;
    }

    /**
     * function to get campaign order by date
     * @return mixed
     */
    public function getCampaignOrderByDate(){
        $bddClass = new Millesima_Bdd();
        //get all mesage
        $res= $bddClass->selectAll("SELECT * FROM campaign_selligente WHERE send_date > DATE_SUB(CURDATE(), INTERVAL 30 DAY) ORDER BY selligente_id DESC");

        return $res;
    }

    /**
     * function to get statut by brief id
     * @param $brief_id
     * @return mixed
     */
    public function getStatutCampaignByBriefId($brief_id){
        $bddClass = new Millesima_Bdd();
        $res= $bddClass->selectAll("select campaign_selligente.statut from campaign_selligente
                                          join message on message.id = campaign_selligente.message_id
                                          join brief on message.brief_id = brief.id
                                          where brief_id = '".$brief_id."'");
        return $res;
    }

    /**
     * function to get stat campaign
     * @param $name
     * @return bool|mixed
     */
    public function getStatCampaign ($name){
        $bddClass = new Millesima_Bdd();
        $res = $bddClass->selectAll("SELECT campaign.campaign_id, message.name, campaign.outMember, campaign.outMemberSent, campaign.descriptionStatut, campaign.send_date
                                          FROM campaign
                                          JOIN message ON message.id = campaign.message_id
                                          WHERE message.name LIKE \"%$name%\"
                                          ORDER BY campaign.campaign_id DESC;");
        if(count($res)>0){
            return $res;
        } else {
            return false;
        }
    }

    /**
     * Function to create a campaign in dolist
     *
     * @param $message
     * @param $name
     * @param $fromMail
     * @param $fromName
     * @param $replyMail
     * @param $replyName
     * @param $subject
     * @param $theme
     * @param $segment
     * @param $dateObj
     * @param $type
     * @return mixed
     */
    public function create($message,$name,$fromMail,$fromName,$replyMail,$replyName,$subject,$theme,$segment,$dateObj,$type){
        //$segment = 7748; //segment test
        //$codeCampaign = $name.'-'.rand(1,6500); //'FIOSLIV18-204-'.rand(1,6500);
        $codeCampaign = $name; //'FIOSLIV18-204-'.rand(1,6500);

        //create element xml
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->startDocument("1.0");
        $writer->startElement('API');

        //set campaign info to xml
        $writer = $this->setCampaignWriter($writer,$codeCampaign,$theme,$dateObj,$type);

        //set email info to xml
        $writer = $this->setEmailWriter($writer,$codeCampaign,$message,$fromMail,$fromName,$replyMail,$replyName,$subject,$segment,$type);

        //close elm api
        $writer->endElement();


        $apiToSell = new Millesima_Api_To_Selligente();
        $client =$apiToSell->getClientBroadcast();

        $results = $client->CreateCampaign(array('Xml' => $writer->outputMemory()));

        if($results->CreateCampaignResult == 0){
            $xmlResult = simplexml_load_string($results->Xml);
            $campaignId = $xmlResult->CAMPAIGN['CAMPAIGNID'];

            $return['success'] = true;
            $return['value'] = "Campagne Creer : ".$campaignId.", message de retour : ".$results->ErrorStr;

            $bddClass = new Millesima_Bdd();
            $briefClass = new Millesima_Brief();
            $bddClass->insert("INSERT INTO campaign_selligente (message_id,selligente_id,segment_id, created_at,statut,send_date) VALUES (?,?,?,?,?,?)",array((string)$message['id'],(int) $campaignId,$segment,date('Y-m-d H:i:s'),$type,$dateObj->format('Y-m-d H:i:s')));

            //verification toutes les campagne pour le brief sont parties
            $briefId = $bddClass->selectone("SELECT * FROM message WHERE id = ?",array($message['id']),'brief_id');
            $nbPays = $briefClass->getNbPaysByBriefId($briefId);
            $nbCampaignCreate = $bddClass->selectAll("select count(*) from campaign_selligente
              join message on message.id = campaign_selligente.message_id
              join brief on message.brief_id = brief.id
              where brief_id = ?
              and campaign_selligente.statut = ? ",array($briefId,$type));
            if($nbCampaignCreate[0]['count(*)'] == $nbPays){
                if($type == 'bat'){
                    $briefClass->updateStatus("8",$briefId);
                }
                if($type == 'reel'){
                    $briefClass->updateStatus("9",$briefId);
                }
            }
        }else {
            $return['success'] = false;
            $return['value'] = "Erreur code : ".$results->CreateCampaignResult.", message : ".$results->ErrorStr;

        }
        return $return;
    }

    /**
     * fonction to add xml comapaign to writer
     * @param $writer
     * @param $codeCampaign
     * @param $theme
     * @param $dateObj
     * @param $type
     * @return mixed
     */
    public function setCampaignWriter($writer,$codeCampaign,$theme,$dateObj,$type){
        /*setFolderId :
        brief/2018  ----> 4803,
        brief/2019 ----> 4804
        brief/2019/BAT ----> 5401
        brief/2019/REEL ----> 5402*/
        if($type == 'reel'){
            $state = 'ACTIVE';
            $folder = 5402;
        } else {
            $state = 'TEST';
            $folder = 5401;
        }

        $writer->startElement('CAMPAIGN');
        $writer->writeAttribute('NAME', $codeCampaign);
        $writer->writeAttribute('STATE' , $state);

        $writer->writeAttribute('FOLDERID' , $folder);
        $writer->writeAttribute('START_DT' , $dateObj->format('YmdHis'));
        $writer->writeAttribute('DESCRIPTION' , $theme);//mettre le theme
        $writer->endElement();

        return $writer;
    }

    /**
     * function to set email xml to writer
     * @param $writer
     * @param $codeCampaign
     * @param $message
     * @param $fromMail
     * @param $fromName
     * @param $replyMail
     * @param $replyName
     * @param $subject
     * @param $segment
     * @param $type
     * @return mixed
     */
    public function setEmailWriter($writer,$codeCampaign,$message,$fromMail,$fromName,$replyMail,$replyName,$subject,$segment,$type){
        $writer->startElement('EMAILS');
        $writer->startElement('EMAIL');
        $writer->writeAttribute('NAME', $codeCampaign);

        /*setFolderId :
        brief/2018  ----> 4803,
        brief/2019 ----> 4804
        brief/2019/BAT ----> 5401
        brief/2019/REEL ----> 5402*/
        if($type == 'reel'){
            $folder = 5402;
        } else {
            $folder = 5401;
        }
        $writer->writeAttribute('FOLDERID' , $folder);
        $writer->writeAttribute('MAILDOMAINID' , 134);
        $writer->writeAttribute('LIST_UNSUBSCRIBE' , false);
        $writer->writeAttribute('QUEUEID' , 2);

        /* Définition de la cible*/
        $writer = $this->setTargetWriter($writer,$segment);

        /*definition du content*/
        $writer->startElement('CONTENT');
        $writer->writeAttribute('HYPERLINKS_TO_SENSORS' , 1);
        $writer = $this->startElmCdata($writer,'HTML',$message['html']);
        $writer = $this->startElmCdata($writer,'TEXT',$message['text']);
        $writer = $this->startElmCdata($writer,'FROM_ADDR',$fromMail);
        $writer = $this->startElmCdata($writer,'FROM_NAME',$fromName);
        $writer = $this->startElmCdata($writer,'TO_ADDR','~MAIL~');
        $writer = $this->startElmCdata($writer,'TO_NAME','~NAME~');
        $writer = $this->startElmCdata($writer,'REPLY_ADDR',$replyMail);
        $writer = $this->startElmCdata($writer,'REPLY_NAME',$replyName);

        $subject = "~(IF(SYSTEM.TESTMAIL=1,'[BAT]',''))~".$subject;
        $writer = $this->startElmCdata($writer,'SUBJECT',$subject);

        $writer->endElement();//fin content
        $writer->endElement();//fin Email
        $writer->endElement();//fin Emails
        return $writer;
    }

    /**
     * function to create elem cdata xml
     * @param $writer
     * @param $name
     * @param $data
     * @return mixed
     */
    public function startElmCdata($writer, $name,$data){
        $writer->startElement($name);
        $writer->startCData();
        $writer->text($data);
        $writer->endCData();
        $writer->endElement();

        return $writer;
    }

    /**
     * function to create segment
     * @param $writer
     * @param $segment
     * @return mixed
     */
    public function setTargetWriter($writer, $segment){
        $writer->startElement('TARGET');
        $writer->writeAttribute('LISTID',657);
        $writer->writeAttribute('SEGMENTID' , $segment);
        $writer->writeAttribute('SCOPES' , '');
        $writer->endElement();

        return $writer;
    }
}