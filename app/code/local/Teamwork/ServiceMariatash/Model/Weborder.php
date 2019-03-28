<?php
class Teamwork_ServiceMariatash_Model_Weborder extends Teamwork_CEGiftcards_Service_Model_Weborder
{
    public function generateXml($params)
    {
        $this->prepareParams($params);
        $webOrders = '<?xml version="1.0" encoding="UTF-8"?><WebOrders xmlns="http://microsoft.com/wsdl/types/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"></WebOrders>';
        $this->_webOrders = new SimpleXMLElement($webOrders);

        $order = empty($this->_from) ? 'DESC' : 'ASC';
        $results = $this->getTable(/**/
            'service_weborder',
            array('EComChannelId = ?', 'ProcessingDate >= ?', 'IsReady = ?'),
            array($this->_channel_id, $this->_from, 1),
            "ProcessingDate {$order}",
            array('*'),
            false,
            $this->_orderLimit
        );

        if(is_array($results))
        {
            foreach($results as $result)
            {
                $this->_webOrderId  = $result['WebOrderId'];
                if(empty($this->_channel_id))
                {
                    $this->_channel_id = $result['EComChannelId'];
                }
                $webOrder = $this->createAttrNode($this->_webOrders, 'WebOrder', array(
                    'WebOrderId'        => $result['WebOrderId'],
                    'EComChannelId'     => $this->_channel_id,
                    'OrderNo'           => $result['OrderNo'],
                    'OrderDate'         => date("Y-m-d\TH:i:s", strtotime($result['OrderDate'])),
                    'ProcessedDate'     => date("Y-m-d\TH:i:s", strtotime($result['ProcessingDate'])),
                    'Status'            => $result['Status'],
                    'GuestCheckout'     => $result['GuestCheckout'] ? 'true' : 'false'
                ));

                $this->generateWebOrder($webOrder, $result);
                $this->generateCustomer($webOrder, $result);
                $this->generatePayment($webOrder);
                $this->generateGlobalFees($webOrder);
                $this->generateWebOrderItemsGroups($webOrder);
                $this->generateInstruction($webOrder, $result);
            }
        }
        return base64_encode($this->_webOrders->asXML());
    }
}