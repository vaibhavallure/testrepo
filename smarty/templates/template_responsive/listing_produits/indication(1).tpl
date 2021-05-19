{if isset($promo) and $promo != ''}
<tr><td height="32" align="center" valign="middle" style="color:#191919;text-align:center;font-family:Open Sans, Arial, Helvetica, sans-serif, Trebuchet MS;">
{$promos.$promo.libelle}
</td></tr>
<tr><td height="7" style="font-size:5px;">&nbsp;</td></tr>
{elseif $type == 'primeurs' OR $isprimeur}
<tr><td height="32" align="center" valign="middle" style="color:#654337;text-align:center;font-family:Open Sans, Arial, Helvetica, sans-serif, Trebuchet MS;">
{$phraseprimeur}
</td></tr>
<tr><td height="7" style="font-size:5px;">&nbsp;</td></tr>
{elseif $type == 'livrable'}
<tr><td height="32" align="center" valign="middle" style="color:#654337;text-align:center;font-family:Open Sans, Arial, Helvetica, sans-serif, Trebuchet MS;">&nbsp;

</td></tr>
<tr><td height="7" style="font-size:5px;">&nbsp;</td></tr>
{/if}