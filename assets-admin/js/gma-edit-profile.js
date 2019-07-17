jQuery( document ).ready( function ($) {

    $( '.gma-userimage' ).click( function() {
        $( '#picture' ).click();
        return false;
    } );

    $( '#picture' ).change( function() {
        var pic     = this.files[0];
        var picname = pic.name;

        var allowexts  = [];
        allowexts.push( 'jpg', 'jpeg', 'pjpeg', 'bmp', 'png' );
        var ext = picname.split( '.' );
        ext  = ext[ ext.length - 1 ];
        ext = ext.toLowerCase();
        var isAllow = $.inArray( ext , allowexts ) == -1 ? false :  true;
        if ( !isAllow ) return;

        var size = pic.size;
        var maxsizeINKB = gma_params.max_upload_size_kb;
        var maxsizeINB  = maxsizeINKB * 1024;

        if (  size > maxsizeINB ) {
            alert(  gma_params.l10n.max_upload_size_err );
            return;
        }

        if (this.files && this.files[0]) {
            var reader = new FileReader();
            var file   = this.files[ 0 ];

            var formData = new FormData();
            formData.append( 'action', 'gma-upload-profile-picture' );
            formData.append( 'user_id', gma_params.user_id );
            formData.append( '_wpnonce', gma_params._wpnonce );
            formData.append( 'picture', $(':file')[0].files[0] );

            $.ajax({
                url : gma_params.ajax_url,
                type : 'POST',
                data : formData,
                timeout: 23000,
                processData: false,  // tell jQuery not to process the data
                contentType: false,  // tell jQuery not to set contentType
                beforeSend: function() {
                    window.question_answer.showPleaseWait();
                },
                success : function( response ) {
                    if ( response.success === true ) {
                        reader.onload = function(e) {
                            $('.gma-profile-header-img .gma-userimage').attr('src', e.target.result);
                        };
                        reader.readAsDataURL(file);

                        window.question_answer.showSuccessMessage('', gma_params.l10n.saved);
                    } else {
                        window.question_answer.showErrorMessage('', response.data);
                    }
                },
                complete: function() {
                    window.question_answer.hidePleaseWait();
                }
            });
        }

    } );

    $( '.save-profile' ).click( function ( e ) {
        var formData = new FormData();

        var self          = $( this ),
            selfText      = self.html(),
            firstname     = $( '[name=firstname]' ).val(),
            lastname      = $( '[name=lastname]' ).val(),
            mobile        = $( '[name=mobile]' ).val(),
            aboutme       = $( '[name=aboutme]' ).val(),
            university    = $( '[name=university]' ).val(),
			oldpass       = $( '[name=oldpass]' ).val(),
            newpass       = $( '[name=newpass]' ).val(),
            newpassagain  = $( '[name=newpassagain]' ).val(),
            nonce         = $( '[name=_wpnonce]' ).val();

        formData.append( 'action', 'gma-save-profile' );
        formData.append( 'firstname', firstname );
        formData.append( 'lastname', lastname );
        formData.append( 'mobile', mobile );
        formData.append( 'university', university );
        formData.append( 'about', aboutme );
		formData.append( 'user_id', gma_params.user_id );
		formData.append( 'oldpass', oldpass );
        formData.append( 'newpass', newpass );
        formData.append( 'newpassagain', newpassagain );
        formData.append( '_wpnonce', nonce );

        if ( $( ':file' ).val() ) {
            formData.append( 'picture', $(':file')[0].files[0] );
        }

        $.ajax({
            url : gma_params.ajax_url,
            type : 'POST',
            data : formData,
            timeout: 23000,
            processData: false,  // tell jQuery not to process the data
            contentType: false,  // tell jQuery not to set contentType
            beforeSend: function() {
                self.addClass( 'disabled-content' );
                window.question_answer.showPleaseWait();
            },
            success : function( response ) {
                if ( response.success === true ) {
                    window.question_answer.showSuccessMessage('', gma_params.l10n.saved);
                } else {
                    window.question_answer.showErrorMessage('', response.data);
                }
            },
            complete: function() {
                self.removeClass( 'disabled-content' );
                window.question_answer.hidePleaseWait();
            }
        });

        e.preventDefault();
        return false;
    } );

} );