{if $country eq 'U'}
<a href="{$push_url}" target="_blank"><img src="{if $typepush == 'message'}https://cdn.millesima.com/ios/{$codemessagegeneral}/push{if $push_exception}{$country}{else}{$langue}{/if}.{$push_type_image}{else}https://cdn.millesima.com/templates/push/push_{$typepush}/push{if $push_exception}{$country}{else}{$langue}{/if}_{$typepush}.{$push_type_image}{/if}" border="0" width="315" height="160" alt="{$libellepush}" title="{$libellepush}" style="display:block;" class="img1" /></a>
{else}
<a href="{$push_url}" target="_blank"><img style="display:block;" src="{if $typepush == 'message'}https://cdn.millesima.com/ios/{$codemessagegeneral}/push{if $push_exception}{$country}{else}{$langue}{/if}.{$push_type_image}{else}https://cdn.millesima.com/templates/push/push_{$typepush}/push{if $push_exception}{$country}{else}{$langue}{/if}_{$typepush}.{$push_type_image}{/if}" width="315" height="210" alt="{$libellepush}" border="0" class="img1"></a>
{/if}
