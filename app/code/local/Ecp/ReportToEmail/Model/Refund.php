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


    public function sendReport($getdate=false,$show=false,$call="cron")
    {


        $this->add_log("script run from ".$call."-------------------------------------------------------");



        $emails = trim(Mage::getStoreConfig('report/refund_report/emails'));
        if (! $emails)
            return;
        $emails = explode(',', $emails);


        $storesId=1;
        $storeId=$storesId;

      /*  if($getdate) {
            $yesterday = $getdate;
            $from = $yesterday . "00:00:00";
            $to = $yesterday . "23:59:59";

            $this->add_log("getdate => from=>".$from." to=>".$to);

        } else {*/

            $days = 1;
            $from = date('Y-m-d 00:00:00', strtotime('-' . ($days) . ' days'));
            $to = date('Y-m-d 23:59:59', strtotime('yesterday'));

            $yesterday=date('Y-m-d', strtotime('yesterday'));

            $this->add_log("default date => from=>".$from." to=>".$to." yesterday=>".$yesterday);


     //   }


        $diffZone = $this->getDiffTimezone();
        $to = date('Y-m-d H:i:s', strtotime($diffZone, strtotime($to)));
        $from = date('Y-m-d H:i:s', strtotime($diffZone, strtotime($from)));

        $this->add_log("timezone date => from=>".$from." to=>".$to." diffZone=>".$diffZone);

        Mage::app()->getStore()->setId($storeId);


        $mailbody = '<style type="text/css">';
        $mailbody .= '.ExternalClass *{line-height:0;}';
        $mailbody .= 'div,p,a,li,td {-webkit-text-size-adjust:none;-moz-text-size-adjust:none;text-size-adjust:none;-ms-text-size-adjust:none;}';
        $mailbody .= '</style><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
        $mailbody.=$this->getCollectionHtmlTable($this->getData($from,$to,"orderdate",null,true),$yesterday,$storesId);
        $mailbody.=$this->getCollectionHtmlTable($this->getData($from,$to,"refunddate",null,true),$yesterday,$storesId);


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


    public function getCollectionHtmlTable($data,$period,$storesId)
    {

        $currency=Mage::app()->getStore($storesId)->getCurrentCurrencyCode();
        $symbol=Mage::app()->getLocale()->currency($currency)->getSymbol();

        if($data['by']=="refunddate")
            $caption='<caption>REFUND REPORT BY ORDER REFUND DATE</caption>';
        elseif ($data['by']=="orderdate")
            $caption='<caption>REFUND REPORT BY ORDER DATE</caption>';

        $mailbody="";

        $mailbody .= '<table style="border:1px solid black;border-collapse: collapse;margin-bottom: 30px;">';
        $mailbody .= $caption;
        $mailbody .= $this->tr($this->th("Period")."".$this->th("Orders Count")."".$this->th("Refunded")."".$this->th("Online Refunded")."".$this->th("Offline Refunded"));


        if($data["orders_count"]>0) {
            $refundOnline = ($data["online_refunded"]) ? $data["online_refunded"] : "0";
            $refundOffline = ($data["offline_refunded"]) ? $data["offline_refunded"] : "0";
            //period,orders_count,refunded,online_refunded,offline_refunded
            $mailbody .= $this->tr($this->td($period) . "" . $this->td($data["orders_count"]) . "" . $this->td($symbol . round($data["refunded"], 2)) . "" . $this->td($symbol . round($refundOnline, 2)) . "" . $this->td($symbol . round($refundOffline, 2)));

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

    public function getReportCSV($from,$to,$by,$status=null,$customerGroup=null,$orderfrom=null)
    {

        $date=date('Ymd');
        $folderPath   = Mage::getBaseDir('var') . DS . 'export';
        $filename     = "refund_report_By_".$by."_".$date.".csv";
        $filepath     = $folderPath . DS . $filename;

        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $io->open(array("path" => $folderPath));
        $csv = new Varien_File_Csv();



        $data=$this->getData($from,$to,$by,$status,false,$customerGroup,$orderfrom);
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

    public function getData($from,$to,$by,$status=null,$total=false,$customerGroup=null,$orderfrom=null)
    {

        $this->add_log("getData => from=>".$from." to=>".$to." status=>".$status." total=>".$total);

        try {

            if($by=="orderdate")
                $query = "SELECT ord.created_at as order_date,memo.created_at as memo_date,ord.increment_id,ord.base_grand_total*ord.store_to_base_rate as base_grand_total,ord.base_total_refunded*ord.store_to_base_rate as base_total_refunded,IFNULL(ord.base_total_online_refunded*ord.store_to_base_rate,0) as base_total_online_refunded,IFNULL(IF(ord.increment_id LIKE '%TW%',ord.base_total_refunded*ord.store_to_base_rate,ord.base_total_offline_refunded*ord.store_to_base_rate),0) as base_total_offline_refunded,ord.customer_id,ord.customer_email,cg.customer_group_code,ord.state FROM `sales_flat_order` ord JOIN `sales_flat_creditmemo` memo ON ord.entity_id=memo.order_id JOIN `customer_group` as cg ON ord.customer_group_id=cg.customer_group_id WHERE (ord.created_at >= '".$from."' AND ord.created_at <= '".$to."') AND ord.base_total_refunded IS NOT NULL  AND ord.store_id=1 ";
            else
                $query = "SELECT ord.created_at as order_date,memo.created_at as memo_date,ord.increment_id,ord.base_grand_total*ord.store_to_base_rate as base_grand_total,ord.base_total_refunded*ord.store_to_base_rate as base_total_refunded,IFNULL(ord.base_total_online_refunded*ord.store_to_base_rate,0) as base_total_online_refunded,IFNULL(IF(ord.increment_id LIKE '%TW%',ord.base_total_refunded*ord.store_to_base_rate,ord.base_total_offline_refunded*ord.store_to_base_rate),0) as base_total_offline_refunded,ord.customer_id,ord.customer_email,cg.customer_group_code,ord.state FROM `sales_flat_order` ord JOIN `sales_flat_creditmemo` memo ON ord.entity_id=memo.order_id JOIN `customer_group` as cg ON ord.customer_group_id=cg.customer_group_id WHERE (memo.created_at >= '".$from."' AND memo.created_at <= '".$to."') AND ord.base_total_refunded IS NOT NULL  AND ord.store_id=1 ";


           if(count($status))
              $query .="  AND ord.state IN(". "'" . implode ( "', '", $status ) . "')";


           if(count($customerGroup))
               $query .= " AND ord.customer_group_id IN(".implode(",",$customerGroup).")";


           if(count($orderfrom))
               $query .=" AND ord.create_order_method IN(".implode(",",$orderfrom).")";



           $query.=" GROUP BY memo.order_id";

//           var_dump($query);
//
//           die();


            $this->add_log("getData => query=>".$query);

            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');

            $results = $readConnection->fetchAll($query);


            $total_data['by']=$by;
            $total_data['count']=0;
            $total_data['refunded']=0;
            $total_data['online_refunded']=0;
            $total_data['offline_refunded']=0;

            foreach ($results as $rs)
            {


                $total_data['orders_count']++;
                $total_data['refunded']+=$rs['base_total_refunded'];
                $total_data['online_refunded']+=$rs['base_total_online_refunded'];
                $total_data['offline_refunded']+=$rs['base_total_offline_refunded'];

                $rs['order_date']=$this->formatDate($rs['order_date']);
                $rs['memo_date']=$this->formatDate($rs['memo_date']);
                $rs['base_grand_total']="$".round($rs['base_grand_total'],2);
                $rs['base_total_refunded']="$".round($rs['base_total_refunded'],2);
                $rs['base_total_online_refunded']="$".round($rs['base_total_online_refunded'],2);
                $rs['base_total_offline_refunded']="$".round($rs['base_total_offline_refunded'],2);
                $data[]=$rs;
            }

          //  $this->add_log("getData => data=>");

          //  Mage::log($data,Zend_log::DEBUG,"report_to_email.log",true);

        //    $this->add_log("totdal data");

          //  Mage::log($total_data,Zend_log::DEBUG,"report_to_email.log",true);


            if($total)
                return $total_data;
            else
                return $data;

        }
        catch (Exception $e)
        {
            $this->add_log("Exception-:".$e->getMessage());
        }
    }

    public function getHeader()
    {
        return array(array(1=>"Order Date",2=>"Credit Memo Date",3=>"Order No",4=>"Order Total",5=>"Refund Amount",6=>"Online Refund",7=>"Offline Refund",8=>"Customer Id",9=>"Customer Email",10=>"Customer Group",11=>"Order Status"));
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

    public function getReport($post)
    {

        $to=$post['to_date'];
        $from=$post['from_date'];
        $by=$post['order_type'];
        $order_statuses=$post['order_statuses'];
        $customerGroup=$post['customer_group'];
        $orderfrom=$post['create_order_method'];



        $diffZone = $this->getDiffTimezone();
        $to = date('Y-m-d H:i:s', strtotime($diffZone, strtotime($to)));
        $from = date('Y-m-d H:i:s', strtotime($diffZone, strtotime($from)));



        if($this->getReportCSV($from,$to,$by,$order_statuses,$customerGroup,$orderfrom))
        {
            return array("is_create"=>true,"value"=>$this->getReportCSV($from,$to,$by,$order_statuses,$customerGroup,$orderfrom));
        }
        else
        {
            return array("is_create"=>false);
        }
    }



}
