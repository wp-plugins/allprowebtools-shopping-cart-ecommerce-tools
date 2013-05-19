function ajaxloadpost_loadpost(name,email,prodid,nonce) {
    jQuery.ajax({
        type: 'POST',
        url: ajaxloadpostajax.ajaxurl,
        data: {
            action: 'ajaxloadpost_ajaxhandler',
            name: jQuery( '#name' ).val(),
            email: jQuery( '#email' ).val(),
            prodid: jQuery( '#prodid' ).val(),
            nonce: nonce
        },
        success: function(data, textStatus, XMLHttpRequest) {
            //var loadpostresult = '#dialog-form';
            var loadpostresult = '#loadpostresult';
            jQuery(loadpostresult).html('');
            jQuery(loadpostresult).append(data);
        },
        error: function(MLHttpRequest, textStatus, errorThrown) {
            alert(errorThrown);
        }
    });
}