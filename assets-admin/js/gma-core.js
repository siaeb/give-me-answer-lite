jQuery(document).ready(function( $ ) {

    var is_rtl = gma_globals.is_rtl == 1 ? true : false;


    var globalMessages = {
        serverTimeout: gma_globals.l10n.timeout,
        successMessage: gma_globals.l10n.success,
        failureMessage: gma_globals.l10n.failure,
        loadingMessage: gma_globals.l10n.loading,
        updateMessage: gma_globals.l10n.operation.update,
        deleteMessage: gma_globals.l10n.operation.delete,
        insertMessage: gma_globals.l10n.operation.insert,
        escapeRegExp: function( str ) {
            return str.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
        },
        numberWithCommas: function( x ) {
            return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        },
        replaceAll: function(str, find, replace) {
            return str.replace(new RegExp(this.escapeRegExp(find), 'g'), replace);
        },
        parseParams: function( strParams ) {
            return str.split('&').reduce(function (params, param) {
                var paramSplit = param.split('=').map(function (value) {
                    return decodeURIComponent(value.replace(/\+/g, ' '));
                });
                params[paramSplit[0]] = paramSplit[1];
                return params;
            }, {});
        },
        showLoadingOnBtn: function( objBtn, loadingText, showLoadingSpinner ) {
            if ( !(objBtn instanceof jQuery ) ) objBtn = $( objBtn );
            if ( typeof loadingText === 'undefined') loadingText = this.loadingMessage;
            if ( typeof showLoadingSpinner === 'undefined' ) showLoadingSpinner = false;
            if ( showLoadingSpinner  ) {
                loadingText = ' <i class="fa fa-spin fa-spinner"></i> ' + loadingText;
            }
            objBtn.html( loadingText ).attr( 'disabled', 'disabled' );
        },
        hideLoadingOnBtn: function( objBtn, text ) {
            if ( !(objBtn instanceof jQuery ) ) objBtn = $( objBtn );
            objBtn.html( text ).removeAttr( 'disabled' );
            objBtn.find(  'div.rippler-div' ).remove();
        },
        parseLtResponse: function( tableClassName, response ) {

            // Add the requested rows
            if ( response.rows.length )
                $( 'table.' + tableClassName +  ' tbody').html( response.rows );

            // Update column headers for sorting
            if ( response.column_headers.length ) {
                var table = 'table.'  + tableClassName;
                $( table + ' thead tr, ' + tableClassName +' tfoot tr').html( response.column_headers );
            }

            // Update pagination for navigation
            if ( response.pagination.top.length )
                $('div.pagination-container').html( response.pagination.bottom );
            else
                $('div.pagination-container').html( '' );

        },
        parseAjaxErrorMessage: function( response ) {
            var err = this.failureMessage;
            if ( response.hasOwnProperty( 'data' ) ) {
                err = response.data;
            }
            this.showErrorMessage('', err);
        },
        showPleaseWait: function( txt ) {
            NProgress.start();
        },
        hidePleaseWait: function() {
            NProgress.done();
        },
        showSuccessMessage: function(title, message) {
            iziToast.success({
                title: title,
                message: message,
                rtl: is_rtl,
                position: is_rtl ? 'bottomRight' : 'bottomLeft',
                animateInside: false,
            });
        },
        showErrorMessage: function(title, message, target) {
            var options = {
                title: title,
                message: message,
                rtl: is_rtl,
                position: is_rtl ? 'bottomRight' : 'bottomLeft',
                animateInside: false,
            };
            if ( typeof target !== 'undefined') {
                options.target = target;
            }
            iziToast.error( options );
        },
        showConfirm: function( title, message, onConfirm ) {
            iziToast.error({
                timeout: false,
                title: title,
                message: message,
                icon: 'fa fa-warning',
                theme: 'dark',
                position: 'center', // bottomRight, bottomLeft, topRight, topLeft, topCenter, bottomCenter
                progressBarColor: 'rgb(0, 255, 184)',
                transitionIn: 'fadeIn',
                transitionOut: 'flipOutX',
                rtl: is_rtl,
                close: false,
                animateInside: false,
                closeOnClick: false,
                messageSize: '16px',
                overlay: true,
                buttons: [
                    [
                        '<button>' + gma_globals[ 'l10n' ]['yes'] + '</button>',
                        function (instance, toast) {
                            instance.hide({
                                transitionOut: 'fadeOutUp'
                            }, toast);

                            if ( typeof onConfirm === 'function') {
                                onConfirm();
                            }
                        }
                    ],
                    [
                        '<button>' + gma_globals[ 'l10n' ]['no'] + '</button>',
                        function (instance, toast) {
                            instance.hide({
                                transitionOut: 'fadeOutUp'
                            }, toast);
                        }
                    ]
                ]
            });
        },
    };

    window.question_answer = globalMessages;
});