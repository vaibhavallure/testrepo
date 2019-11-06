<!-- Espace -->
<table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;clear:both;width:640px" class="t_w100p m_w100p" role="presentation">
    <tr>
        <td height="20" style="height:20px;font-size:20px;line-height:20px" class="">
            &nbsp;
        </td>
    </tr>
</table>

<!-- Debut Contact -->
<table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;background-color:#000000;width:640px" class="t_w100p m_w100p">
    <tr>
        <td style="">

            <!-- Début Besoin d'un conseil-->
            <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;width:640px" class="t_w100p m_w100p" role="presentation">
                <tr>
                    <td style="font-family:Georgia,Times,Times New Roman,serif;font-weight:normal;color:#ffffff;text-align:center;font-size:42px;padding-top:55px;padding-bottom:10px;vertical-align:middle;line-height:46px" class="m_fz30px m_lh32px crimsontext">
                        {$tabcontacts.conseil}
                    </td>
                </tr>
            </table>

            <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;width:640px" class="t_w100p m_w100p" role="presentation">
                <tr>
                    <td style="font-family:Arial,Helvetica,sans-serif;font-weight:normal;color:#9c9487;text-align:center;text-transform:uppercase;font-size:14px;padding-bottom:35px;vertical-align:middle;letter-spacing:3px;line-height:16px" class=" sourcesanspro">
                        {$tabcontacts.conseil2}
                    </td>
                </tr>
            </table>


            <!-- Début Horaires d'ouverture-->
            <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;width:640px" class="t_w100p m_w100p" role="presentation">
                <tr>
                    <td style="font-family:Arial,Helvetica,sans-serif;font-weight:normal;color:#ffffff;text-align:center;font-size:13px;padding-right:100px;padding-bottom:35px;padding-left:100px;vertical-align:middle;line-height:18px" class="t_pr6p t_pl6p m_pr6p m_pl6p opensans">
                        {$tabcontacts.ouverture}
                    </td>
                </tr>
            </table>



            <table cellpadding="0" cellspacing="0" border="0" width="450" align="center" style="margin:auto;width:450px" class="t_w449px m_w100p">
                <tr>
                    <td style="">


                        <table cellpadding="0" cellspacing="0" border="0" width="225" align="left" style="width:225px" class="t_w50p m_w100p" role="presentation">
                            <tr>
                                <td>

                                    <table cellpadding="0" cellspacing="0" border="0" width="160" align="center" style="margin:auto;width:160px" class="t_w160px m_w160px">
                                        <tr>
                                            <td style="">


                                                <table cellpadding="0" cellspacing="0" border="0" width="33" align="left" style="width:33px" class="t_w21p m_w21p" role="presentation">
                                                    <tr>
                                                        <td>
                                                            <!-- Image téléphone-->
                                                            <table cellpadding="0" cellspacing="0" border="0" width="15" align="center" style="margin:auto;width:15px" class="t_w14px m_w14px" role="presentation">
                                                                <tr>
                                                                    <td style="text-align:center" class="m_pb20px">
                                                                       {if isset($numero) }
                                                                        <a href="{$numero.href}" style="color:#000000;outline:none;border:none" title="{$tabcontacts.alt}">
                                                                            <img src="http://cdn.millesima.com.s3.amazonaws.com/templates/00_elements_communs/icon-phone.png" alt="Phone" width="15" style="font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:13px;display:block;border:none" class="t_w100p m_w100p" />
                                                                        </a>
                                                                       {/if}
                                                                    </td>
                                                                </tr>
                                                            </table>


                                                        </td>
                                                    </tr>
                                                </table>

                                                <!--[if gte mso 9]> </td><td style="vertical-align:top;border-collapse:collapse"><![endif]-->

                                                <table cellpadding="0" cellspacing="0" border="0" width="127" align="right" style="width:127px" class="t_w79p m_w79p" role="presentation">
                                                    <tr>
                                                        <td>

                                                            <!-- Numero de téléphone-->
                                                            <table cellpadding="0" cellspacing="0" border="0" width="127" align="center" style="margin:auto;width:127px" class="t_w100p m_w100p" role="presentation">
                                                                <tr>
                                                                    <td style="color:#ffffff;text-align:center;font-size:13px;padding-top:4px;padding-bottom:4px;vertical-align:middle;line-height:15px" class="m_pb24px">
                                                                        {foreach from=$tabcontacts.telephone item=numero name=numeros}{if $smarty.foreach.numeros.first}
                                                                            <a href="{$numero.href}" style="color:#ffffff;line-height:15px;display:block;text-decoration:none;outline:none" title="{$tabcontacts.alt}">
                                                                                <span style="text-decoration: underline">{$numero.label}</span>
                                                                            </a>
                                                                        {/if}
                                                                        {/foreach}

                                                                    </td>
                                                                </tr>
                                                            </table>

                                                        </td>
                                                    </tr>
                                                </table>

                                            </td>
                                        </tr>
                                    </table>


                                </td>
                            </tr>
                        </table>

                        <!--[if gte mso 9]> </td><td style="vertical-align:top;border-collapse:collapse"><![endif]-->

                        <table cellpadding="0" cellspacing="0" border="0" width="225" align="right" style="width:225px" class="t_w50p m_w100p" role="presentation">
                            <tr>
                                <td>

                                    <table cellpadding="0" cellspacing="0" border="0" width="180" align="center" style="margin:auto;width:180px" class="t_w180px m_w180px">
                                        <tr>
                                            <td style="">

                                                <table cellpadding="0" cellspacing="0" border="0" width="32" align="left" style="width:32px" class="t_w18p m_w18p" role="presentation">
                                                    <tr>
                                                        <td>
                                                            <!-- Image Mail-->
                                                            <table cellpadding="0" cellspacing="0" border="0" width="25" align="center" style="margin:auto;width:25px" class="t_w25px m_w25px" role="presentation">
                                                                <tr>
                                                                    <td style="text-align:center">
                                                                        {foreach from=$tabcontacts.emails item=email name=emails}
                                                                            <a href="mailto:{$email}" style="color:#000000;outline:none;border:none" title="{$tabcontacts.alt}">
                                                                                <img src="http://cdn.millesima.com.s3.amazonaws.com/templates/00_elements_communs/icon-mail.png" alt="email" width="25" style="font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:13px;display:block;border:none" class="t_w100p m_w100p" />
                                                                            </a>
                                                                            {if not $smarty.foreach.emails.last}{/if}{/foreach}
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>

                                                <!--[if gte mso 9]> <td style="vertical-align:top;border-collapse:collapse"></td><![endif]-->

                                                <table cellpadding="0" cellspacing="0" border="0" width="148" align="right" style="width:148px" class="t_w82p m_w82p" role="presentation">
                                                    <tr>
                                                        <td>

                                                            <!-- Mail-->
                                                            <table cellpadding="0" cellspacing="0" border="0" width="148" align="center" style="margin:auto;width:148px" class="t_w100p m_w100p" role="presentation">
                                                                <tr>
                                                                    <td style="color:#ffffff;text-align:center;font-size:13px;padding-top:4px;padding-bottom:4px;vertical-align:middle;line-height:15px">
                                                                        {foreach from=$tabcontacts.emails item=email name=emails}
                                                                            <a href="mailto:{$email}" style="color:#ffffff;line-height:15px;display:block;text-decoration:none;outline:none" title="{$tabcontacts.alt}">
                                                                                <span style="text-decoration: underline;font-size:12px">{$email}</span>
                                                                            </a>
                                                                            {if not $smarty.foreach.emails.last}<br />{/if}{/foreach}
                                                                    </td>
                                                                </tr>
                                                            </table>


                                                        </td>
                                                    </tr>
                                                </table>

                                            </td>
                                        </tr>
                                    </table>


                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
            </table>


            <!-- Image Equipe-->
            <table cellpadding="0" cellspacing="0" border="0" width="350" align="center" style="margin:auto;width:350px" class="t_w350px m_w280px" role="presentation">
                <tr>
                    <td style="padding-top:25px;padding-bottom:15px;text-align:center">
                        <img src="{$tabcontacts.image}" alt="Team" width="350" style="font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#000000;line-height:13px;display:block;border:none"
                             class="t_w100p m_w100p" />
                    </td>
                </tr>
            </table>


            <!-- Nom Equipe-->
            <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;width:640px" class="t_w100p m_w100p" role="presentation">
                <tr>
                    <td style="font-family:Arial,Helvetica,sans-serif;font-weight:normal;color:#ffffff;text-align:center;font-size:13px;padding-bottom:25px;vertical-align:middle;line-height:15px" class=" opensans">
                        {$tabcontacts.noms}
                    </td>
                </tr>
            </table>


        </td>
    </tr>
</table>

<!-- Fin Contact -->

<table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;width:640px" class="t_w100p m_w100p">
    <tr>
        <td style="">

            <!-- Espace -->
            <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;clear:both;width:640px" class="t_w100p m_w100p" role="presentation">
                <tr>
                    <td height="20" style="height:20px;font-size:20px;line-height:20px" class="">
                        &nbsp;
                    </td>
                </tr>
            </table>
            <!-- Fin Espace -->

        </td>
    </tr>
</table>


<!-- Début Réassurance -->
<table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;background-color:#ffffff;width:640px" class="t_w100p m_w100p">
    <tr>
        <td style="">

            <!-- Début Espace -->
            <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;clear:both;width:640px" class="t_w100p m_w100p" role="presentation">
                <tr>
                    <td height="45" style="height:45px;font-size:45px;line-height:45px" class="">
                        &nbsp;
                    </td>
                </tr>
            </table>
            <!-- Fin Espace -->


            <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;width:640px" class="t_w100p m_w100p">
                <tr>
                    <td style="">


                        <table cellpadding="0" cellspacing="0" border="0" width="320" align="left" style="width:320px" class="t_w50p m_w100p" role="presentation">
                            <tr>
                                <td>

                                    <table cellpadding="0" cellspacing="0" border="0" width="320" align="center" style="margin:auto;width:320px" class="t_w100p m_w100p">
                                        <tr>
                                            <td style="">


                                                <table cellpadding="0" cellspacing="0" border="0" width="160" align="left" style="width:160px" class="t_w50p m_w50p" role="presentation">
                                                    <tr>
                                                        <td>

                                                            <table cellpadding="0" cellspacing="0" border="0" width="160" align="center" style="margin:auto;width:160px" class="t_w100p m_w100p">
                                                                <tr>
                                                                    <td style="">

                                                                        <!-- Image Choix exceptionnel -->
                                                                        <table cellpadding="0" cellspacing="0" border="0" width="44" align="center" style="margin:auto;width:44px" class="t_w44px m_w44px" role="presentation">
                                                                            <tr>
                                                                                <td style="padding-bottom:10px;text-align:center">
                                                                                    <img src="http://cdn.millesima.com.s3.amazonaws.com/templates/00_elements_communs/icon-choix.png" alt="Product" width="44" style="font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#000000;line-height:13px;display:block;border:none"
                                                                                         class="t_w100p m_w100p" />
                                                                                </td>
                                                                            </tr>
                                                                        </table>


                                                                        <!-- Titre Choix exceptionnel -->
                                                                        <table cellpadding="0" cellspacing="0" border="0" width="160" align="center" style="margin:auto;width:160px" class="t_w100p m_w100p" role="presentation">
                                                                            <tr>
                                                                                <td style="font-family:Georgia,Times,Times New Roman,serif;font-weight:bold;color:#000000;text-align:center;text-transform:uppercase;font-size:11px;padding-top:10px;padding-right:5px;padding-bottom:5px;padding-left:5px;vertical-align:middle;line-height:13px"
                                                                                    class=" crimsontext">
                                                                                    {$tabreassurance.1.titre}
                                                                                </td>
                                                                            </tr>
                                                                        </table>


                                                                        <!-- Texte Choix exceptionnel -->
                                                                        <table cellpadding="0" cellspacing="0" border="0" width="160" align="center" style="margin:auto;width:160px" class="t_w100p m_w100p" role="presentation">
                                                                            <tr>
                                                                                <td style="font-family:Arial,Helvetica,sans-serif;font-weight:normal;color:#000000;text-align:center;font-size:11px;padding-right:20px;padding-bottom:45px;padding-left:20px;vertical-align:middle;line-height:13px" class="t_pr10p t_pl10p m_pr20p m_pl20p sourcesanspro">
                                                                                    {$tabreassurance.1.description}
                                                                                </td>
                                                                            </tr>
                                                                        </table>


                                                                    </td>
                                                                </tr>
                                                            </table>


                                                        </td>
                                                    </tr>
                                                </table>

                                                <!--[if gte mso 9]> </td><td style="vertical-align:top;border-collapse:collapse"><![endif]-->

                                                <table cellpadding="0" cellspacing="0" border="0" width="160" align="right" style="width:160px" class="t_w50p m_w50p" role="presentation">
                                                    <tr>
                                                        <td>

                                                            <table cellpadding="0" cellspacing="0" border="0" width="160" align="center" style="margin:auto;width:160px" class="t_w100p m_w100p">
                                                                <tr>
                                                                    <td style="">

                                                                        <!-- Image Qualité et Provenance-->
                                                                        <table cellpadding="0" cellspacing="0" border="0" width="75" align="center" style="margin:auto;width:75px" class="t_w75px m_w75px" role="presentation">
                                                                            <tr>
                                                                                <td style="padding-bottom:10px;text-align:center">
                                                                                    <img src="http://cdn.millesima.com.s3.amazonaws.com/templates/00_elements_communs/icon-origine.png" alt="Chais Bordeaux" width="75" style="font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#000000;line-height:13px;display:block;border:none"
                                                                                         class="t_w100p m_w100p" />
                                                                                </td>
                                                                            </tr>
                                                                        </table>


                                                                        <!-- Titre  Qualité et Provenance -->
                                                                        <table cellpadding="0" cellspacing="0" border="0" width="160" align="center" style="margin:auto;width:160px" class="t_w100p m_w100p" role="presentation">
                                                                            <tr>
                                                                                <td style="font-family:Georgia,Times,Times New Roman,serif;font-weight:bold;color:#000000;text-align:center;text-transform:uppercase;font-size:11px;padding-top:10px;padding-right:5px;padding-bottom:5px;padding-left:5px;vertical-align:middle;line-height:13px"
                                                                                    class=" crimsontext">
                                                                                    {$tabreassurance.2.titre}
                                                                                </td>
                                                                            </tr>
                                                                        </table>


                                                                        <!-- Texte Qualité et Provenance -->
                                                                        <table cellpadding="0" cellspacing="0" border="0" width="160" align="center" style="margin:auto;width:160px" class="t_w100p m_w100p" role="presentation">
                                                                            <tr>
                                                                                <td style="font-family:Arial,Helvetica,sans-serif;font-weight:normal;color:#000000;text-align:center;font-size:11px;padding-right:20px;padding-bottom:45px;padding-left:20px;vertical-align:middle;line-height:13px" class="t_pr10p t_pl10p m_pr20p m_pl20p sourcesanspro">
                                                                                    {$tabreassurance.2.description}
                                                                                </td>
                                                                            </tr>
                                                                        </table>


                                                                    </td>
                                                                </tr>
                                                            </table>


                                                        </td>
                                                    </tr>
                                                </table>

                                            </td>
                                        </tr>
                                    </table>


                                </td>
                            </tr>
                        </table>

                        <!--[if gte mso 9]> </td><td style="vertical-align:top;border-collapse:collapse"><![endif]-->

                        <table cellpadding="0" cellspacing="0" border="0" width="320" align="right" style="width:320px" class="t_w50p m_w100p" role="presentation">
                            <tr>
                                <td>

                                    <table cellpadding="0" cellspacing="0" border="0" width="320" align="center" style="margin:auto;width:320px" class="t_w100p m_w100p">
                                        <tr>
                                            <td style="">


                                                <table cellpadding="0" cellspacing="0" border="0" width="160" align="left" style="width:160px" class="t_w50p m_w50p" role="presentation">
                                                    <tr>
                                                        <td>

                                                            <table cellpadding="0" cellspacing="0" border="0" width="160" align="center" style="margin:auto;width:160px" class="t_w100p m_w100p">
                                                                <tr>
                                                                    <td style="">

                                                                        <!-- Image Conseil et Services -->
                                                                        <table cellpadding="0" cellspacing="0" border="0" width="40" align="center" style="margin:auto;width:40px" class="t_w40px m_w40px" role="presentation">
                                                                            <tr>
                                                                                <td style="padding-bottom:10px;text-align:center">
                                                                                    <img src="http://cdn.millesima.com.s3.amazonaws.com/templates/00_elements_communs/icon-conseil.png" alt="Europe Usa Asie" width="40" style="font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#000000;line-height:13px;display:block;border:none"
                                                                                         class="t_w100p m_w100p" />
                                                                                </td>
                                                                            </tr>
                                                                        </table>


                                                                        <!-- Titre Conseil et Services -->
                                                                        <table cellpadding="0" cellspacing="0" border="0" width="160" align="center" style="margin:auto;width:160px" class="t_w100p m_w100p" role="presentation">
                                                                            <tr>
                                                                                <td style="font-family:Georgia,Times,Times New Roman,serif;font-weight:bold;color:#000000;text-align:center;text-transform:uppercase;font-size:11px;padding-top:10px;padding-right:5px;padding-bottom:5px;padding-left:5px;vertical-align:middle;line-height:13px"
                                                                                    class=" crimsontext">
                                                                                    {$tabreassurance.3.titre}
                                                                                </td>
                                                                            </tr>
                                                                        </table>


                                                                        <!-- Texte Conseil et Services -->
                                                                        <table cellpadding="0" cellspacing="0" border="0" width="160" align="center" style="margin:auto;width:160px" class="t_w100p m_w100p" role="presentation">
                                                                            <tr>
                                                                                <td style="font-family:Arial,Helvetica,sans-serif;font-weight:normal;color:#000000;text-align:center;font-size:11px;padding-right:20px;padding-bottom:45px;padding-left:20px;vertical-align:middle;line-height:13px" class="t_pr10p t_pl10p m_pr20p m_pl20p sourcesanspro">
                                                                                    {$tabreassurance.3.description}
                                                                                </td>
                                                                            </tr>
                                                                        </table>


                                                                    </td>
                                                                </tr>
                                                            </table>


                                                        </td>
                                                    </tr>
                                                </table>

                                                <!--[if gte mso 9]> </td><td style="vertical-align:top;border-collapse:collapse"><![endif]-->

                                                <table cellpadding="0" cellspacing="0" border="0" width="160" align="right" style="width:160px" class="t_w50p m_w50p" role="presentation">
                                                    <tr>
                                                        <td>

                                                            <table cellpadding="0" cellspacing="0" border="0" width="160" align="center" style="margin:auto;width:160px" class="t_w100p m_w100p">
                                                                <tr>
                                                                    <td style="">

                                                                        <!-- Image Livraison Soignée -->
                                                                        <table cellpadding="0" cellspacing="0" border="0" width="42" align="center" style="margin:auto;width:42px" class="t_w42px m_w42px" role="presentation">
                                                                            <tr>
                                                                                <td style="padding-bottom:10px;text-align:center">
                                                                                    <img src="http://cdn.millesima.com.s3.amazonaws.com/templates/00_elements_communs/icon-livraison.png" alt="Shipping" width="42" style="font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#000000;line-height:13px;display:block;border:none"
                                                                                         class="t_w100p m_w100p" />
                                                                                </td>
                                                                            </tr>
                                                                        </table>


                                                                        <!-- Titre Livraison Soignée -->
                                                                        <table cellpadding="0" cellspacing="0" border="0" width="160" align="center" style="margin:auto;width:160px" class="t_w100p m_w100p" role="presentation">
                                                                            <tr>
                                                                                <td style="font-family:Georgia,Times,Times New Roman,serif;font-weight:bold;color:#000000;text-align:center;text-transform:uppercase;font-size:11px;padding-top:10px;padding-right:5px;padding-bottom:5px;padding-left:5px;vertical-align:middle;line-height:13px"
                                                                                    class=" crimsontext">
                                                                                    {$tabreassurance.4.titre}
                                                                                </td>
                                                                            </tr>
                                                                        </table>


                                                                        <!-- Texte Livraison Soignée -->
                                                                        <table cellpadding="0" cellspacing="0" border="0" width="160" align="center" style="margin:auto;width:160px" class="t_w100p m_w100p" role="presentation">
                                                                            <tr>
                                                                                <td style="font-family:Arial,Helvetica,sans-serif;font-weight:normal;color:#000000;text-align:center;font-size:11px;padding-right:20px;padding-bottom:45px;padding-left:20px;vertical-align:middle;line-height:13px" class="t_pr10p t_pl10p m_pr20p m_pl20p sourcesanspro">
                                                                                    {$tabreassurance.4.description}
                                                                                </td>
                                                                            </tr>
                                                                        </table>


                                                                    </td>
                                                                </tr>
                                                            </table>


                                                        </td>
                                                    </tr>
                                                </table>

                                            </td>
                                        </tr>
                                    </table>


                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
            </table>


            <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;background-color:#f9f9f9;width:640px" class="t_w100p m_w100p">
                <tr>
                    <td style="">

                        <!-- Titre Réseaux Sociaux -->
                        <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;width:640px" class="t_w100p m_w100p" role="presentation">
                            <tr>
                                <td style="font-family:Arial,Helvetica,sans-serif;font-weight:normal;color:#000000;text-align:center;font-size:13px;padding-top:15px;padding-bottom:15px;vertical-align:middle;letter-spacing:3px;line-height:15px" class=" opensans">
                                {if isset($social.titre) }  {$social.titre} {/if}
                                </td>
                            </tr>
                        </table>



                        <table cellpadding="0" cellspacing="0" border="0" width="320" align="center" style="margin:auto;width:320px" class="t_w320px m_w280px">
                            <tr>
                                <td style="">


                                    <table cellpadding="0" cellspacing="0" border="0" width="64" align="left" style="width:64px" class="t_w20p m_w20p" role="presentation">
                                        <tr>
                                            <td>
                                                <!-- Logo Facebook-->
                                                <table cellpadding="0" cellspacing="0" border="0" width="37" align="center" style="margin:auto;width:37px" class="t_w37px m_w37px" role="presentation">
                                                    <tr>
                                                        <td style="text-align:center">
                                                            <a href="{$social.facebook}" style="color:#000000;outline:none;border:none" title="Facebook">
                                                                <img src="http://cdn.millesima.com.s3.amazonaws.com/templates/00_elements_communs/icon_facebook.png" alt="Facebook" width="37" style="font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:13px;display:block;border:none" class="t_w100p m_w100p"
                                                                />
                                                            </a>
                                                        </td>
                                                    </tr>
                                                </table>


                                            </td>
                                        </tr>
                                    </table>

                                    <!--[if gte mso 9]> </td><td style="vertical-align:top;border-collapse:collapse"><![endif]-->

                                    <table cellpadding="0" cellspacing="0" border="0" width="64" align="left" style="width:64px" class="t_w20p m_w20p" role="presentation">
                                        <tr>
                                            <td>
                                                <!-- Logo Twitter -->
                                                <table cellpadding="0" cellspacing="0" border="0" width="37" align="center" style="margin:auto;width:37px" class="t_w37px m_w37px" role="presentation">
                                                    <tr>
                                                        <td style="text-align:center">
                                                            <a href="{$social.twitter}" style="color:#000000;outline:none;border:none" title="Twitter">
                                                                <img src="http://cdn.millesima.com.s3.amazonaws.com/templates/00_elements_communs/icon_twitter.png" alt="Twitter" width="37" style="font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:13px;display:block;border:none" class="t_w100p m_w100p"
                                                                />
                                                            </a>
                                                        </td>
                                                    </tr>
                                                </table>


                                            </td>
                                        </tr>
                                    </table>

                                    <!--[if gte mso 9]> </td><td style="vertical-align:top;border-collapse:collapse"><![endif]-->

                                    <table cellpadding="0" cellspacing="0" border="0" width="64" align="left" style="width:64px" class="t_w20p m_w20p" role="presentation">
                                        <tr>
                                            <td>
                                                <!-- Logo Linkedin -->
                                                <table cellpadding="0" cellspacing="0" border="0" width="37" align="center" style="margin:auto;width:37px" class="t_w37px m_w37px" role="presentation">
                                                    <tr>
                                                        <td style="text-align:center">
                                                            <a href="https://fr.linkedin.com/company/millesima-sa" style="color:#000000;outline:none;border:none" title="Linkedin">
                                                                <img src="http://cdn.millesima.com.s3.amazonaws.com/templates/00_elements_communs/icon_linkedin.png" alt="Linkedin" width="37" style="font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:13px;display:block;border:none" class="t_w100p m_w100p"
                                                                />
                                                            </a>
                                                        </td>
                                                    </tr>
                                                </table>


                                            </td>
                                        </tr>
                                    </table>

                                    <!--[if gte mso 9]> </td><td style="vertical-align:top;border-collapse:collapse"><![endif]-->

                                    <table cellpadding="0" cellspacing="0" border="0" width="64" align="left" style="width:64px" class="t_w20p m_w20p" role="presentation">
                                        <tr>
                                            <td>
                                                <!-- Logo You Tube -->
                                                <table cellpadding="0" cellspacing="0" border="0" width="37" align="center" style="margin:auto;width:37px" class="t_w37px m_w37px" role="presentation">
                                                    <tr>
                                                        <td style="text-align:center">
                                                            <a href="{$social.youtube}" style="color:#000000;outline:none;border:none" title="Youtube">
                                                                <img src="http://cdn.millesima.com.s3.amazonaws.com/templates/00_elements_communs/icon_youtube.png" alt="Youtube" width="37" style="font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:13px;display:block;border:none" class="t_w100p m_w100p"
                                                                />
                                                            </a>
                                                        </td>
                                                    </tr>
                                                </table>


                                            </td>
                                        </tr>
                                    </table>

                                    <!--[if gte mso 9]> </td><td style="vertical-align:top;border-collapse:collapse"><![endif]-->

                                    <table cellpadding="0" cellspacing="0" border="0" width="64" align="right" style="width:64px" class="t_w20p m_w20p" role="presentation">
                                        <tr>
                                            <td>
                                                <!-- Logo Instagram -->
                                                <table cellpadding="0" cellspacing="0" border="0" width="37" align="center" style="margin:auto;width:37px" class="t_w37px m_w37px" role="presentation">
                                                    <tr>
                                                        <td style="text-align:center">
                                                          {if isset($social.instagram) } <a href="{$social.instagram}" style="color:#000000;outline:none;border:none" title="Instagram">
                                                                <img src="http://cdn.millesima.com.s3.amazonaws.com/templates/00_elements_communs/icon_instagram.png" alt="Instagram" width="37" style="font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:13px;display:block;border:none" class="t_w100p m_w100p"
                                                                />
                                                            </a>
                                                          {/if}
                                                        </td>
                                                    </tr>
                                                </table>


                                            </td>
                                        </tr>
                                    </table>

                                </td>
                            </tr>
                        </table>


                        <!-- Début Espace -->
                        <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;clear:both;width:640px" class="t_w100p m_w100p" role="presentation">
                            <tr>
                                <td height="30" style="height:30px;font-size:30px;line-height:30px" class="m_h10px m_fz10px m_lh10px">
                                    &nbsp;
                                </td>
                            </tr>
                        </table>
                        <!-- Fin Espace -->

                    </td>
                </tr>
            </table>


        </td>
    </tr>
</table>
<!-- Fin Réassurance -->


<table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;width:640px" class="t_w100p m_w100p">
    <tr>
        <td style="">

            <!-- Début Espace -->
            <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;clear:both;width:640px" class="t_w100p m_w100p" role="presentation">
                <tr>
                    <td height="20" style="height:20px;font-size:20px;line-height:20px" class="">
                        &nbsp;
                    </td>
                </tr>
            </table>
            <!-- Fin Espace -->

        </td>
    </tr>
</table>


<!-- Début Menu -->
<table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;width:640px" class="t_w100p m_w100p">
    <tr>
        <td style="">


            <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;background-color:#090909;width:640px" class="t_w100p m_w100p">
                <tr>
                    <td style="">


                        <table cellpadding="0" cellspacing="0" border="0" width="608" align="center" style="margin:auto;width:608px" class="t_w100p m_w100p">
                            <tr>
                                <td style="">


                                    <table cellpadding="0" cellspacing="0" border="0" width="202" align="left" style="width:202px" class="t_w33d33p m_w100p" role="presentation">
                                        <tr>
                                            <td>

                                                <!-- Début Espace -->
                                                <table cellpadding="0" cellspacing="0" border="0" width="202" align="center" style="margin:auto;clear:both;width:202px" class="t_w100p m_w100p" role="presentation">
                                                    <tr>
                                                        <td height="5" style="height:5px;font-size:5px;line-height:5px" class="m_h25px m_fz25px m_lh25px">
                                                            &nbsp;
                                                        </td>
                                                    </tr>
                                                </table>
                                                <!-- Fin Espace -->

                                            </td>
                                        </tr>
                                        <tr>
                                            <td>

                                                <!-- Tous nos vins -->
                                                <table cellpadding="0" cellspacing="0" border="0" width="202" align="center" style="margin:auto;width:202px" class="t_w100p m_w270px" role="presentation">
                                                    <tr>
                                                        <td style="font-family:Arial,Helvetica,sans-serif;font-weight:normal;color:#ffffff;text-align:center;font-size:13px;padding-top:20px;padding-bottom:20px;vertical-align:middle;letter-spacing:2px;line-height:15px" class="m_bdt1pxsolidffffff m_bdr1pxsolidffffff m_bdb1pxsolidffffff m_bdl1pxsolidffffff m_pt12px m_pb12px m_lh16px opensans">
                                                            <a href="{$tabmenu.tous.url}" style="color:#ffffff;line-height:15px;display:block;text-decoration:none;outline:none" class="m_lh16px">
                                                                {$tabmenu.tous.nom}
                                                            </a>
                                                        </td>
                                                    </tr>
                                                </table>


                                            </td>
                                        </tr>
                                        <tr>
                                            <td>

                                                <!-- Début Espace -->
                                                <table cellpadding="0" cellspacing="0" border="0" width="202" align="center" style="margin:auto;clear:both;width:202px" class="t_w100p m_w100p" role="presentation">
                                                    <tr>
                                                        <td height="5" style="height:5px;font-size:5px;line-height:5px" class="m_h10px m_fz10px m_lh10px">
                                                            &nbsp;
                                                        </td>
                                                    </tr>
                                                </table>
                                                <!-- Fin Espace -->

                                            </td>
                                        </tr>
                                    </table>

                                    <!--[if gte mso 9]> </td><td style="vertical-align:top;border-collapse:collapse"><![endif]-->

                                    <table cellpadding="0" cellspacing="0" border="0" width="203" align="left" style="width:203px" class="t_w33d33p m_w100p" role="presentation">
                                        <tr>
                                            <td>

                                                <!-- Menu primeurs-->
                                                <table cellpadding="0" cellspacing="0" border="0" width="202" align="center" style="margin:auto;width:202px" class="t_w100p m_w270px" role="presentation">
                                                    <tr>
                                                        <td style="font-family:Arial,Helvetica,sans-serif;font-weight:normal;color:#ffffff;text-align:center;font-size:13px;padding-top:25px;padding-bottom:20px;vertical-align:middle;letter-spacing:2px;line-height:15px" class="m_bdt1pxsolidffffff m_bdr1pxsolidffffff m_bdb1pxsolidffffff m_bdl1pxsolidffffff m_pt12px m_pb12px m_lh16px opensans">
                                                            <a href="{$tabmenu.primeurs.url}" style="color:#ffffff;line-height:15px;display:block;text-decoration:none;outline:none" class="m_lh16px">
                                                                {$tabmenu.primeurs.nom}
                                                            </a>
                                                        </td>
                                                    </tr>
                                                </table>

                                            </td>
                                        </tr>
                                        <tr>
                                            <td>

                                                <!-- Début Espace -->
                                                <table cellpadding="0" cellspacing="0" border="0" width="202" align="center" style="margin:auto;clear:both;width:202px" class="t_w100p m_w100p" role="presentation">
                                                    <tr>
                                                        <td height="5" style="height:5px;font-size:5px;line-height:5px" class="m_h10px m_fz10px m_lh10px">
                                                            &nbsp;
                                                        </td>
                                                    </tr>
                                                </table>
                                                <!-- Fin Espace -->

                                            </td>
                                        </tr>
                                    </table>

                                    <!--[if gte mso 9]> </td><td style="vertical-align:top;border-collapse:collapse"><![endif]-->

                                    <table cellpadding="0" cellspacing="0" border="0" width="203" align="right" style="width:203px" class="t_w33d34p m_w100p" role="presentation">
                                        <tr>
                                            <td>

                                                <!-- Menu Offres Spéciales -->
                                                <table cellpadding="0" cellspacing="0" border="0" width="202" align="center" style="margin:auto;width:202px" class="t_w100p m_w270px" role="presentation">
                                                    <tr>
                                                        <td style="font-family:Arial,Helvetica,sans-serif;font-weight:normal;color:#ffffff;text-align:center;font-size:13px;padding-top:25px;padding-bottom:20px;vertical-align:middle;letter-spacing:2px;line-height:15px" class="m_bdt1pxsolidffffff m_bdr1pxsolidffffff m_bdb1pxsolidffffff m_bdl1pxsolidffffff m_pt12px m_pb12px m_lh16px opensans">
                                                            <a href="{$tabmenu.offspe.url}" style="color:#ffffff;line-height:15px;display:block;text-decoration:none;outline:none" class="m_lh16px">
                                                                {$tabmenu.offspe.nom}
                                                            </a>
                                                        </td>
                                                    </tr>
                                                </table>

                                            </td>
                                        </tr>
                                        <tr>
                                            <td>

                                                <!-- Début Espace -->
                                                <table cellpadding="0" cellspacing="0" border="0" width="202" align="center" style="margin:auto;clear:both;width:202px" class="t_w100p m_w100p" role="presentation">
                                                    <tr>
                                                        <td height="5" style="height:5px;font-size:5px;line-height:5px" class="m_h25px m_fz25px m_lh25px">
                                                            &nbsp;
                                                        </td>
                                                    </tr>
                                                </table>
                                                <!-- Fin Espace -->

                                            </td>
                                        </tr>
                                    </table>

                                </td>
                            </tr>
                        </table>


                        <!-- CGV -->
                        <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;width:640px" class="t_w100p m_w100p" role="presentation">
                            <tr>
                                <td style="font-family:Arial,Helvetica,sans-serif;font-weight:normal;color:#ffffff;text-align:center;font-size:12px;padding-right:10px;padding-bottom:20px;padding-left:10px;vertical-align:middle;line-height:18px" class="t_pr6p t_pl6p m_pr6p m_pl6p sourcesanspro">

                                        <span style="text-decoration: underline">{include file="$tpl/cgv/$typecgv/cgv_$typecgv$country.tpl"}</span>

                                </td>
                            </tr>
                        </table>


                        <!-- Mentions légales -->
                        <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;width:640px" class="t_w100p m_w100p" role="presentation">
                            <tr>
                                <td style="font-family:Arial,Helvetica,sans-serif;font-weight:normal;color:#ffffff;text-align:center;font-size:12px;padding-right:10px;padding-bottom:20px;padding-left:10px;vertical-align:middle;line-height:18px" class="t_pr6p t_pl6p m_pr6p m_pl6p sourcesanspro">
                                    {include file="$tpl/mentions/mention$country.tpl"}
                                </td>
                            </tr>
                        </table>


                        <!-- Lien de désabonnement -->
                        <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;width:640px" class="t_w100p m_w100p" role="presentation">
                            <tr>
                                <td style="font-family:Arial,Helvetica,sans-serif;font-weight:normal;color:#ffffff;text-align:center;font-size:12px;padding-right:10px;padding-bottom:20px;padding-left:10px;vertical-align:middle;line-height:18px" class="t_pr6p t_pl6p m_pr6p m_pl6p sourcesanspro">
                                    <a href="{$desabo.lien}" style="color:#ffffff;line-height:18px;display:block;text-decoration:none;outline:none">
                                        <span style="text-decoration: underline">{$desabo.title}</span>
                                    </a>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
            </table>

        </td>
    </tr>
</table>

