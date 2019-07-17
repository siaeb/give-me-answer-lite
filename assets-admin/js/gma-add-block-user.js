jQuery( document ).ready( function( $ ) {

    $( '[name=gma_add_block_user]' ).click( function( e ) {
        var username = $( '[name=gma_block_user_name]' ).val();
        var reason   = $( '[name=gma_block_reason]' ).val();
        $.ajax( {
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'gma-block-user',
                username: username,
                reason: reason,
                _wpnonce: gma_params.nonce,
            },
            beforeSend: function() { window.question_answer.showPleaseWait(); },
            success: function( response ) {
                if ( response.success === true ) {
                    tb_remove();
                    $( '[name=gma_block_user_name], [name=gma_block_reason]' ).val( '' );
                    window.question_answer.showSuccessMessage('', gma_params.l10n.blocked);
                    window.question_answer.parseLtResponse( 'blocked-users', response.data );
                } else {
                    window.question_answer.showErrorMessage('', response.data);
                }
            },
            complete: function() { window.question_answer.hidePleaseWait(); },
        } );
        e.preventDefault();
        return false;
    } );


    $( document ).on( 'click', '.blocked-users .gma-delete', function( e ) {
        var username = $( this ).closest( 'tr' ).find( '.user_login' ).text();

        var onYes = function() {
            $.ajax( {
                url: ajaxurl,
                type: 'POST',
                data: {
                    action  : 'gma-unblock-user',
                    username: username,
                    _wpnonce: gma_params.nonce,
                },
                beforeSend: function() {
                    window.question_answer.showPleaseWait();
                },
                success: function( response ) {
                    if ( response.success === true ) {
                        window.question_answer.parseLtResponse('blocked-users', response.data);
                    } else {
                        window.question_answer.showErrorMessage('', response.data);
                    }
                },
                complete: function() {
                    window.question_answer.hidePleaseWait();
                },
            } );
        };

        window.question_answer.showConfirm('', gma_params.l10n.remove, onYes);

        e.preventDefault();
        return false;
    } );

} );