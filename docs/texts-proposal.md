# Proposta de textos: voz do acolhimento

**Aprovada pelo mantenedor em 23/11/2026 e aplicada** (arquivos de tradução, código e páginas). Fica como registro das escolhas; a fonte atual dos textos é `docs/texts-review.md`.

Proposta para a revisão R1. Base: `docs/texts-review.md`, as páginas em `plugin/content/pt_BR/` e a skill `euteajudo-voice`. Nada foi mudado nos arquivos de tradução nem nas páginas; este documento só propõe.

Cada tabela segue as telas de `texts-review.md`. "manter" quer dizer que o texto atual já está certo. Placeholders (`%s`, `%1$s`, `%2$s`, `%d`) ficam com o mesmo sentido.

## Princípios aplicados

1. **A pessoa vem antes do sistema.** Os textos falam do que a pessoa pode fazer e sentir, não de como o site funciona por dentro ("você não precisa dizer seu nome" no lugar de "você vira um Amigo com número").
2. **Anonimato é proteção, não número.** O Apelido aparece como o nome que a gente usa para chamar a pessoa, com um número que é só dela. Saem "só um Amigo com número", "vira" e "ninguém sabe quem você é" (que soa como solidão); entra "sem dizer quem você é" e "ninguém vê o seu e-mail".
3. **O acolhimento é o destaque, a IA é informação.** A IA continua dita com clareza nos quatro lugares do ADR 0002 (início, cadastro, escrever, conversa), uma vez por lugar, sempre depois do que a pessoa ganha: uma resposta escrita com atenção, sem julgamento, seguindo regras de respeito. Ela nunca é o título nem a primeira frase.
4. **Primeiro a proteção, depois a regra.** "Para proteger você, a gente tira telefones e e-mails do texto" no lugar de "são removidos antes de salvar". O consentimento explica para que serve antes do que a pessoa aceita.
5. **Erros orientam e não culpam.** Saem "inválido", "não conferem", "venceu". Cada erro diz o que fazer em seguida e onde.
6. **A espera é acompanhada.** "Seu pedido chegou" e "a gente avisa por e-mail" no lugar de "Pedido enviado" e "avisamos". Nenhum prazo é prometido.
7. **"A gente" no lugar do impessoal.** Na interface, "a gente avisa" e "a gente tira" dão um rosto ao site sem prometer uma pessoa. Nas páginas legais, a primeira pessoa do plural continua.
8. **Botões e termos do glossário não mudam.** "Começar a escrever", "Enviar pedido", "Encerrar pedido", Pedido, Resposta, Amigo, Apelido e Encerramento continuam; muda a frase em volta.

## Início

| Atual | Proposta | Por quê |
|---|---|---|
| Ligar para o 188 | manter | |
| Meus pedidos | manter | |
| Tem uma luz acesa aqui. | manter | |
| Pode escrever a qualquer hora, do seu jeito. Ninguém sabe quem você é, e o que você escrever será lido com atenção. | Pode escrever a qualquer hora, do seu jeito, sem dizer quem você é. O que você escrever é lido com atenção. | "Ninguém sabe quem você é" soa como solidão; "sem dizer quem você é" soa como liberdade. |
| Começar a escrever | manter | |
| Já tenho conta | manter | |
| Como funciona | manter | |
| Crie uma conta só com e-mail. | manter | |
| Aqui você vira um Amigo com número, como Amigo 48213. Ninguém sabe quem você é. | Você não precisa dizer seu nome. Aqui chamamos você de Amigo, com um número que é só seu, como Amigo 48213. | O Apelido como cuidado, não como número. Tira o "vira". |
| Escreva o que está acontecendo. | Conte o que está acontecendo. | "Conte" é conversa; "escreva" é tarefa. |
| Do seu jeito, no seu tempo. Não precisa organizar nada. | manter | |
| Receba uma resposta escrita com cuidado. | manter | |
| As respostas são escritas por uma inteligência artificial criada para acolher. Avisamos por e-mail, sem mostrar o conteúdo. | As respostas são escritas por uma inteligência artificial feita para acolher: ela lê com atenção, não julga e segue regras de respeito. Quando chegar uma resposta, a gente avisa por e-mail, sem mostrar o que você escreveu. | A IA dita com o que ela oferece. "Conteúdo" vira "o que você escreveu". |
| Mais sobre como funciona | manter | |
| Precisa de ajuda agora? | Se estiver muito difícil agora | Crise dita como cuidado, não como pergunta de triagem. |
| O CVV atende de graça, a qualquer hora, pelo telefone 188 ou pelo chat em cvv.org.br. | O CVV conversa com você de graça, a qualquer hora, pelo telefone 188 ou pelo chat em cvv.org.br. | "Atende" é de central; "conversa com você" é de gente. |

## Rodapé

| Atual | Proposta | Por quê |
|---|---|---|
| Sobre o site | manter | |
| Em risco agora? Ligue 188 (CVV), de graça, a qualquer hora. | Se estiver difícil agora, ligue 188 (CVV). É de graça, a qualquer hora. | Mesmo cuidado do início, sem a palavra "risco" em todas as páginas. |

## Cabeçalho e navegação

| Atual | Proposta | Por quê |
|---|---|---|
| Sair | manter | |
| Entrar | manter | |
| Escrever | manter | |
| Conta | manter | |
| Principal (nome da navegação para leitor de tela) | manter | |
| EuTeAjudo.org, início (nome do logo para leitor de tela) | manter | |

"Marca" e a descrição do logo só aparecem no editor do painel e ficam fora desta proposta.

## Criar conta

### Tela de menor de 18 anos

| Atual | Proposta | Por quê |
|---|---|---|
| Este espaço é para maiores de 18 anos | Você merece ter com quem falar agora | A pessoa lê primeiro que é bem-vinda, depois a regra. |
| Você merece ser ouvido agora. Estes serviços atendem quem tem menos de 18 anos, de graça e com pessoas preparadas: | Este espaço é para quem tem 18 anos ou mais. Estes serviços conversam com quem tem menos de 18, de graça e com pessoas preparadas: | A regra fica, depois do acolhimento. "Conversam com" no lugar de "atendem". |
| Pode Falar (UNICEF) | manter | |
| Para quem tem de 13 a 24 anos, por WhatsApp. | manter | |
| Abrir o Pode Falar | manter | |
| CVV | manter | |
| Qualquer idade, a qualquer hora, pelo telefone 188 ou pelo chat. | manter | |

### Tela de confirmar o e-mail

| Atual | Proposta | Por quê |
|---|---|---|
| Quase lá | manter | |
| Mandamos um link para o e-mail que você informou. Abra o e-mail e toque no link para confirmar. O link vale por 48 horas. | Mandamos um link para o seu e-mail. Toque nele para confirmar que o e-mail é seu. Ele vale por 48 horas. | "Que você informou" é de formulário. Frases mais curtas. |
| O assunto é "EuTeAjudo: confirme seu e-mail". Se não chegar em alguns minutos, olhe a pasta de spam. Para receber um link novo, tente entrar. | O assunto é "EuTeAjudo: confirme seu e-mail". Se não aparecer em alguns minutos, vale olhar a pasta de spam. Para receber outro link, é só entrar com seu e-mail e senha. | "Tente entrar" soa como teste; "é só entrar" diz o caminho. |

### Formulário

| Atual | Proposta | Por quê |
|---|---|---|
| Aqui você é só um Amigo com número, como Amigo 48213. Ninguém vê seu e-mail. | Você não precisa dizer seu nome. Aqui chamamos você de Amigo, com um número que é só seu, como Amigo 48213. Ninguém vê o seu e-mail. | Era o texto mais pejorativo ("só um Amigo com número"). Mesmo texto do início, para a pessoa reconhecer. |
| Antes de começar | manter | |
| Tenho 18 anos ou mais | manter | |
| Tenho menos de 18 anos | manter | |
| Concordo que o que eu escrever seja lido por uma inteligência artificial criada para acolher, inclusive em serviços fora do Brasil. | Ver a seção "Consentimento no cadastro" | |
| Aceito os %1$s e a %2$s. | manter (ver "Consentimento no cadastro") | |
| Li a %s. | Ver a seção "Consentimento no cadastro" | |
| Termos de uso | manter | |
| Política de privacidade | manter | |
| Senha | manter | |
| Pelo menos 10 caracteres. | Use pelo menos %d caracteres. | O código já usa `At least %d characters.` (mínimo atual: 6). Verbo deixa a dica mais direta. |
| Criar conta | manter | |
| Já tem conta? Entrar | manter | |

## Entrar

| Atual | Proposta | Por quê |
|---|---|---|
| Sua senha foi alterada. Entre com a senha nova. | Senha nova guardada. Agora é só entrar com ela. | Confirma e convida, sem tom de aviso. |
| Bom ter você de volta. | manter | |
| Esqueci minha senha | manter | |
| Ainda não tem conta? Criar conta | manter | |
| Sua conta foi excluída. | Sua conta foi excluída, como você pediu. Se quiser voltar um dia, é só criar uma conta nova. | Reconhece a escolha da pessoa e deixa a porta aberta, sem prometer nada. |

## Esqueci minha senha e nova senha

| Atual | Proposta | Por quê |
|---|---|---|
| Se esse e-mail tiver conta, mandamos um link para criar uma senha nova. Olhe a caixa de entrada e a pasta de spam. | Se esse e-mail tiver uma conta aqui, mandamos um link para criar uma senha nova. Vale olhar a caixa de entrada e a pasta de spam. | "Vale olhar" sugere; "olhe" manda. Continua sem revelar se a conta existe. |
| Informe o e-mail da sua conta. Se ele estiver cadastrado, mandamos um link para criar uma senha nova. | Escreva o e-mail que você usa para entrar. Se ele tiver uma conta aqui, a gente manda um link para criar uma senha nova. | Tira "informe" e "cadastrado". |
| Enviar link | manter | |
| Conta criada com o Google? Volte e use "Continuar com o Google". | Se você entra com o Google, não precisa de senha: volte e use "Continuar com o Google". | Explica o porquê antes do caminho. |
| Voltar para Entrar | manter | |
| Abra esta página pelo link que mandamos para o seu e-mail. | Para criar uma senha nova, abra esta página pelo link que mandamos para o seu e-mail. | Diz para que serve a página. |
| Pedir um link novo | manter | |
| Nova senha | manter | |
| Salvar senha nova | manter | |

## Confirmação de e-mail

| Atual | Proposta | Por quê |
|---|---|---|
| E-mail confirmado. Você entrou como %s. | E-mail confirmado. Bom ter você aqui, %s. | Boas-vindas no lugar de registro de login. `%s` continua sendo o Apelido. |
| Ir para o início | manter | Se um dia o botão levar a Escrever, o texto passa a ser "Começar a escrever". |
| Abra o link que mandamos para o seu e-mail para confirmar. | Para confirmar seu e-mail, toque no link que mandamos para ele. | O motivo primeiro, o gesto depois. |

## Minha conta

| Atual | Proposta | Por quê |
|---|---|---|
| Aqui você é %s. | manter | Chama a pessoa pelo Apelido, como "Oi, Amigo 48213". |
| E-mail | manter | |
| Só você vê este e-mail. Ele serve para entrar e para avisar quando chega uma resposta. | Só você vê este e-mail. Ele serve para você entrar e para a gente avisar quando chegar uma resposta. | Diz quem faz o quê. |
| Aparência | manter | |
| Automática (segue o seu aparelho) | manter | |
| Noite | manter | |
| Dia | manter | |
| Esta escolha fica salva só neste navegador. | manter | Não há palavra mais simples que continue verdadeira ("aparelho" seria impreciso). O código já troca por outro texto; ver o apêndice. |
| Excluir conta | manter | |
| Seu e-mail é removido do site e ninguém consegue mais entrar nesta conta. O que você escreveu continua com o seu Apelido, sem o seu e-mail. Você pode criar uma conta nova depois; ela recebe outro Apelido. | Se você excluir a conta, a gente apaga o seu e-mail e ninguém mais consegue entrar nela. O que você escreveu fica guardado com o seu Apelido, sem o seu e-mail. Se quiser voltar depois, pode criar uma conta nova, que recebe outro Apelido. | Condicional ("se você excluir") porque a pessoa ainda está decidindo. Mesmos fatos. |
| Entendo e quero excluir minha conta | manter | |
| Excluir minha conta | manter | |

"Contas que administram o site não podem ser excluídas aqui." só aparece para o Administrador e fica fora desta proposta.

## Escrever

| Atual | Proposta | Por quê |
|---|---|---|
| Título | manter | |
| para você encontrar depois | manter | |
| O que está acontecendo? | manter | |
| Se ajudar, pense em há quanto tempo isso acontece, no que você já tentou e no que sente que precisa agora. Escreva do seu jeito; não precisa organizar nada. | Ver a seção "Escrever: dica de como começar" | Muda de lugar e de forma. |
| Telefones, e-mails e documentos são removidos antes de salvar. A resposta pode levar algumas horas; avisamos por e-mail. | Para proteger você, evite escrever nomes e endereços. Telefones, e-mails e documentos a gente tira do texto antes de guardar. A resposta pode levar algumas horas, e a gente avisa por e-mail. | Proteção antes da regra. Inclui o cuidado com nomes e endereços, que a remoção automática não pega (Termos de uso). |
| Enviar pedido | manter | |
| As respostas aqui são escritas por uma inteligência artificial criada para acolher. Se você corre risco agora, ligue 188. | As respostas daqui são escritas por uma inteligência artificial feita para acolher, com atenção e com regras de respeito. Se estiver muito difícil agora, ligue 188 (CVV), a qualquer hora. | Divulgação do ADR 0002 sem frieza; crise como cuidado. O mesmo texto aparece em Escrever e na Conversa. |

## Meus pedidos e Conversa

| Atual | Proposta | Por quê |
|---|---|---|
| [dado pessoal removido] | [removido para proteger você] | Aparece no meio do texto da própria pessoa; diz por que algo sumiu. Atualizar também a Política de privacidade. Textos já guardados continuam com o marcador antigo. |
| Aguardando resposta | Esperando resposta | Mais falado, menos de repartição. O mesmo texto é usado no painel; a mudança vale nos dois lugares. |
| Em conversa | manter | |
| Encerrado | manter | |
| Novo pedido | manter | |
| Nada por aqui ainda. Quando quiser escrever, é só começar. | manter | |
| Resposta nova | manter | |
| Você escreveu em %s | manter | |
| Pedido enviado. A resposta pode levar algumas horas; avisamos por e-mail. | Seu pedido chegou e vai ser lido com atenção. A resposta pode levar algumas horas; a gente avisa por e-mail. | "Chegou" diz que alguém recebeu; "enviado" só diz que saiu. |
| Você | manter | |
| Isso me ajudou | manter | |
| Este pedido está encerrado. Você pode reabrir quando quiser continuar a conversa. | manter | |
| Reabrir pedido | manter | |
| Mensagem enviada. A resposta pode levar algumas horas. | Sua mensagem chegou. A resposta pode levar algumas horas; a gente avisa por e-mail. | Este aviso fica na tela durante toda a espera; precisa acompanhar, não só registrar. |
| Responder | manter | |
| Escreva quando quiser | manter | |
| Enviar resposta | manter | |
| Encerrar pedido | manter | |
| Por que está encerrando? (opcional) | Se quiser, conte por que está encerrando | "(opcional)" é de formulário; "se quiser" diz o mesmo com calma. |
| Estou melhor | manter | |
| Não quero mais receber mensagens | Prefiro parar por aqui | Escolha legítima, dita sem tom de reclamação. |
| Prefiro não dizer | manter | |
| j \d\e F (formato de data) | manter | |
| j \d\e M, H\hi (formato de data e hora) | manter | |
| As respostas aqui são escritas por uma inteligência artificial criada para acolher. Se você corre risco agora, ligue 188. | Ver a tabela "Escrever" | Mesmo texto nas duas telas. |

## Mensagens de erro e de aviso (todas as telas)

| Atual | Proposta | Por quê |
|---|---|---|
| E-mail ou senha não conferem. Confira e tente de novo, ou use "Esqueci minha senha". | Não encontramos uma conta com esse e-mail e essa senha. Confira os dois e tente de novo, ou use "Esqueci minha senha". | "Não conferem" soa como acusação; "não encontramos" é do lado do site. |
| Confirme seu e-mail primeiro. Mandamos um link novo para sua caixa de entrada. | Falta só confirmar seu e-mail. Mandamos um link novo para ele; é só tocar no link. | "Primeiro" soa como bronca. |
| Escolha sua idade e aceite os termos para continuar. | Falta pouco: em "Antes de começar", escolha sua idade e marque a caixa de concordância. | Diz onde está o que falta. |
| Informe um e-mail válido. | Confira o e-mail. Ele precisa ter o formato nome@exemplo.com. | Sem "inválido"; mostra como deve ser. |
| Use pelo menos 10 caracteres na senha. | A senha precisa de pelo menos %d caracteres. | Mesmo número da dica do campo. O código já tem `Use at least 6 characters in the password.`; propor `%d` para não divergir. |
| Essa conta do Google ainda não tem cadastro aqui. Crie sua conta para continuar. | Ainda não existe uma conta aqui com esse Google. Crie sua conta para começar. | Tira "cadastro"; "começar" no lugar de "continuar". |
| Entrar com o Google não está disponível agora. Use seu e-mail e senha. | Entrar com o Google não está funcionando agora. Você pode usar seu e-mail e senha. | Oferece, não manda. |
| A entrada com o Google foi cancelada. | Você saiu da tela do Google antes de terminar. Pode tentar de novo quando quiser. | Diz o que aconteceu e o próximo passo. |
| Essa conta do Google não tem e-mail verificado. Use outra conta, ou seu e-mail e senha. | Essa conta do Google ainda não confirmou o e-mail dela. Você pode usar outra conta do Google, ou seu e-mail e senha. | Tira "verificado"; oferece as saídas. |
| A entrada com o Google não terminou. Tente de novo. | manter | |
| Esta página ficou aberta por muito tempo. Tente de novo. | manter | |
| Este link é inválido ou venceu. Peça um novo abaixo. | Este link não vale mais. Peça um novo logo abaixo. | Sem "inválido" nem "venceu". |
| Este link de confirmação é inválido ou venceu. Entre para receber um novo. | Este link de confirmação não vale mais. Entre com seu e-mail e senha para receber um novo. | Sem "inválido"; diz como entrar. |
| Algo deu errado. Tente de novo em alguns minutos. | Alguma coisa não funcionou do nosso lado. Tente de novo em alguns minutos. | Deixa claro que a culpa não é da pessoa. |
| Marque a caixa para confirmar que quer excluir sua conta. | manter | |
| Dê um título ao seu pedido, para encontrá-lo depois. | Falta um título. Pode ser qualquer palavra que ajude você a encontrar este pedido depois. | Tira a pressão de escolher um "bom" título. |
| O texto está vazio. Escreva algumas linhas para enviar o pedido. | manter | |
| A resposta está vazia. Escreva algo para enviar. | manter | |
| Este pedido está encerrado. Reabra para continuar a conversa. | manter | |
| Você não pode fazer isso aqui. | Isso não pode ser feito por aqui. Seus pedidos estão em Meus pedidos. | Sem "você não pode"; mostra um caminho. |
| Não encontramos este pedido. | Não encontramos este pedido. Seus pedidos estão em Meus pedidos. | Mostra um caminho. |

## Partes comuns dos formulários e login com Google

| Atual | Proposta | Por quê |
|---|---|---|
| Mostrar | manter | |
| Ocultar | manter | |
| Continuar com o Google | manter | |
| ou com e-mail | manter | |

## E-mails

Só os e-mails que um Amigo recebe. Os avisos de faixa de Apelidos, de falha na verificação e de pedidos esperando há 24 horas vão para o Administrador e ficam fora desta proposta.

| Atual | Proposta | Por quê |
|---|---|---|
| Amigo %s | manter | |
| EuTeAjudo: confirme seu e-mail | manter | Assunto neutro; também citado na tela "Quase lá". |
| `Olá, %1$s.<br><br>Para confirmar este e-mail no EuTeAjudo.org, abra o link abaixo em até 48 horas:<br><br>%2$s<br><br>Se você não criou uma conta, pode ignorar esta mensagem.` | `Oi, %1$s.<br><br>Falta só confirmar este e-mail para você começar no EuTeAjudo.org. Abra o link abaixo em até 48 horas:<br><br>%2$s<br><br>Se não foi você quem criou a conta, pode ignorar esta mensagem.<br><br>EuTeAjudo.org` | "Oi" como nos outros e-mails; diz para que serve; assina como os outros. |
| EuTeAjudo: acesso à conta | manter | |
| `Alguém tentou criar uma conta no EuTeAjudo.org com este e-mail, que já tem cadastro.<br><br>Se foi você, entre aqui:<br>%1$s<br><br>Esqueceu a senha? Crie uma nova aqui:<br>%2$s<br><br>Se não foi você, pode ignorar esta mensagem. Nada foi alterado.` | `Alguém tentou criar uma conta no EuTeAjudo.org com este e-mail, mas ele já tem uma conta aqui.<br><br>Se foi você, é só entrar:<br>%1$s<br><br>Esqueceu a senha? Crie uma nova aqui:<br>%2$s<br><br>Se não foi você, pode ignorar esta mensagem. Nada foi alterado.` | Tira "cadastro"; "é só entrar". |
| EuTeAjudo: crie uma senha nova | manter | |
| `Para criar uma senha nova no EuTeAjudo.org, abra o link abaixo:<br><br>%s<br><br>Se você não pediu isso, pode ignorar esta mensagem. Sua senha continua a mesma.` | manter | |
| EuTeAjudo: você tem uma nova mensagem | manter | |
| `Oi, %1$s.<br><br>Há uma Resposta nova em um dos seus pedidos. Para ler, entre no site:<br>%2$s<br><br>EuTeAjudo.org` | `Oi, %1$s.<br><br>Chegou uma resposta em um dos seus pedidos. Ela fica guardada lá para quando você quiser ler:<br>%2$s<br><br>EuTeAjudo.org` | Sem pressa: a pessoa lê quando puder. Continua sem mostrar nada do Pedido. |
| EuTeAjudo: um pedido seu foi encerrado | manter | |
| `Oi, %1$s.<br><br>Um dos seus pedidos ficou 30 dias sem mensagens novas e foi encerrado. Você pode reabrir quando quiser:<br>%2$s<br><br>EuTeAjudo.org` | `Oi, %1$s.<br><br>Um dos seus pedidos ficou 30 dias sem mensagens novas, então ele foi encerrado. A conversa continua guardada, e você pode reabrir quando quiser:<br>%2$s<br><br>EuTeAjudo.org` | Diz que nada se perdeu. |

## Nomes de páginas criadas na instalação

| Atual | Proposta | Por quê |
|---|---|---|
| Confirmar e-mail | manter | |
| Minha conta | manter | |
| Sobre | manter | |

Categorias, Gravidades e Sinais de risco não aparecem para o autor do Pedido (glossário: Triagem). As descrições dos Serviços de apoio vão para o Agente pela API e não são mostradas direto na tela. Os dois grupos ficam fora desta proposta.

## Escrever: dica de como começar

**Problema.** A dica está embaixo de uma caixa de texto de 10 linhas. Quem chega lê de cima para baixo, vê a caixa vazia e trava antes de ler a dica, ou começa a escrever e nunca chega nela. Além disso, "Se ajudar, pense em há quanto tempo..." é uma frase longa que esconde três perguntas.

**Proposta (recomendada): a dica entre o rótulo e a caixa, em forma de perguntas.**

Ordem na tela:

```
Título  para você encontrar depois
[ campo do título ]

O que está acontecendo?
Escreva do seu jeito, não precisa organizar nada. Se não souber por onde começar, estas perguntas podem ajudar:
  - Há quanto tempo isso acontece?
  - O que você já tentou?
  - Do que você sente que precisa agora?
[ caixa de texto ]

Para proteger você, evite escrever nomes e endereços. ...
[ Enviar pedido ]

As respostas daqui são escritas por uma inteligência artificial ...
```

Textos exatos:

| Elemento | Texto |
|---|---|
| Rótulo da caixa (manter) | O que está acontecendo? |
| Linha logo abaixo do rótulo | Escreva do seu jeito, não precisa organizar nada. Se não souber por onde começar, estas perguntas podem ajudar: |
| Pergunta 1 | Há quanto tempo isso acontece? |
| Pergunta 2 | O que você já tentou? |
| Pergunta 3 | Do que você sente que precisa agora? |

Notas de implementação:

- A linha e as três perguntas ficam num só bloco com o `id="eta-write-guides"`, que a caixa já usa em `aria-describedby`. Quem usa leitor de tela ouve a dica ao entrar na caixa.
- Estilo da dica (`eta-hint`): texto menor e na cor secundária, lista sem números (as perguntas não são uma sequência). A caixa continua sendo o que mais chama atenção.
- Não usar `placeholder` na caixa para as perguntas: ele some quando a pessoa começa a digitar e tem pouco contraste.
- Cada pergunta é uma string própria, para poder traduzir e ajustar uma de cada vez.

**Alternativa, se no celular a caixa ficar baixa demais na tela:** uma linha visível e as perguntas recolhidas.

| Elemento | Texto |
|---|---|
| Linha logo abaixo do rótulo | Escreva do seu jeito, não precisa organizar nada. |
| Resumo de um `details` fechado, logo abaixo | Não sabe por onde começar? |
| Conteúdo ao abrir | Estas perguntas podem ajudar. Responda só as que fizerem sentido: Há quanto tempo isso acontece? O que você já tentou? Do que você sente que precisa agora? (as três em lista) |

A recomendada é a primeira: quem está travado não precisa descobrir que há um botão para abrir.

## Consentimento no cadastro

**O que a lei e o ADR 0002 exigem e continua.** Uma caixa de marcar visível e separada, que a pessoa marca por conta própria, dizendo de forma específica que (a) o que ela escrever é lido por uma inteligência artificial e (b) isso pode passar por serviços fora do Brasil (LGPD, arts. 11, I, e 33, VIII), com links para os Termos de uso e a Política de privacidade.

**O que muda.** O texto atual põe a IA como sujeito e a pessoa como objeto ("seja lido por uma inteligência artificial"), sem dizer para quê. A proposta diz para que serve (responder a pessoa), diz a proteção que vem junto (o e-mail nunca vai) e deixa a caixa com o mesmo peso visual das opções de idade, sem moldura, cor ou negrito que a façam parecer o centro da tela.

### Proposta (recomendada): uma caixa, duas frases curtas, uma linha de proteção

```
Antes de começar
( ) Tenho 18 anos ou mais
( ) Tenho menos de 18 anos
[ ] Concordo que uma inteligência artificial feita para acolher leia o que eu escrever, para poder me responder, inclusive usando serviços fora do Brasil. Aceito os Termos de uso e a Política de privacidade.
    Seu e-mail nunca vai junto, e telefones e documentos são tirados do texto antes.
```

| Elemento | Atual | Proposta | Por quê |
|---|---|---|---|
| Texto da caixa | Concordo que o que eu escrever seja lido por uma inteligência artificial criada para acolher, inclusive em serviços fora do Brasil. | Concordo que uma inteligência artificial feita para acolher leia o que eu escrever, para poder me responder, inclusive usando serviços fora do Brasil. | Diz para quê. Mantém os dois fatos (IA lê; fora do Brasil). |
| Continuação dentro da caixa, com links | Aceito os %1$s e a %2$s. | manter | Curto e claro; `%1$s` = link dos Termos de uso, `%2$s` = link da Política de privacidade. |
| Continuação quando só existe a Política de privacidade | Li a %s. | Li a %s. (manter) | Caso de reserva; `%s` = link da Política de privacidade. |
| Linha nova, fora do rótulo, em texto menor (`eta-note`) logo abaixo da caixa | (não existe) | Seu e-mail nunca vai junto, e telefones e documentos são tirados do texto antes. | Proteção ao lado do consentimento. Fato já dito na Política de privacidade. |

Notas de implementação:

- A linha de proteção fica fora do `label`, para não aumentar o que a pessoa "assina". Pode ser ligada à caixa por `aria-describedby`.
- O texto da caixa usa o mesmo tamanho e peso das opções de idade. Nada de fundo colorido, borda ou âmbar.
- Não mover o consentimento para uma tela separada nem para depois do botão do Google: ele precisa ser marcado antes das duas formas de criar conta.

### Alternativa, não recomendada: duas caixas

1. "Concordo que uma inteligência artificial feita para acolher leia o que eu escrever, para poder me responder."
2. "Concordo que isso possa passar por serviços fora do Brasil."

Fica mais "específico" no sentido da lei, mas dá à IA e ao exterior dois lugares de destaque na primeira tela, que é o contrário do que o mantenedor pediu. A recomendada já é específica: uma caixa própria, separada da idade, com os dois fatos escritos por extenso.

## Páginas

### Sobre (`plugin/content/pt_BR/about.html`), texto completo

Mudanças principais: o Apelido como cuidado; a IA apresentada pelo que oferece e pelas regras que segue; crise como cuidado; "quem mantém" passa a "quem cuida do projeto". Todos os fatos ficam (regras de conduta, verificação, CVV, 192, 190, código aberto, GitHub, nome do mantenedor, contato).

```html
<!-- wp:paragraph --><p>Tem uma luz acesa aqui. O EuTeAjudo.org é um lugar para escrever o que está pesando, a qualquer hora, sem precisar dizer quem você é. Aqui chamamos você de Amigo, com um número que é só seu, como Amigo 48213. Ninguém vê o seu nome nem o seu e-mail.</p><!-- /wp:paragraph -->

<!-- wp:heading --><h2 class="wp-block-heading">Quem responde</h2><!-- /wp:heading -->

<!-- wp:paragraph --><p>Hoje, as respostas são escritas por uma inteligência artificial feita para acolher. Ela segue regras de conduta: escuta antes de sugerir, não julga, não faz diagnósticos, não receita, não pede dados pessoais e não fala de dinheiro. Antes de chegar até você, cada resposta passa por uma verificação dessas regras, e quem cuida do site acompanha o funcionamento.</p><!-- /wp:paragraph -->

<!-- wp:paragraph --><p>Ela não substitui um profissional de saúde nem um serviço de emergência. Quando percebe que a situação pede mais cuidado, ela indica onde encontrar ajuda, como o CVV (188).</p><!-- /wp:paragraph -->

<!-- wp:heading --><h2 class="wp-block-heading">Como funciona</h2><!-- /wp:heading -->

<!-- wp:list --><ul class="wp-block-list"><!-- wp:list-item --><li>Você cria uma conta com um e-mail ou com o Google. Ninguém vê o seu e-mail.</li><!-- /wp:list-item --><!-- wp:list-item --><li>Você conta o que está acontecendo, do seu jeito e no seu tempo. Não precisa organizar nada.</li><!-- /wp:list-item --><!-- wp:list-item --><li>A resposta pode levar algumas horas. A gente avisa por e-mail, sem mostrar o que você escreveu.</li><!-- /wp:list-item --><!-- wp:list-item --><li>Você pode responder, marcar uma resposta que ajudou, encerrar o pedido e reabrir quando quiser.</li><!-- /wp:list-item --></ul><!-- /wp:list -->

<!-- wp:heading --><h2 class="wp-block-heading">Se estiver muito difícil agora</h2><!-- /wp:heading -->

<!-- wp:paragraph --><p>O CVV conversa com você de graça, a qualquer hora, pelo telefone 188 ou pelo chat em <a href="https://cvv.org.br">cvv.org.br</a>. Em emergência médica, ligue 192 (SAMU). Se você estiver em perigo agora, ligue 190.</p><!-- /wp:paragraph -->

<!-- wp:heading --><h2 class="wp-block-heading">Quem cuida do projeto</h2><!-- /wp:heading -->

<!-- wp:paragraph --><p>O EuTeAjudo.org é um projeto de ajuda, gratuito e de código aberto. Qualquer pessoa pode ver como ele funciona e contribuir em <a href="https://github.com/aleemerich/euteajudo.org">github.com/aleemerich/euteajudo.org</a>. Hoje, quem cuida do projeto é Alessandro Rocha Emerich.</p><!-- /wp:paragraph -->

<!-- wp:paragraph --><p>Se quiser falar com a gente, escreva para <a href="mailto:contato@euteajudo.org">contato@euteajudo.org</a>.</p><!-- /wp:paragraph -->
```

### Termos de uso (`plugin/content/pt_BR/terms.html`), só os trechos frios

Os demais trechos ficam como estão (data, "O que o EuTeAjudo.org não é" exceto o item de emergência, "Não peça nem ofereça dinheiro", "Não use o espaço para ameaçar...", "Você pode encerrar um pedido...", "Você pode excluir a sua conta...", "Mudanças nestes termos", "Lei aplicável").

| Atual | Proposta | Por quê |
|---|---|---|
| (depois do parágrafo da data, não existe) | Estes combinados existem para proteger você e as outras pessoas que escrevem aqui. | Parágrafo novo: diz para que servem os termos antes das regras. |
| Um espaço anônimo para escrever o que você está vivendo e receber respostas de acolhimento. As respostas são escritas por uma inteligência artificial criada para acolher. O serviço é gratuito. | Um espaço para escrever o que você está vivendo, sem dizer quem você é, e receber respostas escritas com cuidado. As respostas são escritas por uma inteligência artificial feita para acolher. O serviço é gratuito. | "Anônimo" dito como liberdade; mesmos fatos. |
| Não é serviço de emergência. Se você corre risco agora, ligue 188 (CVV), 192 (SAMU) ou 190 (Polícia). | Não é serviço de emergência. Se você estiver em risco agora, ligue 188 (CVV), 192 (SAMU) ou 190 (Polícia). Eles atendem a qualquer hora. | Acrescenta que há alguém a qualquer hora. |
| Pessoas com 18 anos ou mais. Se você tem menos de 18 anos, o Pode Falar e o CVV atendem você. (com link no Pode Falar) | Pessoas com 18 anos ou mais. Se você tem menos de 18 anos, você também merece ter com quem falar: o Pode Falar e o CVV conversam com você, de graça. (manter o link no Pode Falar) | A regra fica; a pessoa mais nova não se sente rejeitada. |
| A inteligência artificial pode errar ou não entender o que você quis dizer. Ela não faz diagnósticos, não receita, não se apresenta como profissional e não promete estar sempre presente. Use o que fizer sentido para você e procure ajuda profissional quando precisar. | Mesmo feita para acolher, a inteligência artificial pode errar ou não entender o que você quis dizer. Ela não faz diagnósticos, não receita, não se apresenta como profissional e não promete estar sempre presente. Fique com o que fizer sentido para você, e procure ajuda profissional quando precisar. | "Fique com" é mais gentil que "use". Mesmos limites. |
| (lista "Combinados", primeiro item) Não escreva nome, telefone, endereço, documentos ou outros dados que identifiquem você ou outra pessoa. Telefones, e-mails, CPF, CEP e links são removidos automaticamente, mas a remoção não pega tudo. | Para proteger você e outras pessoas, não escreva nome, telefone, endereço, documentos ou outros dados que identifiquem alguém. Telefones, e-mails, CPF, CEP e links são tirados do texto automaticamente, mas a remoção não pega tudo. | Proteção antes da regra. |
| Não tente fazer a inteligência artificial sair das regras dela. | Não tente fazer a inteligência artificial sair das regras dela. Elas existem para proteger quem escreve aqui. | Explica o porquê. |
| Quem administra o site pode ler, editar ou encerrar qualquer pedido para proteger você e outras pessoas, por exemplo para remover dados pessoais. | Para proteger você e outras pessoas, quem administra o site pode ler, editar ou encerrar qualquer pedido, por exemplo para tirar dados pessoais que ficaram no texto. | Proteção primeiro; mesmo poder do Administrador. |
| Pedidos sem mensagens novas por 30 dias são encerrados automaticamente. Você pode reabrir. | Um pedido que fica 30 dias sem mensagens novas é encerrado automaticamente. A conversa continua guardada, e você pode reabrir quando quiser. | Diz que nada se perde. |
| O serviço é oferecido como está, sem garantia de funcionamento contínuo. Ele pode ficar fora do ar ou mudar. | O EuTeAjudo.org é oferecido como está, sem garantia de funcionamento contínuo. Ele pode ficar fora do ar ou mudar. Por isso, numa emergência, procure sempre o 188, o 192 ou o 190. | Mesma isenção, com um cuidado prático junto. |

### Política de privacidade (`plugin/content/pt_BR/privacy.html`), só os trechos frios

Ficam como estão: data, introdução, "Quem cuida dos dados", "Não guardamos nome, foto...", "Por que podemos tratar esses dados" (artigos 7º, I; 11, I; 33, VIII), cookies, "Mudanças nesta política". Todos os fatos continuam: Alessandro Rocha Emerich como controlador, contato@euteajudo.org, HostGator para hospedagem e e-mail, 7 dias de registros de acesso, Google (Gemini), Anthropic e OpenRouter, art. 18, ANPD.

| Atual | Proposta | Por quê |
|---|---|---|
| Seu Apelido, um número sorteado, como Amigo 48213. | Seu Apelido, o nome com que chamamos você aqui, como Amigo 48213. O número é sorteado e não tem ligação com quem você é. | Apelido como proteção, não como número. |
| O que você escreve: pedidos e respostas. Antes de gravar, telefones, e-mails, CPF, CEP e links são trocados por "[dado pessoal removido]". | O que você escreve: pedidos e respostas. Antes de guardar, telefones, e-mails, CPF, CEP e links são trocados por "[removido para proteger você]". | Acompanha o novo marcador da interface. Se o marcador não mudar, manter o atual. |
| A inteligência artificial que responde. Para isso, o texto do pedido e das respostas, já com os dados pessoais removidos, e o seu Apelido são enviados a provedores de inteligência artificial que podem estar fora do Brasil, como Google (Gemini), Anthropic e OpenRouter. Seu e-mail nunca é enviado. | A inteligência artificial que escreve as respostas. Para que ela possa responder você, o texto do pedido e das respostas, já com os dados pessoais removidos, e o seu Apelido são enviados a provedores de inteligência artificial que podem estar fora do Brasil, como Google (Gemini), Anthropic e OpenRouter. Seu e-mail nunca é enviado. | Diz para quê. Mesmos provedores e mesmo alcance. |
| Quem administra o site, para operação e segurança. | Quem administra o site, para manter tudo funcionando e para proteger você e outras pessoas. | Tira o jargão; mesma finalidade. |
| Mandamos e-mails só para confirmar o cadastro, trocar a senha, avisar que chegou uma resposta e avisar que um pedido foi encerrado. Os e-mails nunca trazem o título nem o texto do pedido. O envio usa o serviço de e-mail da própria hospedagem (HostGator). | Mandamos e-mails só para confirmar o seu e-mail, trocar a senha, avisar que chegou uma resposta e avisar que um pedido foi encerrado. Para proteger você, os e-mails nunca trazem o título nem o texto do pedido. O envio usa o serviço de e-mail da própria hospedagem (HostGator). | Tira "cadastro"; diz por que o e-mail é discreto. |
| Enquanto a sua conta existir. Ao excluir a conta em Minha conta: o seu e-mail é apagado (guardamos só uma marca criptográfica que não permite recuperá-lo), o login com o Google é desligado, as sessões são encerradas e os pedidos abertos são encerrados. Os textos continuam guardados com o Apelido, sem ligação com nenhum dado de contato. | Enquanto a sua conta existir. Se você excluir a conta em Minha conta, a gente apaga o seu e-mail (fica só uma marca criptográfica, que não permite recuperá-lo), desliga a entrada com o Google, tira a conta de todos os aparelhos em que ela estava aberta e encerra os pedidos abertos. Os textos continuam guardados com o Apelido, sem ligação com nenhum dado de contato. | Mesma lista, em frases de gente ("sessões" vira "aparelhos em que a conta estava aberta"). |
| Pela LGPD (art. 18), você pode pedir confirmação de que tratamos seus dados, acesso, correção, informação sobre com quem compartilhamos, e revogar o consentimento. Revogar o consentimento é excluir a conta. Para qualquer pedido, escreva para contato@euteajudo.org. Você também pode reclamar à Autoridade Nacional de Proteção de Dados (ANPD). | Pela LGPD (art. 18), você pode pedir confirmação de que tratamos seus dados, acesso, correção, informação sobre com quem compartilhamos, e revogar o consentimento. Para revogar o consentimento, basta excluir a conta em Minha conta. Para qualquer pedido, escreva para contato@euteajudo.org. Você também pode reclamar à Autoridade Nacional de Proteção de Dados (ANPD). | "Revogar é excluir" vira o caminho a seguir. |
| O EuTeAjudo.org é só para maiores de 18 anos. Se percebermos que uma conta é de alguém com menos de 18, indicamos serviços adequados e podemos encerrar a conta. | O EuTeAjudo.org é só para quem tem 18 anos ou mais. Se percebermos que uma conta é de alguém com menos de 18 anos, indicamos serviços feitos para essa idade, como o Pode Falar e o CVV, e podemos encerrar a conta. | Diz quais serviços; mesma regra. |

## Apêndice: textos que já estão no código e ainda não estão em `texts-review.md`

O código mudou depois da última geração de `texts-review.md`. Estes textos ainda não têm tradução no `.po`. Proposta de `msgstr`:

| Texto-fonte (inglês) | Onde | Proposta | Por quê |
|---|---|---|---|
| Your email is confirmed. Whenever you are ready, write here what is happening. | Escrever, depois da confirmação | E-mail confirmado. Quando você quiser, conte aqui o que está acontecendo. | "Conte", como no início. |
| Before continuing: | Erro, título da lista | Falta pouco: | Sem tom de bloqueio. |
| Tell us whether you are 18 or older. | Erro, Criar conta | Conte para a gente se você tem 18 anos ou mais. | |
| Check the agreement box to continue. | Erro, Criar conta | Marque a caixa de concordância em "Antes de começar". | Diz onde está. |
| Many attempts in a row. Wait 15 minutes and try again, or create a new password with "Forgot password". | Erro, Entrar | Foram muitas tentativas seguidas. Para proteger sua conta, espere 15 minutos e tente de novo, ou crie uma senha nova em "Esqueci minha senha". | Explica que o bloqueio protege a pessoa. |
| Use at least 6 characters in the password. | Erro, Criar conta | A senha precisa de pelo menos 6 caracteres. | Melhor trocar o número fixo por `%d`, como na dica do campo. |
| At least %d characters. | Dica do campo de senha | Use pelo menos %d caracteres. | |
| The change is applied right away and saved only in this browser. | Minha conta, Aparência | A mudança vale na hora e fica guardada só neste navegador. | |
| Appearance saved. | Minha conta, Aparência | Aparência guardada. | |
| If other people use this device, sign out when you finish. | Minha conta, Sair | Se outras pessoas usam este aparelho, saia da conta quando terminar. Assim o que você escreveu fica só com você. | Diz o porquê: proteção. |
| Sign out of this device | Minha conta, Sair | Sair neste aparelho | |

## Novos textos-fonte em inglês (quando o sentido muda)

Pela ADR 0006, quando o sentido muda, o texto-fonte em inglês muda junto. Os textos que só mudam o tom podem manter o `msgid` atual. Estes mudam de sentido:

| Proposta em português | Novo texto-fonte |
|---|---|
| Você não precisa dizer seu nome. Aqui chamamos você de Amigo, com um número que é só seu, como Amigo 48213. | You don't need to give your name. Here we call you Friend, with a number that is only yours, like Friend 48213. |
| (cadastro) ... como Amigo 48213. Ninguém vê o seu e-mail. | You don't need to give your name. Here we call you Friend, with a number that is only yours, like Friend 48213. Nobody sees your email. |
| As respostas são escritas por uma inteligência artificial feita para acolher: ela lê com atenção, não julga e segue regras de respeito. Quando chegar uma resposta, a gente avisa por e-mail, sem mostrar o que você escreveu. | Replies are written by an artificial intelligence made to welcome people: it reads with care, does not judge and follows rules of respect. When a reply arrives, we let you know by email, without showing what you wrote. |
| As respostas daqui são escritas por uma inteligência artificial feita para acolher, com atenção e com regras de respeito. Se estiver muito difícil agora, ligue 188 (CVV), a qualquer hora. | The replies here are written by an artificial intelligence made to welcome people, with care and rules of respect. If things are very hard right now, call 188 (CVV), at any time. |
| Concordo que uma inteligência artificial feita para acolher leia o que eu escrever, para poder me responder, inclusive usando serviços fora do Brasil. | I agree that an artificial intelligence made to welcome people reads what I write so it can reply to me, including through services outside Brazil. |
| Seu e-mail nunca vai junto, e telefones e documentos são tirados do texto antes. | Your email is never sent along, and phone numbers and documents are taken out of the text first. |
| Escreva do seu jeito, não precisa organizar nada. Se não souber por onde começar, estas perguntas podem ajudar: | Write your way; nothing needs to be organised. If you don't know where to start, these questions may help: |
| Há quanto tempo isso acontece? / O que você já tentou? / Do que você sente que precisa agora? | How long has this been going on? / What have you already tried? / What do you feel you need now? |
| Para proteger você, evite escrever nomes e endereços. Telefones, e-mails e documentos a gente tira do texto antes de guardar. A resposta pode levar algumas horas, e a gente avisa por e-mail. | To protect you, avoid writing names and addresses. We take phone numbers, emails and documents out of the text before saving. The reply may take a few hours, and we let you know by email. |
| [removido para proteger você] | [removed to protect you] |
| Seu pedido chegou e vai ser lido com atenção. A resposta pode levar algumas horas; a gente avisa por e-mail. | Your request arrived and will be read with care. The reply may take a few hours; we let you know by email. |
| Sua mensagem chegou. A resposta pode levar algumas horas; a gente avisa por e-mail. | Your message arrived. The reply may take a few hours; we let you know by email. |
| Se quiser, conte por que está encerrando | If you want, tell us why you are closing |
| Prefiro parar por aqui | I'd rather stop here |
| Você merece ter com quem falar agora | You deserve someone to talk to right now |
| Este espaço é para quem tem 18 anos ou mais. Estes serviços conversam com quem tem menos de 18, de graça e com pessoas preparadas: | This space is for people 18 and over. These services talk with people under 18, for free and with trained people: |
| Sua conta foi excluída, como você pediu. Se quiser voltar um dia, é só criar uma conta nova. | Your account was deleted, as you asked. If you want to come back one day, just create a new account. |
| Não encontramos este pedido. Seus pedidos estão em Meus pedidos. | We could not find this request. Your requests are in My requests. |
| Isso não pode ser feito por aqui. Seus pedidos estão em Meus pedidos. | This can't be done here. Your requests are in My requests. |
| Falta pouco: em "Antes de começar", escolha sua idade e marque a caixa de concordância. | Almost there: under "Before you start", choose your age and check the agreement box. |
| Alguma coisa não funcionou do nosso lado. Tente de novo em alguns minutos. | Something didn't work on our side. Try again in a few minutes. |
| Os e-mails de confirmação, de Resposta nova e de Encerramento (ver tabela "E-mails") | Traduzir a proposta em português frase a frase, mantendo `%1$s`, `%2$s` e `<br>` nos mesmos lugares. |
