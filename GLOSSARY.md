# EuTeAjudo.org

Espaço anônimo onde pessoas que precisam de ajuda publicam o que estão vivendo e recebem respostas de quem se dispõe a ajudar.

Os termos do domínio são em português. O código é em inglês: a linha _Código_ de cada termo é a tradução oficial e deve ser usada em nomes de classes, funções, variáveis e identificadores do WordPress. Tudo o que é registrado no WordPress leva o prefixo `eta_`.

## Pessoas e papéis

**Amigo**:
Qualquer pessoa cadastrada. Para os outros Amigos, é identificada apenas pelo Apelido.
_Evitar_: usuário, pessoa, amigo aflito, membro
_Código_: `friend` · papel `eta_friend`

**Apelido**:
Nome público e anônimo, no formato "Amigo NNNNN", com número aleatório atribuído pelo sistema a toda conta do site, qualquer que seja o papel ou a forma de cadastro.
_Evitar_: nick, nome de usuário, login
_Código_: `alias` · `user_meta` `_eta_alias` (não usar `nickname`, campo nativo do WordPress)

**Ajudador**:
Amigo que completou o perfil de ajudador e aceitou o termo de responsabilidade, e por isso pode responder a Pedidos de outros Amigos.
_Evitar_: voluntário, amigo que ajuda, helper
_Código_: `helper` · papel `eta_helper`

**Agente**:
Sistema externo à plataforma (uma IA própria ou de terceiros) autorizado a ler Pedidos e publicar Respostas. Aparece aos Amigos com um Apelido diferente em cada Pedido.
_Evitar_: bot, robô, IA de apoio, serviço de IA
_Código_: `agent` · papel `eta_agent`

**Administrador**:
Pessoa que administra o site e pode ler, editar e responder qualquer Pedido para fins de operação e segurança.
_Evitar_: gestor, admin, moderador
_Código_: `administrator` · papel nativo `administrator`

## Pedido de ajuda

**Pedido**:
Título e texto livre, escritos pelo Amigo, em que ele conta o que está vivendo e pede ajuda.
_Evitar_: post, chamado, ticket, caso
_Código_: `help_request` · CPT `eta_help_request`

**Resumo**:
Frase única que descreve o problema de um Pedido. Todos os envolvidos a veem, exceto o autor do Pedido.
_Evitar_: título, assunto
_Código_: `summary` · `post_excerpt` do Pedido

**Resposta**:
Texto publicado dentro de um Pedido, escrito por um Ajudador, por um Agente, por um Administrador ou pelo próprio autor do Pedido.
_Evitar_: comentário, mensagem, post
_Código_: `reply` · `comment_type` `eta_reply`

**Triagem**:
Avaliação de um Pedido que estima a Gravidade da situação e a Categoria da ajuda necessária. Todos os envolvidos a veem, exceto o autor do Pedido.
_Evitar_: classificação, análise
_Código_: `triage`

**Categoria**:
Tipo de ajuda de que o Pedido precisa, como emocional, trabalho ou violência.
_Evitar_: tipo, assunto, tag
_Código_: `category` · taxonomia `eta_category`

**Gravidade**:
Quanto a situação ameaça a vida ou a integridade e com que urgência: baixa, média, alta ou crítica.
_Evitar_: urgência, prioridade, severidade
_Código_: `severity` · taxonomia `eta_severity`

**Sinal de risco**:
Marcador objetivo detectado no texto, como ideação suicida ou pedido de dinheiro, que pode elevar a Gravidade.
_Evitar_: alerta, flag
_Código_: `risk_signal` · taxonomia `eta_risk_signal`

**Encaminhamento**:
Indicação, feita dentro de uma Resposta, de um Serviço de apoio adequado ao caso.
_Evitar_: referência, indicação, redirecionamento
_Código_: `referral`

**Serviço de apoio**:
Organização ou canal de alcance nacional ou digital, cadastrado pelo Administrador, para onde um Encaminhamento pode levar o Amigo, como o CVV.
_Evitar_: parceiro, recurso, contato
_Código_: `support_service` · CPT `eta_support_service`

**Encerramento**:
Fim de um Pedido, declarado pelo autor ou aplicado pelo sistema após 30 dias sem Respostas novas. O autor pode reabrir o Pedido.
_Evitar_: fechamento, resolução, arquivamento
_Código_: `closure` · status `eta_closed`

## Situação do Pedido

**Aguardando Triagem**:
O autor publicou o Pedido ou uma Resposta nova e o Agente ainda não refez a Triagem.
_Código_: status `eta_awaiting_triage`

**Aguardando Resposta**:
A Triagem está feita e o Pedido espera uma Resposta do Agente.
_Código_: status `eta_awaiting_reply`

**Em conversa**:
A última manifestação no Pedido é de quem ajuda.
_Código_: status `eta_in_conversation`

## Sinais do autor

**Agradecimento**:
Marca que o autor coloca numa Resposta para dizer "isso me ajudou". Só quem escreveu a Resposta a vê.
_Evitar_: curtida, like, voto
_Código_: `thanks` · metadado de comentário

**Desconforto**:
Marca que o autor do Pedido, ou um Administrador, coloca numa Resposta de Ajudador para dizer "não me senti confortável com essa resposta". Nenhum outro Amigo a vê. Respostas de Agente não recebem Desconforto.
_Evitar_: denúncia, reclamação, dislike
_Código_: `discomfort` · metadado de comentário

**Afastamento**:
Remoção silenciosa de um Ajudador de um Pedido depois de um Desconforto. Para o Ajudador afastado, o Pedido aparece como encerrado.
_Evitar_: bloqueio, banimento, suspensão
_Código_: `removal` · metadado de post
