import { test, expect } from '@playwright/test';
import { signedInFriend } from './support/helpers.js';

/*
 * Administrator flows in wp-admin. Desktop only: the dashboard is not part of the phone-first
 * promise. The local and CI sites have the administrator admin / admin (setup.sh).
 */
test.describe( 'Administrator', () => {
	test.skip( ( { isMobile } ) => isMobile, 'wp-admin is used on desktop' );

	test( 'reads a request, replies with the Agent alias and the Friend sees it', async ( {
		page,
		browser,
		request,
	}, testInfo ) => {
		await signedInFriend( page, request, testInfo, 'admin-flow' );
		await page.goto( '/escrever/' );
		await page.getByLabel( 'Título' ).fill( 'Pedido para o Administrador' );
		await page
			.getByLabel( 'O que está acontecendo?' )
			.fill( 'Texto para o painel.' );
		await page.getByRole( 'button', { name: 'Enviar pedido' } ).click();
		const conversation = page.url().split( '?' )[ 0 ];

		const adminContext = await browser.newContext( {
			ignoreHTTPSErrors: true,
		} );
		const admin = await adminContext.newPage();
		await admin.goto( '/wp-login.php' );
		await admin.locator( '#user_login' ).fill( 'admin' );
		await admin.locator( '#user_pass' ).fill( 'admin' );
		await admin.locator( '#wp-submit' ).click();
		await expect( admin.locator( '#eta_agent_status' ) ).toBeVisible();

		await admin.goto( '/wp-admin/edit.php?post_type=eta_help_request' );
		await admin
			.getByRole( 'link', {
				name: 'Pedido para o Administrador',
				exact: true,
			} )
			.first()
			.click();
		await expect( admin.locator( '#eta-request-status' ) ).toContainText(
			'Aguardando triagem'
		);
		const agentAlias = (
			await admin
				.locator( '#eta-request-status dd' )
				.nth( 2 )
				.textContent()
		).trim();

		await admin
			.locator( '#eta-admin-reply-text' )
			.fill( 'Resposta escrita pelo Administrador.' );
		await admin.getByLabel( /Apelido do Agente neste Pedido/ ).check();
		await admin.getByRole( 'button', { name: 'Enviar resposta' } ).click();
		await expect(
			admin.locator( '.notice-success', { hasText: 'Resposta enviada' } )
		).toBeVisible();
		await expect( admin.locator( '#eta-request-status' ) ).toContainText(
			'Em conversa'
		);
		await adminContext.close();

		await page.goto( conversation );
		const reply = page.locator( '.eta-msg-other' );
		await expect( reply ).toContainText(
			'Resposta escrita pelo Administrador'
		);
		await expect( reply.locator( '.eta-who' ) ).toHaveText( agentAlias );
	} );
} );
