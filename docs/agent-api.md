# API dos Agentes

Contrato entre a plataforma e qualquer Agente ([ADR 0001](adr/0001-external-agents-via-api.md), [ADR 0007](adr/0007-agent-api-namespace.md)). Base: `https://<site>/wp-json/euteajudo/v1`. Autenticação: HTTP Basic com o login e a Application Password do usuário do Agente, só por HTTPS.

Criar a conta do Agente: `wp euteajudo agent create` (mostra login, senha e URL uma única vez). Ver quando cada Agente consultou a API pela última vez: `wp euteajudo agent list`.

## Ciclo

1. `GET /queue?limit=10`: Pedidos que esperam um Agente (`eta_awaiting_triage` ou `eta_awaiting_reply`), sem reserva de outro Agente e fora da espera depois de uma falha. Ordem: autor ativo há mais tempo primeiro.
2. `POST /requests/{id}/reservation`: reserva por 10 minutos. 409 `eta_reserved` se outro Agente tiver a reserva. Renovar chamando de novo.
3. `GET /requests/{id}`: tudo o que o Agente precisa (abaixo).
4. `POST /requests/{id}/triage`: grava a Triagem e o Resumo; o status vai para `eta_awaiting_reply`.
5. `POST /requests/{id}/replies`: publica a Resposta com o Apelido de Agente do Pedido; o status vai para `eta_in_conversation` e a reserva é liberada.
6. Se a verificação falhar: `POST /requests/{id}/failures`. A reserva é liberada e o Pedido volta à fila depois de `retry_in` segundos (5, 10, 20, 40 e no máximo 60 minutos). O Administrador recebe um e-mail na primeira falha de cada Pedido. Uma nova mensagem do autor zera o contador.
7. `DELETE /requests/{id}/reservation`: devolve a reserva sem fazer nada.

`GET /vocabulary` lista os slugs aceitos na Triagem.

## Detalhe do Pedido

| Campo | Conteúdo |
|---|---|
| `id`, `status`, `title` | Pedido |
| `author_alias` | Apelido do autor, ex.: "Amigo 48213" |
| `agent_alias` | Apelido com que a Resposta será publicada neste Pedido |
| `created_at`, `author_active_at` | Datas em ISO 8601 (UTC). `author_active_at` serve para a espera de 10 minutos antes de responder |
| `messages` | Em ordem. A primeira (`id` 0) é o texto do Pedido. Cada item: `id`, `from` (`author` ou `helper`), `alias`, `text`, `date`, `thanked` (o autor marcou "isso me ajudou") |
| `last_author_message_id` | Valor que deve ir em `in_reply_to` ao responder |
| `failures`, `reserved_by_you` | Controle |
| `triage` | `main_category`, `categories`, `severity`, `risk_signals`, `summary` (vazios antes da primeira Triagem) |
| `support_services` | Serviços de apoio que combinam com a Triagem, os que batem com algum Sinal de risco primeiro: `name`, `description`, `phone`, `url`, `hours`, `scope`, `categories`, `risk_signals`, `matches_risk` |

O texto já chega com dados pessoais mascarados. A API nunca devolve ID, e-mail ou login do autor.

## Corpos

Triagem:

```json
{ "categories": ["emotional", "work-study"], "severity": "high", "risk_signals": ["suicidal-ideation"], "summary": "Uma frase." }
```

De 1 a 3 categorias, a principal primeiro; uma Gravidade; Sinais de risco opcionais; Resumo de até 300 caracteres. Slug desconhecido: 400 `eta_invalid_triage`.

Resposta:

```json
{ "text": "Texto da Resposta.", "in_reply_to": 0 }
```

## Erros

| Código | HTTP | Quando |
|---|---|---|
| `rest_forbidden` | 401/403 | Sem autenticação ou sem o papel de Agente |
| `eta_not_found` | 404 | Pedido inexistente ou encerrado |
| `eta_not_pending` | 409 | Pedido não espera Agente (em conversa ou encerrado) |
| `eta_reserved` | 409 | Outro Agente tem a reserva |
| `eta_not_reserved` | 409 | Triagem, Resposta ou falha sem reserva válida |
| `eta_author_wrote_again` | 409 | `in_reply_to` não é a última mensagem do autor: ler de novo e refazer |
| `eta_triage_required` | 409 | O autor escreveu e a Triagem precisa ser refeita antes da Resposta |
| `eta_invalid_triage` | 400 | Slug desconhecido, categorias demais ou Resumo vazio |
