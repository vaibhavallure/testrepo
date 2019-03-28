<?php

// Load the Google API PHP Client Library.
require_once '../app/Mage.php';
Mage::app('admin');

require_once '../lib/ALRGoogleAnalytics/vendor/autoload.php';
/**
 * Initializes an Analytics Reporting API V4 service object.
 *
 * @return An authorized Analytics Reporting API V4 service object.
 */
function initializeAnalytics()
{
    
    // Use the developers console and download your service account
    // credentials in JSON format. Place them in this directory or
    // change the key file location if necessary.
    // Create and configure a new client object.
    $client = new Google_Client();
    $client->setApplicationName("Hello Analytics Reporting");

    $key = Mage::helper('alertservices/config')->getGoogleAnayticsJson();
    $client->setAuthConfig($key);
    $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
    $analytics = new Google_Service_AnalyticsReporting($client);
    
    return $analytics;
}


/**
 * Queries the Analytics Reporting API V4.
 *
 * @param service An authorized Analytics Reporting API V4 service object.
 * @return The Analytics Reporting API V4 response.
 */
function getUsersReport($analytics) {
    
    // Replace with your view ID, for example XXXX.
    $VIEW_ID = "181106821";
    // Create the DateRange object.
    $dateRange = new Google_Service_AnalyticsReporting_DateRange();
    $dateRange->setStartDate("today");
    $dateRange->setEndDate("today");
    // Create the Metrics object.
    $sessions = new Google_Service_AnalyticsReporting_Metric();
    $sessions->setExpression("ga:users");
    $sessions->setAlias("users");

    $dimension = new Google_Service_AnalyticsReporting_Dimension();
    $dimension->setName("ga:hour");
    
    // Create the ReportRequest object.
    $request = new Google_Service_AnalyticsReporting_ReportRequest();
    $request->setViewId($VIEW_ID);
    $request->setDateRanges($dateRange);
    $request->setMetrics(array($sessions));
    
    //new added by aws02
    $request->setDimensions(array($dimension));
    
    $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
    $body->setReportRequests( array( $request) );
    return $analytics->reports->batchGet( $body );
}

function getPageReport($analytics) {
    
    // Replace with your view ID, for example XXXX.
    $VIEW_ID = "181106821";
    // Create the DateRange object.
    $dateRange = new Google_Service_AnalyticsReporting_DateRange();
    $dateRange->setStartDate("7daysAgo");
    $dateRange->setEndDate("today");
    //new added by aws02
    $dimension = new Google_Service_AnalyticsReporting_Dimension();
    $dimension->setName("ga:pageTitle");
    
    $dimension1 = new Google_Service_AnalyticsReporting_Dimension();
    $dimension1->setName("ga:pagePath");
    
    // Create the ReportRequest object.
    $request = new Google_Service_AnalyticsReporting_ReportRequest();
    $request->setViewId($VIEW_ID);
    $request->setDateRanges($dateRange);
    $request->setMetrics(array($sessions));
    
    //new added by aws02
    $request->setDimensions(array($dimension,$dimension1));
    
    $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
    $body->setReportRequests( array( $request) );
    return $analytics->reports->batchGet( $body );
}

function getPageReportAvg($analytics) {
    // Replace with your view ID, for example XXXX.
    $VIEW_ID = "181106821";
    // Create the DateRange object.
    $dateRange = new Google_Service_AnalyticsReporting_DateRange();
    $dateRange->setStartDate("1daysAgo");
    $dateRange->setEndDate("today");
    //new added by aws02

    $sessions = new Google_Service_AnalyticsReporting_Metric();
    $sessions->setExpression("ga:pageLoadTime");
    $sessions->setAlias("time");

   $dimension = new Google_Service_AnalyticsReporting_Dimension();
    $dimension->setName("ga:pageTitle");
    
    $dimension1 = new Google_Service_AnalyticsReporting_Dimension();
    $dimension1->setName("ga:pagePath");

    $dimension2 = new Google_Service_AnalyticsReporting_Dimension();
    $dimension2->setName("ga:hour");
    
    // Create the ReportRequest object.
    $request = new Google_Service_AnalyticsReporting_ReportRequest();
    $request->setViewId($VIEW_ID);
    $request->setDateRanges($dateRange);
    $request->setMetrics(array($sessions));
    
    //new added by aws02
    $request->setDimensions(array($dimension,$dimension1,$dimension2));
    
    $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
    $body->setReportRequests( array( $request) );
    return $analytics->reports->batchGet( $body );
}
/**
 * Parses and prints the Analytics Reporting API V4 response.
 *
 * @param An Analytics Reporting API V4 response.
 */
function getResults($reports,$lastHour,$form) {
    for ( $reportIndex = 0; $reportIndex < count( $reports ); $reportIndex++ ) {
        $report = $reports[ $reportIndex ];
        $header = $report->getColumnHeader();
        $dimensionHeaders = $header->getDimensions();
        $metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
        $rows = $report->getData()->getRows();
        $final_report = array();
        for ( $rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
            $row = $rows[ $rowIndex ];
            $dimensions = $row->getDimensions();
            $metrics = $row->getMetrics();
            for ($i = 0; $i < count($dimensionHeaders) && $i < count($dimensions); $i++) {
                if ($form == 'users') {
                    if ($dimensionHeaders[$i] == 'ga:hour' && $dimensions[$i] == $lastHour) {
                        $values = $metrics[$i]->getValues();
                        $entry = $metricHeaders[$i];
                        $final_report = array(
                                        $dimensionHeaders[$i] => $dimensions[$i],
                                        $entry->getName()   =>  $values[$i]
                                    );
                   /*print($dimensionHeaders[$i] . ": " . $dimensions[$i] . "<br/>");
                   print($entry->getName() . ": " . $values[$i] . "<br/>");*/
                    }
                }

                if ($form == 'page') {
                    if ($dimensionHeaders[$i] == 'ga:pageTitle' && $dimensions[$i] == '404 Not Found') {
                        $final_report[] = $dimensions[$i+1];
                    }
                }
                //print($dimensionHeaders[$i] . ": " . $dimensions[$i] . "<br/>");
            }            
            /*for ($j = 0; $j < count($metrics); $j++) {
                $values = $metrics[$j]->getValues();
                for ($k = 0; $k < count($values); $k++) {
                    $entry = $metricHeaders[$k];
                    print($entry->getName() . ": " . $values[$k] . "<br/>");
                }
            }*/
        }
        return $final_report;
    }
}
