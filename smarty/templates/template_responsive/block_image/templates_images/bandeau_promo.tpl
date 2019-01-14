<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr>
<td>{if $lstprmodesc}<a href="{$lstpromotab.url}">{elseif $bdunq_url != ''}<a href="{$bdunq_url}">{/if}<img src="http://cdn.millesima.com.s3.amazonaws.com/templates/block_image/{$lstpromotab.rep}/bandeau{if $exception}{$country}{else}{$langue}{/if}.jpg" border="0" width="650" height="400" alt="{$objet_alt_title}" title="{$objet_alt_title}" style="display:block;"class="banner" />{if $lstprmodesc or $bdunq_url != ''}</a>{/if}</td>
</tr>
</table>
