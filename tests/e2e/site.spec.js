import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';
import { signedInFriend } from './support/helpers.js';

test.describe( 'Public site', () => {
	test( 'home leads guests to sign up and tells that an AI replies', async ( {
		page,
	} ) => {
		await page.goto( '/' );
		await expect(
			page.getByRole( 'heading', {
				level: 1,
				name: 'Tem uma luz acesa aqui.',
			} )
		).toBeVisible();
		await expect( page.locator( '.eta-how' ) ).toContainText(
			'inteligência artificial'
		);
		await page.getByRole( 'link', { name: 'Começar a escrever' } ).click();
		await expect( page ).toHaveURL( /\/criar-conta\/$/ );
		await expect(
			page.getByRole( 'link', { name: 'Termos de uso' } ).first()
		).toBeVisible();
	} );

	test( 'home leads signed-in Friends to write', async ( {
		page,
		request,
	}, testInfo ) => {
		await signedInFriend( page, request, testInfo, 'home' );
		await page.goto( '/' );
		await page.getByRole( 'link', { name: 'Começar a escrever' } ).click();
		await expect( page ).toHaveURL( /\/escrever\/$/ );
	} );

	for ( const [ link, path ] of [
		[ 'Sobre', '/sobre/' ],
		[ 'Termos de uso', '/termos-de-uso/' ],
		[ 'Política de privacidade', '/privacidade/' ],
	] ) {
		test( `footer link "${ link }" opens an accessible page`, async ( {
			page,
		} ) => {
			await page.goto( '/' );
			await page
				.getByRole( 'navigation', { name: 'Sobre o site' } )
				.getByRole( 'link', { name: link } )
				.click();
			await expect( page ).toHaveURL( new RegExp( `${ path }$` ) );
			const results = await new AxeBuilder( { page } ).analyze();
			const serious = results.violations.filter( ( v ) =>
				[ 'serious', 'critical' ].includes( v.impact )
			);
			expect( serious.map( ( v ) => v.id ) ).toEqual( [] );
		} );
	}

	test( 'home has no serious accessibility issues', async ( { page } ) => {
		await page.goto( '/' );
		const results = await new AxeBuilder( { page } ).analyze();
		const serious = results.violations.filter( ( v ) =>
			[ 'serious', 'critical' ].includes( v.impact )
		);
		expect( serious.map( ( v ) => v.id ) ).toEqual( [] );
	} );
} );
