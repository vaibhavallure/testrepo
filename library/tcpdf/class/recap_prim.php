<?php
//============================================================+
// File name   : MC_TCPDF.php
// Begin       : 2008-03-04
// Last Update : 2011-04-26
//
// Description : TCPDF class
//               Text on multiple columns
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               Manor Coach House, Church Hill
//               Aldershot, Hants, GU12 4RQ
//               UK
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: Text on multiple columns
 * @author Nicola Asuni
 * @since 2008-03-04
 */


/**
 * Extend TCPDF to work with multiple columns
 */
class recap_prim extends TCPDF {
	/**
	 * Header string delivery.
	 * @protected
	 */
	protected $string_delivery = '';
	
	/**
	 * Header string prices.
	 * @protected
	 */
	protected $string_prices = '';
	/**
	 * ID of the stored default footer template (-1 = not set).
	 * @protected
	 */
	protected $footer_xobjid = -1;
	
	/**
	 * Set header data.
	 * @param $ln (string) header image logo
	 * @param $lw (string) header image logo width in mm
	 * @param $ht (string) string to print as title on document header
	 * @param $hs (string) string to print on document header
	 * @param $tc (array) RGB array color for text.
	 * @param $lc (array) RGB array color for line.
	 * @public
	 */
	public function setHeaderData($delivery='', $prices='') {
		$this->string_delivery = $delivery;
		$this->string_prices = $prices;
	}
	
	/**
	 * Returns header data:
	 * <ul><li>$ret['delivery'] = delivery string</li><li>$ret['prices'] = prices string</li></ul>
	 * @return array()
	 * @public
	 * @since 4.0.012 (2008-07-24)
	 */
	public function getHeaderData() {
		$ret = array();
		$ret['delivery'] = $this->string_delivery;
		$ret['prices'] = $this->string_prices;
		return $ret;
	}


//Page header
	public function Header() {
		/*$livraison='Livraison fin 2014 - début 2015';
		$prix="Ces prix sont valables jusqu'à la prochaine offre, dans la limite des stocks disponibles";
		//$this->resetColumns();
		//$this->Ln(1);
		
		$this->SetFont('helvetica', 'B', 11);
		$this->MultiCell(60, 5,$livraison, 0, 'L', 0, 0, '', '', true);
		
		$this->SetFont('helvetica', 'B', 9);
		$this->SetTextColor(23, 100, 71, 12);
		$this->MultiCell(0, 5,$prix, 0, 'R', 0, 1, '', '', true);*/
		
		
		if ($this->header_xobjid < 0) {
			// start a new XObject Template
			$this->header_xobjid = $this->startTemplate($this->w, $this->tMargin);
			$headerfont = $this->getHeaderFont();
			$headerdata = $this->getHeaderData();
			$this->y = $this->header_margin;
			if ($this->rtl) {
				$this->x = $this->w - $this->original_rMargin;
			} else {
				$this->x = $this->original_lMargin;
			}
			if (($headerdata['logo']) AND ($headerdata['logo'] != K_BLANK_IMAGE)) {
				$imgtype = TCPDF_IMAGES::getImageFileType(K_PATH_IMAGES.$headerdata['logo']);
				if (($imgtype == 'eps') OR ($imgtype == 'ai')) {
					$this->ImageEps(K_PATH_IMAGES.$headerdata['logo'], '', '', $headerdata['logo_width']);
				} elseif ($imgtype == 'svg') {
					$this->ImageSVG(K_PATH_IMAGES.$headerdata['logo'], '', '', $headerdata['logo_width']);
				} else {
					$this->Image(K_PATH_IMAGES.$headerdata['logo'], '', '', $headerdata['logo_width']);
				}
				$imgy = $this->getImageRBY();
			} else {
				$imgy = $this->y;
			}
			$cell_height = round(($this->cell_height_ratio * $headerfont[2]) / $this->k, 2);
			// set starting margin for text data cell
			if ($this->getRTL()) {
				$header_x = $this->original_rMargin;
			} else {
				$header_x = $this->original_lMargin;
			}
			$cw = $this->w - $this->original_lMargin - $this->original_rMargin;
			$this->SetTextColorArray($this->header_text_color);
			// header delivery
			$this->SetFont(helvetica, 'B', 11);
			$this->SetX($header_x);
			$this->MultiCell($cw, $cell_height, $headerdata['delivery'], 0, 'L', '', 0, $header_x, '');
			// header string
			$this->SetFont('helvetica', 'B', 9);
			$this->SetTextColor(23, 100, 71, 12);
			$this->SetX($header_x);
			$this->MultiCell($cw, $cell_height, $headerdata['prices'], 0, 'R', 0, 1, '', '', true, 0, false, true, 0, 'B', false);
			// print an ending header line
			$this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $headerdata['line_color']));
			$this->SetY((2.835 / $this->k) + max($imgy, $this->y));
			if ($this->rtl) {
				$this->SetX($this->original_rMargin);
			} else {
				$this->SetX($this->original_lMargin);
			}
			$this->Cell(($this->w - $this->original_lMargin - $this->original_rMargin), 0, '', 'T', 1, 'C');
			$this->endTemplate();
		}
		// print header template
		$x = 0;
		$dx = 0;
		if (!$this->header_xobj_autoreset AND $this->booklet AND (($this->page % 2) == 0)) {
			// adjust margins for booklet mode
			$dx = ($this->original_lMargin - $this->original_rMargin);
		}
		if ($this->rtl) {
			$x = $this->w + $dx;
		} else {
			$x = 0 + $dx;
		}
		$this->printTemplate($this->header_xobjid, $x, 0, 0, 0, '', '', false);
		if ($this->header_xobj_autoreset) {
			// reset header xobject template at each page
			$this->header_xobjid = -1;
		}

	}

	// Page footer
	public function Footer() {
		
		/*if ($this->header_xobjid < 0) {
			// start a new XObject Template
			$this->header_xobjid = $this->startTemplate($this->w, $this->tMargin);
			
			//TO DO REMPLIR LE TEMPLATE
			
			$this->endTemplate();
		}/*
		// print header template
		/*$x = 0;
		$dx = 0;
		if (!$this->header_xobj_autoreset AND $this->booklet AND (($this->page % 2) == 0)) {
			// adjust margins for booklet mode
			$dx = ($this->original_lMargin - $this->original_rMargin);
		}
		if ($this->rtl) {
			$x = $this->w + $dx;
		} else {
			$x = 0 + $dx;
		}
		$this->printTemplate($this->header_xobjid, $x, 0, 0, 0, '', '', false);
		if ($this->header_xobj_autoreset) {
			// reset header xobject template at each page
			$this->header_xobjid = -1;
		}*/
		
		
		$cur_y = $this->y;
			$this->SetTextColorArray($this->footer_text_color);
			$w_page = isset($this->l['w_page']) ? $this->l['w_page'].' ' : '';
			if (empty($this->pagegroups)) {
				$pagenumtxt = $w_page.$this->getAliasNumPage().' / '.$this->getAliasNbPages();
			} else {
				$pagenumtxt = $w_page.$this->getPageNumGroupAlias().' / '.$this->getPageGroupAlias();
			}
			$this->SetY($cur_y);
			//Print page number
			if ($this->getRTL()) {
				$this->SetX($this->original_rMargin);
				$this->Cell(0, 0, $pagenumtxt, '', 1, 'L');
			} else {
				$this->SetX($this->original_lMargin);
				$this->Cell(0, 0, $this->getAliasRightShift().$pagenumtxt, '', 1, 'R');
			}
			$region="<strong>Millésima - <small>87, quai de Paludate - CS 11691 - 33050 BORDEAUX CEDEX - Tél. 05 57 808 808 - Fax. 05 57 808 819</small></strong><br>www.millesima.fr";
			$this->SetFont('helvetica', 'B', 10);
			$this->SetFillColor(0, 0, 0);
			$this->SetTextColor(255, 255, 255);
			$this->writeHTMLCell(0, 20, $this->GetX(),$this->GetY(), $region, 0, 1, 1, 1);
		
	}
	

	/**
	 * Set Region title
	 * @param $title (string) region
	 * @public
	 */
	public function RegionTitre($region) {
		$region = mb_strtoupper($region, 'UTF-8');
		$this->SetFont('helvetica', 'B', 15);
		$this->SetFillColor(0, 0, 0);
		$this->SetTextColor(255, 255, 255);
		$this->Cell(0, 8, $region, 0, 1, '', 1);
		$this->Ln(1);
	}
	
	/**
	 * Set type sorties title
	 * @param $type (string) texte à afficher
	 * @public
	 */
	public function SortiesTitre($type) {
		$type = mb_strtoupper($type, 'UTF-8');
		$this->SetFont('helvetica', 'B', 11);
		$this->SetFillColor(255, 255, 255);
		$this->SetTextColor(60, 51, 51, 20);
		$this->setCellPaddings(0);
		$this->Cell(0, 6, $type, array('B' => array('width' => 0.4, 'color' => array(60, 51, 51, 20))), 1, '', 1, false);
		$this->Ln(1);
	}
	/**
	 * Set type appellation title
	 * @param $appellation (string) texte à afficher
	 * @public
	 */
	public function AppellationTitre($appel) {
		$this->SetFont('helvetica', 'B', 11);
		$this->SetFillColor(255, 255, 255);
		$this->SetTextColor(23, 100, 71, 12);
		$this->setCellPaddings(0);
		$this->Cell(0, 6, $appel, array('B' => array('width' => 0.2, 'color' => array(60, 51, 51, 20))), 1, '', 1, false);
		$this->Ln(1);
	}
	
	/**
	 * Set type appellation title
	 * @param $appellation (string) texte à afficher
	 * @public
	 */
	public function getEntetes($pdf_btleht, $pdf_caisht, $pdf_caisttc) {
		//global $pdf_btleht, $pdf_caisht, $pdf_caisttc; 
		$this->SetFont('helvetica', '', 7);
		$this->SetTextColor(0, 0, 0);
		
		$prixhtblle = $pdf_btleht;
		//$prixhtblle = "blabla";
		$this->writeHTMLCell(13,1,$this->GetX()+62, $this->GetY(), $prixhtblle, 0,0,false,true,'R', true);
		
		$this->SetFont('helvetica', 'B', 7);
		$prix_ht = $pdf_caisht;
		//$prixhtblle = "blabla";
		$this->writeHTMLCell(13,1, $this->GetX(), $this->GetY(), $prix_ht, 0,0,false,true,'R', true);
		
		$this->SetFont('helvetica', '', 7);
		$prix_ttc = $pdf_caisttc;
		//$prixhtblle = "blabla";
		$this->writeHTMLCell(13,1, $this->GetX(), $this->GetY(), $prix_ttc, 0,1,false,true,'R', true);
	}
	
	/**
	 * Set type appellation title
	 * @param $appellation (string) texte à afficher
	 * @public
	 */
	public function insertEntetes($pdf_btleht, $pdf_caisht, $pdf_caisttc, $nbprod) {
		$this->setEqualColumns(2, 100);
		
		$this->getEntetes($pdf_btleht, $pdf_caisht, $pdf_caisttc);
		if( $nbprod > 10 ){
			$this->selectColumn(1);
			$this->Ln(1);
			$this->getEntetes($pdf_btleht, $pdf_caisht, $pdf_caisttc);	
		}
		
		$this->resetColumns();
		$this->Ln(1);
	}

	/**
	 * Print chapter body
	 * @param $file (string) name of the file containing the chapter body
	 * @param $mode (boolean) if true the chapter body is in HTML, otherwise in simple text.
	 * @public
	 */
	public function Produit($produit) {
		$this->SetFont('helvetica', '', 7);
		$this->SetTextColor(0, 0, 0);
		/*
		$this->Write(8,utf8_encode($produit->libelle_internet));
		if($produit->classement != ''){
			$this->SetFont('helvetica', 'I', 8);
			$this->Write(8,' / '.utf8_encode($produit->classement));
		}
		*/
		$name = '<strong>'.utf8_encode($produit->libelle_internet).'</strong>';
		if($produit->classement != ''){
			$name .= ' / <em>'.utf8_encode($produit->classement).'</em>';
		}
		$this->writeHTMLCell(48,4, $this->GetX(), $this->GetY(), $name, 0,0,false,true,'L', true);
		//$height = $this->getCellHeightRatio();
		$this->Image(REPAPPLI.'templates/new_template/pdf_primeur/images/'.$produit->quantite.'.png',$this->GetX(), $this->GetY(), '3.5', '3.5', '', '', 'T', true, 150, '', false, false, 0, 'CB', false, false);
		$quantite = ' ..........';
		$this->writeHTMLCell(10,4, $this->GetX(), $this->GetY(), $quantite, 0,0,false,false,'R', true);
		
		$prixhtblle = utf8_encode($produit->prixhtblle);
		$this->writeHTMLCell(13,4, $this->GetX(), $this->GetY(), $prixhtblle, 0,0,false,false,'R', true);
		
		$this->SetFont('helvetica', 'B', 7);
		$prix_ht = utf8_encode($produit->prix_ht);
		$this->writeHTMLCell(13,4, $this->GetX(), $this->GetY(), $prix_ht, 0,0,false,false,'R', true);
		
		$this->SetFont('helvetica', '', 7);
		$prix_ttc = utf8_encode($produit->prix_ttc);
		$this->writeHTMLCell(13,4, $this->GetX(), $this->GetY(), $prix_ttc, 0,4,false,false,'R', true);
		
		//$this->Ln(4);
		
		
	}
	
	/**
	 * Print chapter body
	 * @param $file (string) name of the file containing the chapter body
	 * @param $mode (boolean) if true the chapter body is in HTML, otherwise in simple text.
	 * @public
	 */
	public function InsertListe($liste_produits, $nbprod, $autocol) {
		$this->setEqualColumns(2, 100);
		
		$Ymax = $this->GetY();
		$moitie = number_format($nbprod/2)+1;
		//echo $moitie;
		$i = 1;
		$currentAppellation = '';
		foreach($liste_produits as $produit){
			
			if($currentAppellation != $produit->appellation){
				$this->AppellationTitre(utf8_encode($produit->appellation));
				$currentAppellation = $produit->appellation;
			}
			$this->Produit($produit);
			
			if(!$autocol){
				if( $moitie > 5 && $moitie == $i && $moitie < 25 ){
					//echo "bouh !" ;
					$this->Ln(4);
					$Ymax =  max(array($this->GetY(), $Ymax));
					$this->selectColumn(1);
					$this->Ln(1);
					$currentAppellation = '';
				}
			}
			
			$Ymax = max(array($this->GetY(), $Ymax));
			$i++;
			
		}
		
		$this->resetColumns();
		$this->SetY($Ymax);
		$this->Ln(2);
	}
} // end of extended class

