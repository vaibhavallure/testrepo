<?php
class Teamwork_Service_Model_Status_Chq extends Teamwork_Service_Model_Status_Abstract
{
    protected $_packages = array();

    public function parseXml($xml)
    {
        $this->_xml = simplexml_load_string($xml);

        if(!empty($this->_xml->Package))
        {
            $this->getPackages();

            $response = '';
            if(!empty($this->_packages))
            {
                $response = $this->_helper->runStatus($this->_packages);
            }
            
            return $this->response($this->_getErrorsFromResponse($response)); 
        }
        else
        {
            $message = ($this->_xml === false) ? "Wrong input: no XML given" : "Wrong input: given XML doesn't have <Packages> element";
            return $this->response(array($message)); 
        }
    }

    protected function getPackages()
    {
        $table = 'service_status';
        $this->_request = $this->_parser->getElementVal($this->_xml, false, 'RequestId');

        foreach($this->_xml->Package as $package)
        {
            $package_id = $this->_parser->getElementVal($package, false, 'PackageId');
            $data = array(
                'PackageId'         => $package_id,
                'WebOrderId'        => $this->_parser->getElementVal($package, false, 'WebOrderId'),
                'Status'            => $this->_parser->getElementVal($package, false, 'Status'),
                'ShippingAmount'    => (float)$this->_parser->getElementVal($package, false, 'ShippingAmount')
            );

            if($this->_db->getOne($table, array('PackageId' => $package_id), 'PackageId'))
            {
                $this->_db->update($table, $data, array('PackageId' => $package_id));
            }
            else
            {
                $this->_db->insert($table, $data);
            }

            $this->getPackageItems($package, $package_id);
            $this->_packages[] = $package_id;
        }
    }

    protected function getPackageItems($package, $package_id)
    {
        $table = 'service_status_items';
        $this->_db->delete($table, array('PackageId' => $package_id));

        foreach($package->WebOrderItem as $item)
        {
            $data = array(
                'WebOrderItemId' => $this->_parser->getElementVal($item, false, 'WebOrderItemId'),
                'ItemId'         => $this->_parser->getElementVal($item, false, 'ItemId'),
                'PackageId'      => $package_id,
                'Qty'            => $this->_parser->getElementVal($item, false, 'Qty')
            );
            $this->_db->insert($table, $data);
        }

        if($package->ShippingInformation)
        {
            $this->getPackageShipping($package->ShippingInformation, $package_id);
        }
    }

    protected function getPackageShipping($information, $package_id)
    {
        $table = 'service_status_shipping';
        $shipping_information_id = $this->_parser->getElementVal($information, false, 'ShippingInformationId');
        $data = array(
            'ShippingInformationId' => $shipping_information_id,
            'PackageId'             => $package_id,
            'Carrier'               => $this->_parser->getElementVal($information, false, 'Carrier'),
            'ShippingMethod'        => $this->_parser->getElementVal($information, false, 'ShippingMethod'),
            'TrackingNo'            => $this->_parser->getElementVal($information, false, 'TrackingNo'),
            'Estimate'              => $this->_parser->getElementVal($information, false, 'Estimate'),
            'Description'           => $this->_parser->getElementVal($information, false, 'Description')
        );

        if($this->_db->getOne($table, array('ShippingInformationId' => $shipping_information_id), 'ShippingInformationId'))
        {
            $this->_db->update($table, $data, array('ShippingInformationId' => $shipping_information_id));
        }
        else
        {
            $this->_db->insert($table, $data);
        }
    }
}
