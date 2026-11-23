/*
 * Builds docs/texts-review.md: every interface text people read, grouped by screen, with the
 * Portuguese text shown on the site and where it comes from (R1 in docs/plan.md).
 * Source of truth stays in the .po files; edit the msgstr there and run `ddev composer i18n`.
 *
 * Usage: npm run texts:review
 */
/* eslint-disable no-console -- Command-line script: console output is its interface. */
import { readFileSync, writeFileSync } from 'node:fs';

const SOURCES = [
	{ po: 'plugin/languages/euteajudo-pt_BR.po', root: 'plugin' },
	{ po: 'theme/languages/euteajudo-theme-pt_BR.po', root: 'theme' },
];

// First matching rule wins. Texts only seen in the block editor are left out.
const SCREENS = [
	[ /blocks\/home\//, 'Início' ],
	[ /blocks\/site-links\//, 'Rodapé' ],
	[
		/blocks\/(account-link|app-nav)\/|patterns\/brand/,
		'Cabeçalho e navegação',
	],
	[ /blocks\/sign-up\//, 'Criar conta' ],
	[ /blocks\/sign-in\/|Forms\/SignIn/, 'Entrar' ],
	[
		/blocks\/password-(request|reset)\/|Forms\/PasswordReset/,
		'Esqueci minha senha e nova senha',
	],
	[ /blocks\/email-confirmation\//, 'Confirmação de e-mail' ],
	[ /blocks\/account\/|Forms\/AccountForm/, 'Minha conta' ],
	[ /blocks\/write\//, 'Escrever' ],
	[
		/blocks\/my-requests\/|HelpRequests\/View|HelpRequests\/HelpRequests\.php/,
		'Meus pedidos e Conversa',
	],
	[ /Forms\/Messages/, 'Mensagens de erro e de aviso (todas as telas)' ],
	[
		/Forms\/View|Google\//,
		'Partes comuns dos formulários e login com Google',
	],
	[
		/Notifications|Notices|EmailConfirmation|Forms\/SignUp|Schedules|AgentWork|Aliases\.php/,
		'E-mails',
	],
	[
		/Pages\.php|ContentPages|TriageTerms|SupportServices\.php/,
		'Nomes de páginas, termos e Serviços de apoio criados na instalação',
	],
	[
		/src\/Admin\/|SupportServiceType|HelpRequestType|ContentPages|UserDirectory/,
		'Painel do Administrador e API',
	],
];
const EDITOR_ONLY = /block\.json$|style\.css$|theme\.json$|euteajudo\.php$/;

function parsePo( text ) {
	const entries = [];
	for ( const block of text.split( /\n\n+/ ) ) {
		const refs = [ ...block.matchAll( /^#: (.+)$/gm ) ].flatMap( ( m ) =>
			m[ 1 ].split( ' ' )
		);
		const field = ( name ) => {
			const m = block.match(
				new RegExp( `^${ name } ((?:".*"\\n?)+)`, 'm' )
			);
			return m
				? m[ 1 ]
						.split( '\n' )
						.filter( Boolean )
						.map( ( line ) => JSON.parse( line ) )
						.join( '' )
				: null;
		};
		const id = field( 'msgid' );
		if ( ! id ) {
			continue;
		}
		const str = field( 'msgstr' ) ?? field( 'msgstr\\[0\\]' ) ?? '';
		const plural = field( 'msgstr\\[1\\]' );
		entries.push( {
			refs,
			id,
			str: plural ? `${ str } / ${ plural }` : str,
		} );
	}
	return entries;
}

const cell = ( s ) => s.replace( /\|/g, '\\|' ).replace( /\n/g, '<br>' );
const groups = new Map( SCREENS.map( ( [ , name ] ) => [ name, [] ] ) );
groups.set( 'Outros', [] );

for ( const { po, root } of SOURCES ) {
	for ( const entry of parsePo( readFileSync( po, 'utf8' ) ) ) {
		const refs = entry.refs.filter(
			( r ) => ! EDITOR_ONLY.test( r.replace( /:\d+$/, '' ) )
		);
		if ( ! refs.length ) {
			continue;
		}
		const screen =
			SCREENS.find( ( [ re ] ) =>
				refs.some( ( r ) => re.test( r ) )
			)?.[ 1 ] ?? 'Outros';
		groups.get( screen ).push( {
			...entry,
			where: refs.map( ( r ) => `${ root }/${ r }` ).join( ' ' ),
		} );
	}
}

let out = `# Revisão dos textos (R1)

Gerado por \`npm run texts:review\` a partir dos arquivos de tradução. Não edite este arquivo: ajuste o \`msgstr\` em \`plugin/languages/euteajudo-pt_BR.po\` ou \`theme/languages/euteajudo-theme-pt_BR.po\`, rode \`ddev composer i18n\` e gere a lista de novo.

As páginas Sobre, Termos de uso e Política de privacidade não estão aqui: são conteúdo, editado direto no painel (Páginas).

`;
for ( const [ screen, items ] of groups ) {
	if ( ! items.length ) {
		continue;
	}
	out += `\n## ${ screen }\n\n| Texto no site | Original (inglês) | Onde |\n|---|---|---|\n`;
	for ( const { str, id, where } of items ) {
		out += `| ${ cell( str || '**(sem tradução)**' ) } | ${ cell( id ) } | \`${ where.split( ' ' )[ 0 ] }\` |\n`;
	}
}
writeFileSync( 'docs/texts-review.md', out );
console.log(
	`docs/texts-review.md: ${ [ ...groups.values() ].reduce( ( n, g ) => n + g.length, 0 ) } texts`
);
