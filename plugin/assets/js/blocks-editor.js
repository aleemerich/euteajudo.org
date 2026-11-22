/**
 * Editor preview for EuTeAjudo server-rendered blocks (no build step).
 */
( function ( blocks, element, ServerSideRender ) {
	( window.euteajudoBlocks || [] ).forEach( function ( name ) {
		blocks.registerBlockType( name, {
			edit: function () {
				return element.createElement( ServerSideRender, { block: name } );
			},
			save: function () {
				return null;
			},
		} );
	} );
} )( window.wp.blocks, window.wp.element, window.wp.serverSideRender );
