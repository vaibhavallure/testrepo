
<!-- SECTION / Edito2 -->
<table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;background-color:#ffffff;width:640px" class="t_w100p m_w100p">
    <tr>
        <td style="">


            <table cellpadding="0" cellspacing="0" border="0" width="495" align="center" style="margin:auto;width:495px" class="t_w100p m_w100p">
                <tr>
                    <td style="">

                        <!-- Début titre -->
                        <table cellpadding="0" cellspacing="0" border="0" width="495" align="center" style="margin:auto;width:495px" class="t_w100p m_w100p" role="presentation">
                            <tr>
                                <td style="font-family:Georgia,Times,Times New Roman,serif;font-weight:normal;color:#0a0a0a;text-align:left;font-size:25px;padding-top:20px;vertical-align:middle;line-height:30px" class="t_fz30px t_pl8p t_lh32px m_fz30px m_pl8p m_lh32px crimsontext">
                                    {if $desc.titre}{$desc.titre}{/if}
                                </td>
                            </tr>
                            {if $desc.astdesc != ""}<tr>
                                <td height="16"></td>
                            </tr>{/if}
                        </table>
                        <!-- Fin titre -->

                        <!-- Début texte-->
                        <table cellpadding="0" cellspacing="0" border="0" width="495" align="center" style="width:495px" class="t_w100p m_w100p" role="presentation">
                            <tr>
                                <td style="font-family:Arial,Helvetica,sans-serif;color:#272727;text-align:{$desc.textalign};font-size:13px;padding-top:30px;padding-bottom:20px;vertical-align:middle;line-height:18px" class="t_fz15px t_pt15px t_pr8p t_pb25px t_pl8p t_lh21px m_fz15px m_pt15px m_pr8p m_pb25px m_pl8p m_lh21px">
                                    {$desc.text}{if isset($lstprmodesc)}{if $desc.text != ""}<br /><br />{/if}{$lstpromotab.desc}{/if}
                                </td>
                            </tr>

                            <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin-bottom:15px;width:640px" class="t_w100p m_w100p" role="presentation">
                                {if isset($iscodepromo)}
                                <tr>
                                    <td style="font-family:Arial,Helvetica,sans-serif;font-weight:normal;color:#9c9487;text-align:center;text-transform:uppercase;font-size:13px;padding-bottom:15px;vertical-align:middle;letter-spacing:3px;line-height:15px" class=" sourcesanspro">
                                        {$phrasecode}&nbsp;<span style="font-weight: bold">{$codepromo}</span>
                                    </td>
                                </tr>
                                {/if}
                            </table>

                        </table>
                        <!-- Fin texte -->
                        <table cellpadding="0" cellspacing="0" border="0" width="200" align="left" style="width:200px" class="t_w100p m_w100p">
                            <tr>
                                <td style="">

                                    <table cellpadding="0" cellspacing="0" border="0" width="200" align="center" style="margin:auto;width:200px" class="t_mauto t_w200px m_w200px" role="presentation">
                                        <tr>
                                            <td class="t_fwbold t_fsnormal t_w100p m_fwbold m_fsnormal m_w100p opensans">

                                                {if isset($lstprmodesc)}{include file="$tpl/boutons/structure_btn.tpl" infos=$lstpromotab}{else}{include file="$tpl/boutons/structure_btn.tpl" infos=$desc}{/if}
                                                <br>
                                            </td>
                                        </tr>
                                        {if $typecgv == "livraison" && $fdpo_conditions && $fdpo.detail != ''}
                                            <tr>
                                                <td style="font-size:11px;font-family:Arial, Helvetica, sans-serif, Trebuchet MS;color: #444444;font-style:italic;">{$fdpo.detail}</td>
                                            </tr>
                                        {/if}



                    </td>
                </tr>
            </table>
                                    <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;width:640px" class="t_w100p m_w100p" role="presentation">
                                        {if $desc.astdesc != "" || isset($lstprmodesc)}
                                            <tr>
                                                <td style="font-family:Arial,Helvetica,sans-serif;font-weight:normal;color:#000000;text-align:left;font-size:11px;padding-bottom:5px;padding-left:10px;vertical-align:middle;line-height:15px;margin-bottom:10px;" class=" sourcesanspro">
                                                    {if isset($lstprmodesc)}{$lstpromotab.ast}{else}{$desc.astdesc}{/if}
                                                </td>
                                            </tr>
                                        {/if}

                                        {if isset($ast.description) && $ast.description != "" }
                                            <tr>
                                                <td style="font-family:Arial,Helvetica,sans-serif;font-weight:normal;color:#000000;text-align:left;font-size:11px;padding-bottom:5px;padding-left:10px;vertical-align:middle;line-height:15px;margin-bottom:10px;" class=" sourcesanspro">
                                                    {$ast.description}
                                                </td>
                                            </tr>
                                        {/if}
                                    </table>
                        </table>

        </td>
    </tr>
</table>