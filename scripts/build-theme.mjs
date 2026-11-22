#!/usr/bin/env node
/**
 * Build theme files from design/tokens.json (single source of truth).
 *
 * Writes:
 * - theme/theme.json: presets whose colors point to CSS variables, so Night and Day switch without
 *   touching blocks.
 * - theme/assets/css/color-schemes.css: the --eta-color-* values for Night (default), Day (device
 *   preference) and an explicit visitor choice through html[data-eta-scheme].
 * - theme/assets/fonts/*.woff2: copied from design/fonts/web (self-hosted, never third-party).
 *
 * Usage: node scripts/build-theme.mjs [--check]
 *   --check exits with code 1 when the generated files differ from the ones on disk (used in CI).
 */
/* eslint-disable no-console -- Command-line script: console output is its interface. */
import {
	readFileSync,
	writeFileSync,
	mkdirSync,
	copyFileSync,
	existsSync,
	readdirSync,
} from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join( dirname( fileURLToPath( import.meta.url ) ), '..' );
const tokens = JSON.parse(
	readFileSync( join( root, 'design/tokens.json' ), 'utf8' )
);
const check = process.argv.includes( '--check' );
const value = ( group, key ) => group[ key ].$value;

const ROLE_NAMES = {
	background: 'Background',
	surface: 'Surface',
	field: 'Field',
	text: 'Text',
	'text-strong': 'Text strong',
	'text-muted': 'Text muted',
	line: 'Line',
	'line-strong': 'Line strong',
	accent: 'Accent',
	'on-accent': 'On accent',
	focus: 'Focus',
	'notice-text': 'Notice text',
	'thanks-background': 'Thanks background',
};
const roles = Object.keys( ROLE_NAMES );
const night = tokens.color.night;
const day = tokens.color.day;

const palette = [
	...roles.map( ( slug ) => ( {
		slug,
		name: ROLE_NAMES[ slug ],
		color: `var(--eta-color-${ slug })`,
	} ) ),
	...Object.keys( tokens.color.brand )
		.filter( ( k ) => ! k.startsWith( '$' ) )
		.map( ( k ) => ( {
			slug: `brand-${ k }`,
			name: `Brand ${ k.replace( '-', ' ' ) }`,
			color: value( tokens.color.brand, k ),
		} ) ),
];

const size = tokens.font.size;
const space = tokens.space;
const radius = tokens.radius;
const fontStack = tokens.font.family.base.$value
	.map( ( f ) => ( f.includes( ' ' ) ? `"${ f }"` : f ) )
	.join( ', ' );

const themeJson = {
	$schema: 'https://schemas.wp.org/trunk/theme.json',
	version: 3,
	settings: {
		appearanceTools: true,
		useRootPaddingAwareAlignments: true,
		color: {
			defaultPalette: false,
			defaultGradients: false,
			defaultDuotone: false,
			custom: false,
			customGradient: false,
			palette,
		},
		typography: {
			defaultFontSizes: false,
			customFontSize: false,
			fluid: true,
			fontFamilies: [
				{
					slug: 'base',
					name: 'Atkinson Hyperlegible Next',
					fontFamily: fontStack,
					fontFace: [
						{
							fontFamily: 'Atkinson Hyperlegible Next',
							fontStyle: 'normal',
							fontWeight: '200 800',
							fontDisplay: 'swap',
							src: [
								'file:./assets/fonts/atkinson-hyperlegible-next-variable.woff2',
							],
						},
						{
							fontFamily: 'Atkinson Hyperlegible Next',
							fontStyle: 'italic',
							fontWeight: '200 800',
							fontDisplay: 'swap',
							src: [
								'file:./assets/fonts/atkinson-hyperlegible-next-italic-variable.woff2',
							],
						},
					],
				},
			],
			fontSizes: [
				{
					slug: 'small',
					name: 'Small',
					size: value( size, 'small' ),
					fluid: false,
				},
				{
					slug: 'medium',
					name: 'Body',
					size: value( size, 'body' ),
					fluid: false,
				},
				{
					slug: 'large',
					name: 'Heading 2',
					size: value( size, 'h2' ),
					fluid: false,
				},
				{
					slug: 'x-large',
					name: 'Heading 1',
					size: '2.2rem',
					fluid: { min: value( size, 'h1' ), max: '2.2rem' },
				},
				{
					slug: 'xx-large',
					name: 'Display',
					size: '3.6rem',
					fluid: { min: value( size, 'display' ), max: '3.6rem' },
				},
			],
		},
		spacing: {
			defaultSpacingSizes: false,
			units: [ 'px', 'rem', '%' ],
			spacingSizes: [
				[ '10', '1' ],
				[ '20', '2' ],
				[ '30', '3' ],
				[ '40', '4' ],
				[ '50', '6' ],
				[ '60', '8' ],
				[ '70', '12' ],
				[ '80', '16' ],
			].map( ( [ slug, key ] ) => ( {
				slug,
				name: value( space, key ),
				size: value( space, key ),
			} ) ),
		},
		border: {
			radiusSizes: [ 'field', 'button', 'card', 'panel' ].map(
				( k ) => ( {
					slug: k,
					name: k[ 0 ].toUpperCase() + k.slice( 1 ),
					size: value( radius, k ),
				} )
			),
		},
		layout: {
			contentSize: tokens.size[ 'content-width' ].$value,
			wideSize: tokens.size[ 'wide-width' ].$value,
		},
		custom: {
			touchTarget: tokens.size[ 'touch-target' ].$value,
			lineHeight: {
				body: String( tokens.font[ 'line-height' ].body.$value ),
				heading: String( tokens.font[ 'line-height' ].heading.$value ),
			},
		},
	},
	styles: {
		color: {
			background: 'var(--wp--preset--color--background)',
			text: 'var(--wp--preset--color--text)',
		},
		typography: {
			fontFamily: 'var(--wp--preset--font-family--base)',
			fontSize: 'var(--wp--preset--font-size--medium)',
			fontWeight: String( tokens.font.weight.regular.$value ),
			lineHeight: 'var(--wp--custom--line-height--body)',
		},
		spacing: {
			padding: {
				left: 'var(--wp--preset--spacing--50)',
				right: 'var(--wp--preset--spacing--50)',
			},
			blockGap: 'var(--wp--preset--spacing--40)',
		},
		elements: {
			heading: {
				color: { text: 'var(--wp--preset--color--text-strong)' },
				typography: {
					fontWeight: String( tokens.font.weight.bold.$value ),
					lineHeight: 'var(--wp--custom--line-height--heading)',
				},
			},
			h1: {
				typography: {
					fontSize: 'var(--wp--preset--font-size--x-large)',
				},
			},
			h2: {
				typography: { fontSize: 'var(--wp--preset--font-size--large)' },
			},
			link: {
				color: { text: 'var(--wp--preset--color--text)' },
				typography: { textDecoration: 'underline' },
			},
			button: {
				color: {
					background: 'var(--wp--preset--color--accent)',
					text: 'var(--wp--preset--color--on-accent)',
				},
				border: { radius: 'var(--wp--preset--border-radius--button)' },
				typography: {
					fontWeight: String( tokens.font.weight.bold.$value ),
				},
				spacing: {
					padding: {
						top: '0.75rem',
						bottom: '0.75rem',
						left: '1.5rem',
						right: '1.5rem',
					},
				},
			},
		},
	},
	templateParts: [
		{ name: 'header', title: 'Header', area: 'header' },
		{ name: 'footer', title: 'Footer', area: 'footer' },
	],
};

const declarations = ( mode ) =>
	roles
		.map( ( slug ) => `\t--eta-color-${ slug }: ${ value( mode, slug ) };` )
		.join( '\n' );
const css = `/*
 * Generated by scripts/build-theme.mjs from design/tokens.json. Do not edit by hand.
 *
 * Mode order: explicit visitor choice (html[data-eta-scheme]) > device preference > Night.
 */
:root {
${ declarations( night ) }
\tcolor-scheme: dark;
}

@media (prefers-color-scheme: light) {
\t:root:not([data-eta-scheme="night"]) {
${ declarations( day ).replace( /^\t/gm, '\t\t' ) }
\t\tcolor-scheme: light;
\t}
}

:root[data-eta-scheme="day"] {
${ declarations( day ) }
\tcolor-scheme: light;
}

:focus-visible {
\toutline: 3px solid var(--wp--preset--color--focus);
\toutline-offset: 2px;
}

/* Day: amber has low contrast against the background, so buttons get a darker edge. */
@media (prefers-color-scheme: light) {
\t:root:not([data-eta-scheme="night"]) .wp-element-button,
\t:root:not([data-eta-scheme="night"]) .wp-block-button__link {
\t\tbox-shadow: inset 0 0 0 1px #B7790F;
\t}
}
:root[data-eta-scheme="day"] .wp-element-button,
:root[data-eta-scheme="day"] .wp-block-button__link {
\tbox-shadow: inset 0 0 0 1px #B7790F;
}

.wp-element-button,
.wp-block-button__link {
\tmin-height: var(--wp--custom--touch-target);
}

@media (prefers-reduced-motion: reduce) {
\t*, *::before, *::after {
\t\tanimation: none !important;
\t\ttransition: none !important;
\t}
}
`;

const outputs = [
	[
		join( root, 'theme/theme.json' ),
		JSON.stringify( themeJson, null, '\t' ) + '\n',
	],
	[ join( root, 'theme/assets/css/color-schemes.css' ), css ],
];

let stale = false;
for ( const [ file, content ] of outputs ) {
	const current = existsSync( file ) ? readFileSync( file, 'utf8' ) : null;
	if ( current === content ) {
		continue;
	}
	if ( check ) {
		console.error(
			`Out of date: ${ file.replace( root + '/', '' ) }. Run: node scripts/build-theme.mjs`
		);
		stale = true;
		continue;
	}
	mkdirSync( dirname( file ), { recursive: true } );
	writeFileSync( file, content );
	console.log( `Wrote ${ file.replace( root + '/', '' ) }` );
}

const copies = [
	...readdirSync( join( root, 'design/fonts/web' ) ).map( ( f ) => [
		`design/fonts/web/${ f }`,
		`theme/assets/fonts/${ f }`,
	] ),
	[ 'design/logo/logo-mono.svg', 'theme/assets/images/logo-mono.svg' ],
	[ 'design/logo/favicon.svg', 'theme/assets/images/favicon.svg' ],
];
for ( const [ from, to ] of copies ) {
	const source = join( root, from );
	const target = join( root, to );
	if (
		existsSync( target ) &&
		readFileSync( target ).equals( readFileSync( source ) )
	) {
		continue;
	}
	if ( check ) {
		console.error( `Out of date: ${ to }` );
		stale = true;
		continue;
	}
	mkdirSync( dirname( target ), { recursive: true } );
	copyFileSync( source, target );
	console.log( `Copied ${ to }` );
}

process.exit( stale ? 1 : 0 );
