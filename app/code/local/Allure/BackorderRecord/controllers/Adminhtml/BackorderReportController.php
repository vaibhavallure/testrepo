<?php

class Allure_BackorderRecord_Adminhtml_BackorderReportController extends Mage_Adminhtml_Controller_Action
{



    public function indexAction()
    {

        $this->loadLayout();
        $this->_title($this->__("Back order Record"));

        $this->getLayout ()->getBlock ( "head" )->setCanLoadExtJs ( true );
        $this->_addContent ( $this->getLayout ()->createBlock ( "backorderrecord/adminhtml_backorder_edit" ) )
            ->_addLeft ( $this->getLayout ()->createBlock ( "backorderrecord/adminhtml_backorder_edit_tabs" ) );
        $this->renderLayout ();

    }

    public function saveAction()
    {

        $post_data = $this->getRequest()->getPost();

        $report = Mage::helper("backorderrecord")->getReportXls($post_data);

        if ($report['is_create']) {

            $file = $report['value'];

            $this->_prepareDownloadResponse(basename($file), array("type"=>"filename","value"=>$file));

            /*if (file_exists($file)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($file) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file));
                readfile($file);
                exit;
            }*/
        }else{
            return;
        }
    }

}