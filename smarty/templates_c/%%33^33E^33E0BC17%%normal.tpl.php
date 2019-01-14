<?php /* Smarty version 2.6.22, created on 2015-04-15 12:59:22
         compiled from new_template/normal.tpl */ ?>
<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $this->_tpl_vars['objet_alt_title']; ?>
</title>
<meta name="viewport" content="width=670"><?php echo '
<style type="text/css">
body{margin: 10 0 0 0;background-color:#fff;font-family:Arial, Helvetica, sans-serif;}
img{border:none;display:block;}
</style>
'; ?>
</head>
<body>
<table width="650" border='0' cellspacing='0' cellpadding='0' align="center">
<tr><td>&nbsp;</td></tr>
<tr>
  <td>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['tpl'])."/entete/templates_entetes/sans_smartphone/et".($this->_tpl_vars['langue']).".tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td><?php if ($this->_tpl_vars['version_noire']): ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['tpl'])."/bandeaux/bandeau_noir.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php else: ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['tpl'])."/bandeaux/bandeau.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php endif; ?>
</td></tr>
<tr>
  <td><?php if ($this->_tpl_vars['sans_primeurs']): ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['tpl'])."/menus/sans_primeurs/menu".($this->_tpl_vars['lettremenu']).".tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php elseif ($this->_tpl_vars['version_noire']): ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['tpl'])."/menus/version_noire/menu".($this->_tpl_vars['lettremenu']).".tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php else: ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['tpl'])."/menus/menu".($this->_tpl_vars['lettremenu']).".tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php endif; ?>
</td></tr>
<tr><td>&nbsp;</td></tr>
<?php if ($this->_tpl_vars['bdunq']): ?><tr><td>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['tpl'])."/block_image/templates_images/bandeau_unique.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</td></tr>
<tr><td>&nbsp;</td></tr><?php endif; ?>
<?php if ($this->_tpl_vars['bdtrch']): ?><tr><td>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['tpl'])."/block_image/templates_images/bandeaux_horizontaux.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</td></tr>
<tr><td>&nbsp;</td></tr>
<?php endif; ?>
<?php if ($this->_tpl_vars['bd12x21']): ?><tr><td>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['tpl'])."/block_image/templates_images/1-2x2-1.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</td></tr>
<tr><td>&nbsp;</td></tr>
<?php endif; ?>
<?php if ($this->_tpl_vars['bdbas']): ?><tr><td>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['tpl'])."/block_image/templates_images/bandeau_bas.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</td></tr>
<tr><td>&nbsp;</td></tr><?php endif; ?>
<?php if ($this->_tpl_vars['listing']): ?><tr>
  <td>
<?php if ($this->_tpl_vars['type_listing'] == 'staffpicks'): ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['tpl'])."/listing_produits/staffpicks/listing.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php else: ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['tpl'])."/listing_produits/listing.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php endif; ?>
</td></tr>
<tr><td>&nbsp;</td></tr><?php endif; ?>
<tr><td>
<table width='650' border='0' cellspacing='0' cellpadding='0' align="center"><tr><td style="background-color:#202125;color:#FFFFFF;" valign="middle"><?php if ($this->_tpl_vars['typepush'] == 'caispan'): ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['tpl'])."/push/templates_push/push_max.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php else: ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['tpl'])."/push/templates_push/push.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php endif; ?></td><td><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['tpl'])."/contact/contact".($this->_tpl_vars['country']).".tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></td></tr></table>
</td></tr>
<tr>
  <td>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['tpl'])."/social/templates_social/all.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</td></tr>
<tr><td>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['tpl'])."/cgv/".($this->_tpl_vars['typecgv'])."/cgv_".($this->_tpl_vars['typecgv']).($this->_tpl_vars['country']).".tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td style="border-top:1px dashed black;">&nbsp;</td></tr>
<tr><td>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['tpl'])."/pied_page/pp".($this->_tpl_vars['country']).".tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</td></tr>
<tr><td style="border-bottom:1px dashed black;">&nbsp;</td></tr>
<tr><td>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['tpl'])."/desabo/desabo".($this->_tpl_vars['langue']).".tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</td></tr>
</table>
</body>
</html>