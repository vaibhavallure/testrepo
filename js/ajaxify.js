/**
 * Varnish Caching Refresh Using Ajax
 */
function VarnishHeaderRefresh(refreshUrl, callback) {
    new Ajax.Request(
            refreshUrl,
            {
                method: 'get',
                onSuccess: function(transport) {
                    jQuery.each(transport.responseJSON, function(block, content) {
                        jQuery('#'+block).html(content);
                    })
                }
            }
    );
}
;