<?php
/**
 *
 * Millesima apitotextmaster
 *
 * @category      Millesima
 * @author        DGO
 * @version       0.0.1
 * @copyright     millesimaTeam
 * @licence       millesimaLicence
 */

class Millesima_Api_To_Textmaster {
    /**
     * Function to do a requete to textMaster
     *
     * @param string $dataString
     * @param string $url
     * @param string $type
     * @param bool $test
     * @return SimpleXMLElement
     */
    public function requete($dataString,$url,$type,$test = false){
        //$url = "http://192.168.11.101:8080/textmaster/".$url; //local
        $url = "http://admin.millesima.fr:8082/textmaster/".$url; //prod

        $ch = curl_init();
        $headers = array(
            "Content-Type: application/json",
            "Accept: application/json"
        );

        //var_dump($dataString);echo "<br />";
        //var_dump($url);echo "<br />";

        curl_setopt($ch, CURLOPT_URL, $url);
        if($type == 'POST') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        } else if ($type == 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $data = curl_exec($ch);
        $data = json_decode($data);

        return $data;
        //var_dump($data);
    }
}