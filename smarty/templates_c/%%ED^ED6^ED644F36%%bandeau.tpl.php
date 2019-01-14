<?php /* Smarty version 2.6.22, created on 2015-04-15 12:59:22
         compiled from new_template/bandeaux/bandeau.tpl */ ?>
<?php if ($this->_tpl_vars['country'] == 'U'): ?>
<a href="<?php echo $this->_tpl_vars['siteweb']; ?>
?<?php echo $this->_tpl_vars['tracking']; ?>
"><img src='http://cdn.millesima.com.s3.amazonaws.com/templates/bandeauhtU_simple.png' width="650" height="90" border='0' alt="Mill&eacute;sima USA" title="Mill&eacute;sima USA" style="display:block;" /></a>
<?php elseif ($this->_tpl_vars['country'] == 'H'): ?>
<a href="<?php echo $this->_tpl_vars['siteweb']; ?>
?<?php echo $this->_tpl_vars['tracking']; ?>
"><img src='http://cdn.millesima.com.s3.amazonaws.com/templates/bandeauhtH_simple.png' width="650" height="90" border='0' alt="Mill&eacute;sima Bordeaux" title="Mill&eacute;sima Bordeaux" style="display:block;" /></a>
<?php else: ?>
<a href="<?php echo $this->_tpl_vars['siteweb']; ?>
?<?php echo $this->_tpl_vars['tracking']; ?>
"><img src='http://cdn.millesima.com.s3.amazonaws.com/templates/bandeauht_simple.png' width="650" height="90" border='0' alt="Mill&eacute;sima Bordeaux" title="Mill&eacute;sima Bordeaux" style="display:block;" /></a>
<?php endif; ?>