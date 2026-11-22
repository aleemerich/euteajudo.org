/**
 * Progressive enhancement for account forms: show/hide password buttons.
 * Forms work without JavaScript.
 */
document.querySelectorAll( '.eta-password-toggle' ).forEach( function ( button ) {
	const input = document.getElementById( button.getAttribute( 'aria-controls' ) );
	if ( ! input ) {
		return;
	}
	button.hidden = false;
	button.setAttribute( 'aria-pressed', 'false' );
	button.addEventListener( 'click', function () {
		const showing = input.type === 'text';
		input.type = showing ? 'password' : 'text';
		button.textContent = showing ? button.dataset.show : button.dataset.hide;
		button.setAttribute( 'aria-pressed', String( ! showing ) );
	} );
} );
