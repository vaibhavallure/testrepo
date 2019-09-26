<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
    <tr>
        <td>{if isset($lstprmodesc)}<a href="{$lstpromotab.url}">{elseif $bdunq_url != ''}<a href='{$bdunq_url}'>{/if}
                    <img src="http://cdn.millesima.com.s3.amazonaws.com/{$type_message}/{$codemessagegeneral}/bandeau{if $exception}{$country}{else}{$langue}{/if}.{$bdunq_extension}"
                         border="0" width="650" height="{$bdunq_height}" alt="{$objet_alt_title}"
                         title="{$objet_alt_title}" style="display:block;"
                         class="banner"/>{if isset($lstprmodesc) or $bdunq_url != ''}</a>{/if}</td>
    </tr>
</table>
