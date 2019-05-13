<?php
//require_once Mage::getBaseDir().'/allure/alrGoogleAnalytics.php';
require_once Mage::getBaseDir().'/lib/ALRGoogleAnalytics/vendor/autoload.php';

class Allure_AlertServices_Model_Alerts
{	
	private function getConfigHelper(){
        return Mage::helper("alertservices/config");
    }

	public function alertProductPrice(){
			try{
				$helper = Mage::helper('alertservices');

				$status =	$this->getConfigHelper()->getEmailStatus();
				$productPrice_status =$this->getConfigHelper()->getProductPriceStatus();
				if (!$productPrice_status) {
					return;
				}
					if ($status) {
						$collection = Mage::getModel('catalog/product')->getCollection()
						->addAttributeToSelect('*')
						->addAttributeToFilter(array(array("attribute"=>"price","eq"=>0)))
						->addAttributeToFilter(array(array("attribute"=>"sku","neq"=>'gift')))
						->addAttributeToFilter(array(array("attribute"=>"status","eq"=>1)));
						if (count($collection) > 0) {
							$helper->sendEmailAlertForProductPrice($collection);
						}
					}
				}catch(Exception $e){
					$helper->alr_alert_log($e->getMessage(),'allureAlerts.log');
	    	}
				
		}

	public function alertSalesOfFour($debug = false){
        /* Get the collection */
        try{
            $helper = Mage::helper('alertservices');
            $status =	$this->getConfigHelper()->getEmailStatus();
            $test_status =	$this->getConfigHelper()->getTestEmailStatus();
            if (!$test_status) {
                return;
            }
            if ($status) {
                $currdate = Mage::getModel('core/date')->gmtDate();
                $toDate	= $currdate;
                $fromDate = date('Y-m-d H:i:s', strtotime($currdate) - 60 * 60 * 4);
                /*$fromDate = date('Y-m-d H:i:s', strtotime($toDate) - 60 * 15);*/
                $orders = Mage::getModel('sales/order')->getCollection()
                    ->addAttributeToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate))
                    ->addAttributeToFilter('create_order_method',0)
                    ->addAttributeToSelect('*')
                    ->setCurPage(1)
                    ->setPageSize(1);
                $orders->getSelect()->order("entity_id desc");
//						  ->setOrder('created_at', 'desc');
                if ($debug) {
                    $orderDate = $orders->getFirstItem()->getCreatedAt();
                    echo $orders->getSelect()->__toString();
                    echo "<br>order count : ".count($orders);
                    echo "<br>4 hour for testing for sale<br>";
                    echo "<br>Order Date :".$orderDate;
                    if($orderDate != null)
                        echo "<br>Last Order Date :".Mage::getModel('core/date')->date("F j, Y \a\\t g:i a",$orderDate);

                }
                if (count($orders)<=0) {
                    $lastOrderDate = Mage::getModel("sales/order")
                        ->getCollection()
                        ->addAttributeToFilter('create_order_method',0)
                        ->setCurPage(1)
                        ->setPageSize(1);
                    $lastOrderDate->getSelect()->order("entity_id desc");
//										->setOrder('entity_id', 'desc');

                    $lastDate = $lastOrderDate->getFirstItem()->getCreatedAt();
                    $hourReport = 4;
                    $helper->sendSalesOfEmailAlert($lastDate,$hourReport);
                }
            }
        }catch(Exception $e){
            $helper->alr_alert_log($e->getMessage(),'allureAlerts.log');
        }
		
	}

	public function alertSalesOfSix($debug = false){
		/* Get the collection */
		try{
			$helper = Mage::helper('alertservices');
			$status =	$this->getConfigHelper()->getEmailStatus();
			$sales_status =	$this->getConfigHelper()->getSalesStatus();
			if (!$sales_status) {
					return;
				}
			if ($status) {
				$currdate = Mage::getModel('core/date')->gmtDate();
				$toDate	= $currdate;
				$fromDate = date('Y-m-d H:i:s', strtotime($currdate) - 60 * 60 * 6);
				/*$fromDate = date('Y-m-d H:i:s', strtotime($toDate) - 60 * 15);*/
				$orders = Mage::getModel('sales/order')->getCollection()
						  ->addAttributeToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate))
                          ->addAttributeToFilter('create_order_method',0)
						  ->addAttributeToSelect('*')
						  ->setCurPage(1)
						  ->setPageSize(1)
						  ->setOrder('created_at', 'desc');
					    /*echo $orders->getSelect()->__toString();*/
                if ($debug) {
                    $orderDate = $orders->getFirstItem()->getCreatedAt();
                    echo $orders->getSelect()->__toString();
                    echo "<br>order count : ".count($orders);
                    echo "<br>6 hour for testing for sale<br>";
                    echo "<br>Order Date :".$orderDate;
                    if($orderDate != null)
                        echo "<br>Last Order Date :".Mage::getModel('core/date')->date("F j, Y \a\\t g:i a",$orderDate);

                }
					if (count($orders)<=0) {
						$lastOrderDate = Mage::getModel("sales/order")
										->getCollection()
                                        ->addAttributeToFilter('create_order_method',0)
										->setCurPage(1)
										->setPageSize(1);
                        $lastOrderDate->getSelect()->order("entity_id desc");
//										->setOrder('main_table.entity_id', 'desc');
						$lastDate = $lastOrderDate->getFirstItem()->getCreatedAt();
						$hourReport = 6;
						$helper->sendSalesOfEmailAlert($lastDate,$hourReport);
					}
			}
		}catch(Exception $e){
			$helper->alr_alert_log($e->getMessage(),'allureAlerts.log');
    	}
		
	}

	public function alertCheckoutIssue(){
		try{
			$helper = Mage::helper('alertservices');
			$status =	$this->getConfigHelper()->getEmailStatus();
			$checkout_status =	$this->getConfigHelper()->getCheckoutIssuesStatus();
			if (!$checkout_status) {
					return;
				}
			if ($status) {
				$currdate = Mage::getModel('core/date')->gmtDate();
				$toDate	= $currdate;
				$fromDate = date('Y-m-d H:i:s', strtotime($toDate) - 60 * 60 * 1);
				/*$fromDate = date('Y-m-d H:i:s', strtotime($toDate) - 60 * 10);*/

				$collection = Mage::getModel('alertservices/issues')->getCollection()
					->addFieldToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate))
					->addFieldToFilter('type',array('eq'=>'checkout'));
					//echo $collection->getSelect()->__toString();
					//change count to 10 on live
					if (count($collection) >= 10 && $status) {
						$helper->sendCheckoutIssueAlert($collection);
					}
			}
		}catch(Exception $e){
			$helper->alr_alert_log($e->getMessage(),'allureAlerts.log');
    	}
			
	}

	public function alertNullUsers(){
		try{
			$helper = Mage::helper('alertservices');
			$status =	$this->getConfigHelper()->getEmailStatus();
			$alr_status =	$this->getConfigHelper()->getAlrStatus();

			if (!$status || !$alr_status) {
				return;
			}
				$currdate = Mage::getModel('core/date')->gmtDate();
				$lastHour = date('H', strtotime($currdate) - 60 * 60 * 1);
				$analytics = $this->initializeAnalytics();
				$response = $this->getUsersReport($analytics);
				$users = $this->getResults($response,$lastHour,'users');
				if (!empty($users) && $users['users'] <= 0) {
					$helper->sendEmailAlertForNullUsers();
				}
			}catch(Exception $e){
				$helper->alr_alert_log($e->getMessage(),'allureAlerts.log');
    	}
				
	}

	public function alertPageNotFound(){
		try{
			$helper = Mage::helper('alertservices');

			$status =	$this->getConfigHelper()->getEmailStatus();
			$alr_status =	$this->getConfigHelper()->getAlrStatus();

			if (!$status || !$alr_status) {
				return;
			}
				$analytics = $this->initializeAnalytics();
					$response = $this->getPageReport($analytics);
					$pageReport = $this->getResults($response,null,'page');
					if (count($pageReport) > 0) {
						$helper->sendEmailAlertForPageNotFound($pageReport);
					}
			}catch(Exception $e){
				$helper->alr_alert_log($e->getMessage(),'allureAlerts.log');
    	}				
	}

	public function alertAvgPageLoad(){
		try{
			$helper = Mage::helper('alertservices');
			$status =	$this->getConfigHelper()->getEmailStatus();
			$avg_load_status =	$this->getConfigHelper()->getPageLoadStatus();
			if (!$avg_load_status) {
					return;
				}
			$configPath = $this->getConfigHelper()->getAvgLoadTimePath();
			$timeArray = $this->getConfigHelper()->getAvgLoadTimeArray();
				if ($status) {
					$ch = curl_init("https://www.mariatash.com/");
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$res = curl_exec($ch);
					$info = curl_getinfo($ch);
					$avg_time = number_format((float)$info['total_time'], 2);
					if ($avg_time) {
						if (is_null($timeArray) || !$timeArray) {
							Mage::getModel('core/config')->saveConfig($configPath,$avg_time)->cleanCache();
						}else{
							$timearray = explode(',', $timeArray);
							if(count($timearray) < 7){
								array_push($timearray,$avg_time);

								$newAvgValue = implode(',', $timearray);
								Mage::getModel('core/config')->saveConfig($configPath,$newAvgValue)->cleanCache();
							}
							if(count($timearray) == 7){
								array_shift($timearray);
								array_push($timearray,$avg_time);

								$newAvgValue = implode(',', $timearray);
								Mage::getModel('core/config')->saveConfig($configPath,$newAvgValue)->cleanCache();
							}
						}
					}
				}
			}catch(Exception $e){
				$helper->alr_alert_log($e->getMessage(),'allureAlerts.log');
    	}				
	}

	public function alertAvgPageLoadEmail(){
		try{
			$helper = Mage::helper('alertservices');
			$status =	$this->getConfigHelper()->getEmailStatus();
			$avg_load_status =	$this->getConfigHelper()->getPageLoadStatus();
			if (!$avg_load_status) {
					return;
				}
			$timeArray = $this->getConfigHelper()->getAvgLoadTimeArray();
				if ($status) {
					$timearray = explode(',', $timeArray);
					if(count($timearray) == 7){
						$totAvgTime = (array_sum($timearray))/7;
						if ($totAvgTime >= 30) {
							$helper->sendEmailAlertForAvgPageLoad($totAvgTime);
						}else{
							$helper->alr_alert_log($totAvgTime.' is not > 30','allureAlerts.log');
						}
					}
				}
						
			}catch(Exception $e){
				$helper->alr_alert_log($e->getMessage(),'allureAlerts.log');
    	}				
	}

	public function initializeAnalytics()
		{
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
		public function getUsersReport($analytics) {
		    
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

		public function getPageReport($analytics) {
		    
		    // Replace with your view ID, for example XXXX.
		    $VIEW_ID = "181106821";
		    // Create the DateRange object.
		    $dateRange = new Google_Service_AnalyticsReporting_DateRange();
		    $dateRange->setStartDate("6daysAgo");
		    $dateRange->setEndDate("today");
		    //new added by aws02
		    $dimension = new Google_Service_AnalyticsReporting_Dimension();
		    $dimension->setName("ga:pageTitle");
		    
		    $dimension1 = new Google_Service_AnalyticsReporting_Dimension();
		    $dimension1->setName("ga:pagePath");

		     $dimension2 = new Google_Service_AnalyticsReporting_Dimension();
		    $dimension2->setName("ga:sourceMedium");
		    
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
		public function getResults($reports,$lastHour,$form) {
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
		                        $final_report[] = array($dimensions[$i+1],$dimensions[$i+2]);
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

		public function alertSalesOfTwo($debug = false){
		/* Get the collection */
		try{
			$helper = Mage::helper('alertservices');
			$status =	$this->getConfigHelper()->getEmailStatus();
			$test_status =	$this->getConfigHelper()->getTestEmailStatus();
			if (!$test_status) {
					return;
				}
			if ($status) {
				$currdate = Mage::getModel('core/date')->gmtDate();
				$toDate	= $currdate;
				$fromDate = date('Y-m-d H:i:s', strtotime($currdate) - 60 * 60 * 2);
				/*$fromDate = date('Y-m-d H:i:s', strtotime($toDate) - 60 * 15);*/
				$orders = Mage::getModel('sales/order')->getCollection()
						  ->addAttributeToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate))
                          ->addAttributeToFilter('create_order_method',0)
						  ->addAttributeToSelect('*')
						  ->setCurPage(1)
						  ->setPageSize(1);
                $orders->getSelect()->order("entity_id desc");
//						  ->setOrder('created_at', 'desc');
				if ($debug) {
				    $orderDate = $orders->getFirstItem()->getCreatedAt();
					echo $orders->getSelect()->__toString();
					echo "<br>order count : ".count($orders);
					echo "<br>2 hour for testing for sale<br>";
					echo "<br>Order Date :".$orderDate;
					if($orderDate != null)
                    echo "<br>Last Order Date :".Mage::getModel('core/date')->date("F j, Y \a\\t g:i a",$orderDate);
//					if(count($orders)>0) {https://mt-staging.allurecommerce.com/
//                        $helper->sendSalesOfEmailAlert($orderDate, 2);
//                    }
					/*$lastOrderDate = Mage::getModel("sales/order")
										->getCollection()
										->setCurPage(1)
										->setPageSize(1)
										->setOrder('main_table.entity_id', 'desc');
					$lastDate = $lastOrderDate->getLastItem()->getCreatedAt();
					var_dump($currdate);
					var_dump($lastDate);
					$lDate = new DateTime($lastDate);
					$cDate = new DateTime($currdate);
					$interval = $lDate->diff($cDate);
					$diffhours = $interval->h;
					$diffhours = $diffhours + ($interval->days*24);
					echo $diffhours."<br>";*/
				}
					if (count($orders)<=0) {
						$lastOrderDate = Mage::getModel("sales/order")
										->getCollection()
                                        ->addAttributeToFilter('create_order_method',0)
										->setCurPage(1)
										->setPageSize(1);
						$lastOrderDate->getSelect()->order("entity_id desc");
//										->setOrder('entity_id', 'desc');

                        $lastDate = $lastOrderDate->getFirstItem()->getCreatedAt();
						$hourReport = 2;
						$helper->sendSalesOfEmailAlert($lastDate,$hourReport);
					}
			}
		}catch(Exception $e){
			$helper->alr_alert_log($e->getMessage(),'allureAlerts.log');
    	}
		
	}
		/*$lastOrderDate = Mage::getModel("sales/order")
										->getCollection()
										->setCurPage(1)
										->setPageSize(1)
										->setOrder('main_table.entity_id', 'desc');
				$cdate = date_create($currdate);
				$ldate = date_create($lastOrderDate->getLastItem()->getCreatedAt());

				$diff=date_diff($cdate,$ldate);
				if (condition) {
					# code...
				}
				var_dump('last order date : ');
				var_dump($ldate);
				var_dump('hours date diff: ');
				var_dump($diff);
				die();*/




	
}