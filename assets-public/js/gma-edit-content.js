jQuery( document ).ready( function( $ ) {
    $( '[name=commenton]' ).change( function() {
        var isChecked = $( this ).is( ':checked' );
        if ( isChecked ) {
            $( '[name=commenton-post]' ).show();
        } else {
            $( '[name=commenton-post]' ).hide();
        }
    } );
} );
