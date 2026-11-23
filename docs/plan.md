# Plano de implementação do MVP

Base: [docs/mvp.md](mvp.md), [GLOSSARY.md](../GLOSSARY.md) e [docs/adr](adr/). Cada entrega termina com algo que funciona de ponta a ponta e tem testes automatizados. Identificadores de código seguem a linha _Código_ do glossário ([ADR 0006](adr/0006-english-code-portuguese-docs.md)).

```
E0 → ID → E1 → E2 → E3 → E4 → E5 ─┐
                            └→ E6 ─┼→ R1 → E8
                       E2 → E7 ────┘
```

E4 e E5 são do Agente e ficam detalhadas no repositório privado `euteajudo.org-agente`. E5 e E6 podem andar em paralelo depois de E3. A parte B de E0 pode andar em paralelo com ID.

## Pendências

| # | Pendência | Situação |
|---|---|---|
| P1 | Ambiente local com as versões da hospedagem | Resolvida: DDEV com PHP 8.5, MySQL 5.7 e Apache ([ADR 0004](adr/0004-ddev-and-hosting-parity.md)). |
| P2 | Separar o que é público do que é do Agente | Resolvida: dois repositórios; pipeline e riscos no repositório privado. |
| P3 | Login com Google: plugin pronto ou código próprio | Avaliar um plugin maduro e compatível com PHP 8.5; aceitar só se for possível impedir a gravação de nome e foto. Senão, código próprio com a biblioteca oficial do Google. (E1) |
| P4 | Serviço de e-mail transacional | Escolher pelo plano gratuito e pela entregabilidade (ex.: Brevo, Amazon SES). (E7) |
| P5 | Abilities API (núcleo do WordPress) como alternativa à REST nativa pura para os Agentes | Avaliar no início de E3. Se não simplificar, seguir com a REST nativa. |

## E0. Fundação

Objetivo: repositórios, regras e ambiente prontos antes de qualquer funcionalidade.

**Parte A: estrutura e regras**
- Pasta de trabalho `/mnt/dados/Dev/euteajudo.org` com `platform/` (repositório público `aleemerich/euteajudo.org`) e `agent/` (repositório privado `aleemerich/euteajudo.org-agente`).
- Ajustes de git para o disco NTFS: `core.fileMode=false`, `safe.directory`, `.gitattributes` com `eol=lf`.
- `AGENTS.md` e `CLAUDE.md` nos dois repositórios.
- Skills de terceiros em `.claude/skills/`, com origem registrada em `SOURCES.md`.
- Glossário com a linha _Código_; ADRs 0004 a 0006.

**Parte B: ambiente e qualidade**
- DDEV em `.ddev/` com as versões da hospedagem; WordPress baixado em pasta ignorada; plugin e tema montados; `WP_DEBUG_LOG` ligado; Query Monitor só local.
- Esqueleto do plugin: cabeçalho, text domain `euteajudo`, namespace `EuTeAjudo\` com autoload PSR-4 do Composer, módulos (`Accounts`, `HelpRequests`, `Replies`, `Triage`, `SupportServices`, `Agents`, `Privacy`, `Notifications`, `Admin`), mecanismo de migração por versão.
- Esqueleto do tema de blocos: `theme.json` e templates mínimos.
- PHPUnit (unidade sem WordPress e integração com a suíte de testes do WordPress), PHPStan, PHPCS (WordPress Coding Standards e PHPCompatibility para PHP 8.5), Playwright com `@axe-core/playwright`, ESLint e Prettier pelo `@wordpress/scripts`.
- Hook de pre-commit com as verificações rápidas e bloqueio de `.env` e de arquivos do Agente.
- Comando de exportação do banco para `backups/`. O script de dados de teste (WP-CLI) entra na E2, quando existirem Pedidos para criar.
- CI no GitHub Actions com a action oficial do DDEV.

Pronto quando: `ddev start` sobe `https://euteajudo.ddev.site` com plugin e tema ativos, e um teste de cada tipo passa no CI.

## ID. Identidade visual e tema

Objetivo: identidade aprovada e aplicada como tokens no tema, nas versões Noite e Dia.

- Três direções de identidade (conceito, logo, paleta, tipografia, tom de voz), cada uma aplicada às telas Início, Desabafar, Conversa e Meus Pedidos, no celular e no desktop, em `design/proposals/`. Skills: `frontend-design`, `theme-factory`.
- Revisão do mantenedor: aprova uma ou mais direções.
- `design/identity.md`, `design/tokens.json` (formato DTCG), `design/logo/`.
- Direção aprovada: **Luz acesa** ([design/identity.md](../design/identity.md)), com as versões Noite e Dia. A versão segue a escolha do visitante; sem escolha, a preferência do aparelho; sem preferência, Noite.
- `theme/theme.json` gerado a partir dos tokens, com as cores apontando para variáveis de CSS que trocam entre Noite e Dia (feito junto com o esqueleto do tema, na E0 parte B).
- Fontes servidas pelo tema (`design/fonts/web/`), nunca por terceiros.
- Skill do projeto `euteajudo-identity` (criada com `skill-creator`) com tokens, tom de voz e regras de uso para site, redes sociais e vídeos.
- Kit de divulgação em `design/social-kit/` (modelos de post, capa de vídeo, legenda).

Pronto quando: o mantenedor aprova a identidade e o tema exibe as versões Noite e Dia.

## E1. Contas e Apelido

Objetivo: qualquer pessoa cria conta e passa a existir só como "Amigo NNNNN".

- Apelido aleatório de 5 dígitos, único, em `_eta_alias`, gravado no hook `user_register` (cobre cadastro, Google, painel e WP-CLI). `display_name` e `nickname` iguais ao Apelido.
- Papéis `eta_friend` e `eta_agent`.
- Páginas Entrar, Cadastrar, Recuperar senha e Confirmar e-mail ([ADR 0005](adr/0005-public-and-private-areas.md)); confirmação de e-mail obrigatória antes do primeiro Pedido.
- Login com Google (P3), descartando nome e foto.
- Declaração de 18 anos ou mais e consentimento destacado, com data e versão do texto aceito em `user_meta`. Quem informa menos de 18 vê o Encaminhamento ao Pode Falar e ao CVV e não cria conta.
- Avatares desligados no site inteiro; `wp-admin` e barra de administração bloqueados para Amigos e Agentes.
- Exclusão lógica: e-mail trocado por hash, conta marcada como excluída, sessão encerrada, novo cadastro permitido com o mesmo e-mail.

Pronto quando: os testes E2E cobrem cadastro por e-mail, confirmação, exclusão e novo cadastro, e nenhuma tela ou resposta da API mostra nome ou e-mail.

## E2. Pedido do Amigo (primeira fatia de ponta a ponta)

Objetivo: o Amigo publica um Pedido, conversa nele e o encerra, ainda sem Agente.

- CPT `eta_help_request` não público, com capabilities próprias e `post_name` aleatório.
- Status `eta_awaiting_triage`, `eta_awaiting_reply`, `eta_in_conversation`, `eta_closed`, mudados por hooks ([ADR 0003](adr/0003-classic-wordpress-native-structures.md)).
- Taxonomias `eta_category`, `eta_severity`, `eta_risk_signal`, com os termos iniciais.
- Mascaramento de dados pessoais (telefone, e-mail, CPF, CEP, links) aplicado ao Pedido e às Respostas antes de gravar. É a parte com mais casos de teste de unidade.
- Blocos dinâmicos renderizados no servidor, com formulários comuns que funcionam sem JavaScript: Escrever (`/escrever/`), Meus Pedidos (`/meus-pedidos/`) e Conversa no mesmo bloco (Respostas `eta_reply`, Agradecimento, Encerramento com motivo, reabertura). Interactivity API fica para quando uma interação pedir atualização sem recarregar a página.
- Barra de navegação do Amigo (Escrever, Meus pedidos, Conta): links no cabeçalho em telas largas, barra inferior no celular.
- Comando `wp euteajudo seed` para criar Amigos e Pedidos fictícios no ambiente local (`--clean` remove) e `wp euteajudo reply <código> <texto>` para responder como Administrador enquanto não há Agente.
- Endereço `/meus-pedidos/{código}/` com 404 para quem não tem acesso ([ADR 0005](adr/0005-public-and-private-areas.md)).
- Proteções do ADR 0003: feeds, sitemaps e busca sem Pedidos, sem e-mails nativos de comentário, sem IP e user agent nos comentários, sem Akismet.

Pronto quando: o E2E cobre o caminho completo no tamanho de celular, e testes de integração provam que um Pedido não aparece para visitantes, para outros Amigos, nos feeds, na busca ou na REST sem autenticação.

## E3. API para Agentes

Objetivo: um cliente externo autenticado consegue fazer todo o trabalho de um Agente usando a REST nativa (P5).

- Rotas próprias em `/wp-json/euteajudo/v1` ([ADR 0007](adr/0007-agent-api-namespace.md)); contrato em [agent-api.md](agent-api.md). Capability `eta_work_as_agent` no papel `eta_agent`.
- Application Password para o usuário do Agente, só por HTTPS (`wp euteajudo agent create`).
- CPT `eta_support_service`, não público, com campos em metadados e as mesmas taxonomias do Pedido; catálogo inicial com CVV, SAMU, 190, 180, Disque 100 e Pode Falar.
- Metadados de controle: reserva (validade de 10 minutos), contador de falhas, horário da próxima tentativa, data da última manifestação do autor.
- Banco de Apelidos de Agente em `wp_options` sem autoload; um Apelido sorteado por Pedido.
- Hook na publicação da Resposta do Agente: aplica o Apelido do Pedido e recusa se a Resposta do autor informada não for a última.
- Registro da última consulta do Agente, para a tela de estado.

Pronto quando: testes de integração simulam um Agente que lista pendências, reserva, grava Triagem e Resumo, publica Resposta, recebe recusa por conflito e vê o status mudar.

## E4 e E5. Agente

Detalhadas no repositório privado. A plataforma só precisa estar com a E3 pronta.

## E6. Telas do Administrador

Objetivo: operar o site pelo painel do WordPress.

- Lista de Pedidos com filtros por Gravidade, Categoria, status, "sem Resposta há 24h" e "falha na verificação".
- Leitura do Pedido com Triagem, Resumo e Respostas.
- Edição de qualquer Pedido ou Resposta, com o original guardado em metadado.
- Resposta com o próprio Apelido (padrão) ou com o Apelido do Agente do Pedido, com a autoria real em metadado.
- Catálogo de Serviços de apoio pela interface nativa do CPT.
- Estado do Agente: última consulta e tamanho da fila.
- Agendamentos no WP-Cron: Encerramento automático após 30 dias com aviso ao autor; alerta de 24h sem Resposta de Agente; e-mail único por Pedido com falha na verificação.

Como ficou:

- A lista usa colunas próprias (Autor pelo Apelido, status, falhas, última mensagem do autor) e filtros de Gravidade, Categoria e situação ("Sem Resposta do Agente há 24h", "Falha na verificação").
- Na tela do Pedido, salvar nunca muda o status. As caixas nativas de comentários, autor e slug saem; entram "Pedido" (situação e histórico de edições), "Resumo (o autor não vê)" e "Conversa" (Respostas com a autoria real e formulário de Resposta com o próprio Apelido ou o do Agente). Pedidos não podem ser criados pelo painel.
- O original de Pedidos e Respostas editados fica em `_eta_original_title`, `_eta_original_content` e `_eta_edit_log` (Pedido) e em `_eta_original_content`, `_eta_edited_by` e `_eta_edited_at` (Resposta).
- Widget "EuTeAjudo: Agente" no Painel: última consulta à API (em vermelho depois de 30 minutos sem consulta), fila, atrasados e falhas.
- E-mails neutros ao autor: Resposta nova e Encerramento automático. O e-mail de falha na verificação sai na hora, uma vez por Pedido (E3).
- Agendamentos registrados no `init` (a publicação por FTP não roda a ativação): `euteajudo_close_inactive` (diário) e `euteajudo_alert_late` (de hora em hora, um aviso por mensagem do autor).

Pronto quando: os testes de integração cobrem a edição com auditoria, a Resposta com cada Apelido e os agendamentos.

## E7. E-mail e operação

Objetivo: o site roda na hospedagem compartilhada com e-mails confiáveis.

- Plugin SMTP com o serviço transacional (P4), compatível com PHP 8.5. SPF, DKIM e DMARC no domínio.
- E-mails neutros: confirmação de cadastro, Resposta nova, Encerramento automático, alertas ao Administrador.
- Cron do cPanel chamando `wp-cron.php` com `ea-php85` a cada 5 minutos.
- Instalação do WordPress em produção pelo cPanel; publicação do plugin e do tema por FTPS a partir do CI, com uma conta de FTP restrita ao `wp-content` do site.
- Log do WordPress fora do `public_html` e acesso a arquivos `error_log` bloqueado no `.htaccess`.
- Usuário de banco exclusivo do EuTeAjudo. Backup do banco conferido no painel da hospedagem.
- Teste do Agente contra a REST de produção (ModSecurity).
- Agente rodando na máquina do mantenedor contra o site publicado, com reinício automático.

Como ficou na plataforma (o restante é configuração do servidor, em [deploy.md](deploy.md)):

- SMTP sem plugin de terceiros: `EuTeAjudo\Operations\Mail` lê `EUTEAJUDO_SMTP_*` e `EUTEAJUDO_MAIL_FROM` do `wp-config.php`; remetente "EuTeAjudo".
- Endurecimento: XML-RPC desligado, versão do WordPress fora do `<head>`, Application Passwords só para Agentes e Administradores.
- Workflow manual `Deploy` (FTPS com `lftp`, só da `main`), que envia `plugin/` e `theme/` e visita o site para rodar as migrações.
- Migração 0.11.0 liga os links permanentes `/%postname%/` numa instalação nova.

Pronto quando: um Pedido criado no site publicado recebe Resposta e o e-mail chega com remetente e assunto neutros.

## R1. Revisão dos textos da interface

Objetivo: o mantenedor revisa e ajusta todos os textos que as pessoas leem.

- Fonte única dos textos: os arquivos de tradução `plugin/languages/euteajudo-pt_BR.po` e `theme/languages/euteajudo-theme-pt_BR.po` (os textos-fonte do código são em inglês; o que aparece no site é o `msgstr`), mais os e-mails e as páginas criadas pelas migrações.
- Gerar uma lista navegável de todos os textos, agrupados por tela, para a revisão.
- Aplicar os ajustes nos `.po`, compilar (`ddev composer i18n`) e conferir as telas.
- Pode acontecer a qualquer momento depois da E2; obrigatória antes da E8.

Pronto quando: o mantenedor aprova todos os textos.

## E8. Lançamento

Objetivo: abrir ao público.

- Termos de uso, política de privacidade e texto do consentimento.
- Textos de divulgação da IA no cadastro e na página do Pedido ([ADR 0002](adr/0002-ai-disclosure-in-the-interface.md)).
- Revisão do layout no celular, tablet e desktop, e de acessibilidade (contraste, teclado, leitor de tela).
- Rodada de testes manuais com cadastros e fluxos simulados pelo mantenedor.
- Validação do profissional concluída e ajustes aplicados.
- Remoção do help.euteajudo.org antigo.

Já feito na plataforma:

- Início público (`theme/templates/front-page.html` com o bloco `euteajudo/home`): janela acesa com a única animação do site (respeita `prefers-reduced-motion`), como funciona, aviso de IA e CVV.
- Páginas Sobre (`/sobre/`), Termos de uso (`/termos-de-uso/`) e Política de privacidade (`/privacidade/`), criadas pela migração 0.12.0 a partir de `plugin/content/pt_BR/` e editáveis no painel. A de privacidade vira a página de privacidade do WordPress. Os textos são rascunhos: campos marcados com `[preencher: ...]` (controlador, contato, provedores, prazos, foro) geram um aviso no painel até serem preenchidos.
- Consentimento do cadastro com links para os Termos e a Política.
- Rodapé com os três links e o 188.
- Exclusão de conta encerra os Pedidos abertos.

Pronto quando: os itens acima estão feitos e o mantenedor aprova a abertura.
