<!-- Début CTA -->
{assign var='btn' value=$infos.btn}
<table cellpadding="0" cellspacing="0" border="0" width="200" align="center" style="margin:auto;width:200px" class="t_mauto t_w200px m_w200px" role="presentation" >
	<tr>
		<td style="padding-left:20px!important">
            <!--[if mso]>
            <v:roundrect
                    xmlns:v="urn:schemas-microsoft-com:vml"
                    xmlns:w="urn:schemas-microsoft-com:office:word"
                    href="{$infos.url}" style="background-color:{$codecouleur};border-radius:3px;color:{$couleurtxtbtn};font-family:Georgia,Times,Times New Roman,serif;font-weight:bold;font-size:16px;height:40px;text-align:center;line-height:40px;mso-hide:all;display:inline-block;-webkit-text-size-adjust:none;text-decoration:none;width:161px"
                    style="v-text-anchor:middle;height:40px;width:161px"
                    arcsize="8%"
                    stroke="f"
                    fillcolor="{$codecouleur}"
                    fill="t">
                <w:anchorlock/>
                <center style="color:{$couleurtxtbtn};font-size:16px;font-family:Georgia,Times,Times New Roman,serif;font-weight:bold;height:40px;text-align:center;width:161px">
                    {$tradbtns.$btn.$country}
                </center>
            </v:roundrect>
            <![endif]-->
			<a href="{$infos.url}" style="background-color:{$codecouleur};color:{$couleurtxtbtn};font-family:Arial,Helvetica,sans-serif;font-weight:bold;font-size:13px;height:40px;text-align:center;line-height:40px;mso-hide:all;display:inline-block;-webkit-text-size-adjust:none;text-decoration:none;width:200px;margin-bottom:15px"
			   class="t_fwbold t_fsnormal t_w100p m_fwbold m_fsnormal m_w100p opensans">
				{$tradbtns.$btn.$country}
			</a>
		</td>
	</tr>
</table>
<!-- Fin CTA -->
