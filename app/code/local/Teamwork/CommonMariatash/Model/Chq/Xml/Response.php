<?php
class Teamwork_CommonMariatash_Model_Chq_Xml_Response extends Teamwork_Common_Model_Chq_Xml_Response
{
    public function getLastUpdatedTimeFromResponse($response)
    {
        $lastUpdatedTime = $response->getData('ApiRequestTime');
        if($lastUpdatedTime && $response->getData('ElapsedTime'))
        {
            $date = new DateTime($lastUpdatedTime);
            $parsed = date_parse($response->getData('ElapsedTime'));

            $date->sub(new DateInterval("PT{$parsed['hour']}H{$parsed['minute']}M{$parsed['second']}S"));
            $lastUpdatedTime = $date->format('Y-m-d H:i:s');
        }
        return $lastUpdatedTime;
    }
}