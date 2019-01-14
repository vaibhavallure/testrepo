<table width="650" border="0" cellspacing="0" cellpadding="0" align="center" class="main">
                          <tbody>
                            <tr>
                              <td width="20" bgcolor="{$codecouleur}" ></td>
                              <td valign="top" bgcolor="{$codecouleur}" style="color:{$couleurtxtbtn}"><table width="100%" border="0" cellspacing="0" cellpadding="0">
                                  <tbody>
                                    <tr>
                                      <td height="15" bgcolor="{$codecouleur}"></td>
                                    </tr>
                                    <tr>
                                      <td style="font-size:14px; text-align:center; color:{$couleurtxtbtn}; font-family:Arial, Helvetica, sans-serif, Trebuchet MS;{$livraison.style}">{if ($dateenvoi|replace:'/':'.'|date_format:"%Y%m%d") >= ($datelivraison|replace:'/':'.'|date_format:"%Y%m%d")}{$livraison2.phrase}{else}{$livraison.phrase}{/if}</td>
                                    </tr>
                                    <tr>
                                      <td height="15"></td>
                                    </tr>
                                  </tbody>
                                </table></td>
                              <td width="20" bgcolor="{$codecouleur}" ></td>
                            </tr>
                          </tbody>
                        </table>