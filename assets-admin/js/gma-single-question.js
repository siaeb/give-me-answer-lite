(function($){

	var ajax_url = gma.ajax_url;

    NProgress.configure({ showSpinner: false });


	function resetDefaultCaptchaForm(num1, num2) {
        $( '#gma-captcha-number-1' ).val( num1 );
        $( '#gma-captcha-number-2' ).val( num2 );
        $( '.gma-number-one' ).html( num1 );
        $( '.gma-number-two' ).html( num2 );
        $( '#gma-captcha-result' ).val( '' );
    }


    if ( 'tinymce' == gma.editor ) {
        window.gma_editor = {
            clear: function( elemID ) {
                tinyMCE.activeEditor.setContent('');
            },
            getElementText: function( elemID ) {
                tinyMCE.triggerSave();
                return $( '#' + elemID ).val();
            },
        };
    } else if ( 'simple' == gma.editor ) {
        window.gma_editor = {
            clear: function( elemID ) {
                $( '#' + elemID ).val( '' );
            },
            getElementText: function( elemID ) {
                return $( '#' + elemID ).val();
            },
        };
    }


    $( document ).on( 'click', '.gma-vote-up', function(e) {
        e.preventDefault();
        var t = $(this),
            parent = t.parent(),
            id = parent.data('post'),
            nonce = parent.data('nonce'),
            vote_for = 'question';

        if ( parent.hasClass( 'gma-answer-vote' ) ) {
            vote_for = 'answer';
        }

        var data = {
            action: 'gma-action-vote',
            vote_for: vote_for,
            nonce: nonce,
            post: id,
            type: 'up',
        };

        $.ajax({
            url: ajax_url,
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function( response ) {
                if (response.success) {
                    if ( response.data.is_vote ) {
                        t.addClass( 'border-bottom-orange' );
                    } else {
                        t.removeClass( 'border-bottom-orange' );
                    }
                    t.siblings( '.gma-vote-down' ).removeClass( 'border-top-orange' );
                    parent.find('.gma-vote-count').text(response.data.vote);
                } else {
                    if ( response.data.hasOwnProperty( 'error_message' ) ) {
                        window.question_answer.showErrorMessage('', response.data.error_message);
                    }
                }
            },
            error:function( data ) {
                console.log("error",data);
            },
        });
    } );

    $( document ).on( 'click', '.gma-vote-down', function(e) {
        e.preventDefault();
        var t = $(this),
            parent = t.parent(),
            id = parent.data('post'),
            nonce = parent.data('nonce'),
            vote_for = 'question';

        if ( parent.hasClass( 'gma-answer-vote' ) ) {
            vote_for = 'answer';
        }

        var data = {
            action: 'gma-action-vote',
            vote_for: vote_for,
            nonce: nonce,
            post: id,
            type: 'down'
        };

        $.ajax({
            url: ajax_url,
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function( response ) {
                if (response.success) {
                    if ( response.data.is_vote ) {
                        t.addClass( 'border-top-orange' );
                    } else {
                        t.removeClass( 'border-top-orange' );
                    }
                    t.siblings( '.gma-vote-up' ).removeClass( 'border-bottom-orange' );
                    parent.find('.gma-vote-count').text( response.data.vote );
                } else {
                    if ( response.data.hasOwnProperty( 'error_message' ) ) {
                        window.question_answer.showErrorMessage('', response.data.error_message);
                    }
                }
            },
            error:function( data ) {
                console.log("error",data);
            },
        });
    } );

	// delete question
	$( '.gma_delete_question' ).on('click', function(e) {
        var self = $( this ),
            nonce = self.data( 'wpnonce' ),
            question = self.data( 'question' );

		var onYes = function() {
            $.ajax( {
                url: ajax_url,
                type: 'POST',
                data: {
                    action: 'gma-ajax-delete-question',
                    question_id: question,
                    _wpnonce: nonce,
                },
                beforeSend: function( response ) {
                    window.question_answer.showPleaseWait();
                },
                success: function( response ) {
                    if ( response.success === true ) {
                        window.location.href = response.data.list_questions;
                    } else {
                        window.question_answer.parseAjaxResponse( response );
                    }
                },
                complete: function() {
                    window.question_answer.hidePleaseWait();
                },
            } );

        };

		window.question_answer.showConfirm('', gma.l10n[ 'delete-question' ], onYes );

		e.preventDefault();

	});

	$( document ).on( 'click', '.gma_delete_answer', function(e) {

	    e.preventDefault();

        var self   = $( this ),
            container = self.closest( '.gma-answer-item' ),
            action = 'gma-ajax-delete-answer',
            answerID = self.data( 'answer'  ),
            nonce  = self.attr( 'data-wpnonce' );

        var onYes = function() {
            $.ajax( {
                url: ajax_url,
                type: 'POST',
                data: {
                    action: action,
                    answerID: answerID,
                    _wpnonce: nonce,
                },
                beforeSend: function() {
                    container.addClass( 'bg-yellow-light' );
                    container.animateCSS('fadeOut', {
                        duration: 400,
                        callback: function() {
                            container.hide();
                        },
                    } )
                },
                success: function( response ) {
                    if ( response.success === true ) {
                        container.remove();

                        if ( response.data.answers_count > 0 ) {
                            $( '.gma-answers-title' ).text( response.data.answers_title );
                        } else {
                            $( '.gma-answers-subheader' ).hide();
                        }

                        $( '.gma-activity-total-answers' ).text( response.data.total_answers_format );


                        window.question_answer.showSuccessMessage('', gma_globals.l10n[ 'success' ]);

                        if ( response.data.need_refresh === true ) {
                            location.reload();
                        }

                    } else {
                        window.question_answer.showErrorMessage('', gma.l10n[ 'err-delete-answer' ]);
                        container.removeClass( 'bg-yellow-light' );
                        container.show();
                    }
                },
                error: function() {
                    window.question_answer.showErrorMessage('', gma.l10n[ 'server-not-available' ])
                    container.removeClass( 'bg-yellow-light' );
                    container.show();
                },
            } );
        };

        window.question_answer.showConfirm('', gma.l10n[ 'delete-answer' ], onYes );

        e.preventDefault();
        e.stopPropagation();
        return false;
	} );

	$( document ).on( 'click', '.gma-delete-comment', function( e ) {

        var
            self             = $( this ),
            action           = 'gma-ajax-delete-comment',
            commentContainer = self.closest(  '.gma-comment' ),
            nonce            = commentContainer.find( '.gma-comment-id' ).data( 'wpnonce' ),
            commentID        = commentContainer.find( '.gma-comment-id' ).val();

        var onYesCallback = function() {
            $.ajax( {
                url: ajax_url,
                type: 'POST',
                data: {
                    action   : action,
                    commentID: commentID,
                    _wpnonce : nonce,
                },
                beforeSend: function() {
                    commentContainer.addClass( 'bg-yellow-light' );
                    commentContainer.animateCSS( 'flipOutX', {
                        duration: 600,
                        callback: function() {
                            commentContainer.hide();
                        },
                    });
                },
                success: function( response ) {
                    if ( response.success === true ) {
                        commentContainer.remove();
                        $( '.gma-activity-total-comments' ).text( response.data.total_comments );
                    } else {
                        commentContainer.removeClass( 'bg-yellow-light' );
                        commentContainer.show();
                    }
                },
                error: function() {
                    commentContainer.removeClass( 'bg-yellow-light' );
                    commentContainer.show();
                },
                complete: function () {
                    window.question_answer.hidePleaseWait();
                }
            } );
        };

        window.question_answer.showConfirm('', gma.l10n[ 'delete-comment' ], onYesCallback );

        e.preventDefault();
	} );

	// change question status
	$('#gma-question-status').on('change', function(e){
		var t = $(this),
			nonce = t.data('nonce'),
			post = t.data('post'),
			status = t.val(),
			data = {
				action: 'gma-update-privacy',
				post: post,
				nonce: nonce,
				status: status
			};

		$.ajax({
			url: ajax_url,
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function(data) {
            	if ( data.success == false ) {
            		alert( data.data.message );
            	} else {
            		window.location.reload();
            	}
            }
		})
	});

	var originHeight, current_form;
	if ( gma.is_anonymous == 0 ) {
	    $( '.gma-anonymous-fields' ).hide();
    }

	$('.gma-comment-form #comment').on('focus',function(e){
		var t = $(this);

        //Collapse all comment form
        if (current_form && t.get(0) != current_form.get(0)) {
            $('[id^=comment_form_]').each(function(index, el) {
                var comment_form = $(this);
                comment_form.find('.gma-form-submit').hide();
                comment_form.find('textarea').height(comment_form.find('textarea').css('line-height').replace('px', ''));
            });
        }
        current_form = t.closest('.gma-comment-form');
        var lineHeight = parseInt(t.css('line-height').replace('px', '')),
            thisPadding = parseInt(t.css('padding-top').replace('px', '')),
            defaultHeight = (lineHeight + thisPadding) * 3;

        originHeight = t.height();
        var changeHeight = function() {
            var matches = t.val().match(/\n/g);
            var breaks = matches ? matches.length : 0;
            t.height(defaultHeight);
        }

        changeHeight();
        $(this).closest('form').addClass( 'gma-comment-show-button' ).find('.gma-anonymous-fields').slideDown();
        current_form.find('.gma-form-submit').show();
	});

	$( document ).on( 'click', '.gma-vote-best-answer, .gma-unvote-best-answer', function(e) {
        var self   = $( this ),
            nonce  = self.data( 'wpnonce' ),
            post   = self.data( 'post' ),
            type   = '';

        if ( self.hasClass( 'gma-vote-best-answer' ) ) {
            type = 'check';
        } else if ( self.hasClass( 'gma-unvote-best-answer' ) ) {
            type = 'uncheck';
        }

        var data = {
            action	: 'gma-voting-best-answer',
            answer	: post,
            type    : type,
            _wpnonce: nonce,
        };

        $.ajax({
            url: ajax_url,
            type: 'POST',
            dataType: 'json',
            data: data,
            beforeSend: function() {
                window.question_answer.showPleaseWait();
            },
            success: function(response) {
                if ( response.success == true ) {
                    if ( type == 'check' ) {
                        self.removeClass( 'gma-vote-best-answer' );
                        self.addClass( 'gma-unvote-best-answer' );
                        self.closest( '.gma-answer-item' ).addClass( 'gma-best-answer' );

                        self.closest( '.gma-answer-item' ).siblings().removeClass( 'gma-best-answer' );
                        self.closest( '.gma-answer-item' ).siblings().find( '.gma-unvote-best-answer' ).removeClass('gma-unvote-best-answer').addClass('gma-vote-best-answer');

                        self.closest( '.gma-answer-item' ).addClass( 'bg-light-green' );
                        self.closest( '.gma-answer-item' ).animateCSS('pulse', {
                            duration: 600,
                            callback: function () {
                                self.closest( '.gma-answer-item' ).removeClass( 'bg-light-green' );
                            }
                        });
                    } else {
                        self.addClass( 'gma-vote-best-answer' );
                        self.removeClass( 'gma-unvote-best-answer' );
                        self.closest( '.gma-answer-item' ).removeClass( 'gma-best-answer' );

                        window.location.reload();
                    }
                } else {
                    window.question_answer.showErrorMessage('', gma.l10n[ 'operation-error' ]);
                }
            },
            complete: function() {
                window.question_answer.hidePleaseWait();
            },
        });

        e.preventDefault();
        return false;
	} );

	$( document ).on( 'click', '.gma-leave-comment', function( e ) {
        $( this ).parent().hide();

        if ( gma.is_anonymous == 1 ) {
            $( this )
                .parent()
                .siblings( '.gma-comment-form' )
                .show()
                .find( '[name=name]' )
                .focus();
        } else {
            $( this )
                .parent()
                .siblings( '.gma-comment-form' )
                .show()
                .find( '#comment' )
                .focus();
        }



        $( '.gma-leave-comment' ).not( $( this ) ).parent().show();
        $( '.gma-leave-comment' ).not( $( this ) ).parent().next().hide();

        e.preventDefault();
        return false;
	} );

	$( document ).click( function( event ) {
		var target = $( event.target );

		var inCommentForm = target.closest( '#gma-respond' ).length === 1;
        var inEditCommentForm = target.closest( '.gma-comment-form-ajax' ).length === 1;

		if ( ! inCommentForm || inEditCommentForm ) {
			$( '.comment-form' ).parent().hide();
			$( '.gma-leave-comment' ).parent().show();
		}
	} );

	$( document ).on( 'click', '[name=comment-submit]', function( e ) {
        var self = $( this );
        var isFormValid = self.closest( 'form' ).valid();
        if ( false === isFormValid ) return;

        var commentText = self.closest( 'form' ).find( '#comment' ).val();
        var postID      = self.closest( 'form' ).find( '[name=comment_post_ID]' ).val();
        var authorName  = self.closest( 'form' ).find( '[name=name]' ).val();
        var authorEmail = self.closest( 'form' ).find( '[name=email]' ).val();

        if ( commentText.trim() == ''  ) {
            e.preventDefault();
            return;
        }

        $.ajax( {
            url: ajax_url,
            type: 'POST',
            data: {
                action  : 'gma-ajax-add-comment',
                comment : commentText,
                postID  : postID,
                name    : authorName,
                email   : authorEmail,
            },
            beforeSend: function() {
                window.question_answer.showPleaseWait();
                self.addClass( 'content-disabled' );
            },
            success: function( response ) {
                if ( response.success === true ) {
                    var template = $( '#gma-comment-template' ).html();
                    template     = window.question_answer.replaceAll(template, '{comment-id}', response.data.id);
                    template     = $( template );
                    template.find( '.gma-comment-id' ).val( response.data.id );
                    template.find( '.gma-comment-id' ).attr( 'data-wpnonce', response.data.nonce );
                    template.find( '.gma-comment-date' ).find( 'time' ).html( response.data.date_fa ).attr( 'datetime', response.data.date );
                    template.find( '.gma-comment-author' ).attr( 'href', response.data.author.url );
                    template.find( '.gma-comment-author .author-name' ).text( response.data.author.name );
                    template.find( '.gma-comment-user-avatar' ).attr('style', 'background-image: url(' + response.data.author.avatar + ')' );
                    template.find( '.gma-comment-content' ).html( response.data.text );
                    template.hide();
                    if ( response.data.is_anonymous ) {
                        template.find( '.gma-edit-comment, .gma-delete-comment' ).remove();
                        template.find( '.gma-comment-author' ).replaceWith( function() {
                            var contents = $( this ).html();
                            return '<span class="text-info gma-comment-author">' + contents + '</span>';
                        } );
                    } else {
                        if ( response.data.owner == true ) {
                            template.find( '.gma-comment-author' ).addClass( 'gma-owner' );
                        }
                    }

                    self.closest( '.gma-comments').find( '.gma-comments-list' ).append( template );
                    self.closest( '.gma-comments').find( '.gma-leave-comment-parent' ).show();

                    template.addClass( 'bg-yellow-light' );
                    template.fadeIn();
                    setTimeout( function() {
                        template.removeClass( 'bg-yellow-light' );
                    }, 1000 );

                    self.closest( '#gma-respond' ).slideUp( 'fast' );
                    self.closest( '#gma-respond' ).next().show();
                    self.closest( '#gma-respond' ).find( '#comment, [type=text]' ).val( '' ) ;


                    $( '.gma-activity-total-comments' ).text( response.data.total_comments );
                } else {
                    window.question_answer.showErrorMessage('', response.data);
                }
            },
            complete: function() {
                self.removeClass( 'content-disabled' );
                window.question_answer.hidePleaseWait();
            },
        } );


        e.preventDefault();
        return false;
	} );

	$( '.gma_close_question' ).click( function( e ) {
		$( '.gma-close-question' ).slideToggle( 'normal' );

		e.preventDefault();
	} );

	$( '.close-question' ).click( function (e) {
		var self = $( this ), selfText = self.html(),
			questionID = self.data( 'question' ),
			wpnonce    = self.data( 'wpnonce' ),
			reason	   = self.closest( '.gma-close-question' ).find( '[name=reason]' ).val();

		$.ajax( {
			url: ajax_url,
			type: 'POST',
			data: {
				action    : 'gma-ajax-close-question',
				questionID: questionID,
				reason    : reason,
				_wpnonce  : wpnonce,
			},
			beforeSend: function() {
				window.question_answer.showPleaseWait();
				window.question_answer.showLoadingOnBtn( self, selfText );
			},
			success: function( response ) {
				if ( response.success === true ) {
					// Redirect to question list
					window.location.href = response.data.list_questions;
				} else {
					window.question_answer.showErrorMessage('', gma.l10n[ 'operation-error' ]);
				}
			},
			complete: function() {
                window.question_answer.hidePleaseWait();
				window.question_answer.hideLoadingOnBtn( self, selfText );
			},
		} );

		e.preventDefault();
		return false;
    } );

	$( '.gma_open_question' ).click( function( e ) {

		e.preventDefault();

		var self 		= $( this ),
			selfText 	= self.html(),
			wpnonce 	= self.data( 'wpnonce' ),
			questionID  = self.data( 'question' );

        $.ajax( {
            url: ajax_url,
            type: 'POST',
            data: {
                action    : 'gma-ajax-open-question',
                questionID: questionID,
                _wpnonce  : wpnonce,
            },
            beforeSend: function() {
                window.question_answer.showPleaseWait();
            },
            success: function( response ) {
                if ( response.success === true ) {
                    // Refresh page
					window.location.href = response.data.url;
                }
            },
            complete: function() {
                window.question_answer.hidePleaseWait();
            },
        } );

		e.preventDefault();
	} );

    if ( gma.is_anonymous == 1 ) {
        $( '#gma-answer-form' ).validate( {
            rules: {
                "user-email": {
                    required: true,
                    email: true,
                },
                "user-name": {
                    required: true,
                },
            },
            messages: {
                "user-email": {
                    required: gma.l10n[ 'validation' ][ 'email-required' ],
                    email:  gma.l10n[ 'validation' ]['email-not-valid']
                },
                "user-name": {
                    required: gma.l10n[ 'validation' ]['name-required'],
                },
            },
            errorPlacement: function(error, element) {
                error.insertAfter( element.parent() );
            },
        } );
    }

	$( '[name=submit-answer]' ).click( function( e ) {
	    
	    if ( gma.is_anonymous ) {
	        var isFormValid = $( '#gma-answer-form' ).valid();
	        if ( isFormValid === false ) return;
        }

		var self        = $( this ),
			selfText    = self.html(),
			answer      = window[ 'gma_editor' ].getElementText( 'gma-answer-content' ),
			questionID  = $( '[name=question_id]' ).first().val(),
            userName    = $( '[name=user-name]' ).val(),
            userEmail   = $( '[name=user-email]' ).val(),
            capnum1     = $( '#gma-captcha-number-1' ).val(),
            capnum2     = $( '#gma-captcha-number-2' ).val(),
            capresult   = $( '#gma-captcha-result' ).val(),
			wpnonce     = $( '#_wpnonce' ).val();

		$.ajax({
            url: gma.ajax_url,
            type: 'POST',
            data: {
                action: 'gma-ajax-new-answer',
                answer: answer,
                question: questionID,
                userName: userName,
                userEmail: userEmail,
                "gma-captcha-number-1": capnum1,
                "gma-captcha-number-2": capnum2,
                "gma-captcha-result"  : capresult,
                nonce: wpnonce,
            },
            beforeSend: function () {
				window.question_answer.showPleaseWait();
            },
            success: function ( response ) {
            	if ( response.success === true ) {

                    var template = $( '#answer-template' ).html();
                    template     = window.question_answer.replaceAll( template, '{author-image}', response.data.answer.author.image );
                    template     = window.question_answer.replaceAll( template, '{answer-vote-nonce}', response.data.answer.nonce.vote );
                    template     = window.question_answer.replaceAll( template, '{answer-id}', response.data.answer.id );
                    template     = window.question_answer.replaceAll( template, '{answer-content}', response.data.answer.content );
                    template     = window.question_answer.replaceAll( template, '{author-name}', response.data.answer.author.name);
                    template     = window.question_answer.replaceAll( template, '{author-url}', response.data.answer.author.url);
                    template     = window.question_answer.replaceAll( template, '{answer-date}', response.data.answer.date);
                    template     = window.question_answer.replaceAll( template, '{best-answer-nonce}', response.data.answer.nonce.best_answer);
                    template     = window.question_answer.replaceAll( template, '{edit-url}', response.data.answer.edit.url);
                    template     = window.question_answer.replaceAll( template, '{delete-url}', response.data.answer.delete.url);
                    template     = window.question_answer.replaceAll( template, '{delete-answer-nonce}', response.data.answer.nonce.delete);
                    template     = window.question_answer.replaceAll( template, '{question-id}', response.data.question.id);
                    template     = window.question_answer.replaceAll( template, '{author-id}', response.data.answer.author.id);
                    template     = window.question_answer.replaceAll( template, '{user-summary-nonce}', response.data.answer.nonce.user_summary);
                    template     = $( template );

                    if ( ! response.data.answer.delete.cap ) {
                        template.find( '.gma_delete_answer' ).remove();
                    }

                    if ( ! response.data.answer.edit.cap ) {
                        template.find( '.gma_edit_answer' ).remove();
                    }

                    if ( response.data.answer.best_answer.can_select ) {
                        template.find( '.gma-pick-best-answer' ).show();
                    } else {
                        template.find( '.gma-pick-best-answer' ).hide();
                    }

                    // Is answer waiting for moderation ?
                    if ( 'on' == gma.answer_moderation ) {
                        var moderationTmpl = $( $( '#answer-moderation-tmpl' ).html() );
                        template.find( '.gma-answer-content-wrapper' ).append( moderationTmpl );
                        // Remove Editor
                        $( '.gma-answer-form' ).hide();
                        // Remove comment form
                        template.find( '.gma-comments' ).remove();
                    }

                    template.hide();
                    if ( $( '.gma-answers-list .gma-pagination' ).length > 0 ) {
                        $( '.gma-answers-list .gma-pagination' ).before( template );
                    } else {
                        $( '.gma-answers-list' ).append( template );
                    }

                    template.show().addClass( 'bg-light-green' );
                    setTimeout( function() {
                        template.removeClass( 'bg-light-green' );
                    }, 700 );

                    if ( false === response.data.can_submit_comment ) {
                        template.find( '#gma-respond' ).remove();
                        template.find( '.gma-leave-comment' ).parent().remove();
                    }


                    $('.gma-answers-subheader').show();
                    $( '.gma-answers-title' ).html( response.data.answers_title );

                    window[ 'gma_editor' ].clear( 'gma-answer-content' );

                    $( '.gma-activity-total-answers' ).text( response.data.total_answers );

                    if ( typeof Prism !== 'undefined') {
                        Prism.highlightAll();
                    }

                    resetDefaultCaptchaForm( response.data.captcha.number1, response.data.captcha.number2 );
				} else {
            	    if ( response.data.hasOwnProperty( 'captcha' ) ) {
                        if ( response.data.captcha.type ) {
                            resetDefaultCaptchaForm( response.data.captcha.number_1, response.data.captcha.number_2 );
                        }
                    }

            	    if ( response.data.hasOwnProperty( 'error' ) ) {
                        window.question_answer.showErrorMessage('', response.data.error);
                    } else {
                        window.question_answer.showErrorMessage('', response.data);
                    }

				}

            	window.gma.tippy.refresh();
            },
            complete: function () {
            	window.question_answer.hidePleaseWait();
            }
        });

		e.preventDefault();
		return false;
	} );

	$( '[name=question-tag]' ).tagify( {
		maxTags: gma.tags_per_question,
        enforceWhitelist: true,
		whitelist: gma.available_terms,
        dropdown : {
            classname : "color-blue",
            enabled   : 1,
            maxItems  : 5
        },
	});

	$( document ).on( 'click', '.gma-comment .gma-edit-comment', function( e ) {
	    var container    = $( this ).closest( '.gma-comment-main' );
        var commentID    = container.find( '.gma-comment-id' ).val();
        var commentNonce = container.find( '.gma-comment-id' ).data( 'wpnonce' );

        $.ajax( {
            url: gma.ajax_url,
            type: 'GET',
            data: {
                action: 'gma-get-comment-form',
                commentID: commentID,
                _wpnonce : commentNonce,
            },
            beforeSend: function() {
                window.question_answer.showPleaseWait();
            },
            success: function( response ) {
                if ( response.success === true ) {
                    container.hide();
                    container.next( '.gma-comment-form' ).html( response.data ).fadeIn( 'fast' );
                    $( '.comment-form' ).parent().hide();
                    $( '.gma-leave-comment' ).parent().show();
                } else {
                    window.question_answer.showErrorMessage('', response.data);
                }
            },
            complete: function() {
                window.question_answer.hidePleaseWait();
            },
        } );

	    e.preventDefault();
        e.stopPropagation();
	    return false;
    } );

    // Follow and Unfollow Question
    $('.gma-favorites').click(function(e){
        e.preventDefault();

        var self = $( this );

        var data = {
            action: 'gma-follow-question',
            nonce: self.data('nonce'),
            post: self.data('post')
        };

        $.ajax({
            url: gma.ajax_url,
            data: data,
            type: 'POST',
            dataType: 'json',
            beforeSend: function() {
                window.question_answer.showPleaseWait();
            },
            success: function(response){
                if ( response.success === true ) {
                    if ( response.data.code == 'unfollowed' ) {
                        self.removeClass( 'text-warning' ).addClass( 'text-muted' );
                    } else {
                        self.addClass( 'text-warning' ).removeClass( 'text-muted' );
                    }

                    if ( response.data.followers > 0 ) {
                        self
                            .find( '.gma-favorite-count' )
                            .text( response.data.followers )
                            .show();
                    } else {
                        self
                            .find( '.gma-favorite-count' )
                            .text( response.data.followers )
                            .hide();
                    }

                }
            },
            complete: function() {
                window.question_answer.hidePleaseWait();
            },
        });
    });

   if ( window.location.href.indexOf( '#answer' ) > 0 ){
       setTimeout( function() {
           var answerToHighlight = window.location.href.substr( window.location.href.indexOf( '#answer' ) );
           $( answerToHighlight ).css( { backgroundColor: 'rgb(254, 194, 105)' } );

           setTimeout( function() {
               $( answerToHighlight ).animate({ backgroundColor: '#fff' }, 1150);
           }, 500 );
       }, 600 );
   }

    if ( window.location.href.indexOf( '#comment' ) > 0 ){

        setTimeout(function() {
            var commentToHighlight = window.location.href.substr( window.location.href.indexOf( '#comment' ) );
            $( commentToHighlight ).css( { backgroundColor: 'rgb(254, 194, 105)' } );

            setTimeout( function() {
                $( commentToHighlight ).animate({ backgroundColor: '#fff' }, 1150);
            }, 500 );

        }, 600)

    }


   $( document ).on( 'click', '.comment-up, .comment-up-on', function(e) {
       var self  = $( this ),
           cmID  = self.data( 'comment' ),
           nonce = self.data( 'wpnonce' );
       $.post( {
           url: gma.ajax_url,
           data: {
               action: 'gma-vote-comment',
               commentID: cmID,
               _wpnonce: nonce,
           },
           beforeSend: function () {
               window.question_answer.showPleaseWait();
           },
           success: function (response) {
               if ( response.success === true ) {

                   if ( response.data.voted == 1 ) {
                       self.removeClass( 'comment-up comment-up-off' );
                       self.addClass( 'comment-up-on comment-up-undo' );
                   } else {
                       self.removeClass( 'comment-up-on comment-up-undo' );
                       self.addClass( 'comment-up comment-up-off' );
                   }

                   if ( response.data.vote ) {
                       self.closest( '.gma-comment-actions' ).find( '.gma-cm-vote-count' ).text( response.data.vote  );
                   } else {
                       self.closest( '.gma-comment-actions' ).find( '.gma-cm-vote-count' ).text( "" );
                   }

               }
           },
           complete: function () {
               window.question_answer.hidePleaseWait();
           }
       } );

       e.preventDefault();
       return false;
   } );

   $( document ).on( 'click', '.gma-show-hidden-comments, .gma-leave-comment', function (e) {
       var self              = $( this ),
           selfText          = self.html(),
           postid            = self.data( 'post' ),
           commentsContainer = self.closest( '.gma-comments' ).find('.gma-comments-list'),
           hiddenComments    = commentsContainer.attr( 'data-remaining-comments-count' );

       if ( false == hiddenComments ) {
           e.preventDefault();
           return;
       }


       $.get(gma.ajax_url, {action: 'gma-get-hidden-comments', postid: postid}, function (response) {
           if ( response.success === true ) {

               var comments = response.data.comments;
               for ( var i = 0 ; i < comments.length ; i++ ) {
                   var template = $( '#gma-comment-template' ).html();
                   template     = window.question_answer.replaceAll(template, '{comment-id}', comments[i].id);
                   console.warn( template );
                   template     = $( template );
                   template.find( '.gma-comment-id' ).val( comments[i].id );
                   template.find( '.gma-comment-id' ).attr( 'data-wpnonce', response.data.nonce );
                   template.find( '.gma-comment-date' ).html( comments[i].date );
                   template.find( '.gma-comment-author' ).attr( 'href', comments[i].author.url );
                   template.find( '.gma-comment-author .author-name' ).text( comments[i].author.name );
                   template.find( '.gma-comment-content' ).html( comments[i].text );
                   template.find( '.gma-comment-user-avatar' ).attr('style', 'background-image: url(' +  comments[i].author.avatar + ')' );
                   template.hide();

                   if ( comments[i].is_anonymous ) {
                       template.find( '.gma-edit-comment, .gma-delete-comment' ).remove();
                       template.find( '.gma-comment-author' ).replaceWith( function() {
                           var contents = $( this ).html();
                           return '<span class="text-info gma-comment-author">' + contents + '</span>';
                       } );
                   } else {
                       if ( comments[i].is_owner == true ) {
                           template.find( '.gma-comment-author' ).addClass( 'gma-owner' );
                       }
                   }

                   // Vote count
                   if ( comments[i].voting.count > 0 ) {
                       template.find( '.gma-cm-vote-count' ).text( comments[i].voting.count );
                   }

                   // Vote
                   if ( response.data.voting_status === true ) {
                       if ( comments[ i ].is_yours !== true ) {
                           template.find( '.comment-up' ).removeClass( 'disabled-content' );
                       }
                       if ( comments[ i ].voting.voted === true ) {
                           template
                               .find( '.comment-up' )
                               .removeClass( 'comment-up-off' )
                               .addClass( 'comment-up-undo comment-up-on' );
                       }
                       template.find( '.comment-up' ).attr( 'data-wpnonce', response.data.vote_nonce );
                   }

                   // Check anonymous voting
                   if ( false === response.data.logged_in && false === response.data.anonymous_vote  ) {
                       template.find( '.comment-up' ).addClass( 'disabled-content' );
                   }

                   if ( false === comments[i].can_delete ) {
                       template.find( '.gma-delete-comment' ).remove();
                   }

                   if ( false === comments[i].can_edit ) {
                       template.find( '.gma-edit-comment' ).remove();
                   }

                   self.closest( '.gma-comments' ).find( '.gma-comments-list' ).append( template );
                   template.addClass( 'bg-yellow-light' ).show();
                   template.animate({ backgroundColor: '#fff' }, 2400, function() {
                       $(this).removeClass( 'bg-yellow-light' );
                       $(this).removeAttr( 'style' );
                   });
               }
               commentsContainer.parent().find( '.gma-leave-comment' ).removeClass( 'border-right' );
               commentsContainer.attr( 'data-remaining-comments-count', 0);
               commentsContainer.parent().find( '.gma-show-hidden-comments' ).remove();
           }
       });

       e.preventDefault();
   } );

   $( document ).on( 'click', '.gma-share', function(e) {
       var quora = $( this ).data( 'post' );
       var modalShare = $( '#modal-share' ).iziModal( {
           title: gma.l10n.words[ 'shareinsocials' ],
           rtl: false,
           width: 400,
           onOpening: function (modal) {
               $( 'body' ).addClass( 'gma-blur' );
               $.ajax( {
                   url: gma.ajax_url,
                   type: 'GET',
                   data: {
                       action: 'gma-get-socials',
                       post  : quora,
                   },
                   beforeSend: function() {
                       modal.startLoading();
                   },
                   success: function (response) {
                       if ( response.success === true ) {
                           $( '#modal-share' ).find( '.gma-telegram' ).attr( 'href', response.data.urls.telegram );
                           $( '#modal-share' ).find( '.gma-linkedin' ).attr( 'href', response.data.urls.linkedin );
                           $( '#modal-share' ).find( '.gma-facebook' ).attr( 'href', response.data.urls.facebook );
                           $( '#modal-share' ).find( '.gma-twitter' ).attr( 'href', response.data.urls.twitter );
                           $( '#modal-share' ).find( '.gma-whatsapp' ).attr( 'href', response.data.urls.whatsapp );
                           $( '#modal-share' ).find( '[name=share-link]' ).val( response.data.url ).select();
                       }
                   },
                   complete: function () {
                       modal.stopLoading();
                   }
               } );
           },
           onClosed: function (modal) {
               $( 'body' ).removeClass( 'gma-blur' );
           },
           overlay: false,
           transitionIn: '',
           transitionOut: '',
       } );
       modalShare.iziModal( 'open' );

       e.preventDefault();
       return false;
   } );

  $( '.gma-approve-question' ).click( function() {
        var self = $( this ),
            selfText = self.html(),
            questionID = self.data( 'post' );
        $.ajax( {
            url: gma.ajax_url,
            type: 'POST',
            data: {
                action: 'gma-publish-question',
                questionID: questionID,
            },
            beforeSend: function() {
                self.attr( 'disabled' );
                window.question_answer.showPleaseWait();
            },
            success: function (response) {
                if ( response.success === true ) {
                    window.location.reload();
                }
            },
            complete: function () {
                window.question_answer.hidePleaseWait();
            }
        } );
    } );

})(jQuery);