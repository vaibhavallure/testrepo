<?php
/*
 * allure -------
 *
 * */
class Ecp_ReportToEmail_Model_Refund
{

    public function add_log($message) {
        if (!Mage::getStoreConfig('report/scheduled_reports/debug_enabled')) {
            return;
        }
        Mage::log("Refund Report=> ".$message,Zend_log::DEBUG,"report_to_email.log",true);
    }


    public function sendReport($getdate=null,$show=false)
    {


        $this->add_log("script run");

        $currentDate22 = Mage::app()->getLocale()->date();
        $date22 = $currentDate22->subHour(25);
        Mage::getResourceModel('sales/report_refunded')->aggregate($date22);



        $emails = trim(Mage::getStoreConfig('report/refund_report/emails'));
        if (! $emails)
            return;
        $emails = explode(',', $emails);


        $storesId=1;
        $storeId=$storesId;

        if($getdate) {
            $yesterday = $getdate;
            $from = $yesterday . "00:00:00";
            $to = $yesterday . "23:59:59";

        } else {

            $days = 1;
            $from = date('Y-m-d 00:00:00', strtotime('-' . ($days) . ' days'));
            $to = date('Y-m-d 23:59:59', strtotime('yesterday'));

            $yesterday=date('Y-m-d', strtotime('yesterday'));


        }


            $diffZone = $this->getDiffTimezone();
            $to = date('Y-m-d H:i:s', strtotime($diffZone, strtotime($to)));
            $from = date('Y-m-d H:i:s', strtotime($diffZone, strtotime($from)));

        Mage::app()->getStore()->setId($storeId);


        $mailbody = '<style type="text/css">';
        $mailbody .= '.ExternalClass *{line-height:0;}';
        $mailbody .= 'div,p,a,li,td {-webkit-text-size-adjust:none;-moz-text-size-adjust:none;text-size-adjust:none;-ms-text-size-adjust:none;}';
        $mailbody .= '</style><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
        $mailbody.=$this->getCollectionHtmlTable('sales/report_refunded_collection_refunded',$yesterday,$yesterday,$storesId);
        $mailbody.=$this->getCollectionHtmlTable('sales/report_refunded_collection_order',$yesterday,$yesterday,$storesId);


         if($show) {
             echo $mailbody;
         }


         $mail = new Zend_Mail();


        /* Sender Email */
        $sender = Mage::getStoreConfig('trans_email/ident_general/email');
        $storeDate = date('Y-m-d');
        $website = Mage::getModel('core/store')->load($storesId);


        if($this->getReportCSV($from,$to,"orderdate")) {
            $name1 = "Order_Refund_Report_By_Order_date_" . $yesterday . ".csv";
            $mail->createAttachment(
                file_get_contents($this->getReportCSV($from, $to, "orderdate")),
                Zend_Mime::TYPE_OCTETSTREAM,
                Zend_Mime::DISPOSITION_ATTACHMENT,
                Zend_Mime::ENCODING_BASE64,
                $name1
            );
        }

        if($this->getReportCSV($from,$to,"refunddate")) {
            $name2 = "Order_Refund_Report_By_Refund_date_" . $yesterday . ".csv";
            $mail->createAttachment(
                file_get_contents($this->getReportCSV($from, $to, "refunddate")),
                Zend_Mime::TYPE_OCTETSTREAM,
                Zend_Mime::DISPOSITION_ATTACHMENT,
                Zend_Mime::ENCODING_BASE64,
                $name2
            );
        }
        $mail->setBodyHtml($mailbody)
            ->setSubject($website->getName() . ': Daily Sales Refund Report for ' . $yesterday)
            ->addTo($emails)
            ->setFrom($sender, "Refund Report");

        try {
                $mail->send();
                $this->add_log("mail sent");

        } catch (Mage_Core_Exception $e) {
            Mage::log('Sending report ' . $e->getMessage(), Zend_log::DEBUG, 'accounting_report.log',true);
        } catch (Exception $e) {
            Mage::logException($e);
        }

    }


    public function getCollectionHtmlTable($collection,$from,$to,$storesId)
    {

        $currency=Mage::app()->getStore($storesId)->getCurrentCurrencyCode();
        $symbol=Mage::app()->getLocale()->currency($currency)->getSymbol();

        if($collection=="sales/report_refunded_collection_refunded")
            $caption='<caption>REFUND REPORT BY ORDER REFUND DATE</caption>';
        elseif ($collection=="sales/report_refunded_collection_order")
            $caption='<caption>REFUND REPORT BY ORDER DATE</caption>';

        $mailbody="";

        $resourceCollection = Mage::getResourceModel($collection)
            ->setPeriod('day')
            ->setDateRange($from, $to)
            ->addStoreFilter($storesId);

        $mailbody .= '<table style="border:1px solid black;border-collapse: collapse;margin-bottom: 30px;">';
        $mailbody .= $caption;
        $mailbody .= $this->tr($this->th("Period")."".$this->th("Orders Count")."".$this->th("Refunded")."".$this->th("Online Refunded")."".$this->th("Offline Refunded"));

        if(count($resourceCollection))
        {
            foreach ($resourceCollection as $rs) {
                $data = $rs->getData();
                $refundOnline = ($data["online_refunded"]) ? $data["online_refunded"] : "0";
                $refundOffline = ($data["offline_refunded"]) ? $data["offline_refunded"] : "0";
                /* period,orders_count,refunded,online_refunded,offline_refunded       */
                $mailbody .= $this->tr($this->td($data["period"]) . "" . $this->td($data["orders_count"]) . "" . $this->td($symbol . round($data["refunded"], 2)) . "" . $this->td($symbol . round($refundOnline, 2)) . "" . $this->td($symbol . round($refundOffline, 2)));

            }
        }
        else
        {
            $mailbody .=$this->tr($this->td("NO RECORD FOUND",5));
        }


        $mailbody.='</table>';


        return $mailbody;

    }

    public function tr($text)
    {
         return '<tr style="box-shadow: 2px 2px 8px gray">'.$text.'</tr>';
    }

    public function td($text,$colspan=0)
    {
        return '<td style="border:1px solid black;text-align: center;padding: 10px;" colspan="'.$colspan.'">'.$text.'</td>';
    }

    public function th($text)
    {
        return '<th style="border:1px solid black;padding: 5px 20px;background-color: #0A263C;color: white;font-family:Arial;font-size: 14px;text-transform: uppercase;">'.$text.'</th>';
    }

    public function getReportCSV($from,$to,$by)
    {

        $date=date('Ymd');
        $folderPath   = Mage::getBaseDir('var') . DS . 'export';
        $filename     = "refund_report_By_".$by."_".$date.".csv";
        $filepath     = $folderPath . DS . $filename;

        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $io->open(array("path" => $folderPath));
        $csv = new Varien_File_Csv();



        $data=$this->getData($from,$to,$by);
        if(count($data)) {
            $data = array_merge($this->getHeader(), $data);

            $csv->saveData($filepath, $data);
            return $filepath;
        }
        else
        {
            return false;
        }


    }

    public function getData($from,$to,$by)
    {
        try {

        if($by=="orderdate")
            $query = "SELECT ord.created_at as order_date,memo.created_at as memo_date,ord.increment_id,ord.base_grand_total,ord.base_total_refunded,ord.base_total_online_refunded,ord.base_total_offline_refunded,ord.customer_id,ord.customer_email FROM `sales_flat_order` ord JOIN `sales_flat_creditmemo` memo ON ord.entity_id=memo.order_id WHERE (ord.created_at >= '".$from."' AND ord.created_at <= '".$to."') AND ord.base_total_refunded IS NOT NULL  AND ord.store_id=1";
        else
          $query = "SELECT ord.created_at as order_date,memo.created_at as memo_date,ord.increment_id,ord.base_grand_total,ord.base_total_refunded,ord.base_total_online_refunded,ord.base_total_offline_refunded,ord.customer_id,ord.customer_email FROM `sales_flat_order` ord JOIN `sales_flat_creditmemo` memo ON ord.entity_id=memo.order_id WHERE (memo.created_at >= '".$from."' AND memo.created_at <= '".$to."') AND ord.base_total_refunded IS NOT NULL  AND ord.store_id=1";


        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');

        $results = $readConnection->fetchAll($query);

            foreach ($results as $rs)
            {
                $rs['order_date']=$this->formatDate($rs['order_date']);
                $rs['memo_date']=$this->formatDate($rs['memo_date']);

                $data[]=$rs;
            }

        return $data;

        }
        catch (Exception $e)
        {
            $this->add_log("Exception-:".$e->getMessage());
        }
    }

    public function getHeader()
    {
         return array(array(1=>"Order Date",2=>"Credit Memo Date",3=>"Order No",4=>"Order Total",5=>"Refund Amount",6=>"Online Refund",7=>"Offline Refund",8=>"Customer Id",9=>"Customer Email"));
    }



    public function getDiffTimezone()
    {

        $local_tz = new DateTimeZone('UTC');
        $local = new DateTime('now', $local_tz);


        $user_tz = new DateTimeZone(Mage::getStoreConfig('general/locale/timezone',1));
        $user = new DateTime('now', $user_tz);

        $usersTime = new DateTime($user->format('Y-m-d H:i:s'));
        $localsTime = new DateTime($local->format('Y-m-d H:i:s'));
        $offset = $local_tz->getOffset($local) - $user_tz->getOffset($user);
        $interval = $usersTime->diff($localsTime);

        if($offset > 0)
            return  $diffZone=$interval->h .' hours'.' '. $interval->i .' minutes';
        else
            return  $diffZone= '-'.$interval->h .' hours'.' '. $interval->i .' minutes';

    }

    public function  formatDate($date)
    {
        $diffZone="-".$this->getDiffTimezone();
        return date('Y-m-d h:i:s a', strtotime($diffZone,strtotime($date)));

    }



}

