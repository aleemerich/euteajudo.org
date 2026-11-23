# API dos Agentes num namespace próprio da REST do WordPress

Os Agentes usam rotas próprias em `/wp-json/euteajudo/v1`, registradas com `register_rest_route`, em vez dos endpoints genéricos `wp/v2` do CPT e dos comentários. Continua sendo a REST nativa do WordPress (mesma autenticação por Application Password, mesmo servidor, mesmas ferramentas), mas com um contrato pequeno e fechado. Decidimos assim porque três regras do Agente não cabem nos endpoints genéricos sem remendos:

- **Reserva atômica**: dois Agentes não podem pegar o mesmo Pedido. A reserva é um único `UPDATE` condicional no metadado, que só vence se a reserva atual tiver expirado ou já for do mesmo Agente.
- **Recusa por conflito**: a Resposta informa a última mensagem do autor que o Agente leu. Se o autor escreveu depois, a plataforma responde 409 e o Agente refaz.
- **Apelido por Pedido**: a Resposta sai com o Apelido de Agente sorteado para aquele Pedido, nunca com o Apelido da conta do Agente.

Os endpoints `wp/v2` também exporiam campos que o Agente não precisa (ID do autor, por exemplo) e exigiriam abrir o CPT e os comentários na REST, aumentando a superfície de vazamento.

## Considered Options

- `show_in_rest` no CPT, nas taxonomias e nos metadados, com Respostas pelo endpoint de comentários: descartado pelos três motivos acima. O endpoint de comentários não aceita `comment_type` próprio na criação.
- Abilities API do WordPress: deixada para quando houver um segundo tipo de cliente (por exemplo, um servidor MCP). As operações de `AgentWork` podem ser registradas como abilities sem mudar as regras.

## Consequências

- O contrato está em [docs/agent-api.md](../agent-api.md). Mudanças incompatíveis exigem `euteajudo/v2`.
- Só usuários com a capability `eta_work_as_agent` (papel `eta_agent`) passam do `permission_callback`. Administradores operam pelo painel, não pela API.
- As regras ficam em `EuTeAjudo\Agents\AgentWork`; o controlador REST só traduz HTTP.
