import { test, expect } from '@playwright/test';

test.describe( 'Routes', () => {
	test( 'pretty REST URLs work (Apache rewrite rules are in place)', async ( {
		request,
	} ) => {
		const response = await request.get( '/wp-json/' );
		expect( response.status() ).toBe( 200 );
	} );

	test( 'Google sign-in start never leaks to Google without credentials', async ( {
		request,
	} ) => {
		const response = await request.get(
			'/wp-json/euteajudo/v1/auth/google/start?intent=login',
			{ maxRedirects: 0 }
		);
		expect( response.status() ).toBe( 302 );
		expect( response.headers().location ).toMatch(
			/eta_auth_error=not_configured|accounts\.google\.com/
		);
	} );
} );
