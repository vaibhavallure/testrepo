<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright  Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales Order Invoice PDF model
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Allure_Appointments_Model_Pdf extends Mage_Sales_Model_Order_Pdf_Abstract
{
    /**
     * Draw header for item table
     *
     * @param Zend_Pdf_Page $page
     * @return void
     */
    
    public $pages = array();
    protected function _drawHeader(Zend_Pdf_Page $page)
    {
        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y -15);
        $this->y -= 10;
        $page->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0));
        
        //columns headers
        $lines[0][] = array(
            'text' => Mage::helper('sales')->__('Time'),
            'feed' => 35
        );
        
        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('Name'),
            'feed'  => 175,
            'align' => 'right'
        );
        
        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('Piercer Name'),
            'feed'  => 350,
            'align' => 'right'
        );
        
        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('Note'),
            'feed'  => 450,
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
    public function getPdf($appointments = array())
    {
        $this->_beforeGetPdf();
        //$this->_initRenderer('invoice');
        
        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
      
        $page  = $this->newPage();
        $this->_drawHeader($page);
        foreach ($appointments as $appointment) {
            
            
            $order = Mage::getModel('sales/order');
           
            /* Add body */
           
                /* Draw item */
            $this->drawTest($appointment,$pdf,$page);
            $page = end($pdf->pages);
            
        }
        $this->_afterGetPdf();
        return $pdf;
    }
    
    
    public function drawTest($appointment,$pdf,$page)
    {
        $lines  = array();
        $pdf    = $this->_getPdf();
        // draw Product name
        $lines[][] = array(
            'text' => date("F j, Y H:i", strtotime($appointment->getAppointmentStart())),
            'font' => 'italic',
            'feed' => 35
        );
       
        $lines[0][] = array(
            'text'  => Mage::helper('core/string')->str_split($appointment->getFirstname().' '.$appointment->getLastname(), 35),
            'feed'  => 150,
            'align' => 'left'
        );
        $lines[0][] = array(
            'text'  => Mage::helper('core/string')->str_split($appointment->getFname().' '.$appointment->getLname(), 35),
            'feed'  => 300,
            'align' => 'left'
        );
        
        $lines[0][] = array(
            'text'  => Mage::helper('core/string')->str_split($appointment->getSpecialNotes(), 35),
            'feed'  => 435,
            'align' => 'left'
        );
        
        
        $lineBlock = array(
            'lines'  => $lines,
            'height' => 20
        );
        
        $page = $this->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
       $this->setPage($page);
    }
    
    
    public function drawLineBlocks(Zend_Pdf_Page $page, array $draw, array $pageSettings = array())
    {
        foreach ($draw as $itemsProp) {
            if (!isset($itemsProp['lines']) || !is_array($itemsProp['lines'])) {
                Mage::throwException(Mage::helper('sales')->__('Invalid draw line data. Please define "lines" array.'));
            }
            $lines  = $itemsProp['lines'];
            $height = isset($itemsProp['height']) ? $itemsProp['height'] : 10;
            
            if (empty($itemsProp['shift'])) {
                $shift = 0;
                foreach ($lines as $line) {
                    $maxHeight = 0;
                    foreach ($line as $column) {
                        $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                        if (!is_array($column['text'])) {
                            $column['text'] = array($column['text']);
                        }
                        $top = 0;
                        foreach ($column['text'] as $part) {
                            $top += $lineSpacing;
                        }
                        
                        $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                    }
                    $shift += $maxHeight;
                }
                $itemsProp['shift'] = $shift;
            }
            
            if ($this->y - $itemsProp['shift'] < 15) {
                $page = $this->newPage($pageSettings);
            }
            
            foreach ($lines as $line) {
                $maxHeight = 0;
                foreach ($line as $column) {
                    $fontSize = empty($column['font_size']) ? 10 : $column['font_size'];
                    if (!empty($column['font_file'])) {
                        $font = Zend_Pdf_Font::fontWithPath($column['font_file']);
                        $page->setFont($font, $fontSize);
                    } else {
                        $fontStyle = empty($column['font']) ? 'regular' : $column['font'];
                        switch ($fontStyle) {
                            case 'bold':
                                $font = $this->_setFontBold($page, $fontSize);
                                break;
                            case 'italic':
                                $font = $this->_setFontItalic($page, $fontSize);
                                break;
                            default:
                                $font = $this->_setFontRegular($page, $fontSize);
                                break;
                        }
                    }
                    
                    if (!is_array($column['text'])) {
                        $column['text'] = array($column['text']);
                    }
                    
                    $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                    $top = 0;
                    foreach ($column['text'] as $part) {
                        if ($this->y - $lineSpacing < 15) {
                            $page = $this->newPage($pageSettings);
                        }
                        
                        $feed = $column['feed'];
                        $textAlign = empty($column['align']) ? 'left' : $column['align'];
                        $width = empty($column['width']) ? 0 : $column['width'];
                        switch ($textAlign) {
                            case 'right':
                                if ($width) {
                                    $feed = $this->getAlignRight($part, $feed, $width, $font, $fontSize);
                                }
                                else {
                                    $feed = $feed - $this->widthForStringUsingFontSize($part, $font, $fontSize);
                                }
                                break;
                            case 'center':
                                if ($width) {
                                    $feed = $this->getAlignCenter($part, $feed, $width, $font, $fontSize);
                                }
                                break;
                        }
                        $page->drawText($part, $feed, $this->y-$top, 'UTF-8');
                        $top += $lineSpacing;
                    }
                    
                    $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                }
                $this->y -= $maxHeight;
            }
        }
        
        return $page;
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
