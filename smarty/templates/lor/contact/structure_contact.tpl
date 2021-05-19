<table width="100%"  height="{if $country == 'U'}160{else}210{/if}" border="0" cellspacing="0" cellpadding="0" bgcolor="#9a9999" class="contact">
                                                          
                                                            <tr>
                                                              {if $country == 'F' OR $country == 'B' OR $country == 'L'}<td height="10" bgcolor="#9a9999"></td>{elseif $country == 'P'}<td height="5" bgcolor="#9a9999"></td>{else}<td height="15" bgcolor="#9a9999"></td>{/if}
                                                            </tr>
                                                            {if $country != 'U'}<tr>
                                                              <td style="font-family:Arial, Helvetica, sans-serif, Trebuchet MS; font-size:20px; line-height:22px; color:#000; text-align: center;" bgcolor="#9a9999"><strong>{$tabcontacts.conseil}</strong></td>
                                                            </tr>
                                                            <tr>
                                                              <td style="font-family:Arial, Helvetica, sans-serif, Trebuchet MS; font-size:12px; line-height:18px; color:#000; text-align: center;" bgcolor="#9a9999">{$tabcontacts.noms}</td>
                                                            </tr>
                                                            <tr>
                                                              {if $country == 'P'}<td height="5" bgcolor="#9a9999"></td>{else}<td height="8" bgcolor="#9a9999"></td>{/if}
                                                            </tr>{/if}
                                                            <tr>
                                                              <td bgcolor="#9a9999"><table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#9a9999">
                                                                  
                                                                    <tr>
                                                                      <td width="75" bgcolor="#9a9999"><img style="display:block;" src="http://cdn.millesima.com.s3.amazonaws.com/test/new-emailing/img/icon-tel.jpg" width="75" height="60" alt="" border="0"></td>
                                                                      <td style="font-family:Arial, Helvetica, sans-serif, Trebuchet MS; color:#fff; text-align: left;" bgcolor="#9a9999">{foreach from=$tabcontacts.telephone item=numero name=numeros}{if $smarty.foreach.numeros.first}<a href="tel:{$numero.href}" style="font-size:{if $country == 'P'}20px{else}23px{/if};font-weight:bold;color:#FFFFFF !important;text-decoration:none;"><span class="tel" style="font-size:{if $country == 'P'}20px{else}23px{/if};font-weight:bold;color:#FFFFFF !important;">{$numero.label}</span></a>{else}<a href="tel:{$numero.href}" style="font-size:12px;color:#FFFFFF !important;text-decoration:none;"><span style="font-size:12px;color:#FFFFFF !important;">{$numero.label}</span></a>{/if}
                                                                         {if not $smarty.foreach.numeros.last}<br />{/if}{/foreach}<br /><span style="font-size:{if $country == 'P'}12px{else}14px{/if}; text-align:left;">{$tabcontacts.ouverture}</span></td>
                                                                    </tr>
                                                                  
                                                                </table></td>
                                                            </tr>
                                                            <tr>
                                                              <td height="0" class="view30" bgcolor="#9a9999"></td>
                                                            </tr>
                                                            <tr>
                                                              <td bgcolor="#9a9999"><table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#9a9999">
                                                                  
                                                                    <tr>
                                                                      <td width="75" bgcolor="#9a9999"><img style="display:block;" src="http://cdn.millesima.com.s3.amazonaws.com/test/new-emailing/img/icon-mail.jpg" width="75" height="60" alt="" border="0"></td>
                                                                      <td bgcolor="#9a9999" style="font-family:Arial, Helvetica, sans-serif, Trebuchet MS; font-size:14px; line-height:22px; color:#fff; text-align: left;">{foreach from=$tabcontacts.emails item=email name=emails}<a href="mailto:{$email}" style="color:#FFFFFF !important; "><span style="color:#FFFFFF">{$email}</span></a>{if not $smarty.foreach.emails.last}<br />{/if}{/foreach}</td>
                                                                    </tr>
                                                                  
                                                                </table></td>
                                                            </tr>
                                                            {if $country != 'U'}<tr>
                                                              {if $country == 'F' OR $country == 'B' OR $country == 'L'}<td height="10" bgcolor="#9a9999"></td>{elseif $country == 'P'}<td height="5" bgcolor="#9a9999"></td>{else}<td height="16" bgcolor="#9a9999"></td>{/if}
                                                            </tr>{/if}
                                                        </table>