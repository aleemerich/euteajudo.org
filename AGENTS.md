# AGENTS.md: EuTeAjudo.org (plataforma)

Instruções para qualquer agente ou pessoa que desenvolva ou mantenha este repositório. Leia antes de mudar código.

## Missão

Espaço anônimo onde Amigos desabafam e recebem Respostas de um Agente de IA (no MVP) e, no futuro, de Ajudadores humanos. Anonimato e segurança de quem pede ajuda vêm antes de qualquer conveniência técnica.

## Mapa da documentação

| Documento | Conteúdo |
|---|---|
| [GLOSSARY.md](GLOSSARY.md) | Termos do domínio (PT) e o identificador de código oficial de cada um (EN). Fonte de verdade de nomes. |
| [docs/mvp.md](docs/mvp.md) | O que o MVP faz. |
| [docs/plan.md](docs/plan.md) | Entregas, ordem e critério de pronto. |
| [docs/adr/](docs/adr/) | Decisões estruturais e o motivo de cada uma. Não reverter uma decisão registrada sem um ADR novo. |
| [design/identity.md](design/identity.md) | Identidade "Luz acesa": conceito, logo, cores Noite e Dia, tipografia, tom de voz. Valores em `design/tokens.json`. |
| [.claude/skills/SOURCES.md](.claude/skills/SOURCES.md) | Skills de terceiros instaladas, origem e uso. |

## Limites: o que é público e o que não é

- Este repositório é **público** (GPL-2.0-or-later). O Agente fica no repositório **privado** `aleemerich/euteajudo.org-agente`.
- Nunca colocar aqui: prompts, regras de conduta, definições comentadas de Categoria e Gravidade, habilidades do Agente, chaves de API, `.env`, dados reais de Amigos, os Apelidos sorteados para Agentes.
- A plataforma não contém IA. Ela expõe uma API para Agentes ([ADR 0001](docs/adr/0001-external-agents-via-api.md)).

## Stack e ambiente

- WordPress clássico: plugin próprio (`plugin/`) e tema de blocos próprio (`theme/`), estruturas nativas ([ADR 0003](docs/adr/0003-classic-wordpress-native-structures.md)).
- Produção: hospedagem compartilhada cPanel, Apache, **PHP 8.5**, **MySQL 5.7**, sem SSH, publicação por FTPS.
- Local: DDEV com as mesmas versões ([ADR 0004](docs/adr/0004-ddev-and-hosting-parity.md)). Não usar recursos do MySQL 8 em SQL próprio.
- O repositório fica num disco NTFS: `core.fileMode=false`, `safe.directory` e `.gitattributes` com `eol=lf` já estão configurados. Não versionar mudanças de permissão de arquivo.

## Comandos

Primeira vez num clone novo:

```bash
git config core.hooksPath .githooks   # ativa o pre-commit
npm ci                                # ferramentas de JavaScript (no host, Node 24)
ddev start                            # sobe o site e instala o WordPress na primeira vez
ddev composer install                 # ferramentas de PHP (dentro do DDEV, PHP 8.5)
```

| Comando | Faz |
|---|---|
| `ddev start` | Sobe `https://euteajudo.ddev.site`. Na primeira vez baixa e instala o WordPress, ativa plugin e tema, instala o Query Monitor e cria o banco de testes. Admin local: `admin` / `admin` (só local). |
| `ddev composer test:unit` | Testes de unidade (sem WordPress) |
| `ddev composer test:integration` | Testes de integração (suíte de testes do WordPress, banco `wordpress_test`) |
| `ddev composer test` | Os dois acima |
| `ddev composer lint` | PHPCS (WordPress Coding Standards, PHPCompatibility 8.5) e PHPStan nível 8 |
| `npm run build:theme` | Gera `theme/theme.json`, `theme/assets/css/color-schemes.css` e copia fontes e logo a partir de `design/` |
| `npm run check:theme` | Falha se os arquivos gerados estiverem desatualizados em relação a `design/tokens.json` |
| `npm run lint` / `npm run format` | ESLint e Prettier (`@wordpress/scripts`) |
| `npm run test:e2e` | Playwright no celular e no desktop, com o Google Chrome instalado e verificação de acessibilidade (axe) |
| `ddev composer i18n` | Extrai os textos (`.pot`), atualiza `euteajudo-pt_BR.po` e compila o `.mo`, no plugin e no tema. Traduzir as linhas novas do `.po` e rodar de novo para compilar. |
| `ddev wp ...` | WP-CLI no site local |
| `ddev export-db --file=backups/db-$(date +%F).sql.gz` | Cópia do banco local (pasta ignorada pelo git) |

Logs: `wordpress/wp-content/debug.log` (PHP e WordPress) e `ddev logs`.

Nunca editar à mão `theme/theme.json` nem `theme/assets/css/color-schemes.css`: mudar `design/tokens.json` e rodar `npm run build:theme`.

## Convenções de código

- **Código em inglês**: identificadores, comentários, mensagens de commit e textos-fonte da interface. Documentação do domínio em português ([ADR 0006](docs/adr/0006-english-code-portuguese-docs.md)).
- **Nomes de pastas e arquivos em inglês**, sem acentos nem espaços, inclusive na documentação (ex.: `docs/plan.md`, `design/identity.md`). Só o conteúdo da documentação é em português.
- **Nomes vêm do glossário**: use a linha _Código_ do termo. Não invente sinônimos (ex.: Pedido é sempre `help_request`, nunca `ticket` ou `post`).
- Prefixo `eta_` em tudo o que é registrado no WordPress (post types, status, taxonomias, papéis, metadados com `_eta_`, opções). Hooks próprios, funções e constantes globais usam `euteajudo_` (o PHPCS exige prefixo de pelo menos 4 letras), mas o código fica em namespaces e quase não precisa de globais. Namespace PHP `EuTeAjudo\`, autoload PSR-4. Text domain `euteajudo`, tradução `pt_BR`.
- Post type com até 20 caracteres; `post_status` cabe em 20 caracteres.
- Segurança do WordPress sempre: capabilities em toda ação, nonces em formulários, sanitização na entrada, escape na saída, `$wpdb->prepare` em SQL.
- Anonimato: nunca exibir nome ou e-mail; `display_name`, `nickname` e autor de comentário são sempre o Apelido. Ver as proteções obrigatórias do ADR 0003.
- Acesso a Pedido por código aleatório e 404 para quem não tem acesso ([ADR 0005](docs/adr/0005-public-and-private-areas.md)).
- Migrações por versão do plugin (a publicação por FTP não executa o hook de ativação).

## Metodologia

- `main` sempre publicável. Cada tarefa em um branch curto, integrado por PR com CI verde. Mensagens de commit em inglês, no imperativo.
- **TDD por fatia vertical**: teste que falha, código mínimo, refatoração.
- Três níveis de teste:
  - **Unidade** (PHPUnit, sem WordPress): lógica pura, como mascaramento, gerador de Apelido e regras de status. A maior parte dos casos.
  - **Integração** (PHPUnit com a suíte de testes do WordPress, no DDEV): registro de CPTs, permissões, hooks, REST, e a prova de que um Pedido não vaza.
  - **Ponta a ponta** (Playwright com `@wordpress/e2e-test-utils-playwright` e `@axe-core/playwright`): fluxos do Amigo e do Administrador, no tamanho de celular primeiro.
- Análise estática: PHPStan com regras do WordPress, PHPCS com WordPress Coding Standards e PHPCompatibility para PHP 8.5, ESLint e Prettier.
- **Pronto** significa: testes passando, análise estática limpa, E2E verde no celular, e glossário ou ADR atualizados quando a mudança tocar o domínio ou uma decisão.

## Skills de referência

Instaladas em `.claude/skills/` (origem e versão em `SOURCES.md`). Use a skill da área antes de escrever código nela.

| Skill | Quando usar |
|---|---|
| `wp-plugin-development` | Arquitetura do plugin, hooks, segurança, cron, migrações |
| `wp-block-themes` | `theme.json`, templates, template parts, variações de estilo |
| `wp-block-development` | Blocos dinâmicos (`block.json`, `render.php`) |
| `wp-patterns` | Patterns das páginas públicas |
| `wp-interactivity-api` | Interação nas telas sem app React |
| `wp-rest-api` | API dos Agentes, permissões, schema |
| `wp-phpstan` | Configuração e correções do PHPStan |
| `wp-wpcli-and-ops` | WP-CLI, dados de teste, operação |
| `euteajudo-identity` (do projeto) | **Sempre** que mexer em algo visível: cores, tipografia, `theme.json`, CSS, textos da interface, e-mails, redes sociais, vídeos |
| `frontend-design` | Identidade visual e direção das telas |
| `theme-factory` | Aplicar a identidade em materiais de divulgação |
| `webapp-testing` | Verificar o site local com Playwright |
| `skill-creator` (global do Claude Code) | Criar skills do projeto, como `euteajudo-identity` |
