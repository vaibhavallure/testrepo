<table width="100%" border="0" cellspacing="0" cellpadding="0">
                          <tbody>
                            <tr>
                              <td valign="top" style="border-left:1px solid #dddddd; border-right:1px solid #dddddd; border-bottom:1px solid #dddddd;"><table width="100%" border="0" cellspacing="0" cellpadding="0">

                                          <tbody>
                                    <tr>
                                      <td width="20"></td>
                                      <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
                                          <tbody>
                                            <tr>
                                              <td height="18"></td>
                                            </tr>
                                            <tr>
                                              <td style="font-family:Arial, Helvetica, sans-serif, Trebuchet MS; font-size:18px; line-height:26px; text-align:center;color:#202125;{if $desc.titreupper}text-transform:uppercase;{/if}"><strong>{if $desc.titre}{$desc.titre}{/if}</strong></td> 
                                            </tr>
											{if $desc.offexc}<tr>
                                              <td height="16"></td>
                                            </tr>{/if}
                                            <tr>
                                              <td style="font-family:Arial, Helvetica, sans-serif, Trebuchet MS; font-size:13px; line-height:18px; color:#313440;padding-top:4px;text-align:{$desc.textalign};" class="description">{$desc.text}{if $lstprmodesc}{if $desc.text != ""}<br /><br />{/if}{$lstpromotab.desc}{/if}</td>
                                            </tr>
											{if $iscodepromo}<tr>
                                              <td height="16"></td>
                                            </tr>
											<tr>
												<td align="center" class="code" style="font-family:Arial, Helvetica, sans-serif, Trebuchet MS; font-size:13px;color:#313440;text-transform:uppercase;">
												<strong>{$phrasecode}</strong></td>
											</tr>
											<tr>
                                              <td height="16"></td>
                                            </tr>
											<tr>
											  <td align="center" class="code">{include file="$tpl/codepromo/structure.tpl"}</td>
											</tr>{/if}
                                            <tr>
                                              <td height="16"></td>
                                            </tr>
											<tr>
											  <td align="center" class="button">{if $lstprmodesc}{include file="$tpl/boutons/structure_btn.tpl" infos=$lstpromotab}{else}{include file="$tpl/boutons/structure_btn.tpl" infos=$desc}{/if}</td>
											</tr>
                                            <tr>
                                              <td height="{if $desc.offexc || $lstprmodesc}16{else}35{/if}"></td>
                                            </tr>
                                            {if $typecgv == "livraison" && $fdpo_conditions && $country != "D" && $country != "O" && $country != "Y" && $country != "E" && $country != "SG" && $country != "H" && $country != "U"}<tr>
                                              <td style="font-size:11px;font-family:Arial, Helvetica, sans-serif, Trebuchet MS;color: #444444;font-style:italic;{$fdpo.styledetail}">{$fdpo.detail}</td>
                                            </tr>
                                            <tr>
                                              <td height="{if $desc.offexc}20{else}35{/if}"></td>
                                            </tr>{/if}
											{if $desc.offexc || $lstprmodesc}
                                            <tr>
                                              <td style="font-size:11px;font-family:Arial, Helvetica, sans-serif, Trebuchet MS;color: #444444;font-style:italic;">{if $lstprmodesc}{$lstpromotab.ast}{else}{$offexc.valid}{/if}</td>
                                            </tr>
                                            <tr>
                                              <td height="35"></td>
                                            </tr>{/if}
											{if $country != "U" && $country != "SG" && $country != "H" && $country != "Y" && $country != "E" && $country != "F" && $country != "B" && $country != "L" && $country != "SF" && $country != "SA" && $country != "G" && $country != "I" && $country != "P" && $country != "D" && $country != "O"}<tr>
                                              <td style="font-size:12px;font-family:Arial, Helvetica, sans-serif, Trebuchet MS;{$livraison.styledetail}">{$livraison.detail}</td>
                                            </tr>
                                            <tr>
                                              <td height="35"></td>
                                            </tr>{/if}
											{if $country != "U" && $country != "SG" && $country != "H" && $country != "Y" && $country != "E" && $country != "F" && $country != "B" && $country != "L" && $country != "SF" && $country != "SA" && $country != "G" && $country != "I" && $country != "P" && $country != "D" && $country != "O"}<tr>
                                              <td style="font-size:11px;font-family:Arial, Helvetica, sans-serif, Trebuchet MS;color:#313440;{$conditionvalidite.style}">{$conditionvalidite.phrase}</td>
                                            </tr>
                                            <tr>
                                              <td height="35"></td>
                                            </tr>{/if}
                                          </tbody>
                                        </table></td>
                                      <td width="20"></td>
                                    </tr>
                                  </tbody>
                                </table></td>
                            </tr>
                          </tbody>
                        </table>