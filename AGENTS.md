# AGENTS.md: EuTeAjudo.org (plataforma)

Instruções para qualquer agente ou pessoa que desenvolva ou mantenha este repositório. Leia antes de mudar código.

## Missão

Espaço anônimo onde Amigos desabafam e recebem Respostas de um Agente de IA (no MVP) e, no futuro, de Ajudadores humanos. Anonimato e segurança de quem pede ajuda vêm antes de qualquer conveniência técnica.

## Mapa da documentação

| Documento | Conteúdo |
|---|---|
| [GLOSSARY.md](GLOSSARY.md) | Termos do domínio (PT) e o identificador de código oficial de cada um (EN). Fonte de verdade de nomes. |
| [docs/mvp.md](docs/mvp.md) | O que o MVP faz. |
| [docs/plano.md](docs/plano.md) | Entregas, ordem e critério de pronto. |
| [docs/adr/](docs/adr/) | Decisões estruturais e o motivo de cada uma. Não reverter uma decisão registrada sem um ADR novo. |
| `design/` | Identidade visual, tokens e propostas de tema (entrega ID). |
| [.claude/skills/SOURCES.md](.claude/skills/SOURCES.md) | Skills de terceiros instaladas, origem e uso. |

## Limites: o que é público e o que não é

- Este repositório é **público** (GPL-2.0-or-later). O Agente fica no repositório **privado** `aleemerich/euteajudo.org-agente`.
- Nunca colocar aqui: prompts, regras de conduta, definições comentadas de Categoria e Gravidade, habilidades do Agente, chaves de API, `.env`, dados reais de Amigos, os Apelidos sorteados para Agentes.
- A plataforma não contém IA. Ela expõe uma API para Agentes ([ADR 0001](docs/adr/0001-agentes-externos-via-api.md)).

## Stack e ambiente

- WordPress clássico: plugin próprio (`plugin/`) e tema de blocos próprio (`theme/`), estruturas nativas ([ADR 0003](docs/adr/0003-wordpress-classico-cpt-e-comentarios.md)).
- Produção: hospedagem compartilhada cPanel, Apache, **PHP 8.5**, **MySQL 5.7**, sem SSH, publicação por FTPS.
- Local: DDEV com as mesmas versões ([ADR 0004](docs/adr/0004-ddev-e-paridade-com-a-hospedagem.md)). Não usar recursos do MySQL 8 em SQL próprio.
- O repositório fica num disco NTFS: `core.fileMode=false`, `safe.directory` e `.gitattributes` com `eol=lf` já estão configurados. Não versionar mudanças de permissão de arquivo.

## Comandos

Disponíveis a partir da E0 parte B:

| Comando | Faz |
|---|---|
| `ddev start` | Sobe o site em `https://euteajudo.ddev.site` |
| `ddev composer test:unit` | Testes de unidade (sem WordPress) |
| `ddev composer test:integration` | Testes de integração (suíte de testes do WordPress, MySQL 5.7) |
| `npm run test:e2e` | Playwright, celular primeiro, com verificação de acessibilidade |
| `ddev composer lint` | PHPCS e PHPStan |
| `npm run lint` / `npm run build` | ESLint, Prettier e build dos blocos (`@wordpress/scripts`) |
| `ddev export-db --file=backups/db-$(date +%F).sql.gz` | Cópia do banco local |

## Convenções de código

- **Código em inglês**: identificadores, comentários, mensagens de commit e textos-fonte da interface. Documentação do domínio em português ([ADR 0006](docs/adr/0006-codigo-em-ingles-documentacao-em-portugues.md)).
- **Nomes vêm do glossário**: use a linha _Código_ do termo. Não invente sinônimos (ex.: Pedido é sempre `help_request`, nunca `ticket` ou `post`).
- Prefixo `eta_` em tudo o que é registrado no WordPress (post types, status, taxonomias, papéis, metadados com `_eta_`, opções, hooks próprios). Namespace PHP `EuTeAjudo\`, autoload PSR-4. Text domain `euteajudo`, tradução `pt_BR`.
- Post type com até 20 caracteres; `post_status` cabe em 20 caracteres.
- Segurança do WordPress sempre: capabilities em toda ação, nonces em formulários, sanitização na entrada, escape na saída, `$wpdb->prepare` em SQL.
- Anonimato: nunca exibir nome ou e-mail; `display_name`, `nickname` e autor de comentário são sempre o Apelido. Ver as proteções obrigatórias do ADR 0003.
- Acesso a Pedido por código aleatório e 404 para quem não tem acesso ([ADR 0005](docs/adr/0005-areas-publica-e-privada.md)).
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
| `frontend-design` | Identidade visual e direção das telas |
| `theme-factory` | Aplicar a identidade em materiais de divulgação |
| `webapp-testing` | Verificar o site local com Playwright |
| `skill-creator` (global do Claude Code) | Criar skills do projeto, como `euteajudo-identity` |
