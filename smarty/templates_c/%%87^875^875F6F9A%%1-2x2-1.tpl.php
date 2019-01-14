<?php /* Smarty version 2.6.22, created on 2015-04-16 09:39:08
         compiled from new_template/block_image/templates_images/1-2x2-1.tpl */ ?>
<table width="650" border="0" cellspacing="0" cellpadding="0" align="center">
<?php $_from = $this->_tpl_vars['bandeauxArray']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['bd'] => $this->_tpl_vars['value']):
?><?php if ($this->_tpl_vars['bandeauxArray'][$this->_tpl_vars['bd']]['bdnb'] == 1): ?> <tr>
<td colspan="2"><?php if ($this->_tpl_vars['bandeauxArray'][$this->_tpl_vars['bd']]['url'] != ''): ?><a href="<?php echo $this->_tpl_vars['bandeauxArray'][$this->_tpl_vars['bd']]['url']; ?>
"><?php endif; ?><img src="http://cdn.millesima.com.s3.amazonaws.com/<?php echo $this->_tpl_vars['type_message']; ?>
/<?php echo $this->_tpl_vars['codemessagegeneral']; ?>
/bandeau<?php if ($this->_tpl_vars['bandeauxArray'][$this->_tpl_vars['bd']]['exception']): ?><?php echo $this->_tpl_vars['country']; ?>
<?php else: ?><?php echo $this->_tpl_vars['langue']; ?>
<?php endif; ?>_<?php echo $this->_tpl_vars['bandeauxArray'][$this->_tpl_vars['bd']]['bdnb']; ?>
.<?php echo $this->_tpl_vars['bandeauxArray'][$this->_tpl_vars['bd']]['extension']; ?>
" border="0" width="650" height="<?php echo $this->_tpl_vars['bandeauxArray'][$this->_tpl_vars['bd']]['height']; ?>
" alt="<?php echo $this->_tpl_vars['objet_alt_title']; ?>
" title="<?php echo $this->_tpl_vars['objet_alt_title']; ?>
" style="display:block;" /><?php if ($this->_tpl_vars['bandeauxArray'][$this->_tpl_vars['bd']]['url'] != ''): ?></a><?php endif; ?></td></tr><?php else: ?><?php if (!(1 & $this->_tpl_vars['bandeauxArray'][$this->_tpl_vars['bd']]['bdnb'])): ?><tr><?php endif; ?><td><?php if ($this->_tpl_vars['bandeauxArray'][$this->_tpl_vars['bd']]['url'] != ''): ?><a href="<?php echo $this->_tpl_vars['bandeauxArray'][$this->_tpl_vars['bd']]['url']; ?>
"><?php endif; ?><img src="http://cdn.millesima.com.s3.amazonaws.com/<?php echo $this->_tpl_vars['type_message']; ?>
/<?php echo $this->_tpl_vars['codemessagegeneral']; ?>
/bandeau<?php if ($this->_tpl_vars['bandeauxArray'][$this->_tpl_vars['bd']]['exception']): ?><?php echo $this->_tpl_vars['country']; ?>
<?php else: ?><?php echo $this->_tpl_vars['langue']; ?>
<?php endif; ?>_<?php echo $this->_tpl_vars['bandeauxArray'][$this->_tpl_vars['bd']]['bdnb']; ?>
.<?php echo $this->_tpl_vars['bandeauxArray'][$this->_tpl_vars['bd']]['extension']; ?>
" border="0" width="325" height="<?php echo $this->_tpl_vars['bandeauxArray'][$this->_tpl_vars['bd']]['height']; ?>
" alt="<?php echo $this->_tpl_vars['objet_alt_title']; ?>
" title="<?php echo $this->_tpl_vars['objet_alt_title']; ?>
" style="display:block;" /><?php if ($this->_tpl_vars['bandeauxArray'][$this->_tpl_vars['bd']]['url'] != ''): ?></a><?php endif; ?></td><?php if (!(!(1 & $this->_tpl_vars['bandeauxArray'][$this->_tpl_vars['bd']]['bdnb']))): ?></tr><?php endif; ?><?php endif; ?><?php endforeach; endif; unset($_from); ?>
</table>