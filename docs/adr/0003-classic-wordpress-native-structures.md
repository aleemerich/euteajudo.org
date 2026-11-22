# WordPress clássico com estruturas nativas: CPT, comentários, status, taxonomias e REST

A plataforma é um WordPress clássico, com tema de blocos próprio focado em celular (depois tablet e desktop) e um plugin próprio. O Pedido é o custom post type `eta_help_request`, a Resposta é um comentário nativo com `comment_type` `eta_reply`, a situação do Pedido é um post status registrado com `register_post_status`, a Triagem usa taxonomias registradas com `register_taxonomy`, o Resumo fica no `post_excerpt`, e os Agentes usam a API REST nativa. O mantenedor conhece WordPress a fundo, opera o projeto sozinho e prefere estender os mecanismos nativos (hooks, actions, status, taxonomias, REST) a escrever estruturas paralelas.

## Considered Options

- WordPress headless com React: descartado por exigir dois sistemas.
- Tabelas próprias e endpoints REST próprios: mais isolados, descartados para aproveitar os recursos nativos.
- Taxonomia nativa `category`: descartada porque é compartilhada com posts comuns e tem arquivos públicos.

## Consequências

Os recursos nativos expõem dados por padrão. O plugin precisa garantir:

- CPT não público (`public`, `publicly_queryable` e `exclude_from_search` restritivos), com capabilities próprias. Feeds e sitemaps desligados.
- Pedidos nunca usam o status `publish`. Os status próprios (`eta_awaiting_triage`, `eta_awaiting_reply`, `eta_in_conversation`, `eta_closed`) são não públicos, e a API REST nativa só os entrega a quem tem a capability, como o papel `eta_agent` e o Administrador.
- Mudanças de status feitas por hooks quando uma ação acontece: Pedido criado ou Resposta do autor leva a `eta_awaiting_triage`; Triagem gravada leva a `eta_awaiting_reply`; Resposta de Agente leva a `eta_in_conversation`; Encerramento leva a `eta_closed`.
- Taxonomias `eta_category`, `eta_severity` e `eta_risk_signal` não públicas, editáveis no painel, compartilhadas com o CPT `eta_support_service`.
- Limites de tamanho do WordPress: nome de post type com até 20 caracteres e `post_status` numa coluna `varchar(20)`. Os nomes escolhidos respeitam isso.
- O Apelido de toda conta fica em `user_meta` (`_eta_alias`), gravado na criação do usuário por qualquer caminho.
- Avatares (Gravatar) desligados: o hash do e-mail enviado ao Gravatar permite identificar o Amigo.
- E-mails nativos de comentário (`wp_notify_postauthor`, `wp_notify_moderator`) desligados, porque incluem o texto da Resposta. A plataforma envia os próprios e-mails neutros.
- IP e user agent do autor do comentário não gravados.
- Nenhum antispam que envie o texto a terceiros (ex.: Akismet).
- `display_name`, `nickname` e `comment_author` sempre iguais ao Apelido. Nome vindo do login Google é descartado.
- A reserva de um Pedido por um Agente é um metadado gravado pela REST nativa. Isso não é atômico: basta com um único Agente, mas precisa de um mecanismo de trava antes de haver vários Agentes.
