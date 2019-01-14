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
class recap_prim_ligne extends TCPDF {
	/**
	 * Header country
	 * @protected
	 */
	protected $country = '';
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
	 * entête prix 1
	 * @protected
	 */
	protected $ent_1 = '';
	/**
	 * entête prix 2
	 * @protected
	 */
	protected $ent_2 = '';
	/**
	 * entête prix 3
	 * @protected
	 */
	protected $ent_3 = '';
	/**
	 * entête prix 3
	 * @protected
	 */
	protected $coordonnees = '';
	/**
	 * entête prix 3
	 * @protected
	 */
	protected $site_court = '';
	/**
	 * entête prix 3
	 * @protected
	 */
	protected $validite = '';
	/**
	 * entête prix 3
	 * @protected
	 */
	protected $revendeurs = '';
	/**
	 * entête prix 3
	 * @protected
	 */
	protected $indicprix = '';
	/**
	 * entête prix 3
	 * @protected
	 */
	protected $caisse12 = '';
	/**
	 * entête prix 3
	 * @protected
	 */
	protected $caisse6 = '';
	/**
	 * entête prix 3
	 * @protected
	 */
	protected $lastCell = '';
	
	// AddPage
	/**
	 * Appelle la fonction parent
	 * a la fin de l'execution, rajoute un entête si nbpage > 1
	 */
	/*public function AddPage($orientation='', $format='', $keepmargins=false, $tocpage=false) {
		parent::AddPage($orientation, $format, $keepmargins, $tocpage);
		$page = $this->getPage();
		if($page > 1){
			$this->SetY=0;
			$this->getEntetes();
		}
	}*/
	
	/*public function startPage($orientation='', $format='', $tocpage=false) {
		parent::startPage($orientation, $format, $tocpage);
		$page = $this->getPage();
		if($page > 1){
			$this->SetY=0;
			$this->getEntetes();
		}
	}*/
	
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
	public function setHeaderData($delivery='', $prices='', $country='') {
		$this->string_delivery = $delivery;
		$this->string_prices = $prices;
		$this->country = $country;
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
			
			switch($this->country){
				case 'F':
				case 'B':
				case 'L':
				case 'G':
				case 'I':
				case 'Y':
				case 'P':
				case 'SF':
				case 'H':
				case 'U':
					// header delivery
					$this->SetFont(helvetica, 'B', 11);
					$this->SetX($header_x);
					$this->MultiCell($cw, $cell_height, $headerdata['delivery'], 0, 'L', '', 0, $header_x, '');
					// header string
					$this->SetFont('helvetica', 'B', 9);
					$this->SetTextColor(23, 100, 71, 12);
					$this->SetX($header_x);
					$this->MultiCell($cw, $cell_height, $headerdata['prices'], 0, 'R', 0, 1, '', '', true, 0, false, true, 0, 'B', false);
					break;
					
				case 'D':
				case 'O':
				case 'SA':
				
					// header delivery
					$this->SetFont(helvetica, 'B', 10);
					$this->SetX($header_x);
					$this->MultiCell($cw, $cell_height, $headerdata['delivery'], 0, 'L', '', 0, $header_x, '');
					// header string
					$this->SetFont('helvetica', 'B', 9);
					$this->SetTextColor(23, 100, 71, 12);
					$this->SetX($header_x);
					$this->MultiCell($cw, $cell_height, $headerdata['prices'], 0, 'R', 0, 1, '', '', true, 0, false, true, 0, 'B', false);
					break;
				case 'E':
					// header delivery
					$this->SetFont(helvetica, 'B', 10);
					$this->SetX($header_x);
					$this->MultiCell($cw, $cell_height, $headerdata['delivery'], 0, 'L', '', 0, $header_x, '');
					// header string
					$this->SetFont('helvetica', 'B', 8);
					$this->SetTextColor(23, 100, 71, 12);
					$this->SetX($header_x);
					$this->MultiCell($cw, $cell_height, $headerdata['prices'], 0, 'R', 0, 1, '', '', true, 0, false, true, 0, 'B', false);
					break;
				
			}
			// print an ending header line
			$this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $headerdata['line_color']));
			$this->SetY((2.835 / $this->k) + max($imgy, $this->y));
			if ($this->rtl) {
				$this->SetX($this->original_rMargin);
			} else {
				$this->SetX($this->original_lMargin);
			}
			$this->Cell(($this->w - $this->original_lMargin - $this->original_rMargin), 0, '', 'T', 1, 'C');
			$this->SetX($header_x);
			$this->getEntetes();
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
		$font_size=10;
		$cell_height = round(($this->cell_height_ratio * $font_size) / $this->k, 2);
		$cw = $this->w - $this->original_lMargin - $this->original_rMargin;
		
		$cur_y = $this->y-8;
		$this->SetTextColorArray($this->footer_text_color);
		
		$this->SetY($cur_y);
		$header_x = $this->original_lMargin;
		//Print page number
		if($this->country == 'L' or $this->country == 'H'  or $this->country == 'Y'){ //phrases trop longues
			$this->SetFont('helvetica', 'B', 7);
		}else{
			$this->SetFont('helvetica', 'B', 8);
		}
		$this->SetX($header_x);
		$width_indic=$this->GetStringWidth($this->indicprix, '', 'B', 8, false);
		$this->MultiCell($width_indic +2, $cell_height, $this->indicprix, 0, 'L', 0, 0, '', '', true, 0, false, true, $cell_height, 'B', false);
		if($this->country == 'L' or $this->country == 'H' or $this->country == 'Y'){ //phrases trop longues
			$this->SetFont('helvetica', '', 7);
		}else{
			$this->SetFont('helvetica', 'B', 7);
		}
		//$this->SetX($header_x);
		$this->Image(REPAPPLI.'templates/new_template/pdf_primeur/images/12.png',$this->GetX(), $this->GetY(), '3.5', '3.5', '', '', 'T', true, 150, '', false, false, 0, 'CB', false, false);
		$caisse12=$this->caisse12;
		$width_indic=$this->GetStringWidth($caisse12, '', 'B', 7, false);
		$this->MultiCell($width_indic + 3, $cell_height, $caisse12, 0, 'L', 0, 0, '', '', true, 0, false, true, $cell_height, 'B', false);
		$this->Image(REPAPPLI.'templates/new_template/pdf_primeur/images/6.png',$this->GetX(), $this->GetY(), '3.5', '3.5', '', '', 'T', true, 150, '', false, false, 0, 'CB', false, false);
		$caisse6=$this->caisse6;
		$width_indic=$this->GetStringWidth($caisse6, '', 'B', 7, false);
		$this->MultiCell($width_indic + 2, $cell_height, $caisse6, 0, 'L', 0, 1, '', '', true, 0, false, true, $cell_height, 'B', false);
		
		$this->Ln(1);
		$coord="<center><strong>".utf8_encode($this->coordonnees)."</strong></center>";
		$coord2 ="<center>".$this->site_court." - <small>".$this->validite.' '.$this->revendeurs."</small></center>";
		$this->SetFont('helvetica', 'B', 10);
		$this->SetFillColor(0, 0, 0);
		$this->SetTextColor(255, 255, 255);
		$this->writeHTMLCell($this->getPageWidth()+$this->original_lMargin+$this->original_rMargin, 7, 0,$this->GetY(), $coord, 0, 1, 1, 1, 0, '');
		$this->writeHTMLCell($this->getPageWidth()+$this->original_lMargin+$this->original_rMargin, 7, 0,$this->GetY()-1, $coord2, 0, 1, 1, 1, 0, '');
		
	}
	
	/**
	 * Set Footer Data
	 * @param 
	 * @public
	 */
	public function setFooterData($coordonnees, $site_court, $validite, $indications = '', $caisse12='', $caisse6='', $revendeurs='', $tc=array(0,0,0), $lc=array(0,0,0)) {
		parent::setFooterData($tc, $lc);
		$this->coordonnees = $coordonnees;
		$this->site_court = $site_court;
		$this->validite = $validite;
		$this->revendeurs = $revendeurs;
		$this->indicprix = $indications;
		$this->caisse12 = $caisse12;
		$this->caisse6 = $caisse6;
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
		$this->SetTextColor(0, 0, 0);
		$this->setCellPaddings(0);
		$this->Cell(0, 6, $type, array('B' => array('width' => 0.4, 'color' => array(0, 0, 0))), 1, '', 1, false);
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
	public function MarqueTitre($marque) {
		$this->SetFont('helvetica', 'B', 11);
		$this->SetFillColor(255, 255, 255);
		$this->SetTextColor(0, 0, 0);
		$this->setCellPaddings(0);
		$this->Cell(0, 6, $marque, array('B' => array('width' => 0.2, 'color' => array(60, 51, 51, 20))), 1, '', 1, false);
		$this->Ln(1);
	}
	/**
	 * Set type appellation title
	 * @param $appellation (string) texte à afficher
	 * @public
	 */
	public function setEntetes($pdf_btleht, $pdf_caisht, $pdf_caisttc) {
		$this->ent_1 = $pdf_btleht;
		$this->ent_2 = $pdf_caisht;
		$this->ent_3 = $pdf_caisttc;
	}
	
	/**
	 * Set type appellation title
	 * @param $appellation (string) texte à afficher
	 * @public
	 */
	public function getEntetes() {
		switch($this->country){
			case 'F':
			case 'B':
			case 'L':
			case 'G':
			case 'I':
			case 'Y':
			case 'E':
			case 'P':
			case 'D':
			case 'O':
				$this->SetFont('helvetica', '', 8);
				$this->SetTextColor(0, 0, 0);
				
				$prixhtblle = $this->ent_1;
				//$prixhtblle = "blabla";
				$this->writeHTMLCell(16,1,$this->GetX()+155, $this->GetY(), $prixhtblle, 0,0,false,true,'R', true);
				
				$this->SetFont('helvetica', 'B', 8);
				$prix_ht = $this->ent_2;
				//$prixhtblle = "blabla";
				$this->writeHTMLCell(16,1, $this->GetX(), $this->GetY(), $prix_ht, 0,0,false,true,'R', true);
				
				$this->SetFont('helvetica', '', 8);
				$prix_ttc = $this->ent_3;
				//$prixhtblle = "blabla";
				$this->writeHTMLCell(16,1, $this->GetX(), $this->GetY(), $prix_ttc, 0,1,false,true,'R', true);
				break;
				
			case 'SF':
			case 'SA':
			case 'H':
			case 'U':
				$this->SetFont('helvetica', '', 8);
				$this->SetTextColor(0, 0, 0);
				
				$ent1 = $this->ent_1;
				$this->writeHTMLCell(16,1,$this->GetX()+155, $this->GetY(), $ent1, 0,0,false,true,'R', true);
				
				$ent2 = $this->ent_2;
				$this->writeHTMLCell(16,1, $this->GetX(), $this->GetY(), $ent2, 0,0,false,true,'R', true);
				
				$this->SetFont('helvetica', 'B', 8);
				$ent3 = $this->ent_3;
				$this->writeHTMLCell(16,1, $this->GetX(), $this->GetY(), $ent3, 0,1,false,true,'R', true);
				break;
			
			
		
		}
	}
	
	/**
	 * Print chapter body
	 * @param $file (string) name of the file containing the chapter body
	 * @param $mode (boolean) if true the chapter body is in HTML, otherwise in simple text.
	 * @public
	 */
	public function getPrix($produit, $region='bordeaux') {
		$this->SetFont('helvetica', '', 8);
		$this->SetTextColor(0, 0, 0);
		switch($produit->pays){
			case 'F':
			case 'B':
			case 'L':
			case 'G':
			case 'I':
			case 'Y':
			case 'E':
			case 'P':
				$prixhtblle = utf8_encode($produit->prixhtblle);
				$this->writeHTMLCell(16,$this->lastCell, $this->GetX(), $this->GetY(), $prixhtblle, 0,0,false,false,'R', true);
				
				$this->SetFont('helvetica', 'B', 8);
				$prix_ht = utf8_encode($produit->prix_ht);
				$this->writeHTMLCell(16,$this->lastCell, $this->GetX(), $this->GetY(), $prix_ht, 0,0,false,false,'R', true);
				
				$this->SetFont('helvetica', '', 8);
				$prix_ttc = utf8_encode($produit->prix_ttc);
				$this->writeHTMLCell(16,$this->lastCell, $this->GetX(), $this->GetY(), $prix_ttc, 0,4,false,false,'R', true);
				break;
			case 'D':
			case 'O':
				
				$prixttcblle = utf8_encode($produit->prixttcblle);
				$this->writeHTMLCell(16,$this->lastCell, $this->GetX(), $this->GetY(), $prixttcblle, 0,0,false,false,'R', true);
				
				$this->SetFont('helvetica', 'B', 8);
				$prix_ttc = utf8_encode($produit->prix_ttc);
				$this->writeHTMLCell(16,$this->lastCell, $this->GetX(), $this->GetY(), $prix_ttc, 0,0,false,false,'R', true);
				
				$this->SetFont('helvetica', '', 8);
				$prixlitrettc = "(".utf8_encode($produit->prixlitrettc).")";
				$this->writeHTMLCell(16,$this->lastCell, $this->GetX(), $this->GetY(), $prixlitrettc, 0,4,false,false,'R', true);
				break;
			case 'SF':
			case 'SA':
				$blank = '';
				$this->writeHTMLCell(16,$this->lastCell, $this->GetX(), $this->GetY(), $blank, 0,0,false,false,'R', true);
				
				$prixttcblle = utf8_encode($produit->prixttcblle);
				$this->writeHTMLCell(16,$this->lastCell, $this->GetX(), $this->GetY(), $prixttcblle, 0,0,false,false,'R', true);
				
				$this->SetFont('helvetica', 'B', 8);
				$prix_ttc = utf8_encode($produit->prix_ttc);
				$this->writeHTMLCell(16,$this->lastCell, $this->GetX(), $this->GetY(), $prix_ttc, 0,4,false,false,'R', true);
				break;
			case 'H':
			case 'U':
				$blank = '';
				$this->writeHTMLCell(16,$this->lastCell, $this->GetX(), $this->GetY(), $blank, 0,0,false,false,'R', true);
				
				$prixhtblle = utf8_encode($produit->prixhtblle);
				$this->writeHTMLCell(16,$this->lastCell, $this->GetX(), $this->GetY(), $prixhtblle, 0,0,false,false,'R', true);
				
				$this->SetFont('helvetica', 'B', 8);
				$prix_ht = utf8_encode($produit->prix_ht);
				$this->writeHTMLCell(16,$this->lastCell, $this->GetX(), $this->GetY(), $prix_ht, 0,4,false,false,'R', true);
				break;
		}
	}

	/**
	 * Print chapter body
	 * @param $file (string) name of the file containing the chapter body
	 * @param $mode (boolean) if true the chapter body is in HTML, otherwise in simple text.
	 * @public
	 */
	public function Produit($produit, $region='bordeaux') {
		$this->SetFont('helvetica', '', 8);
		$this->SetTextColor(0, 0, 0);
		/*
		$this->Write(8,utf8_encode($produit->libelle_internet));
		if($produit->classement != ''){
			$this->SetFont('helvetica', 'I', 8);
			$this->Write(8,' / '.utf8_encode($produit->classement));
		}
		*/
		switch($region){
			case 'bourgogne':
				$libelle_cru = str_replace('&lt;br/&gt;', ' ', utf8_encode($produit->cru));
				$name = '<strong>'.$libelle_cru.'</strong>';
				if($produit->classement != ''){
					$name .= ' / <em>'.utf8_encode($produit->classement).'</em>';
				}
				break;
			case 'vdr':
				$libelle_cru = str_replace('&lt;br/&gt;', ' ', utf8_encode($produit->cru));
				$name = '<strong>'.$libelle_cru.'</strong>';
				if($produit->classement != ''){
					$name .= ' / <em>'.utf8_encode($produit->classement).'</em>';
				}
				if($produit->appellation != $produit->cru){
					$name .= ' / <em>'.utf8_encode($produit->appellation).'</em>';
				}
				break;
			case 'bordeaux':
			default:
				$libelle_internet = str_replace('&lt;br/&gt;', ' ', utf8_encode($produit->libelle_internet));
				$name = '<strong>'.$libelle_internet.'</strong>';
				if($produit->classement != ''){
					$name .= ' / <em>'.utf8_encode($produit->classement).'</em>';
				}
				break;
		}
		
		$this->writeHTMLCell(96,4, $this->GetX(), $this->GetY(), $name, 0,0,false,true,'L', true);
		$this->lastCell=$this->getLastH();
		//$height = $this->getCellHeightRatio();
		if($produit->idcouleur == 5){
		$this->Image(REPAPPLI.'templates/new_template/pdf_primeur/images/blanc.png',$this->GetX(), $this->GetY(), '3.5', '3.5', '', '', 'T', true, 150, '', false, false, 0, 'CB', false, false);
		}else if($produit->idcouleur == 8){
			$this->Image(REPAPPLI.'templates/new_template/pdf_primeur/images/rouge.png',$this->GetX(), $this->GetY(), '3.5', '3.5', '', '', 'T', true, 150, '', false, false, 0, 'CB', false, false);
		}
		$espace = ' ';
		$width_indic=$this->GetStringWidth($espace, '', 'B', 8, false);
		$this->MultiCell($width_indic, $this->lastCell, $espace, 0, 'L', 0, 0, '', '', true, 0, false, true, $cell_height, 'B', false);
		// image quantité
		$this->Image(REPAPPLI.'templates/new_template/pdf_primeur/images/'.$produit->quantite.'.png',$this->GetX(), $this->GetY(), '3.5', '3.5', '', '', 'T', true, 150, '', false, false, 0, 'CB', false, false);
		$pointilles = ' ...............................................................';
		$this->writeHTMLCell(52,$this->lastCell, $this->GetX(), $this->GetY(), $pointilles, 0,0,false,false,'R', true);
		
		$this->getPrix($produit);
		
		//$this->Ln(4);
		
		
	}
	
	/**
	 * Print chapter body
	 * @param $file (string) name of the file containing the chapter body
	 * @param $mode (boolean) if true the chapter body is in HTML, otherwise in simple text.
	 * @public
	 */
	public function InsertListe($liste_produits, $region='bordeaux') {
		switch($region){
			case 'bordeaux':
				$sauternes=false;
				$currentAppellation = '';
				foreach($liste_produits as $produit){
					
					if($currentAppellation != $produit->appellation){
						if($produit->appellation == 'Sauternes' || $produit->appellation == 'Barsac'){
							if(!$sauternes){
								$this->Ln(2);
								$this->AppellationTitre("Sauternes & Barsac");
								$sauternes = true;
							}
						}else{
							$sauternes=false;
							$this->Ln(2);
							$this->AppellationTitre(utf8_encode($produit->appellation));
							$currentAppellation = $produit->appellation;
						}
						
					}
					$this->Produit($produit);
					
				}
				$this->Ln(2);
				break;
			case 'bourgogne':
			case 'vdr':
				$currentMarque = '';
				foreach($liste_produits as $produit){
					
					if($currentMarque != $produit->marque){
						$this->Ln(2);
						$this->MarqueTitre(utf8_encode(__decode($produit->marque)));
						$currentMarque = $produit->marque;
						
					}
					$this->Produit($produit, $region);
					
				}
				$this->Ln(2);
				break;
			
		}
	}
} // end of extended class

