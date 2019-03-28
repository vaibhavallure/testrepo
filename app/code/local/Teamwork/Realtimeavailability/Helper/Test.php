<?php
class Teamwork_Realtimeavailability_Helper_Test extends Mage_Core_Helper_Abstract
{
    /**
     * Method for testing 'changedInventory' method in Realtimeavailability.php
     *
     * Runs several test cases.
     * For each test case, input data contains:
     *  - stub for itemQuantities (that usually come from SVS)
     *  - stub for ARRANGED CHQ channels array (by changing EC channel order ONE CAN SIMULATE CHANNEL PRIORITIES(usually, default channel for 'multi channel product' option determines this arrangement))
     *  - stub for itemAssignments on each EC channel (item EC statuses)
     *
     *  - expected result. It contains info about from which EC channel Transfer module will take quantities.
     *    It has form like:
     *      array(
     *          <item 1> => array(<channel to take inventory 1>, <channel to take inventory 2>, ...),
     *          <item 2> => array(...),
     *          ...
     *      )
     *
     * @return array ('success' => bool, 'message' => string)
     */
    public function scheduleTest()
    {
        $xmlItemInfo = array(
          'itemQuantities' =>
          array(
            array(
              'itemId'     => 'item1',
              'locationId' => 'location1',
              'available'  => 1,
            )
          ),
          'cursor' => '',
          'lastUpdateTime' => 1422892190058,
        );

        $testCases = array(
            'item is assigned to 1 channel.test #1' => array(
                    'xmlItemInfo'     => $xmlItemInfo,
                    'channels'        => array(                  'channel1' => 'request1' ), // channel 1 is default
                    'itemAssignments' => array( 'item1' => array('channel1' => 'ec offer')),
                    'expected_result' => array( 'item1' => array('channel1')              )
                 ),
            'item is assigned to 1 channel.test #2' => array(
                    'xmlItemInfo'     => $xmlItemInfo,
                    'channels'        => array(                  'channel1' => 'request1' ), // channel 2 is default
                    'itemAssignments' => array( 'item1' => array('channel1' => 'ec suspe')),
                    'expected_result' => array( 'item1' => array('channel1')              )
                 ),


            'item is assigned to 2 channels.test #1' => array(
                    'xmlItemInfo'     => $xmlItemInfo,
                    'channels'        => array(                  'channel1' => 'request1', 'channel2' => 'request2' ), // channel 1 is default
                    'itemAssignments' => array( 'item1' => array('channel1' => 'ec offer', 'channel2' => 'ec offer')),
                    'expected_result' => array( 'item1' => array('channel1')                                        )
                 ),
            'item is assigned to 2 channels.test #2' => array(
                    'xmlItemInfo'     => $xmlItemInfo,
                    'channels'        => array(                  'channel2' => 'request2', 'channel1' => 'request1' ), // channel 2 is default
                    'itemAssignments' => array( 'item1' => array('channel1' => 'ec offer', 'channel2' => 'ec offer')),
                    'expected_result' => array( 'item1' => array('channel2')                                        )
                 ),
            'item is assigned to 2 channels.test #3' => array(
                    'xmlItemInfo'     => $xmlItemInfo,
                    'channels'        => array(                  'channel1' => 'request1', 'channel2' => 'request2' ), // channel 1 is default
                    'itemAssignments' => array( 'item1' => array('channel1' => 'ec offer', 'channel2' => 'ec suspe')),
                    'expected_result' => array( 'item1' => array('channel1')                                        )
                 ),
            'item is assigned to 2 channels.test #4' => array(
                    'xmlItemInfo'     => $xmlItemInfo,
                    'channels'        => array(                  'channel2' => 'request2', 'channel1' => 'request1' ), // channel 2 is default
                    'itemAssignments' => array( 'item1' => array('channel1' => 'ec offer', 'channel2' => 'ec suspe')),
                    'expected_result' => array( 'item1' => array('channel1')                                        )
                 ),
            'item is assigned to 2 channels.test #5' => array(
                    'xmlItemInfo'     => $xmlItemInfo,
                    'channels'        => array(                  'channel1' => 'request1', 'channel2' => 'request2' ), // channel 1 is default
                    'itemAssignments' => array( 'item1' => array('channel1' => 'ec suspe', 'channel2' => 'ec offer')),
                    'expected_result' => array( 'item1' => array('channel2')                                        )
                 ),
            'item is assigned to 2 channels.test #6' => array(
                    'xmlItemInfo'     => $xmlItemInfo,
                    'channels'        => array(                  'channel2' => 'request2', 'channel1' => 'request1' ), // channel 2 is default
                    'itemAssignments' => array( 'item1' => array('channel1' => 'ec suspe', 'channel2' => 'ec offer')),
                    'expected_result' => array( 'item1' => array('channel2')                                        )
                 ),
            'item is assigned to 2 channels.test #7' => array(
                    'xmlItemInfo'     => $xmlItemInfo,
                    'channels'        => array(                  'channel1' => 'request1', 'channel2' => 'request2' ), // channel 1 is default
                    'itemAssignments' => array( 'item1' => array('channel1' => 'ec suspe', 'channel2' => 'ec suspe')),
                    'expected_result' => array( 'item1' => array('channel1')                                        )
                 ),
            'item is assigned to 2 channels.test #8' => array(
                    'xmlItemInfo'     => $xmlItemInfo,
                    'channels'        => array(                  'channel2' => 'request2', 'channel1' => 'request1' ), // channel 2 is default
                    'itemAssignments' => array( 'item1' => array('channel1' => 'ec suspe', 'channel2' => 'ec suspe')),
                    'expected_result' => array( 'item1' => array('channel2')                                        )
                 ),
        );

        $message .= "=============\n";
        $success = true;
        foreach ($testCases as $testNumber => $testCase)
        {
            $result = Mage::getSingleton('teamwork_realtimeavailability/realtimeavailability')->changedInventoryTest(null,null, $testCase);

            if (json_encode($testCase['expected_result']) == json_encode($result))
            {
                $message .= "test '{$testNumber}' passed \n";
            }
            else
            {
                $success = false;
                $message .= "test '{$testNumber}' failed \n\n";

                $message .= "actual result: \n";
                $message .= print_r($result, true);

                $message .= "\nexpected result: \n";
                $message .= print_r($testCase['expected_result'], true);
            }
            $message .= "\n=============\n";
        }

        return array('success' => $success, 'message' => $message);
    }
}