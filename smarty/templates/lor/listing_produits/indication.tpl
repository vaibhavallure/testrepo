{if isset($promo) and $promo != ''}
<tr><td align="center" valign="middle" style="font-size:12px;font-family: Arial,Helvetica,sans-serif;color:{$codecouleur};padding: 10px;">
{$promos.$promo.libelle}
</td></tr>
{elseif $type == 'primeurs' OR $isprimeur}
<tr><td align="center" valign="middle" style="font-size:12px;font-family: Arial,Helvetica,sans-serif;color:{$codecouleur};">
{$phraseprimeur}
</td></tr>
{elseif $type == 'livrable'}
<tr><td align="center" valign="middle" style="font-size:12px;font-family: Arial,Helvetica,sans-serif;color:{$codecouleur};">&nbsp;
</td></tr>
{/if}