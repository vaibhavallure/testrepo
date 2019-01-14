<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
{foreach from=$bandeauxArray key=bd item=value}<tr>
<td>{if $bandeauxArray.$bd.url != ''}<a href="{$bandeauxArray.$bd.url}">{/if}<img src="http://cdn.millesima.com.s3.amazonaws.com/{$type_message}/{$codemessagegeneral}/bandeau{if $bandeauxArray.$bd.exception}{$country}{else}{$langue}{/if}_{$bandeauxArray.$bd.bdnb}.{$bandeauxArray.$bd.extension}" border="0" width="650" height="{$bandeauxArray.$bd.height}" alt="{$objet_alt_title}" title="{$objet_alt_title}" style="display:block;" class="banner"/>{if $bandeauxArray.$bd.url != ''}</a>{/if}</td>
</tr>{/foreach}
</table>
