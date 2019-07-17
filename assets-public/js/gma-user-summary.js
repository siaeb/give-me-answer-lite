jQuery(document).ready( function ($) {
    function inittippy() {
        tippy('.gma-user-summary', {
            content: gma_user_summary_params.l10n['loading'],
            animateFill: true,
            animation: 'fade',
            // This option is recommended if your tooltip changes size while showing
            flipOnUpdate: true,
            hideOnClick: false,
            distance: 1,
            interactive: true,
            theme: 'google',
            onShow: function(instance) {

                // We can monkey-patch the instance's state object with our own state
                if (instance.state.ajax === undefined) {
                    instance.state.ajax = {
                        isFetching: false,
                        canFetch: true,
                    }
                }

                // Now we will avoid initiating a new request unless the old one
                // finished (`isFetching`).
                // We also only want to initiate a request if the tooltip has been
                // reset back to Loading... (`canFetch`).
                if (instance.state.ajax.isFetching || !instance.state.ajax.canFetch) {
                    return
                }

                var userid = $(instance.reference).data('user-id');
                var nonce  = $(instance.reference).data('nonce');
                $.ajax( {
                    url: gma_user_summary_params.ajaxurl,
                    type: 'GET',
                    data: {
                        action: 'gma-get-user-summary',
                        userid: userid,
                        _wpnonce: nonce,
                    },
                    success: function(response) {
                        instance.setContent(response.data);
                    },
                    complete: function () {
                        instance.state.ajax.isFetching = false
                    },
                } );
            },
            onHidden: function (instance) {
                instance.setContent('Loading...');
                instance.state.ajax.canFetch = true
            }
        });
    }
    inittippy();
    window.gma.tippy = {
        refresh: function () {
            inittippy();
        }
    };
} );