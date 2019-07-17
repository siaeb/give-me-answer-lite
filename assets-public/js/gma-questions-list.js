jQuery(function($){
	// Search form
	$('form#gma-search input').autocomplete({
		appendTo: 'form#gma-search',
		source: function( request, resp ) {
			$.ajax({
				url: gma.ajax_url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'gma-auto-suggest-search-result',
					title: request.term,
					nonce: $('form#gma-search input').data('nonce')
				},
				success: function( data ) {
					resp( $.map( data.data, function( item ) {
						if ( true == data.success ) {
							return {
								label: item.title,
								value: item.title,
								url: item.url,
							}
						} else {
							return {
								label: item.message,
								value: item.message,
								click: false
							}
						}
					}))
				}
			});
		},
		select: function( e, ui ) {
			if ( ui.item.url ) {
				window.location.href = ui.item.url;
			} else {
				if ( ui.item.click ) {
					return true;
				} else {
					return false;
				}
			}
		},
		open: function( e, ui ) {
			var acData = $(this).data( 'uiAutocomplete' );
			acData.menu.element.addClass('gma-autocomplete').find('li').each(function(){
				var $self = $(this),
					keyword = $.trim( acData.term ).split(' ').join('|');
					$self.html( $self.text().replace( new RegExp( "(" + keyword + ")", "gi" ), '<span class="gma-text-highlight">$1</span>' ) );
			});
		},
		minLength: 3,
	});

	 $('form#gma-search input').keyup( function() {
    	if ( $( this ).val().trim() == '' ) {
    		$( '.ui-autocomplete-loading' ).removeClass( 'ui-autocomplete-loading' );
		}
	} );

	$.urlParam = function (url, name) {
		var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(url);
		return (results !== null) ? results[1] || 0 : false;
	};

	$( document ).on( 'click', '.gma-page-numbers', function (e) {
		var href = $( this ).attr( 'href' );
		var pagenumber = $.urlParam(href, 'paged');
		if ( typeof  pagenumber === 'undefined' || ! pagenumber ) {
			pagenumber = $.urlParam(href, 'page');
		}

		if ( ! pagenumber ) pagenumber = 1;

		var query_vars 		   = {};
		query_vars.paged 	   = gma.query_vars.paged;
		query_vars.post_type   = gma.query_vars.post_type;
		query_vars.meta_key    = gma.query_vars.meta_key;
		query_vars.meta_query  = gma.query_vars.meta_query;
		query_vars.post_status = gma.query_vars.post_status;
		query_vars.author__in  = gma.query_vars.author__in;

		if ( gma.query_vars.hasOwnProperty( 'tax_query' ) ) {
			query_vars.tax_query = gma.query_vars[ 'tax_query' ];
		}


		jQuery.ajax( {
			url: gma.ajaxurl,
			type: 'GET',
			data: {
				action	  : 'gma-questions-list',
				paged     : pagenumber,
				vars	  : JSON.stringify( query_vars ),
				_wpnonce  : gma._wpnonce,
			},
			beforeSend: function () {
				window.question_answer.showPleaseWait();
			},
			success: function (response) {
				if ( response.success === true ) {
					jQuery( '.gma-questions-list' ).closest('.gma-questions-list-wrapper').html( response.data );

					$('html, body').animate({
						scrollTop: $('.gma-questions-list').offset().top - $( '#wpadminbar' ).outerHeight()
					}, 800);
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
