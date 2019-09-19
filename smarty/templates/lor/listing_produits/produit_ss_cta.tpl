{if $produit->primeur}{assign var='type' value='primeurs'}{else}{assign var='type' value='livrable'}{/if}
{if isset($produit->code_promo) and $produit->code_promo != ''}{assign var='promo' value=$produit->code_promo}{/if}
<!-- COLUMN / f49fb9c9-9cca-49db-a337-d8fbb998760e -->
<table cellpadding="0" cellspacing="0" border="0" width="213" align="left" style="width:213px" class="t_w33d33p m_w100p" role="presentation">
    <tr>
        <td>
            <!-- WRAPPER / 81e54611-a4e0-4a64-a023-c62b424761d9 -->

            <table cellpadding="0" cellspacing="0" border="0" width="202" align="center" style="margin:auto;background-color:#f2f2f2;width:202px" class="t_w95p m_w100p">
                <tr>
                    <td style="">

                        <!-- text / 714ea1ee-ab47-4f8f-9943-86830907f57f -->
                        <table cellpadding="0" cellspacing="0" border="0" width="202" height="80" align="center" style="margin:auto;width:202px;height:80px;" class="t_w100p m_w100p" role="presentation">
                            <tr>
                                <td style="background-color:#ccc7bc;font-family:Arial,Helvetica,sans-serif;color:#000000;text-align:center;font-size:14px;vertical-align:middle">
                                    <a href="{$siteweb}{$produit->url_produit}.html?{$tracking}">
                                        <span style="font-weight: bold">{$produit->libelle_internet_html}</span>&nbsp;<span style="">{if ($type != 'primeurs' OR !$isprimeur) AND $produit->millesime != 0 } {$produit->millesime}{/if}</span>
                                    </a>
                                </td>
                            </tr>
                        </table>
                        <!-- EO - text - 714ea1ee-ab47-4f8f-9943-86830907f57f -->



                        <!-- image / e2e7ac1d-435f-4f6b-bf05-6d20a551260c -->
                        <table cellpadding="0" cellspacing="0" border="0" width="202" align="center" style="margin:auto;width:202px" class="t_w100p m_w100p" role="presentation">
                            <tr>
                                <td style="text-align:center">
                                    <span style="font-size:12px;font-family: Arial,Helvetica,sans-serif;"><br>{$produit->appellation}<br/>{$produit->classement}</span><br>
                                    <a href="{$siteweb}{$produit->url_produit}.html?{$tracking}">
                                        <img src="{$produit->url_image_thumb}" alt="{$produit->libelle_internet|replace:'&lt;br/&gt;':' '} {$produit->millesime}" style="font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#000000;line-height:13px;border:none" class="t_w100p t_hauto m_hauto img_produit" />
                                    </a>
                                </td>
                            </tr>
                        </table>
                        <!-- EO - image - e2e7ac1d-435f-4f6b-bf05-6d20a551260c -->

                        <!-- text / ad563a56-8adc-4257-9b3e-e75825b9dd3d -->
                        <table cellpadding="0" cellspacing="0" border="0" width="202" align="center" style="margin:auto;width:202px" class="t_w100p m_w100p" role="presentation">
                            <tr>
                                <td style="font-family:Arial,Helvetica,sans-serif;color:#000000;text-align:center;font-size:16px;vertical-align:middle;font-style:italic">
                                                            <span style="font-size: 10px; color: #535512;">
	                                                                                                                           {if $produit->pays == 'G' OR $produit->pays == 'I' OR $produit->pays == 'H' OR $produit->pays == 'SG'}
                                                                                                                                   {$produit->LLibelleCouleur}&nbsp;{$produit->typedevin}
                                                                                                                               {elseif $produit->pays == 'D' OR $produit->pays == 'O' OR $produit->pays == 'SA'}
                                                                                                                                   {$produit->LibelleCouleur}{$produit->typedevin|lower}
                                                                                                                               {elseif $produit->pays == 'U'}
                                                                                                                                   {$produit->LibelleCouleur}
                                                                                                                               {else}
                                                                                                                                   {$produit->typedevin}&nbsp;{$produit->LibelleCouleur}
                                                                                                                               {/if}
                                                            </span>
                                </td>
                            </tr>
                        </table>
                        <!-- EO - text - ad563a56-8adc-4257-9b3e-e75825b9dd3d -->

                        <!-- text / 37d19f1e-e96f-4980-9fb9-4c030896961b -->
                        <table cellpadding="0" cellspacing="0" border="0" width="202" align="center" style="margin:auto;width:202px;height:100px;" class="t_w100p m_w100p" role="presentation">
                            {if $typecgv != 'primeurs' AND !$lstprmodesc AND (substr_count($codemessagegeneral, "uiospick") == 0)}{include file="$tpl/listing_produits/indication.tpl"}{/if}
                            {if !$ssprix}{include file="$tpl/listing_produits/prix.tpl"}{/if}

                        </table>
                        <!-- EO - text - 37d19f1e-e96f-4980-9fb9-4c030896961b -->


                    </td>
                </tr>
            </table>

            <!-- EO - WRAPPER / 81e54611-a4e0-4a64-a023-c62b424761d9 -->
        </td>
    </tr>
</table>