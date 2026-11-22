import { defineConfig, devices } from '@playwright/test';

/**
 * End-to-end tests against the local DDEV site. Phone first, then desktop.
 * Uses the installed Google Chrome (channel "chrome") instead of downloading browsers.
 */
export default defineConfig( {
	testDir: './tests/e2e',
	fullyParallel: true,
	forbidOnly: !! process.env.CI,
	retries: process.env.CI ? 1 : 0,
	reporter: process.env.CI
		? [ [ 'list' ], [ 'html', { open: 'never' } ] ]
		: 'list',
	use: {
		baseURL: process.env.WP_BASE_URL || 'https://euteajudo.ddev.site',
		ignoreHTTPSErrors: true,
		trace: 'retain-on-failure',
		screenshot: 'only-on-failure',
		channel: 'chrome',
	},
	projects: [
		{ name: 'phone', use: { ...devices[ 'Pixel 7' ], channel: 'chrome' } },
		{
			name: 'desktop',
			use: { ...devices[ 'Desktop Chrome' ], channel: 'chrome' },
		},
	],
} );
