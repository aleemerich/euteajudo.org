# Roteiro de testes manuais

Passo a passo para ver no site local tudo o que foi implementado até a E8. Cada item diz o que fazer e o que deve acontecer. Marque `[x]` no que conferiu; anote o que estranhar, com a tela e o passo.

Tempo estimado: de 1h30 a 2h para o roteiro inteiro. Os blocos são independentes; dá para fazer em partes.

## Preparação

- [ ] No terminal, na pasta `platform/`: `ddev start`.
- [ ] Abra três abas fixas:
  - **Site**: https://euteajudo.ddev.site
  - **Mailpit** (todos os e-mails que o site manda localmente): https://euteajudo.ddev.site:8026
  - **Painel**: https://euteajudo.ddev.site/wp-admin (usuário `admin`, senha `admin`)
- [ ] Use **duas janelas**: uma normal, para o Administrador, e uma anônima, para o Amigo. Assim as duas sessões não se misturam.
- [ ] Para ver como celular: na janela anônima, F12 > ícone de celular (Toggle device toolbar) > Pixel 7.

Comandos úteis (no terminal, em `platform/`):

| Comando | Faz |
|---|---|
| `ddev wp euteajudo seed --friends=2` | Cria Amigos e Pedidos fictícios. Mostra e-mails e códigos. Senha de todos: `seed password 123` |
| `ddev wp euteajudo reply <código> "texto"` | Responde a um Pedido como Administrador |
| `ddev wp euteajudo age <código> --days=31` | Joga um Pedido para o passado (também aceita `--hours=25`) |
| `ddev wp cron event run <evento>` | Roda um agendamento na hora |
| `ddev wp euteajudo seed --clean` | Remove só o que o seed criou |

O **código** de um Pedido é a última parte do endereço da conversa: em `/meus-pedidos/k3x9q2m7a1bz/`, o código é `k3x9q2m7a1bz`.

## A. Início, identidade e páginas públicas

- [ ] **Início sem login** (janela anônima): título "Tem uma luz acesa aqui.", a janela âmbar acende uma vez, o bloco "Como funciona" diz que as respostas são de uma inteligência artificial, e há a caixa "Precisa de ajuda agora?" com "Ligar para o 188".
  - Esperado: só os botões e a janela usam âmbar.
- [ ] **Noite e Dia**: troque o tema do sistema operacional (ou, no DevTools, Rendering > `prefers-color-scheme`).
  - Esperado: o site segue o aparelho, e Noite é o padrão.
- [ ] **Rodapé**: abra "Sobre", "Termos de uso" e "Política de privacidade".
  - Esperado: as três abrem e mostram campos `[preencher: ...]`.
- [ ] **Botão "Começar a escrever"** sem login.
  - Esperado: leva para Criar conta.

## B. Cadastro e conta

- [ ] **Validação**: em Criar conta, clique em "Criar conta" sem marcar nada.
  - Esperado: um aviso pede a idade e o aceite.
- [ ] **Menor de 18**: marque "Tenho menos de 18 anos" e envie.
  - Esperado: a tela indica o Pode Falar e o CVV, e nenhuma conta é criada.
- [ ] **Cadastro por e-mail**: marque 18+, aceite os termos e use um e-mail inventado, como `teste1@example.org`, com senha de 10 caracteres ou mais.
  - Esperado: tela "Quase lá".
  - O texto do aceite tem links para os Termos e a Política.
- [ ] **Confirmação**: no Mailpit, abra o e-mail "EuTeAjudo: confirme seu e-mail" e clique no link.
  - Esperado: você entra como "Amigo NNNNNN".
  - No celular aparece a barra inferior (Escrever, Meus pedidos, Conta).
- [ ] **Sair e entrar de novo**: clique em "Sair" e entre com o mesmo e-mail e senha. Tente também uma senha errada.
  - Esperado: a senha errada mostra uma mensagem genérica, sem dizer se o e-mail existe.
- [ ] **Esqueci minha senha**: peça o link, abra o e-mail no Mailpit, crie uma senha nova e entre com ela.
- [ ] **Login com Google**: crie uma conta e entre com "Continuar com o Google".
  - Esperado: a conta Google também ganha um Apelido.
- [ ] **Minha conta**: clique no seu Apelido no topo.
  - Esperado: o e-mail aparece mascarado.
  - Troque a aparência entre Automática, Noite e Dia e recarregue: a escolha fica.
- [ ] **Exclusão de conta** (faça por último neste bloco, com uma conta descartável):
  - Escreva um Pedido com essa conta.
  - Em Minha conta, tente excluir sem marcar a caixa: aparece um aviso.
  - Marque a caixa e exclua: a conta some e você não consegue mais entrar com ela.
  - No Painel, o Pedido dessa conta aparece como "Encerrado".

## C. Pedido do Amigo

- [ ] **Escrever**: com um Amigo, vá em Escrever. Use o título "Teste de pedido" e um texto com um telefone, como "me liga 11912345678", e um e-mail.
  - Esperado: a conversa abre com "Pedido enviado".
  - O telefone e o e-mail viram "[dado pessoal removido]".
  - O aviso sobre a IA aparece embaixo.
- [ ] **Meus pedidos**: o Pedido aparece como "Aguardando resposta".
- [ ] **Acesso de outra pessoa**: copie o endereço da conversa. Numa terceira janela anônima, entre com outro Amigo (use um do seed) e cole o endereço.
  - Esperado: página não encontrada (404).
- [ ] **Sem login**: abra o mesmo endereço numa janela anônima sem login.
  - Esperado: vai para Entrar e, depois de entrar como o autor, volta para a conversa.

## D. Respostas, agradecimento, encerramento

- [ ] **Resposta**: no terminal, rode `ddev wp euteajudo reply <código> "Oi. Estou aqui com você."`.
  - Esperado: em Meus pedidos aparece "Resposta nova" com o ponto âmbar.
  - No Mailpit chega "EuTeAjudo: você tem uma nova mensagem", sem título nem texto do Pedido.
- [ ] **Agradecimento**: abra a conversa e clique em "Isso me ajudou".
  - Esperado: o botão fica marcado. Clique de novo para desmarcar.
- [ ] **Responder**: escreva uma resposta e envie.
  - Esperado: ela aparece na conversa como "Você", e o Pedido volta para "Aguardando resposta".
- [ ] **Encerrar**: em "Encerrar pedido", escolha "Estou melhor" e confirme.
  - Esperado: o aviso diz que o Pedido está encerrado, e em Meus pedidos o Pedido fica apagado.
- [ ] **Reabrir**: clique em "Reabrir pedido".
  - Esperado: o campo de resposta volta.

## E. Painel do Administrador

- [ ] **Widget**: no Painel, o widget "EuTeAjudo: Agente" mostra a última consulta (em vermelho se nunca houve consulta ou se passou de 30 minutos), a fila, os atrasados e as falhas.
- [ ] **Aviso de páginas**: há o aviso amarelo "preencha os campos marcados com [preencher: ...]", com links para as páginas.
- [ ] **Usuários**: a lista mostra a coluna de Apelido, e a busca por número encontra o Amigo.
- [ ] **Lista de Pedidos**: em Pedidos, as colunas mostram Autor (Apelido), Status, Última mensagem do autor e Respostas.
  - Teste os filtros de Gravidade, Categoria e Situação.
  - "Novo" não aparece: Pedidos não são criados pelo painel.
- [ ] **Abrir um Pedido**: a caixa "Pedido" mostra status, Apelido do autor, Apelido do Agente e falhas. As caixas "Resumo (o autor não vê)" e "Conversa" aparecem.
- [ ] **Editar com auditoria**: mude uma palavra do texto e clique em "Salvar alterações".
  - Esperado: o status não muda.
  - A caixa "Pedido" mostra "Editado por..." e "Texto original".
- [ ] **Resposta com o próprio Apelido**: em Conversa, escreva e envie com "Responder como Amigo N (você)".
- [ ] **Resposta com o Apelido do Agente**: envie outra com "Responder como Amigo M (Apelido do Agente neste Pedido)".
  - Esperado: na janela do Amigo, cada Resposta aparece com o Apelido escolhido.
  - No painel, entre parênteses, aparece quem escreveu de verdade.
- [ ] **Editar uma Resposta**: clique em "Editar" numa Resposta, mude o texto e salve.
  - Esperado: ao voltar ao Pedido, a Resposta mostra "Texto original".
- [ ] **Serviços de apoio**: em Pedidos > Serviços de apoio, os 6 serviços iniciais estão lá (CVV, SAMU, 190, 180, Disque 100, Pode Falar).
  - Abra um, mude o horário e salve: o campo fica.

## F. API do Agente

No terminal, em `platform/`:

```bash
ddev wp euteajudo agent create          # anote USER e PASSWORD (a senha só aparece agora)
U='amigo-NNNNNN'                         # o EUTEAJUDO_API_USER mostrado
P='xxxx xxxx xxxx xxxx xxxx xxxx'        # o EUTEAJUDO_API_PASSWORD mostrado
API=https://euteajudo.ddev.site/wp-json/euteajudo/v1
```

- [ ] **Sem login**: `curl -sk $API/queue`.
  - Esperado: `rest_forbidden`.
- [ ] **Fila**: `curl -sk -u "$U:$P" $API/queue`.
  - Esperado: lista de `id` dos Pedidos que aguardam. Escreva um Pedido novo como Amigo e anote o `id` dele: `ID=...`.
- [ ] **Reserva**: `curl -sk -u "$U:$P" -X POST $API/requests/$ID/reservation`.
  - Esperado: `reserved_until` 10 minutos à frente.
- [ ] **Detalhe**: `curl -sk -u "$U:$P" $API/requests/$ID`.
  - Esperado: o texto já mascarado, `author_alias`, `agent_alias` e `last_author_message_id` (0).
  - Não aparecem e-mail nem ID do autor.
- [ ] **Triagem**:

  ```bash
  curl -sk -u "$U:$P" -X POST -H 'Content-Type: application/json' \
    -d '{"categories":["emotional"],"severity":"high","risk_signals":["suicidal-ideation"],"summary":"Teste manual."}' \
    $API/requests/$ID/triage
  ```

  - Esperado: no Painel, o Pedido mostra Gravidade, Categoria e Resumo.
  - De novo no Detalhe: `support_services` traz o CVV primeiro.
- [ ] **Conflito**: como Amigo, responda no Pedido. Depois tente responder pela API:

  ```bash
  curl -sk -u "$U:$P" -X POST -H 'Content-Type: application/json' \
    -d '{"text":"Oi. Li o que você escreveu.","in_reply_to":0}' $API/requests/$ID/replies
  ```

  - Esperado: `eta_author_wrote_again` (409).
  - Refaça a Reserva, o Detalhe (anote o novo `last_author_message_id`) e a Triagem. Responda com `"in_reply_to":<esse número>`.
  - Esperado: `{"id":...}`. O Amigo vê a Resposta com o Apelido do Agente e recebe o e-mail de Resposta nova.
- [ ] **Falha na verificação**: reserve outro Pedido e rode `curl -sk -u "$U:$P" -X POST $API/requests/<outro id>/failures`.
  - Esperado: `failures: 1`, `retry_in: 300`. No Mailpit chega um e-mail para o Administrador, sem o texto do Pedido.
  - O Pedido sai da fila por 5 minutos e aparece no filtro "Falha na verificação" do painel.
- [ ] **Widget**: no Painel, a última consulta virou "há alguns segundos".
- [ ] `ddev wp euteajudo agent list` mostra o Agente e a última consulta.

## G. Agendamentos

- [ ] **Encerramento por inatividade**: rode `ddev wp euteajudo age <código> --days=31` e depois `ddev wp cron event run euteajudo_close_inactive`.
  - Esperado: o Pedido fica "Encerrado" e chega o e-mail "EuTeAjudo: um pedido seu foi encerrado".
  - O Amigo consegue reabrir.
- [ ] **Alerta de 24h**: com um Pedido aguardando resposta, rode `ddev wp euteajudo age <código> --hours=25` e depois `ddev wp cron event run euteajudo_alert_late`.
  - Esperado: chega ao Administrador "EuTeAjudo: pedidos esperando há mais de 24 horas", com o link do Pedido.
  - Rodando de novo, nenhum e-mail novo chega.

## H. Celular, teclado e acessibilidade

- [ ] No modo celular, percorra Início, Criar conta, Escrever, Meus pedidos e Conversa.
  - Esperado: nada corta, nada rola para o lado, e os botões são fáceis de tocar.
- [ ] Só com o teclado (Tab, Shift+Tab, Enter, espaço), crie um Pedido e encerre.
  - Esperado: o foco aparece sempre, em Noite e em Dia.
- [ ] Opcional: com um leitor de tela (Orca no Linux), ouça a tela Escrever e uma conversa.

## I. Revisão de textos (R1)

- [ ] Leia [texts-review.md](texts-review.md): todos os textos das telas, agrupados por tela, com o texto atual e o original em inglês.
- [ ] Anote o que quer mudar (tela, texto atual, texto novo). Também valem ajustes nas páginas Sobre, Termos e Política, que são editadas direto no painel.

## Limpeza

- [ ] `ddev wp euteajudo seed --clean`. Contas criadas à mão podem ser excluídas em Minha conta ou em Usuários.
