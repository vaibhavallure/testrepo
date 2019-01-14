<?php /* Smarty version 2.6.22, created on 2015-04-15 12:59:22
         compiled from new_template/listing_produits/prix.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'replace', 'new_template/listing_produits/prix.tpl', 18, false),array('modifier', 'lower', 'new_template/listing_produits/prix.tpl', 68, false),)), $this); ?>
<?php if (isset ( $this->_tpl_vars['promo'] ) && $this->_tpl_vars['promo'] != ''): ?>
<tr><td align="center" valign="top" height="75">
<?php if ($this->_tpl_vars['produit']->pays == 'U'): ?>
<span style="font-size:16px;font-weight:bold"><strong><?php echo $this->_tpl_vars['produit']->devise; ?>
&nbsp;<?php echo $this->_tpl_vars['produit']->prix_ht; ?>
</strong></span><br />
<span style="color:#654337;font-size:10px;"><?php echo $this->_tpl_vars['produit']->boiscarton; ?>
 <?php echo $this->_tpl_vars['produit']->quantite; ?>
 <?php echo $this->_tpl_vars['produit']->conditionnement; ?>
</span><br />
<span style="color:#654337;font-size:10px;font-weight:bold;"><?php echo $this->_tpl_vars['produit']->devise; ?>
<?php echo $this->_tpl_vars['produit']->prixhtblle; ?>
<?php echo $this->_tpl_vars['fnpx1btlleht']; ?>
</span>
<?php else: ?>
<span style="text-decoration:line-through;font-size:13px;"><?php if ($this->_tpl_vars['produit']->pays == 'G' || $this->_tpl_vars['produit']->pays == 'I'): ?><?php echo $this->_tpl_vars['produit']->devise; ?>
&nbsp;<?php echo $this->_tpl_vars['produit']->prix_ttc; ?>
<?php else: ?><?php echo $this->_tpl_vars['produit']->prix_ttc; ?>
&nbsp;<?php echo $this->_tpl_vars['produit']->devise; ?>
<?php endif; ?></span>&nbsp;&nbsp;&nbsp;
<span style="font-size:16px;font-weight:bold"><strong><?php if ($this->_tpl_vars['produit']->pays == 'G' || $this->_tpl_vars['produit']->pays == 'I'): ?><?php echo $this->_tpl_vars['produit']->devise; ?>
&nbsp;<?php echo $this->_tpl_vars['produit']->prix_promo; ?>
<?php else: ?><?php echo $this->_tpl_vars['produit']->prix_promo; ?>
&nbsp;<?php echo $this->_tpl_vars['produit']->devise; ?>
<?php endif; ?></strong></span>&nbsp;<?php if ($this->_tpl_vars['produit']->pays != 'H'): ?><span style="font-size:10px;font-weight:bold;"><?php echo $this->_tpl_vars['ttc']; ?>
</span><?php endif; ?><br />
<?php if ($this->_tpl_vars['produit']->pays == 'D' || $this->_tpl_vars['produit']->pays == 'O'): ?><span style="color:#000000; font-size:11px;">(<?php echo $this->_tpl_vars['produit']->prixlitrettc; ?>
<?php echo $this->_tpl_vars['produit']->devise; ?>
/L)</span><br /><?php endif; ?>
<span style="color:#654337;font-size:10px;"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['produit']->boiscarton)) ? $this->_run_mod_handler('replace', true, $_tmp, 'Une', 'La') : smarty_modifier_replace($_tmp, 'Une', 'La')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'Un', 'Le') : smarty_modifier_replace($_tmp, 'Un', 'Le')); ?>
 <?php echo $this->_tpl_vars['produit']->quantite; ?>
 <?php echo $this->_tpl_vars['produit']->conditionnement; ?>
</span><br />
<?php if (isset ( $this->_tpl_vars['promos'][$this->_tpl_vars['promo']]['nbcaisses'] ) && $this->_tpl_vars['promos'][$this->_tpl_vars['promo']]['nbcaisses'] != ''): ?><span style="color:#654337;font-size:10px;"><?php echo $this->_tpl_vars['promos'][$this->_tpl_vars['promo']]['nbcaisses']; ?>
 <?php echo $this->_tpl_vars['produit']->Packaging; ?>
</span><?php endif; ?>
<?php endif; ?>
</td></tr>
<tr><td height="7" style="font-size:5px;">&nbsp;</td></tr>
<?php elseif ($this->_tpl_vars['type'] == 'livrable'): ?>
<tr><td align="center" valign="top" height="75">
<?php if ($this->_tpl_vars['produit']->pays == 'U'): ?>
<span style="font-size:16px;font-weight:bold"><strong><?php echo $this->_tpl_vars['produit']->devise; ?>
&nbsp;<?php echo $this->_tpl_vars['produit']->prix_ht; ?>
</strong></span><br />
<span style="color:#654337;font-size:10px;"><?php echo $this->_tpl_vars['produit']->boiscarton; ?>
 <?php echo $this->_tpl_vars['produit']->quantite; ?>
 <?php echo $this->_tpl_vars['produit']->conditionnement; ?>
</span><br />
<span style="color:#654337;font-size:10px;font-weight:bold;"><?php echo $this->_tpl_vars['produit']->devise; ?>
<?php echo $this->_tpl_vars['produit']->prixhtblle; ?>
<?php echo $this->_tpl_vars['fnpx1btlleht']; ?>
</span>
<?php else: ?>
<span style="font-size:16px;font-weight:bold"><?php if ($this->_tpl_vars['produit']->pays == 'G' || $this->_tpl_vars['produit']->pays == 'I'): ?><?php echo $this->_tpl_vars['produit']->devise; ?>
&nbsp;<?php echo $this->_tpl_vars['produit']->prix_ttc; ?>
<?php else: ?><?php echo $this->_tpl_vars['produit']->prix_ttc; ?>
&nbsp;<?php echo $this->_tpl_vars['produit']->devise; ?>
<?php endif; ?></span><?php if ($this->_tpl_vars['produit']->pays != 'H'): ?><span style="font-size:10px;font-weight:bold;"><?php echo $this->_tpl_vars['ttc']; ?>
</span><?php endif; ?>&nbsp;&nbsp;&nbsp;<br />
<?php if ($this->_tpl_vars['produit']->pays == 'D' || $this->_tpl_vars['produit']->pays == 'O'): ?><span style="color:#000000; font-size:11px;">(<?php echo $this->_tpl_vars['produit']->prixlitrettc; ?>
<?php echo $this->_tpl_vars['produit']->devise; ?>
/L)</span><br /><?php endif; ?>
<span style="color:#654337;font-size:10px;"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['produit']->boiscarton)) ? $this->_run_mod_handler('replace', true, $_tmp, 'Une', 'La') : smarty_modifier_replace($_tmp, 'Une', 'La')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'Un', 'Le') : smarty_modifier_replace($_tmp, 'Un', 'Le')); ?>
 <?php echo $this->_tpl_vars['produit']->quantite; ?>
 <?php echo $this->_tpl_vars['produit']->conditionnement; ?>
</span><br /><br />
<?php endif; ?>
</td></tr>
<tr><td height="7" style="font-size:5px;">&nbsp;</td></tr>
<?php elseif ($this->_tpl_vars['type'] == 'primeurs' || $this->_tpl_vars['isprimeur']): ?>
<tr><td align="center" valign="top" height="75">
<?php if ($this->_tpl_vars['produit']->pays == 'D' || $this->_tpl_vars['produit']->pays == 'O' || $this->_tpl_vars['produit']->pays == 'SA' || $this->_tpl_vars['produit']->pays == 'SF'): ?>
<span style="font-size:16px;font-weight:bold"><strong><?php echo $this->_tpl_vars['produit']->prix_ttc; ?>
&nbsp;<?php echo $this->_tpl_vars['produit']->devise; ?>
</strong></span>&nbsp;<span style="font-size:10px;font-weight:bold;"><?php echo $this->_tpl_vars['ttc']; ?>
</span><br />
<?php if ($this->_tpl_vars['produit']->pays == 'D' || $this->_tpl_vars['produit']->pays == 'O'): ?><span style="font-size:10px;font-weight:bold;">(<?php echo $this->_tpl_vars['produit']->prixlitrettc; ?>
&nbsp;<?php echo $this->_tpl_vars['produit']->devise; ?>
/L)</span><br /><?php endif; ?>
<span style="color:#654337;font-size:10px;"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['produit']->boiscarton)) ? $this->_run_mod_handler('replace', true, $_tmp, 'kiste', 'Kiste') : smarty_modifier_replace($_tmp, 'kiste', 'Kiste')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'karton', 'Karton') : smarty_modifier_replace($_tmp, 'karton', 'Karton')); ?>
 <?php echo $this->_tpl_vars['produit']->quantite; ?>
 <?php echo $this->_tpl_vars['produit']->conditionnement; ?>
</span><br />
<span style="color:#654337;font-size:10px;font-weight:bold;"><?php echo $this->_tpl_vars['produit']->prixttcblle; ?>
&nbsp;<?php echo $this->_tpl_vars['produit']->devise; ?>
 <?php echo $this->_tpl_vars['fnpx1btllettc']; ?>
</span><br />

<?php elseif ($this->_tpl_vars['produit']->pays == 'H'): ?>
<span style="font-size:16px;font-weight:bold"><strong><?php echo $this->_tpl_vars['produit']->devise; ?>
&nbsp;<?php echo $this->_tpl_vars['produit']->prix_ht; ?>
</strong></span><br />
<span style="color:#654337;font-size:10px;"><?php echo $this->_tpl_vars['produit']->boiscarton; ?>
 <?php echo $this->_tpl_vars['produit']->quantite; ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['produit']->conditionnement)) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)); ?>
</span><br />
<span style="color:#654337;font-size:10px;font-weight:bold;"><?php echo $this->_tpl_vars['produit']->devise; ?>
&nbsp;<?php echo $this->_tpl_vars['produit']->prixhtblle; ?>
 <?php echo $this->_tpl_vars['fnpx1btlleht']; ?>
</span>
<?php elseif ($this->_tpl_vars['produit']->pays == 'U'): ?>
<span style="font-size:16px;font-weight:bold"><strong><?php echo $this->_tpl_vars['produit']->devise; ?>
&nbsp;<?php echo $this->_tpl_vars['produit']->prix_ht; ?>
</strong></span><br />
<span style="color:#654337;font-size:10px;"><?php echo $this->_tpl_vars['produit']->boiscarton; ?>
 <?php echo $this->_tpl_vars['produit']->quantite; ?>
 <?php echo $this->_tpl_vars['produit']->conditionnement; ?>
</span><br />
<span style="color:#654337;font-size:10px;font-weight:bold;"><?php echo $this->_tpl_vars['produit']->devise; ?>
<?php echo $this->_tpl_vars['produit']->prixhtblle; ?>
<?php echo $this->_tpl_vars['fnpx1btlleht']; ?>
</span>
<?php else: ?>
<span style="font-size:16px;font-weight:bold"><strong><?php if ($this->_tpl_vars['produit']->pays == 'G' || $this->_tpl_vars['produit']->pays == 'I'): ?><?php echo $this->_tpl_vars['produit']->devise; ?>
&nbsp;<?php echo $this->_tpl_vars['produit']->prix_ht; ?>
<?php else: ?><?php echo $this->_tpl_vars['produit']->prix_ht; ?>
&nbsp;<?php echo $this->_tpl_vars['produit']->devise; ?>
<?php endif; ?></strong></span>&nbsp;<span style="font-size:10px;font-weight:bold;"><?php echo $this->_tpl_vars['ht']; ?>
</span><br />
<span style="color:#654337;font-size:10px;"><?php if ($this->_tpl_vars['produit']->pays == 'F'): ?><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['produit']->boiscarton)) ? $this->_run_mod_handler('replace', true, $_tmp, 'Une', 'La') : smarty_modifier_replace($_tmp, 'Une', 'La')))) ? $this->_run_mod_handler('replace', true, $_tmp, 'Un', 'Le') : smarty_modifier_replace($_tmp, 'Un', 'Le')); ?>
<?php else: ?><?php echo $this->_tpl_vars['produit']->boiscarton; ?>
<?php endif; ?> <?php echo $this->_tpl_vars['produit']->quantite; ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['produit']->conditionnement)) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)); ?>
</span><br />
<span style="color:#654337;font-size:10px;font-weight:bold;"><?php if ($this->_tpl_vars['produit']->pays == 'G' || $this->_tpl_vars['produit']->pays == 'I'): ?><?php echo $this->_tpl_vars['produit']->devise; ?>
<?php echo $this->_tpl_vars['produit']->prixhtblle; ?>
<?php else: ?><?php echo $this->_tpl_vars['produit']->prixhtblle; ?>
<?php echo $this->_tpl_vars['produit']->devise; ?>
<?php endif; ?><?php echo $this->_tpl_vars['fnpx1btlleht']; ?>
 - <?php if ($this->_tpl_vars['produit']->pays == 'G' || $this->_tpl_vars['produit']->pays == 'I'): ?><?php echo $this->_tpl_vars['produit']->devise; ?>
<?php echo $this->_tpl_vars['produit']->prix_ttc; ?>
<?php else: ?><?php echo $this->_tpl_vars['produit']->prix_ttc; ?>
<?php echo $this->_tpl_vars['produit']->devise; ?>
<?php endif; ?><?php echo $this->_tpl_vars['fnpxcaissettc']; ?>
</span><br />

<?php endif; ?>
</td></tr>
<tr><td height="7" style="font-size:5px;">&nbsp;</td></tr>
<?php endif; ?>