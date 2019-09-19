{if isset($promo) and $promo != ''}
<tr><td height="32" align="center" valign="middle" style="background-color:#654337;color:#FFFFFF;text-align:center;background-image:url(http://cdn.millesima.com.s3.amazonaws.com/templates/listing/picto-promo.png);background-position:center;">
{$promos.$promo.libelle}
</td></tr>
<tr><td height="7" style="font-size:5px;">&nbsp;</td></tr>
{elseif $type == 'primeurs' OR $isprimeur}
<tr><td height="32" align="center" valign="middle" style="color:#654337;text-align:center;">
{$phraseprimeur}
</td></tr>
<tr><td height="7" style="font-size:5px;">&nbsp;</td></tr>
{elseif $type == 'livrable'}
<tr><td height="32" align="center" valign="middle" style="color:#654337;text-align:center;">&nbsp;

</td></tr>
<tr><td height="7" style="font-size:5px;">&nbsp;</td></tr>
{/if}