<?php /* Smarty version 2.6.22, created on 2015-04-15 12:59:22
         compiled from new_template/listing_produits/bordeaux.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'replace', 'new_template/listing_produits/bordeaux.tpl', 5, false),array('modifier', 'lower', 'new_template/listing_produits/bordeaux.tpl', 11, false),)), $this); ?>
<tr><td>
<table border='0' cellspacing='0' cellpadding='0' width="100%"><tr><td style="color:#654337;font-size:16px;font-weight:bold"><strong><?php echo $this->_tpl_vars['produit']->millesime; ?>
</strong></td><td align="right" style="color:#654337;font-size:14px;text-transform:uppercase;"><?php if ($this->_tpl_vars['type'] == 'primeurs' || $this->_tpl_vars['isprimeur']): ?>&nbsp;<?php elseif ($this->_tpl_vars['type'] == 'livrable'): ?><?php echo $this->_tpl_vars['produit']->region; ?>
<?php endif; ?></td></tr>
</table></td></tr>
<tr><td height="100" valign="top">
<span style="font-weight:bold;text-transform:uppercase;"><strong><?php echo ((is_array($_tmp=$this->_tpl_vars['produit']->libelle_internet)) ? $this->_run_mod_handler('replace', true, $_tmp, '&lt;br/&gt;', '<br />') : smarty_modifier_replace($_tmp, '&lt;br/&gt;', '<br />')); ?>
</strong></span><br />
<span style="text-transform:uppercase;"><?php echo $this->_tpl_vars['produit']->appellation; ?>
</span><br />
<?php if ($this->_tpl_vars['produit']->classement != ''): ?><span style="font-size:10px;"><?php echo $this->_tpl_vars['produit']->classement; ?>
</span><br /><?php endif; ?>
<span style="color:#654337;font-size:11px;font-weight:bold;"><?php if ($this->_tpl_vars['produit']->pays == 'G' || $this->_tpl_vars['produit']->pays == 'I' || $this->_tpl_vars['produit']->pays == 'H'): ?>
	<?php echo $this->_tpl_vars['produit']->couleur; ?>
&nbsp;<?php echo $this->_tpl_vars['produit']->typedevin; ?>

<?php elseif ($this->_tpl_vars['produit']->pays == 'D' || $this->_tpl_vars['produit']->pays == 'O' || $this->_tpl_vars['produit']->pays == 'SA'): ?>
	<?php echo $this->_tpl_vars['produit']->couleur; ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['produit']->typedevin)) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)); ?>

<?php elseif ($this->_tpl_vars['produit']->pays == 'U'): ?>
    <?php echo $this->_tpl_vars['produit']->couleur; ?>

<?php else: ?>
	<?php echo $this->_tpl_vars['produit']->typedevin; ?>
&nbsp;<?php echo $this->_tpl_vars['produit']->couleur; ?>

<?php endif; ?></span><?php if ($this->_tpl_vars['type'] == 'primeurs'): ?><span style="color:#654337;"> - <?php echo $this->_tpl_vars['primeur']; ?>
</span><?php endif; ?>
</td></tr>