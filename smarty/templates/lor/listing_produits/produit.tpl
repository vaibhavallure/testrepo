{if $produit->primeur}{assign var='type' value='primeurs'}{else}{assign var='type' value='livrable'}{/if}
{if isset($produit->code_promo) and $produit->code_promo != ''}{assign var='promo' value=$produit->code_promo}{/if}
<!-- COLUMN / f49fb9c9-9cca-49db-a337-d8fbb998760e -->
<table cellpadding="0" cellspacing="0" border="0" width="204" align="left" style="width:204px" class="t_w32p m_w100p" role="presentation">
    <tr>
        <td>
            <!-- WRAPPER / 81e54611-a4e0-4a64-a023-c62b424761d9 -->

            <table cellpadding="0" cellspacing="0" border="0" width="204" align="center" style="margin:auto;background-color:#f2f2f2;width:204px;"  class="t_w95p m_w100p">
                <tr>
                    <td style="">

                        <!-- text / 714ea1ee-ab47-4f8f-9943-86830907f57f -->
                        <table cellpadding="0" cellspacing="0" border="0" width="204" height="80" align="center" style="margin:auto;width:204px;height:80px;" class="t_w100p m_w100p" role="presentation">
                            <tr>
                                <td height="80" style="background-color:#ccc7bc;font-family:Arial,Helvetica,sans-serif;color:#000000;text-align:center;font-size:14px;vertical-align:middle;height: 80px;">
                                    <a href="{$siteweb}{$produit->url_produit}.html?{$tracking}" style="color:#000000;display:inline-block;-webkit-text-size-adjust:none;text-decoration:none">
                                    <span style="">{$produit->libelle_internet_html}&nbsp;{if ($type != 'primeurs' OR !$isprimeur) AND $produit->millesime != 0 } {$produit->millesime}{/if}</span>
                                    </a>
                                </td>
                            </tr>
                        </table>
                        <!-- EO - text - 714ea1ee-ab47-4f8f-9943-86830907f57f -->



                        <!-- image / e2e7ac1d-435f-4f6b-bf05-6d20a551260c -->
                        <table cellpadding="0" cellspacing="0" border="0" width="204" align="center" style="margin:auto;width:204px" class="t_w100p m_w100p" role="presentation">
                            <tr>
                                <td style="text-align:center">
                                     <span style="font-size:12px;font-family: Arial,Helvetica,sans-serif;"><br>{$produit->appellation}<br/>{$produit->classement}</span><br>
                                    <a href="{$siteweb}{$produit->url_produit}.html?{$tracking}">
                                    <img src="{$produit->url_image_thumb}" alt="{$produit->libelle_internet|replace:'&lt;br/&gt;':' '} {$produit->millesime}" style="font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#000000;line-height:13px;border:none" class="t_w100p t_hauto m_hauto" />
                                    </a>
                                </td>
                            </tr>
                        </table>
                        <!-- EO - image - e2e7ac1d-435f-4f6b-bf05-6d20a551260c -->

                        <!-- text / ad563a56-8adc-4257-9b3e-e75825b9dd3d -->
                        <table cellpadding="0" cellspacing="0" border="0" width="204" align="center" style="margin:auto;width:204px" class="t_w100p m_w100p" role="presentation">
                            <tr>
                                <td style="font-family:Arial,Helvetica,sans-serif;color:#000000;text-align:center;font-size:16px;vertical-align:middle;font-style:italic">
                                 <span style="font-size: 10px; color: #535512;">{if $produit->pays == 'G' OR $produit->pays == 'I' OR $produit->pays == 'H' OR $produit->pays == 'SG'}{$produit->LibelleCouleur}&nbsp;{$produit->typedevin}{elseif $produit->pays == 'D' OR $produit->pays == 'O' OR $produit->pays == 'SA'}{$produit->LibelleCouleur}{$produit->typedevin|lower}{elseif $produit->pays == 'U'}{$produit->LibelleCouleur}{else}{$produit->typedevin}&nbsp;{$produit->LibelleCouleur}{/if}
                                 </span>
                                </td>
                            </tr>
                        </table>
                        <!-- EO - text - ad563a56-8adc-4257-9b3e-e75825b9dd3d -->

                            <!-- text / 37d19f1e-e96f-4980-9fb9-4c030896961b -->
                            <table cellpadding="0" cellspacing="0" border="0" width="204" align="center" style="margin:auto;width:204px;height:100px;" class="t_w100p m_w100p" role="presentation">
{if $typecgv != 'primeurs' AND !$lstprmodesc AND (substr_count($codemessagegeneral, "uiospick") == 0)}{include file="$tpl/listing_produits/indication.tpl"}{/if}
                                    {if !$ssprix}{include file="$tpl/listing_produits/prix.tpl"}{/if}

                            </table>
                            <!-- EO - text - 37d19f1e-e96f-4980-9fb9-4c030896961b -->


                        <!-- cta / 86bdddea-44d2-4168-8366-b8b212c5a396 -->
                        <table cellpadding="0" cellspacing="0" border="0" width="163" align="center" style="margin:auto;width:163px" class="t_w80p m_w50p" role="presentation">
                            <tr>
                                <td style="padding-top:10px;padding-bottom:10px">
                                    <!--[if mso]>
                                    <v:roundrect
                                            xmlns:v="urn:schemas-microsoft-com:vml"
                                            xmlns:w="urn:schemas-microsoft-com:office:word"
                                            href="{$siteweb}{$produit->url_produit}.html?{$tracking}"
                                            style="v-text-anchor:middle;height:40px;width:163px"
                                            arcsize="8%"
                                            stroke="f"
                                            fillcolor="{$codecouleur}"
                                            fill="t">
                                        <w:anchorlock/>
                                        <center style="color:{$couleurtxtbtn};font-size:16px;font-family:Georgia,Times,Times New Roman,serif;font-weight:bold;height:40px;text-align:center;width:163px">
                                            {$tradbtns.savr.$country}
                                        </center>
                                    </v:roundrect>
                                    <![endif]-->
                                    <a href="{$siteweb}{$produit->url_produit}.html?{$tracking}" style="background-color:{$codecouleur};color:{$couleurtxtbtn};font-family:Arial,Helvetica,sans-serif;font-weight:bold;font-size:13px;height:40px;text-align:center;line-height:40px;mso-hide:all;display:inline-block;-webkit-text-size-adjust:none;text-decoration:none;width:163px" class="t_fwbold t_fsnormal t_w100p m_fwbold m_fsnormal m_w100p opensans">
                                        {$tradbtns.savr.$country}
                                    </a>
                                </td>
                            </tr>
                        </table>

                        <!-- EO - cta - 86bdddea-44d2-4168-8366-b8b212c5a396 -->


                    </td>
                </tr>
            </table>

            <!-- EO - WRAPPER / 81e54611-a4e0-4a64-a023-c62b424761d9 -->
        </td>
    </tr>
</table>
<!--[if gte mso 9]> </td><td style="vertical-align:top;border-collapse:collapse"><![endif]-->
<!-- COLUMN / aebfd1c5-b921-4761-b843-d757f6d958d9 -->
<table cellpadding="0" cellspacing="0" border="0" width="13" align="left" style="width:13px" class="t_w2p m_dnone m_w0p" role="presentation">
    <tr>
        <td>
            <span>&nbsp;</span>
        </td>
    </tr>
</table>

<!--[if gte mso 9]> </td><td style="vertical-align:top;border-collapse:collapse"><![endif]-->