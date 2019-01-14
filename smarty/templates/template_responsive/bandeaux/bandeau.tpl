{if $country eq 'U'}
<a href="{$siteweb}?{$tracking}"><img src='http://cdn.millesima.com.s3.amazonaws.com/templates/logoU.png' width="350" height="90" alt="Millesima Bringing fine wine to you" title="Millesima Bringing fine wine to you" border="0" class="logo" style="display:block;" /></a>
{elseif $country eq 'H' or $country eq 'SG'}
<a href="{$siteweb}?{$tracking}"><img src='http://cdn.millesima.com.s3.amazonaws.com/templates/logoH.png' width="350" height="90" alt="Mill&eacute;sima specialized in fine wine since 1983" title="Mill&eacute;sima specialized in fine wine since 1983" border="0" class="logo" style="display:block;" /></a>
{else}
<a href="{$siteweb}?{$tracking}"><img src='http://cdn.millesima.com.s3.amazonaws.com/templates/logo.png' width="350" height="90" alt="Mill&eacute;sima Bordeaux" title="Mill&eacute;sima Bordeaux" border="0" class="logo" style="display:block;" /></a>
{/if}