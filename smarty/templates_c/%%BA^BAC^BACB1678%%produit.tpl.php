<?php /* Smarty version 2.6.22, created on 2015-04-15 12:59:22
         compiled from new_template/listing_produits/produit.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'lower', 'new_template/listing_produits/produit.tpl', 4, false),array('modifier', 'replace', 'new_template/listing_produits/produit.tpl', 4, false),)), $this); ?>
<?php if ($this->_tpl_vars['produit']->primeur): ?><?php $this->assign('type', 'primeurs'); ?><?php else: ?><?php $this->assign('type', 'livrable'); ?><?php endif; ?>
<?php if (isset ( $this->_tpl_vars['produit']->code_promo ) && $this->_tpl_vars['produit']->code_promo != ''): ?><?php $this->assign('promo', $this->_tpl_vars['produit']->code_promo); ?><?php endif; ?>
<table border='0' cellspacing='0' cellpadding='0' style="font-size:12px;" width="198">
<?php $this->assign('region', ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['produit']->region)) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)))) ? $this->_run_mod_handler('replace', true, $_tmp, 'burdeos', 'bordeaux') : smarty_modifier_replace($_tmp, 'burdeos', 'bordeaux')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'burgundy', 'bourgogne') : smarty_modifier_replace($_tmp, 'burgundy', 'bourgogne')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'burgund', 'bourgogne') : smarty_modifier_replace($_tmp, 'burgund', 'bourgogne')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'borgo&ntilde;a', 'bourgogne') : smarty_modifier_replace($_tmp, 'borgo&ntilde;a', 'bourgogne')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'borgogna', 'bourgogne') : smarty_modifier_replace($_tmp, 'borgogna', 'bourgogne')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'elsass', 'alsace') : smarty_modifier_replace($_tmp, 'elsass', 'alsace')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'alsazia', 'alsace') : smarty_modifier_replace($_tmp, 'alsazia', 'alsace')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'alsacia', 'alsace') : smarty_modifier_replace($_tmp, 'alsacia', 'alsace')))) ? $this->_run_mod_handler('replace', true, $_tmp, ' ', '') : smarty_modifier_replace($_tmp, ' ', '')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'vall&eacute;edurh&ocirc;ne', 'valleedurhone') : smarty_modifier_replace($_tmp, 'vall&eacute;edurh&ocirc;ne', 'valleedurhone')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'rh&ocirc;ne-tal', 'valleedurhone') : smarty_modifier_replace($_tmp, 'rh&ocirc;ne-tal', 'valleedurhone')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'vall&eacute;edurh&ocirc;ne', 'valleedurhone') : smarty_modifier_replace($_tmp, 'vall&eacute;edurh&ocirc;ne', 'valleedurhone')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'rhonevalley', 'valleedurhone') : smarty_modifier_replace($_tmp, 'rhonevalley', 'valleedurhone')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'valledelrodano', 'valleedurhone') : smarty_modifier_replace($_tmp, 'valledelrodano', 'valleedurhone')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'valledelr&oacute;dano', 'valleedurhone') : smarty_modifier_replace($_tmp, 'valledelr&oacute;dano', 'valleedurhone')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'basquecountry', 'bordeaux') : smarty_modifier_replace($_tmp, 'basquecountry', 'bordeaux')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'provence', 'bordeaux') : smarty_modifier_replace($_tmp, 'provence', 'bordeaux')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'toscane', 'bordeaux') : smarty_modifier_replace($_tmp, 'toscane', 'bordeaux')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'tuscany', 'bordeaux') : smarty_modifier_replace($_tmp, 'tuscany', 'bordeaux')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'emilia-romagna', 'bordeaux') : smarty_modifier_replace($_tmp, 'emilia-romagna', 'bordeaux')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'piedmont', 'bordeaux') : smarty_modifier_replace($_tmp, 'piedmont', 'bordeaux'))); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['tpl'])."/listing_produits/".($this->_tpl_vars['region']).".tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<tr><td>
<a href="<?php echo $this->_tpl_vars['siteweb']; ?>
<?php echo $this->_tpl_vars['produit']->url_produit; ?>
?<?php echo $this->_tpl_vars['tracking']; ?>
"><img src="http://cdn.millesima.com.s3.amazonaws.com/product/198-80/<?php echo $this->_tpl_vars['produit']->shortref; ?>
.jpg" border="0" width="198" height="80" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['produit']->libelle_internet)) ? $this->_run_mod_handler('replace', true, $_tmp, '&lt;br/&gt;', ' ') : smarty_modifier_replace($_tmp, '&lt;br/&gt;', ' ')); ?>
 <?php echo $this->_tpl_vars['produit']->millesime; ?>
"/></a>
</td></tr>
<tr><td height="7" style="font-size:5px;">&nbsp;</td></tr>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['tpl'])."/listing_produits/indication.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['tpl'])."/listing_produits/prix.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<tr><td align="center" valign="middle"><a href="<?php echo $this->_tpl_vars['siteweb']; ?>
<?php echo $this->_tpl_vars['produit']->url_produit; ?>
?<?php echo $this->_tpl_vars['tracking']; ?>
"><img src="http://cdn.millesima.com.s3.amazonaws.com/templates/listing/btn<?php echo $this->_tpl_vars['langue']; ?>
.png" border="0" width="158" height="28" alt="<?php echo $this->_tpl_vars['produit']->libelle_internet; ?>
 <?php echo $this->_tpl_vars['produit']->millesime; ?>
"/></a>
</td></tr>
<tr><td height="7" style="font-size:5px;">&nbsp;</td></tr></table>