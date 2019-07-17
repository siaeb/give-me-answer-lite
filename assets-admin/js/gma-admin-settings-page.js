jQuery(document).ready(function($) {

    function hide_item(element,element2) {
        if ($(element).is(':checked')) {
            $(element2).attr('disabled','disabled');
            if ($(element2).is(':checked')) {
                $(element2).removeAttr('checked');
            }
        } else {
            $(element2).removeAttr('disabled');
        }
    }

    hide_item('#gma_options_gma_show_all_answers','#gma_setting_answers_per_page');
    hide_item('#gma_options_gma_disable_question_status','#gma_options_enable_show_status_icon');

    $('#gma_options_gma_show_all_answers').on('change',function() {
        hide_item(this,'#gma_setting_answers_per_page');
    });

    $('#gma_options_gma_disable_question_status').on('change',function(){
        hide_item(this,'#gma_options_enable_show_status_icon');
    });

    $('#gma-message').on('click', function(e){
        document.cookie = "qa-pro-notice=off";
    });


    $(".chosen-select").chosen({disable_search_threshold: 10, rtl: false, width: '30%'});

    $( '[data-toggle-row]' ).change( function() {
        var isChecked = $( this ).is( ':checked' );
        var rowID     = $( this ).attr( 'data-toggle-row' );
        if ( isChecked ) {
            $( '#' + rowID).fadeIn();
        } else {
            $( '#' + rowID).hide();
        }
    } );

    $( '#save-voting' ).click( function( e ) {
        var data = $( 'form' ).serialize();

        $.ajax( {
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'gma-save-settings',
                data: data,
            },
            beforeSend: function() {
                window.question_answer.showPleaseWait();
            },
            success: function( response ) {
                if ( response.success === true ) {
                    window.question_answer.showSuccessMessage('', gma_params.l10n.saved);
                } else {
                    window.question_answer.showErrorMessage('', response.data);
                }
            },
            complete: function() {
                window.question_answer.hidePleaseWait();
            },
        } );

        return false;
    } );

    $( '#save-avatar-settings' ).click( function ( e ) {
        var unchecked = $('form').find(':checkbox:not(:checked)');
        unchecked.attr('value', '0').prop('checked', true);
        var data = $( 'form' ).serialize();
        unchecked.attr('value', '1').prop('checked', false);

        $.ajax( {
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'gma-save-settings',
                data: data,
            },
            beforeSend: function() {
                window.question_answer.showPleaseWait();
            },
            success: function (response) {
                if ( response.success === true ) {
                    window.question_answer.showSuccessMessage('', gma_params.l10n.saved);
                } else {
                    window.question_answer.showErrorMessage('', response.data);
                }
            },
            complete: function () {
                window.question_answer.hidePleaseWait();
            }
        } );

        e.preventDefault();
    } );

    $( '#save-pages-settings' ).click( function( e ) {
        var data = $( 'form' ).serialize();

        $.ajax( {
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'gma-save-pages-settings',
                data: data,
            },
            beforeSend: function() {
                window.question_answer.showPleaseWait();
            },
            success: function( response ) {
                if ( response.success === true ) {
                    window.question_answer.showSuccessMessage('', gma_params.l10n.saved);
                } else {
                    window.question_answer.showErrorMessage('', response.data);
                }
            },
            complete: function() {
                window.question_answer.hidePleaseWait();
            },
        } );

        e.preventDefault();
    } );

    $( '#save-settings' ).click( function (e) {
        var unchecked = $('form').find(':checkbox:not(:checked)');
        unchecked.attr('value', '0').prop('checked', true);
        var data = $( 'form' ).serialize();
        unchecked.attr('value', '1').prop('checked', false);
        $.ajax( {
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'gma-save-settings',
                data: data,
            },
            beforeSend: function() {
                window.question_answer.showPleaseWait();
            },
            success: function (response) {
                if ( response.success === true ) {
                    window.question_answer.showSuccessMessage('', gma_params.l10n.saved);
                } else {
                    window.question_answer.showErrorMessage('', response.data);
                }
            },
            complete: function () {
                window.question_answer.hidePleaseWait();
            }
        } );
        e.preventDefault();
    } );

});