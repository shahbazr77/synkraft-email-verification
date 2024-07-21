jQuery(document).ready(function($){
    if(jQuery("#resend-email").length > 0) {
        jQuery("#resend-email").on('click', function () {
             var user_id = jQuery(this).data("user-id");
            var email_send_count=jQuery(this).data("resend-count");
            jQuery.post(synk_email_string.ajax_url, {
                action: 'send_verify_email',
                nonce: synk_email_string.nonce,
                userid: user_id,
                resend_count:email_send_count,
            }).done(function (response) {
                if(response.success === true) {
                    console.log(response.data.message);
                    jQuery("#email-name").html(response.data.email_name);
                    jQuery("#resend-count").val(response.data.email_count);
                    window.location.href = response.data.url_path;
                }else {
                    jQuery("#custom-msg").html(response.data.message);
                }

            })

        })
    }

})