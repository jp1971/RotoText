/**
 *		js module:
 *			rotate_html.js
 *
 *		desc:
 *			Rotates evenly through multiple CTA and 800 numbers in header.
 *
 *		requires:
 *			jQuery
 */

jQuery( document ).ready( function( $ ) {	
	var nonce = 'krt_ajax.nonce';
	category = $( '.krt_roto_text' ).data( 'category' );

	$.ajax({
	    url: krt_ajax.url,
	    dataType:'html',
	    data: ( {action:'krt_roto_text', category:category, nonce:nonce} ),
	    success: function( html ) {
	    	$( '.krt_roto_text' ).html( html );
	    	console.log( 'WTF?!?!' );
	    	// $( '.krt_roto_text' ).css( 'visibility', 'visible' );
	    },
	    error: function( jqXHR, textStatus, errorThrown ) {
	    	$( '.krt_roto_text' ).html( errorThrown );
	    }
	});
});
