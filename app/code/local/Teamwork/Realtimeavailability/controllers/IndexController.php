<?php
class Teamwork_Realtimeavailability_IndexController extends Mage_Core_Controller_Front_Action
{
    public function scheduleAction()
	{
		Mage::getSingleton('teamwork_realtimeavailability/realtimeavailability')->changedInventory();
    }

    /**
     * Action for testing 'changedInventory' method in Realtimeavailability.php
     */
    public function scheduletestAction()
    {
        $testResult = Mage::helper('teamwork_realtimeavailability/test')->scheduleTest();
        echo "<pre>";
        echo ($testResult['success']) ? "Schedule test passed" : "Schedule test failed";
        echo "\n\n" . $testResult['message'];
    }

    public function getversionAction()
    {
        header('Content-Type: text/xml');
        $version = '<?xml version="1.0" encoding="UTF-8"?>';
        $version .= '<PluginInformation Name="Realtimeavailability Teamwork Plug-in for Magento" Version="' . Mage::getConfig()->getNode('modules')->children()->Teamwork_Realtimeavailability->version . '"> Description of Plug-in. Plug-in for Magento ' . Mage::getVersion() . ' created by Teamwork Retailer Co. </PluginInformation>';
        $this->getResponse()
            ->clearHeaders()
            ->setHeader('Content-Type', 'text/xml')
        ->setBody($version);
    }

	public function magentoinventoryreportAction()
    {
        $name = "{$_SERVER['SERVER_NAME']}_{$_SERVER['REQUEST_TIME']}.csv";
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename={$name}");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        $inventory = Mage::getSingleton('teamwork_realtimeavailability/report')->getMagentoItems();
        $reserved = Mage::getSingleton('teamwork_realtimeavailability/resource')->getCommitedInventoryUnknownForChq();
        
        echo "ItemId,Plu,Qty,CommitedNotInChq\n";
        foreach($inventory as $product)
        {
            $reservedColumn = floatval( isset($reserved[$product['item_id']]) ? $reserved[$product['item_id']] : 0 );
            echo "{$product['item_id']},{$product['plu']},{$product['qty']},{$reservedColumn}\n";
        }
        exit();
    }

	public function isActiveAction()
	{
		var_dump( Mage::helper('teamwork_service')->useRealtimeavailability() );
	}
    
    public function rtareportAction()
	{
        $start = microtime(true);
        $report = Mage::getSingleton('teamwork_realtimeavailability/report');
        $table = '';
        
        $stagingMismatchedItems = $report->getStagingMismatchedItems();
        if($stagingMismatchedItems)
        {
            $table .= "<h2>Staging tables inconsistency</h2><table border='1' style='background-color:FFFFCC;border-collapse:collapse;border:1px solid FFCC00;color:000000;width:100%' cellpadding='3' cellspacing='3'>
                <tr style='background-color:ccffcc;'>
                    <td>PLU (ItemId)</td>
                    <td>Channel Id</td>
                    <td>Magento qty</td>
                    <td>Staging total</td>
                    <td>Locations</td>
                    <td>Location Availability</td>
                    <td>Quantity by Location</td>
                </tr>";
                
            foreach($stagingMismatchedItems as $stagingMismatchedItem)
            {
                $table .= "<tr>
                    <td>{$stagingMismatchedItem['plu']} ({$stagingMismatchedItem['item_id']})</td>
                    <td>{$stagingMismatchedItem['channel_id']}</td>
                    <td>{$stagingMismatchedItem['MAGENTO_QTY']}</td>
                    <td>{$stagingMismatchedItem['ECOMM_TOTAL']}</td>
                    <td>{$stagingMismatchedItem['LOCATION_CODES']}</td>
                    <td>{$stagingMismatchedItem['LOCATION_AVAILABLE']}</td>
                    <td>{$stagingMismatchedItem['RTA_QTY_BY_LOCATION']}</td>
                </tr>";
            }
            $table .= '</table><br/>';
        }
        
        $report->getRtaMismatchedItems();
        
        if( !empty($report->rtaMismatchedItems) )
        {
            $channelColoumnTitle = $report->prepareRtaTitle();
            $table .= "<h2>RTA inconsistency</h2><table border='1' style='background-color:FFFFCC;border-collapse:collapse;border:1px solid FFCC00;color:000000;width:100%' cellpadding='3' cellspacing='3'>
                <tr style='background-color:ccffcc;'>
                    <td>PLU (ItemId)</td>
                    <td>Magento Qty</td>
                    {$channelColoumnTitle}
                </tr>";
                foreach($report->rtaMismatchedItems as $mismatchedItemId => $mismatchedItemRtaStatment)
                {
                    $channelColoumnValue = $report->prepareRtaColoumns($mismatchedItemId, $report->magentoItemsInventory[$mismatchedItemId]['qty']);
                    $table .= "<tr>
                        <td>{$report->magentoItemsInventory[$mismatchedItemId]['plu']} ({$report->magentoItemsInventory[$mismatchedItemId]['item_id']})</td>
                        <td>{$report->magentoItemsInventory[$mismatchedItemId]['qty']}</td>
                        {$channelColoumnValue}
                    </tr>";
                }
            $table .= '</table>';
        }
        
        $table .= "Memory Usage: " . round(memory_get_usage()/1024/1024, 2) . "Mb<br />";
        $table .= "Memory Peak:  " . round(memory_get_peak_usage()/1024/1024, 2) . "Mb<br />";
        $table .= "Total time:  " . sprintf('%.4F sec.', (microtime(true) - $start));
        echo $table;
        
        exit;
	}
}