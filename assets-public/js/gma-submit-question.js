(function($){

	jQuery.validator.addMethod( 'mintags', function( value, element ) {
		if ( gma.qs_min_tags > 0 && value.length < gma.qs_min_tags ) {
			return false;
		}
		return true;
	} );

	$('#question-title').autocomplete({
			appendTo: '.gma-search',
			source: function( request, resp ) {
				$.ajax({
					url: gma.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'gma-auto-suggest-search-result',
						title: request.term,
						nonce: $('#question-title').data('nonce')
					},
					success: function( data ) {
						console.log( data );
						resp( $.map( data.data, function( item ) {
							if ( true == data.success ) {
								return {
									label: item.title,
									value: item.title,
									url: item.url,
								}
							}
						}))
					}
				});
			},
			select: function( e, ui ) {
				keycode = e.which || e.keyCode;

				if ( keycode == 13 ) {
					return true;
				} else {
					if ( ui.item.url ) {
						window.open( ui.item.url );
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
			}
		});

	$( '.gma-content-edit-form' ).validate( {
		rules: {
			"question-title": {
				required: true,
				minlength: gma.qs_min_length,
			},
		},
		messages: {
			"question-title": {
				required: gma['l10n']['title-required'],
				minlength: gma['l10n']['title-min-length'].toString().replace( '%s', gma.qs_min_length  ),
			},
		},
	} );

	var tagifyOpts = {
		maxTags: gma.tags_per_question,
		enforceWhitelist: gma.predefined_tags == 1 ? true : false,
		whitelist: gma.available_terms,
		dropdown : {
			classname : "color-blue",
			enabled   : 1,
			maxItems  : 5
		},
	};

	$( '[name=question-tag]' ).tagify( tagifyOpts );

	$( '[name=gma-question-submit]' ).click( function (e) {
		if ( gma.display_tags == 'on' ) {
			$( '.gma-content-edit-form' ).valid();

			$( '#question_tag_error' ).remove();

			// validate tags
			var tags = $( '[name=question-tag]' ).val();
			if ( gma.qs_min_tags > 0 ) {
				tags = $.parseJSON( tags );
				if ( tags == null || tags.length < gma.qs_min_tags ) {
					var err = '<label id="question_tag_error" class="d-block color-red">';
					err	    += gma['l10n']['min-tags'].toString().replace( '%s', gma.qs_min_tags );
					err  	+= '</label>';
					$( err ).insertAfter( $( '[name=question-tag]' ).closest( '.input-group' ) );
					e.preventDefault();
				} else {
					$( '#question_tag_error' ).remove();
				}
			} else {
				$( '#question_tag_error' ).remove();
			}
		}
	} );

})(jQuery);