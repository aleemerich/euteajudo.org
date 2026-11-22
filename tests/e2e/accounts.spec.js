import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

/*
 * Account flows against the local site. Emails are read from Mailpit, which DDEV runs to capture
 * everything the site sends.
 */
const MAILPIT = process.env.MAILPIT_URL || 'https://euteajudo.ddev.site:8026';

const uniqueEmail = ( testInfo, label ) =>
	`e2e-${ label }-${ testInfo.project.name }-${ Date.now() }@example.org`;

async function linkFromLatestEmail( request, to, pathPart ) {
	for ( let attempt = 0; attempt < 20; attempt++ ) {
		const search = await request.get(
			`${ MAILPIT }/api/v1/search?query=${ encodeURIComponent( 'to:' + to ) }`,
			{ ignoreHTTPSErrors: true }
		);
		const { messages = [] } = await search.json();
		if ( messages.length ) {
			const message = await (
				await request.get(
					`${ MAILPIT }/api/v1/message/${ messages[ 0 ].ID }`,
					{
						ignoreHTTPSErrors: true,
					}
				)
			).json();
			const link = ( message.Text.match( /https:\/\/\S+/g ) || [] ).find(
				( url ) => url.includes( pathPart )
			);
			if ( link ) {
				return link;
			}
		}
		await new Promise( ( resolve ) => setTimeout( resolve, 250 ) );
	}
	throw new Error( `No email with a ${ pathPart } link for ${ to }` );
}

async function signUpWithEmail( page, email, password ) {
	await page.goto( '/criar-conta/' );
	await page.getByLabel( 'Tenho 18 anos ou mais' ).check();
	await page.getByRole( 'checkbox' ).check();
	await page.getByLabel( 'E-mail' ).fill( email );
	await page.getByLabel( 'Senha', { exact: true } ).fill( password );
	await page.getByRole( 'button', { name: 'Criar conta' } ).click();
}

test.describe( 'Accounts', () => {
	test( 'sign-up with email, confirm by link, sign out and sign in again', async ( {
		page,
		request,
	}, testInfo ) => {
		const email = uniqueEmail( testInfo, 'signup' );
		await signUpWithEmail( page, email, 'long enough secret' );
		await expect(
			page.getByRole( 'heading', { name: 'Quase lá' } )
		).toBeVisible();

		await page.goto(
			await linkFromLatestEmail( request, email, '/confirmar-e-mail/' )
		);
		await expect(
			page.getByText( /E-mail confirmado\. Você entrou como Amigo \d+\./ )
		).toBeVisible();
		await expect(
			page.locator( '.eta-account-link .eta-alias' )
		).toHaveText( /^Amigo \d+$/ );

		await page.getByRole( 'link', { name: 'Sair' } ).click();
		await page.goto( '/entrar/' );
		await page.getByLabel( 'E-mail' ).fill( email );
		await page
			.getByLabel( 'Senha', { exact: true } )
			.fill( 'long enough secret' );
		await page.getByLabel( 'Senha', { exact: true } ).press( 'Enter' );
		await expect(
			page.locator( '.eta-account-link .eta-alias' )
		).toHaveText( /^Amigo \d+$/ );
	} );

	test( 'sign-up asks for age and consent', async ( { page } ) => {
		await page.goto( '/criar-conta/' );
		await page.getByRole( 'button', { name: 'Criar conta' } ).click();
		await expect( page.getByRole( 'alert' ) ).toContainText(
			'Escolha sua idade e aceite os termos'
		);
	} );

	test( 'people under 18 are referred and get no account', async ( {
		page,
	} ) => {
		await page.goto( '/criar-conta/' );
		await page.getByLabel( 'Tenho menos de 18 anos' ).check();
		await page.getByRole( 'button', { name: 'Criar conta' } ).click();
		await expect(
			page.getByRole( 'heading', {
				name: 'Este espaço é para maiores de 18 anos',
			} )
		).toBeVisible();
		await expect(
			page.getByRole( 'link', { name: 'Abrir o Pode Falar' } )
		).toBeVisible();
	} );

	test( 'wrong password gets one generic message', async ( { page } ) => {
		await page.goto( '/entrar/' );
		await page.getByLabel( 'E-mail' ).fill( 'nobody@example.org' );
		await page
			.getByLabel( 'Senha', { exact: true } )
			.fill( 'whatever password' );
		await page.getByRole( 'button', { name: 'Entrar' } ).click();
		await expect( page.getByRole( 'alert' ) ).toContainText(
			'E-mail ou senha não conferem'
		);
	} );

	test( 'forgot password: link by email, new password, sign in', async ( {
		page,
		request,
	}, testInfo ) => {
		const email = uniqueEmail( testInfo, 'reset' );
		await signUpWithEmail( page, email, 'first long secret' );
		await page.goto(
			await linkFromLatestEmail( request, email, '/confirmar-e-mail/' )
		);
		await page.getByRole( 'link', { name: 'Sair' } ).click();

		await page.goto( '/esqueci-minha-senha/' );
		await page.getByLabel( 'E-mail' ).fill( email );
		await page.getByRole( 'button', { name: 'Enviar link' } ).click();
		await expect( page.getByRole( 'status' ) ).toContainText(
			'Se esse e-mail tiver conta'
		);

		await page.goto(
			await linkFromLatestEmail( request, email, '/nova-senha/' )
		);
		await page.getByLabel( 'Nova senha' ).fill( 'second long secret' );
		await page.getByRole( 'button', { name: 'Salvar senha nova' } ).click();
		await expect( page.getByRole( 'status' ) ).toContainText(
			'Sua senha foi alterada'
		);

		await page.getByLabel( 'E-mail' ).fill( email );
		await page
			.getByLabel( 'Senha', { exact: true } )
			.fill( 'second long secret' );
		await page.getByRole( 'button', { name: 'Entrar' } ).click();
		await expect(
			page.locator( '.eta-account-link .eta-alias' )
		).toHaveText( /^Amigo \d+$/ );
	} );

	for ( const path of [
		'/entrar/',
		'/criar-conta/',
		'/esqueci-minha-senha/',
	] ) {
		test( `${ path } has no serious accessibility issues and is not indexed`, async ( {
			page,
		} ) => {
			await page.goto( path );
			await expect(
				page.locator( 'meta[name="robots"]' )
			).toHaveAttribute( 'content', /noindex/ );
			const results = await new AxeBuilder( { page } ).analyze();
			const serious = results.violations.filter( ( v ) =>
				[ 'serious', 'critical' ].includes( v.impact )
			);
			expect( serious.map( ( v ) => v.id ) ).toEqual( [] );
		} );
	}
} );
