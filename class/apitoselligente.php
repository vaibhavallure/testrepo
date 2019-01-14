<?php
/**
 *
 * Millesima apitoselligente
 * @category      Millesima
 * @author        DGO
 * @version       0.0.1
 * @copyright     millesimaTeam
 * @licence       millesimaLicence
 */

class Millesima_Api_To_Selligente{

    const URL_SOAP_INDIVIDUAL = "https://avanci.emsecure.net/automation/Individual.asmx?WSDL";
    const URL_SOAP_BROADCAST = "https://avanci.emsecure.net/automation/Broadcast.asmx?WSDL";
    const LOGIN_SOAP = 'MIL_api_2';
    const PASS_SOAP = '9$2#GiEv';


    /**
     * function to create client
     */
    public function getClientIndividual(){
        $client = new soapclient(self::URL_SOAP_INDIVIDUAL);
        $header = new SoapHeader(
            'http://tempuri.org/',
            'AutomationAuthHeader',
            array(
                'Login' => self::LOGIN_SOAP,
                'Password' => self::PASS_SOAP,
            )
        );
        $client->__setSoapHeaders($header);
        return $client;
    }

    public function getClientBroadcast(){
        $client = new soapclient(self::URL_SOAP_BROADCAST);
        $header  =new SoapHeader(
            'http://tempuri.org/',
            'AutomationAuthHeader',
            array(
                'Login' => self::LOGIN_SOAP,
                'Password' => self::PASS_SOAP,
            )
        );

        $client->__setSoapHeaders($header);
        return $client;
    }
}