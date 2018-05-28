<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_advr
 * @version   1.0.40
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Advr_Helper_Geo extends Mage_Core_Helper_Abstract
{
    protected $googleActiveKeyIdx = 0;

    protected $keys = array(
        'AIzaSyCoaEg7EY4p7q-c_LPp7E0ae7ELcJkxoj0',
        'AIzaSyATubS2QrFS6Jv4C1ZdNTdNlIFDP8dZ6U8',
        'AIzaSyCpF7Uh5hMbUGrhu7V-iRv_s1-ibUQuLG0',
        'AIzaSyBvSdYpkR2vpv5YYMnPuIn3mkTnkPTofYo',
        'AIzaSyBy-oTubLJE6AVJdk0AvSV1aAp7c8hadjg',
        'AIzaSyDKRAMcp70v7xhuUhTJ7aqzXrSPDvct9ik',
        'AIzaSyDLlCEc34NBO7L03PyKKVhQ4iqyCg9vIkU',
        'AIzaSyBthibGDkDrcqC0lKLwaIG4WockJeeABl8',
        'AIzaSyC5hOG_xIH3tduvk2NyIv8nR59tsUyVULk',
        'AIzaSyDq9dscxS_-qefHcQJPp7DqnR4tvRPMoQc',
        'AIzaSyAqJWHpgDmRyiLZzuyoP0BtNE9Rckvhw3E',
        'AIzaSyBjFj-3VupOGIcoRlgx3yNyAfm81udUrMM',
        'AIzaSyBqOkrS1HvtsfYldK-Sq91qURBGqWEI_vU',
        'AIzaSyCGpXAPGtiWkBfLtcd0msrcFbkg3hV96tg',
        'AIzaSyDOcoIgRmy7_yv_30OuqvZkulTwF2KJMiI',
    );

    public function findInMapQuestApi($locations)
    {
        $result = array();

        $get = array();
        foreach ($locations as $id => $location) {
            $result[$id] = array();
            $location = str_replace(' ', '%20', $location);
            $get[] = 'location=' . $location;
        }

        $get = implode('&', $get);
        $url = 'http://www.mapquestapi.com/geocoding/v1/batch?key=Kmjtd|luua2qu7n9,7a=o5-lzbgq&'
            . $get . '&outFormat=json';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.mapquestapi.com/geocoding/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = json_decode(curl_exec($ch), true);

        $keys = array_keys($locations);

        if (isset($response['results'])) {
            foreach ($response['results'] as $idx => $locations) {
                $id = $keys[$idx];

                foreach ($locations['locations'] as $location) {
                    if ($location['postalCode']) {
                        $result[$id][] = $location;
                    }
                }
            }
        }

        return $result;
    }

    public function findInGoogle($locations, $byAddress = false)
    {
        sleep(1);

        $result = array();
        foreach ($locations as $id => $location) {
            $result[$id] = array();

            do {
                $key = '&key='.$this->keys[$this->googleActiveKeyIdx];

                if (!$byAddress) {
                    $locationArray = explode(':', $location);
                    $country = trim($locationArray[0]);
                    $code = trim($locationArray[1]);

                    $url = 'https://maps.googleapis.com/maps/api/geocode/json?language=EN&components=country:'
                        . $country . '|postal_code:'
                        . $code . $key;
                } else {
                    $url = 'https://maps.googleapis.com/maps/api/geocode/json?language=EN&address='
                        . urlencode($location)
                        . $key;
                }

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_REFERER, 'http://www.mapquestapi.com/geocoding/');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $content = json_decode(curl_exec($ch), true);

                if (isset($content['status'])) {
                    switch ($content['status']) {
                        case "ZERO_RESULTS":
                            break 2;
                        case "OVER_QUERY_LIMIT":
                        case "REQUEST_DENIED":
                            $this->googleActiveKeyIdx++;
                            if ($this->googleActiveKeyIdx > count($this->keys) - 1) {
                                break 2;
                            }
                            break;
                    }
                }
            } while (isset($content['status'])  && "OK" !== $content['status']);

//            foreach ($content['results'] as $location) {
//                $result[$id][] = $location;
//            }
            if (count($content['results']) > 0)  {
                $result[$id][] = $content['results'][0];
            }
        }

        return $result;
    }

    public function formatPostcode($code)
    {
        return preg_replace("/[^A-Z0-9]/", "", strtoupper($code));
    }

    public function formatName($name)
    {
        if (strlen($name) <= 3) {
            return $name;
        }

        $name = $this->ucname($name);

        return $name;
    }

    public function ucname($string)
    {
        if (!strpos(mb_strtolower($string), '?')) {
            $string = mb_strtolower($string, 'UTF-8');
        }

        $string = ucwords($string);

        foreach (array('-', '\'') as $delimiter) {
            if (strpos($string, $delimiter) !== false) {
                $string = implode($delimiter, array_map('ucfirst', explode($delimiter, $string)));
            }
        }

        return $string;
    }
}
