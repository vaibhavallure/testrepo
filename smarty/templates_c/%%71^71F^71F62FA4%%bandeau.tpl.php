<?php /* Smarty version 2.6.22, created on 2018-12-10 16:17:05
         compiled from template_responsive/bandeaux/bandeau.tpl */ ?>
<?php if ($this->_tpl_vars['country'] == 'U'): ?>
<a href="<?php echo $this->_tpl_vars['siteweb']; ?>
?<?php echo $this->_tpl_vars['tracking']; ?>
"><img src='http://cdn.millesima.com.s3.amazonaws.com/templates/logoU.png' width="350" height="90" alt="Millesima Bringing fine wine to you" title="Millesima Bringing fine wine to you" border="0" class="logo" style="display:block;" /></a>
<?php elseif ($this->_tpl_vars['country'] == 'H' || $this->_tpl_vars['country'] == 'SG'): ?>
<a href="<?php echo $this->_tpl_vars['siteweb']; ?>
?<?php echo $this->_tpl_vars['tracking']; ?>
"><img src='http://cdn.millesima.com.s3.amazonaws.com/templates/logoH.png' width="350" height="90" alt="Mill&eacute;sima specialized in fine wine since 1983" title="Mill&eacute;sima specialized in fine wine since 1983" border="0" class="logo" style="display:block;" /></a>
<?php else: ?>
<a href="<?php echo $this->_tpl_vars['siteweb']; ?>
?<?php echo $this->_tpl_vars['tracking']; ?>
"><img src='http://cdn.millesima.com.s3.amazonaws.com/templates/logo.png' width="350" height="90" alt="Mill&eacute;sima Bordeaux" title="Mill&eacute;sima Bordeaux" border="0" class="logo" style="display:block;" /></a>
<?php endif; ?>