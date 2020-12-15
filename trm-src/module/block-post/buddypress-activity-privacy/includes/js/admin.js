jQuery( document ).ready( function() {

	jQuery( '.trsap-options-box' ).sortable( {
		items: 'p.sortable',
		tolerance: 'pointer',
		axis: 'y',
		handle: 'span'
	});

	jQuery( '.sortable span' ).css( 'cursor', 'move' );

});
