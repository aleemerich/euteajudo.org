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

/**
 * Appearance choice (Night / Day / Automatic), stored only in this browser. The theme reads the
 * same "eta-scheme" key in <head> and sets html[data-eta-scheme] before the page paints.
 */
( function () {
	const fieldset = document.querySelector( '.eta-scheme-choice' );
	if ( ! fieldset ) {
		return;
	}
	let stored = null;
	try {
		stored = window.localStorage.getItem( 'eta-scheme' );
	} catch ( e ) {}
	const current = stored === 'night' || stored === 'day' ? stored : 'auto';
	fieldset.hidden = false;
	fieldset.querySelectorAll( 'input[name="eta-scheme"]' ).forEach( function ( input ) {
		input.checked = input.value === current;
		input.addEventListener( 'change', function () {
			try {
				if ( input.value === 'auto' ) {
					window.localStorage.removeItem( 'eta-scheme' );
				} else {
					window.localStorage.setItem( 'eta-scheme', input.value );
				}
			} catch ( e ) {}
			if ( input.value === 'auto' ) {
				document.documentElement.removeAttribute( 'data-eta-scheme' );
			} else {
				document.documentElement.setAttribute( 'data-eta-scheme', input.value );
			}
			const saved = fieldset.querySelector( '.eta-saved' );
			if ( saved ) {
				saved.textContent = saved.dataset.saved;
			}
		} );
	} );
} )();

/**
 * A form that came back with problems: take the person to the first field to fix, with its
 * message right above it. The browser would otherwise restore the scroll position from before
 * sending (near the button), leaving the messages out of view.
 */
( function () {
	const notice = document.querySelector( '.eta-notice-error' );
	if ( ! notice ) {
		return;
	}
	if ( 'scrollRestoration' in window.history ) {
		window.history.scrollRestoration = 'manual';
	}
	const show = function () {
		const field = document.querySelector( '.eta-form [aria-invalid="true"]' );
		const target = field || notice;
		const message = field && field.closest( 'fieldset' ) ? field.closest( 'fieldset' ) : target;
		message.scrollIntoView( { block: 'center' } );
		target.focus( { preventScroll: true } );
	};
	if ( document.readyState === 'complete' ) {
		show();
	} else {
		window.addEventListener( 'load', show );
	}
} )();
