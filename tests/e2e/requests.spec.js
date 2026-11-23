import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';
import { signedInFriend, wp } from './support/helpers.js';

const codeFrom = ( url ) =>
	new URL( url ).pathname.split( '/' ).filter( Boolean ).pop();

test.describe( 'Help requests', () => {
	test( 'write, get a reply, thank, answer, close and reopen', async ( {
		page,
		request,
	}, testInfo ) => {
		await signedInFriend( page, request, testInfo, 'request' );

		await page.goto( '/escrever/' );
		await page.getByLabel( 'Título' ).fill( 'Não consigo dormir' );
		await page
			.getByLabel( 'O que está acontecendo?' )
			.fill( 'Faz três semanas que fui demitido. Me liga 11912345678.' );
		await page.getByRole( 'button', { name: 'Enviar pedido' } ).click();

		await expect( page.getByRole( 'status' ) ).toContainText(
			'Seu pedido chegou'
		);
		await expect( page.locator( '.eta-msg-request' ) ).toContainText(
			'[removido para proteger você]'
		);
		await expect( page.locator( '.eta-msg-request' ) ).not.toContainText(
			'11912345678'
		);
		await expect( page ).toHaveTitle( /^Meus pedidos/ );
		const code = codeFrom( page.url() );

		await page.goto( '/meus-pedidos/' );
		const item = page.locator( '.eta-requests li', {
			hasText: 'Não consigo dormir',
		} );
		await expect( item ).toContainText( 'Esperando resposta' );

		wp( 'euteajudo', 'reply', code, 'Oi. Estou aqui com você.' );
		await page.reload();
		await expect( item ).toContainText( 'Resposta nova' );

		await item.getByRole( 'link' ).click();
		await expect( page.locator( '.eta-msg-other' ) ).toContainText(
			'Estou aqui com você'
		);
		await page.getByRole( 'button', { name: 'Isso me ajudou' } ).click();
		await expect(
			page.getByRole( 'button', { name: 'Isso me ajudou' } )
		).toHaveAttribute( 'aria-pressed', 'true' );

		await page.getByLabel( 'Responder' ).fill( 'Obrigado por responder.' );
		await page.getByRole( 'button', { name: 'Enviar resposta' } ).click();
		await expect( page.locator( '.eta-msg-mine' ).last() ).toContainText(
			'Obrigado por responder'
		);

		await page
			.getByText( 'Encerrar pedido', { exact: true } )
			.first()
			.click();
		await page.getByLabel( 'Estou melhor' ).check();
		await page.getByRole( 'button', { name: 'Encerrar pedido' } ).click();
		await expect( page.getByRole( 'status' ) ).toContainText(
			'Este pedido está encerrado'
		);

		await page.getByRole( 'button', { name: 'Reabrir pedido' } ).click();
		await expect( page.getByLabel( 'Responder' ) ).toBeVisible();
	} );

	test( 'other people get 404 and guests are sent to sign in', async ( {
		page,
		browser,
		request,
	}, testInfo ) => {
		await signedInFriend( page, request, testInfo, 'owner' );
		await page.goto( '/escrever/' );
		await page.getByLabel( 'Título' ).fill( 'Particular' );
		await page
			.getByLabel( 'O que está acontecendo?' )
			.fill( 'Texto que só eu posso ver.' );
		await page.getByRole( 'button', { name: 'Enviar pedido' } ).click();
		const address = page.url().split( '?' )[ 0 ];

		const strangerContext = await browser.newContext( {
			ignoreHTTPSErrors: true,
		} );
		const stranger = await strangerContext.newPage();
		await signedInFriend( stranger, request, testInfo, 'stranger' );
		const response = await stranger.goto( address );
		expect( response.status() ).toBe( 404 );
		await expect(
			stranger.getByText( 'Texto que só eu posso ver' )
		).toHaveCount( 0 );
		await strangerContext.close();

		const guestContext = await browser.newContext( {
			ignoreHTTPSErrors: true,
		} );
		const guest = await guestContext.newPage();
		await guest.goto( address );
		await expect( guest ).toHaveURL( /\/entrar\/\?redirect_to=/ );
		await guestContext.close();
	} );

	test( 'write page and conversation have no serious accessibility issues', async ( {
		page,
		request,
	}, testInfo ) => {
		await signedInFriend( page, request, testInfo, 'a11y' );
		for ( const step of [ 'write', 'conversation' ] ) {
			if ( step === 'write' ) {
				await page.goto( '/escrever/' );
			} else {
				await page.getByLabel( 'Título' ).fill( 'Teste' );
				await page
					.getByLabel( 'O que está acontecendo?' )
					.fill( 'Texto de teste.' );
				await page
					.getByRole( 'button', { name: 'Enviar pedido' } )
					.click();
			}
			const results = await new AxeBuilder( { page } ).analyze();
			const serious = results.violations.filter( ( v ) =>
				[ 'serious', 'critical' ].includes( v.impact )
			);
			expect(
				serious.map( ( v ) => v.id ),
				step
			).toEqual( [] );
		}
	} );
} );
