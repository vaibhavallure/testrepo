<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{$objet_alt_title}</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">
{literal}<style type="text/css">
/*Hotmail et Yahoo code*/ 
.ReadMsgBody {width: 100%;}
.ExternalClass {width: 100%;}
.yshortcuts {color: #ffffff;}
.yshortcuts a span {color: #ffffff;border-bottom: none !important;background: none !important;}
/*Hotmail et Yahoo code*/
body {-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;-webkit-font-smoothing: antialiased;margin: 0 !important;padding: 0 !important;width: 100% !important;}
p {margin: 0px !important;padding: 0px !important;}
.view {display : none;}
td [class="title"] {font-family: Arial, Helvetica, sans-serif, Trebuchet MS;}
.menu a {color: white;text-decoration: none;display: block;text-align: center;text-transform: uppercase;letter-spacing: 0.1em;font-size: 12px;font-weight: bold;font-family: Arial, Helvetica, sans-serif, Trebuchet MS;}
/*-----Responsive code début------*/
@media only screen and (max-width: 649px) {
body {width: auto !important;}
table[class="main"] {max-width: 650px !important;width: 100% !important;}
img[class="banner"] {max-width: 650px !important;width: 100% !important;height: auto;display: block;margin: 0 auto;}
img[class="img1"] {max-width: 100% !important;width: 100% !important;height: auto;display: block;margin: 0 auto;}
.view {display : block;}
td[class="box4"] {max-width: 100% !important;width: 100% !important;margin: 18px auto !important;margin-bottom: inherit !important;display: block;border-bottom: 1px solid #222222;min-height: 55px;}
td[class="menu"] {max-width: 100% !important;width: 100% !important;margin: 18px auto !important;margin-top: inherit !important;display: block;background-color: #686868;}
td[class="cache1"] {width: 0px;display: none;}
img[class="logo"] {width: 272px;height: 70px;}
.menu a {font-size: 14px;}
}
@media only screen and (max-width: 479px) {
body {width: auto !important;}
table[class="main"] {max-width: 650px !important;width: 100% !important;}
td[class="social"] {max-width: 173px !important;width: 100% !important;margin: 0 auto !important;display: block;text-align: center !important;}
td[class="box1"] {max-width: 100% !important;width: 100% !important;margin: 0 auto !important;display: block;text-align: center !important;border-bottom: 1px solid #aaaaaa;}
td[class="box2"] {max-width: 100% !important;width: 100% !important;margin: 25px auto !important;margin-bottom: inherit !important;display: block;text-align: center !important;}
td[class="box3"] {max-width: 100% !important;width: 100% !important;margin: 18px auto !important;margin-bottom: inherit !important;display: block;}
td[class="cache2"] {width: 0px;display: none;}
td[class="button"] {max-width: 144px !important;width: 100% !important;margin: 0 auto !important;display: block;}
td[class="bg2"] {height: 240px !important;}
td[class="box4"] {min-height: 70px;}
}
/*-----Responsive code fin------*/
</style>{/literal}
</head>
<body style="margin:0px;" bgcolor="#eeeeee">
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#eeeeee">
    <tr>
        <td bgcolor="#ffffff" align="center">
        	{include file="$tpl/entete/templates_entetes/et$langue.tpl"}
        </td>
    </tr>
    <tr>
        <td height="20" bgcolor="#ffffff"></td>
    </tr>
    <!--==============header début===============-->
    <tr>
      <td bgcolor="#ffffff"><table width="650" border="0" cellspacing="0" cellpadding="0" align="center" class="main">
            <tr>
              <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#202125">
                  
                    <tr>
                      <td height="30"></td>
                    </tr>
                    <!--==============logo début===============-->
                    <tr>
                      <td align="center" style="color:#FFFFFF;">{include file="$tpl/bandeaux/bandeau.tpl"}</td>
                    </tr>
                    <!--==============logo fin===============-->
                    <tr>
                      <td height="30"></td>
                    </tr>
                    <tr>
                      <td>{include file="$tpl/menus/structure_menu.tpl"}</td>
                    </tr>
                    <!--==============banner début===============-->
                    <tr>
                      <td height="20" bgcolor="#ffffff"></td>
                    </tr>
                    <tr>
                      <td class="title" bgcolor="#ffffff" style="font-size:27px; text-align:center; text-transform:uppercase; color:#000;">Découvrez 300 Grands vins venus d’Italie</td>
                    </tr>
                    <tr>
                      <td height="20" bgcolor="#ffffff"></td>
                    </tr>
                    {if $bdunq}<tr><td valign="top" bgcolor="#ffffff">
{include file="$tpl/block_image/templates_images/bandeau_unique.tpl"}
</td></tr>{/if}
{if $bdtrch}<tr><td valign="top" bgcolor="#ffffff">
{include file="$tpl/block_image/templates_images/bandeaux_horizontaux.tpl"}
</td></tr>
{/if}
{if $bd12x21}<tr><td valign="top" bgcolor="#ffffff">
{include file="$tpl/block_image/templates_images/1-2x2-1.tpl"}
</td></tr>
{/if}
{if $bdbas}<tr><td valign="top" bgcolor="#ffffff">
{include file="$tpl/block_image/templates_images/bandeau_bas.tpl"}
</td></tr>{/if}
                    <!--==============banner fin===============-->
                </table></td>
            </tr>
        </table></td>
    </tr>
    <!--==============header fin===============--> 
    <!--==============content début===============-->
    <tr>
      <td valign="top"><table width="650" border="0" cellspacing="0" cellpadding="0" align="center" class="main">
            <tr>
              <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0">
<!--==============Bloc push debut===============-->   
                            <tr>
                              <td height="25"></td>
                            </tr>
                            <tr>
                              <td bgcolor="#eeeeee" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0">
                                  
                                    <tr>
                                      <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0">
                                          
                                            <tr>
                                              <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                  
                                                    <tr>
                                                      <td width="315" valign="top" class="box1">{include file="$tpl/push/push.tpl"}</td>
                                                      <td width="20" class="cache2"></td>
                                                      <td width="5" bgcolor="#9a9999" class="cache2"></td>
                                                      <td valign="top" class="box2">{include file="$tpl/contact/structure_contact.tpl"}</td>
                                                      <td width="5" bgcolor="#9a9999" class="cache2"></td>
                                                    </tr>
                                                  
                                                </table></td>
                                            </tr>
                                          
                                        </table></td>
                                    </tr>
                                  
                                </table></td>
                            </tr>
                    <!--==============left image fin===============-->
                    <tr>
                      <td height="25"></td>
                    </tr>
                  
                </table></td>
            </tr>
          
        </table></td>
    </tr>
    
    <!--==============content fin===============-->
    
    <tr>
      <td bgcolor="#909090"><table width="650" border="0" cellspacing="0" cellpadding="0" align="center" class="main">
          
            <tr>
              <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
                  
                    <tr>
                      <td height="15"></td>
                    </tr>
                    <tr>
                      <td>{include file="$tpl/reassurance/structure_reassurance.tpl"}</td>
                    </tr>
                    <tr>
                      <td height="15"></td>
                    </tr>
                  
                </table></td>
            </tr>
          
        </table></td>
    </tr>
    <!--==============footer début===============-->
    <tr>
      <td bgcolor="#202125"><table width="650" border="0" cellspacing="0" cellpadding="0" align="center" class="main">
          
            <tr>
              <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0">
                  <!--==============image début===============-->
                  
                    <!--==============image fin===============-->
                    <tr>
                      <td height="16"></td>
                    </tr>
                    <tr>
                      <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
                          
                            <tr>
                              <td width="15" class="view"></td>
                              <td style="font-family:Arial, Helvetica, sans-serif, Trebuchet MS; font-size:13px; line-height:18px; color:#ffffff; text-align:justify;">{include file="$tpl/cgv/$typecgv/cgv_$typecgv$country.tpl"}</td>
                              <td width="15" class="view"></td>
                            </tr>
                          
                        </table></td>
                    </tr>
                    <tr>
                      <td height="8"></td>
                    </tr>
                    <tr>
                      <td style="font-family:Arial, Helvetica, sans-serif, Trebuchet MS; font-size:13px; line-height:22px; color:#ffffff; text-align:center; padding-top:8px;">{include file="$tpl/desabo/structure_desabo.tpl"}</td>
                    </tr>
                    <tr>
                      <td height="15"></td>
                    </tr>
                    <!--==============social icon début===============-->
                    <tr>
                      <td>{include file="$tpl/social/structure_social.tpl"}</td>
                    </tr>
                    <!--==============social icon fin===============-->
                    <tr>
                      <td height="20"></td>
                    </tr>
                  
                </table></td>
            </tr>
          
        </table></td>
    </tr>
    <!--==============footer fin===============-->

	<tr>
    	<td bgcolor="#ffffff" align="center">
            <table width="650" border="0" cellspacing="0" cellpadding="0" align="center" class="main">
            	<tr>
                  	<td height="15"></td>
                </tr>
                <tr>
                    <td bgcolor="#ffffff" align="center" style="font-family:Arial, Helvetica, sans-serif, Trebuchet MS; font-size:11px; text-align:center; padding-top:8px;">{include file="$tpl/pied_page/pp$country.tpl"}</td>
                </tr>
                <tr>
                  	<td height="15"></td>
                </tr>
            </table>
        </td>
	</tr>


</table>
</body>
</html>