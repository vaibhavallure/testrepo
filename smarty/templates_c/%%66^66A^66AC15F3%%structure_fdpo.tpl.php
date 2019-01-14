<?php /* Smarty version 2.6.22, created on 2018-12-11 09:45:10
         compiled from template_responsive/informations_importantes/structure_fdpo.tpl */ ?>
<table width="650" border="0" cellspacing="0" cellpadding="0" align="center" class="main">
                          <tbody>
                            <tr>
                              <td width="20" bgcolor="#202125" ></td>
                              <td valign="top" bgcolor="#202125" style="color:#FFFFFF"><table width="100%" border="0" cellspacing="0" cellpadding="0">
                                  <tbody>
                                    <tr>
                                      <td height="15" bgcolor="#202125"></td>
                                    </tr>
                                    <tr>
                                      <td style="font-size:32px; text-align:center; color:#FFFFFF; font-family:Arial, Helvetica, sans-serif, Trebuchet MS;text-transform:uppercase;<?php echo $this->_tpl_vars['fdpo']['style']; ?>
"><?php echo $this->_tpl_vars['fdpo']['phrase']; ?>
<?php if ($this->_tpl_vars['typecgv'] == 'livraison'): ?>*<?php endif; ?></td>
                                    </tr>
                                    <?php if ($this->_tpl_vars['fdpo']['ssphrase'] != ""): ?><tr>
                                      <td style="font-size:12px; text-align:center; color:#FFFFFF; font-family:Arial, Helvetica, sans-serif, Trebuchet MS;<?php echo $this->_tpl_vars['fdpo']['style']; ?>
"><?php echo $this->_tpl_vars['fdpo']['ssphrase']; ?>
</td>
                                    </tr><?php endif; ?>
                                    <tr>
                                      <td height="15" bgcolor="#202125"></td>
                                    </tr>
                                  </tbody>
                                </table></td>
                              <td width="20" bgcolor="#202125" ></td>
                            </tr>
                          </tbody>
                        </table>