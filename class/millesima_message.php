
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

class Millesima_Message extends Millesima_Abstract
{

    /**
     * Function to get info, from message by id
     * @param string $id
     * @param string $info
     * @return mixed
     */
    public function getInfoById ($id,$info){
        $bddClass = new Millesima_Bdd();
        $res = $bddClass->selectone("SELECT * FROM message WHERE id = ?",array($id),$info);
        return $res;
    }

    /**
     * Function to get info, from message by id
     * @param string $id
     * @return mixed
     */
    public function getMessageById ($id){
        $bddClass = new Millesima_Bdd();
        $res = $bddClass->selectAll("SELECT * FROM message WHERE id = ?",array($id));
        return $res;
    }

    /**
     * Function to get list of message
     * @return mixed
     */
    public function getMessageList(){
        $bddClass = new Millesima_Bdd();
        $res= $bddClass->selectAll("SELECT * FROM message ORDER BY id DESC");
        return $res;
    }

    /**
     * Function to get list of message
     * @param string $briefId
     * @return mixed
     */
    public function getMessageListByBriefId($briefId){
        $bddClass = new Millesima_Bdd();
        $res= $bddClass->selectAll("SELECT * FROM message WHERE brief_id = ?",array($briefId));
        return $res;
    }

    /**
     * Function to get list of message if create is less than 30 days
     * @return mixed
     */
    public function getMessageOrderByDate(){
        $bddClass = new Millesima_Bdd();
        $res= $bddClass->selectAll("SELECT * FROM message WHERE created_at > DATE_SUB(CURDATE(), INTERVAL 30 DAY) ORDER BY id DESC");
        return $res;
    }
	
    /**
     * Function to get trad obj lang message by id
     * @param string $id
     * @param string $lang
     * @return mixed
     */
    public function getTradObjetSendFromId ($id,$lang){
        $bddClass = new Millesima_Bdd();
        $res = $bddClass->selectone("SELECT * FROM message WHERE id = ?",array($id),'brief_id');
        $res = $bddClass->selectALL('SELECT value FROM traduction WHERE brief_id = '.$res.' AND type = "objtrad" AND lang = "'.$lang.'" ORDER BY id DESC');
        return $res[0]['value'];
    }

    /**
     * Function to get all message without html
     * @return mixed
     */
    public function getSentMessageWithoutHtmlList(){
        $bddClass = new Millesima_Bdd();
        $res= $bddClass->selectAll("SELECT id FROM message WHERE html is NULL");
        return $res;
    }



    public function getBriefIdByMessageId($id){
        $bddClass = new Millesima_Bdd();
        $brief_id = $bddClass->selectone("SELECT * FROM message where id = ?", array($id),'brief_id');
        return $brief_id;
    }

    public function getNbMessageByBrief($briefId){
        $bddClass = new Millesima_Bdd();
        $res = $bddClass->selectAll("select count(*) from message where brief_id = (?)",array($briefId));
        return (int) $res[0]["count(*)"];
    }

    /**
     * Function to delete html message from dolist and set content in bdd
     *
     */
    public function delete($messageId){
        $bddClass = new Millesima_Bdd();
        $bddClass->delete("Delete from message WHERE id = (?)",array($messageId));
        $return[$messageId]['success'] = true;
        $return[$messageId]['value'] = 'OK';
        return $return;
    }
}