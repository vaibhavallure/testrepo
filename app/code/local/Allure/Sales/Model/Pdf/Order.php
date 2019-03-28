<?php
/**
 * @author Allure
 *
 */
class Allure_Sales_Model_Pdf_Order extends Allure_Sales_Model_Pdf_Abstract
{
    /**
     * Draw header for item table
     *
     * @param Zend_Pdf_Page $page
     * @return void
     */
    protected function _drawHeader(Zend_Pdf_Page $page)
    {
        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y -15);
        $this->y -= 10;
        $page->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0));

        //columns headers
        $lines[0][] = array(
            'text' => Mage::helper('sales')->__('Products'),
            'feed' => 35
        );

        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('SKU'),
            'feed'  => 290,
            'align' => 'right'
        );

        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('Qty'),
            'feed'  => 435,
            'align' => 'right'
        );

        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('Price'),
            'feed'  => 360,
            'align' => 'right'
        );

        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('Tax'),
            'feed'  => 495,
            'align' => 'right'
        );

        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('Subtotal'),
            'feed'  => 565,
            'align' => 'right'
        );

        $lineBlock = array(
            'lines'  => $lines,
            'height' => 5
        );

        $this->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }

    /**
     * Return PDF document
     *
     * @param  array $invoices
     * @return Zend_Pdf
     */
    public function getPdf($order = array())
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('order');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        if ($order->getStoreId()) {
            Mage::app()->getLocale()->emulate($order->getStoreId());
            Mage::app()->setCurrentStore($order->getStoreId());
        }
        $page  = $this->newPage();
            /* Add image */
        $this->insertLogo($page, $order->getStore());
            /* Add address */
        $this->insertAddress($page, $order->getStore());
            /* Add head */
        $this->insertOrder(
            $page,
            $order,
            Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID, $order->getStoreId())
        );
        /* Add document text and number */
            /* $this->insertDocumentNumber(
                $page,
                Mage::helper('sales')->__('Order # ') . $invoice->getIncrementId()
            ); */
        /* Add table */
        $this->_drawHeader($page);
        /* Add body */
        $cnt = 0;
        foreach ($order->getAllVisibleItems() as $item){
            $cnt ++;
            if ($item->getParentItem()) {
                continue;
            }
            /* Draw item */
            //$this->_drawItem($item, $page, $order);
                
            if(count($order->getAllVisibleItems()) == $cnt){
                $item = $item->setIsLastItem(1);
                $this->_drawItem($item, $page, $order);
            }else{
                $this->_drawItem($item, $page, $order);
            }
                
            $page = end($pdf->pages);
        }
        /* Add totals */
        $this->insertTotals($page, $order);
        if ($order->getStoreId()) {
            Mage::app()->getLocale()->revert();
        }
        $this->_afterGetPdf();
        return $pdf;
    }

    /**
     * Create new page and assign to PDF object
     *
     * @param  array $settings
     * @return Zend_Pdf_Page
     */
    public function newPage(array $settings = array())
    {
        /* Add new table head */
        $page = $this->_getPdf()->newPage(Zend_Pdf_Page::SIZE_A4);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;
        if (!empty($settings['table_header'])) {
            $this->_drawHeader($page);
        }
        return $page;
    }
}
