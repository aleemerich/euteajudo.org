import { execFileSync } from 'node:child_process';
import { expect } from '@playwright/test';

/*
 * Shared helpers for end-to-end tests. Emails are read from Mailpit, which DDEV runs to capture
 * everything the site sends; server-side steps use WP-CLI inside DDEV.
 */
export const MAILPIT =
	process.env.MAILPIT_URL || 'https://euteajudo.ddev.site:8026';

export const uniqueEmail = ( testInfo, label ) =>
	`e2e-${ label }-${ testInfo.project.name }-${ Date.now() }@example.org`;

export function wp( ...args ) {
	return execFileSync(
		'ddev',
		[
			'exec',
			'php',
			'-d',
			'error_reporting=24575',
			'/usr/local/bin/wp',
			...args,
		],
		{ encoding: 'utf8', stdio: [ 'ignore', 'pipe', 'ignore' ] }
	).trim();
}

export async function linkFromLatestEmail( request, to, pathPart ) {
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

export async function signUpWithEmail( page, email, password ) {
	await page.goto( '/criar-conta/' );
	await page.getByLabel( 'Tenho 18 anos ou mais' ).check();
	await page.getByRole( 'checkbox' ).check();
	await page.getByLabel( 'E-mail' ).fill( email );
	await page.getByLabel( 'Senha', { exact: true } ).fill( password );
	await page.getByRole( 'button', { name: 'Criar conta' } ).click();
}

/**
 * Creates a confirmed Friend and leaves the page signed in as them.
 *
 * @param {import('@playwright/test').Page}              page     Page.
 * @param {import('@playwright/test').APIRequestContext} request  Request context.
 * @param {import('@playwright/test').TestInfo}          testInfo Test info.
 * @param {string}                                       label    Label for the email.
 * @return {Promise<string>} Email of the new Friend.
 */
export async function signedInFriend( page, request, testInfo, label ) {
	const email = uniqueEmail( testInfo, label );
	await signUpWithEmail( page, email, 'long enough secret' );
	await page.goto(
		await linkFromLatestEmail( request, email, '/confirmar-e-mail/' )
	);
	await expect(
		page.locator( '.eta-account-link .eta-alias' )
	).toBeVisible();
	return email;
}
