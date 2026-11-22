# MVP do EuTeAjudo.org

Resumo das decisões tomadas na sessão de definição de 17/11/2026, atualizado em 22/11/2026. Vocabulário e identificadores de código em [GLOSSARY.md](../GLOSSARY.md). Decisões estruturais em [docs/adr](adr/). O funcionamento interno do Agente fica no repositório privado `euteajudo.org-agente`.

## Conceito

Amigos desabafam de forma anônima e um Agente de IA responde a todos. Ajudadores humanos entram numa fase posterior, porque a carga emocional sobre quem ajudava foi o maior problema do projeto anterior.

## Amigos e cadastro

- Login com Google ou com e-mail e senha. E-mail e senha exigem confirmação do e-mail antes do primeiro Pedido.
- Só o e-mail é guardado. O nome vindo do Google é descartado.
- Toda conta do WordPress, de qualquer papel e criada por qualquer caminho, recebe um Apelido "Amigo NNNNN" (número aleatório, sem gênero) em `user_meta`.
- Idade mínima de 18 anos, por autodeclaração. Quem tem menos é encaminhado ao Pode Falar (UNICEF) e ao CVV.
- Consentimento destacado no cadastro para o tratamento dos textos e o envio a provedores de IA fora do Brasil.
- Exclusão lógica da conta: o e-mail vira hash, a conta é marcada como excluída e a pessoa pode se cadastrar de novo com conta e Apelido novos.

## Pedido e conversa

- Pedido com título obrigatório e texto livre, numa área limpa. Perguntas-guia aparecem como sugestão, sem campos.
- Telefone, e-mail, CPF, CEP e links detectados viram "[dado pessoal removido]".
- Conversa assíncrona, uma thread por Pedido. Sem mensagens privadas, sem curtidas, ranking ou contadores públicos.
- Agradecimento numa Resposta, visível só para quem a escreveu.
- Encerramento com um clique e motivo opcional ("Estou melhor", "Não quero mais receber mensagens", "Prefiro não dizer"). Encerramento automático após 30 dias sem Respostas novas. O autor pode reabrir.
- E-mails com remetente e assunto neutros, sem conteúdo do Pedido.
- Ao publicar, o autor é avisado de que a Resposta pode demorar. Não há prazo prometido.
- Meus Pedidos lista título, data, situação e indicador de Resposta nova.
- O Pedido é visível ao autor, aos Agentes e ao Administrador. Resumo e Triagem são visíveis a todos, exceto o autor.
- Áreas pública e privada, endereços e acesso: [ADR 0005](adr/0005-areas-publica-e-privada.md).

## Plataforma (este repositório, GPL-2.0-or-later)

- WordPress clássico: tema de blocos próprio focado em celular, Interactivity API, plugin próprio. Ver [ADR 0003](adr/0003-wordpress-classico-cpt-e-comentarios.md).
- Pedido como CPT `eta_help_request`, Resposta como comentário `eta_reply`, Resumo no `post_excerpt`.
- Status do Pedido, mudados por hooks: `eta_awaiting_triage`, `eta_awaiting_reply`, `eta_in_conversation`, `eta_closed`.
- Taxonomias não públicas `eta_category`, `eta_severity` e `eta_risk_signal`, editáveis no painel.
- API REST nativa para os Agentes, restrita por capabilities. Agente autenticado por Application Password no papel `eta_agent`.
- Proteções de anonimato: sem Gravatar, sem e-mails nativos de comentário, sem gravar IP e user agent, sem Akismet.
- Banco de Apelidos de Agente gerado aleatoriamente e guardado em `wp_options`. Cada Pedido sorteia um.
- Triagem gravada pelo Agente: Categoria, Gravidade e sinais de risco (taxonomias) e Resumo (`post_excerpt`).
- Hospedagem compartilhada cPanel (Apache, PHP 8.5, MySQL 5.7) e ambiente local DDEV com as mesmas versões ([ADR 0004](adr/0004-ddev-e-paridade-com-a-hospedagem.md)).
- Publicação por FTPS, sem SSH em produção.
- E-mail por serviço transacional com plugin SMTP.
- WP-Cron disparado pelas consultas do Agente e por um cron do cPanel que chama `wp-cron.php` a cada 5 minutos, independente do Agente, o que garante o alerta de fila parada.
- Alerta ao Administrador quando um Pedido passa 24 horas sem Resposta de Agente.
- Telas do Administrador:
  - lista de Pedidos com filtros por Gravidade, Categoria, status, "sem Resposta há 24h" e "falha na verificação";
  - leitura do Pedido com as Respostas;
  - edição de qualquer Pedido ou Resposta, com o texto original guardado em metadado;
  - Resposta com o próprio Apelido (padrão) ou com o Apelido do Agente daquele Pedido, com a autoria real registrada em metadado;
  - catálogo de Serviços de apoio (CPT `eta_support_service`, apenas nacionais ou digitais), com nome, descrição, Categorias, Gravidades, sinais de risco atendidos, canais, horário e público;
  - estado do Agente: hora da última consulta e tamanho da fila.

## Agente (repositório privado)

O Agente é um sistema externo: Triagem, Roteamento, Geração, Verificação e Publicação. Da plataforma ele precisa apenas da API: listar Pedidos pendentes por status, reservar, ler a conversa, gravar Triagem e Resumo, consultar Serviços de apoio, publicar Respostas e registrar falhas. Os detalhes ficam em `euteajudo.org-agente/docs/pipeline.md`.

## Fora do MVP

- Ajudadores, Desconforto, Afastamento, e-mail diário e Denúncia.
- Doações, parcerias, biblioteca, vídeos e outros idiomas (o código já nasce traduzível: textos-fonte em inglês e tradução `pt_BR`).
- A faixa de 16 a 17 anos.
- Trava atômica para vários Agentes simultâneos.
