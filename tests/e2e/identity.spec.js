import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const backgroundOf = ( page ) =>
	page.evaluate(
		() => window.getComputedStyle( document.body ).backgroundColor
	);

test.describe( 'Identity "Luz acesa"', () => {
	test( 'uses Night when the device prefers dark', async ( { page } ) => {
		await page.emulateMedia( { colorScheme: 'dark' } );
		await page.goto( '/' );
		expect( await backgroundOf( page ) ).toBe( 'rgb(15, 37, 41)' );
	} );

	test( 'uses Day when the device prefers light', async ( { page } ) => {
		await page.emulateMedia( { colorScheme: 'light' } );
		await page.goto( '/' );
		expect( await backgroundOf( page ) ).toBe( 'rgb(238, 243, 241)' );
	} );

	test( 'a visitor choice overrides the device preference', async ( {
		page,
	} ) => {
		await page.emulateMedia( { colorScheme: 'light' } );
		await page.goto( '/' );
		await page.evaluate( () =>
			document.documentElement.setAttribute( 'data-eta-scheme', 'night' )
		);
		expect( await backgroundOf( page ) ).toBe( 'rgb(15, 37, 41)' );
	} );

	test( 'loads fonts from the site only', async ( { page } ) => {
		const external = [];
		page.on( 'request', ( request ) => {
			const host = new URL( request.url() ).host;
			if ( host && host !== 'euteajudo.ddev.site' ) {
				external.push( request.url() );
			}
		} );
		await page.goto( '/' );
		await page.waitForLoadState( 'networkidle' );
		expect( external ).toEqual( [] );
		expect(
			await page.evaluate( () =>
				document.fonts.check( '16px "Atkinson Hyperlegible Next"' )
			)
		).toBe( true );
	} );

	test( 'home has a labelled brand link and no serious accessibility issues', async ( {
		page,
	} ) => {
		await page.goto( '/' );
		await expect(
			page.getByRole( 'link', { name: /EuTeAjudo\.org/ } )
		).toBeVisible();
		const results = await new AxeBuilder( { page } ).analyze();
		const serious = results.violations.filter( ( v ) =>
			[ 'serious', 'critical' ].includes( v.impact )
		);
		expect( serious.map( ( v ) => v.id ) ).toEqual( [] );
	} );
} );
