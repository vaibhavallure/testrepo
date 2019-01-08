<?php

class Allure_BackorderRecord_Adminhtml_RefundReportController extends Mage_Adminhtml_Controller_Action
{


    public function indexAction()
    {

        $this->loadLayout();
        $this->_title($this->__("Refund  Detailed Report"));

        $this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock("backorderrecord/adminhtml_refund_edit"))
            ->_addLeft($this->getLayout()->createBlock("backorderrecord/adminhtml_refund_edit_tabs"));
        $this->renderLayout();

    }

    public function saveAction()
    {

        $post_data = $this->getRequest()->getPost();

        $report = Mage::getModel("ecp_reporttoemail/refund")->getReport($post_data);


        if ($report['is_create']) {

            $file = $report['value'];

            $this->_prepareDownloadResponse(basename($file), array("type" => "filename", "value" => $file));

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
        } else {
            Mage::getSingleton("core/session")->addError('NO RECORDS FOUND FOR FROM '.$post_data['from_date'].' TO '.$post_data['to_date']);
            $this->_redirect('*/*/index');
        }

    }
}