# Revisão dos textos (R1)

Gerado por `npm run texts:review` a partir dos arquivos de tradução. Não edite este arquivo: ajuste o `msgstr` em `plugin/languages/euteajudo-pt_BR.po` ou `theme/languages/euteajudo-theme-pt_BR.po`, rode `ddev composer i18n` e gere a lista de novo.

As páginas Sobre, Termos de uso e Política de privacidade não estão aqui: são conteúdo, editado direto no painel (Páginas).


## Cabeçalho e navegação

| Texto no site | Original (inglês) | Onde |
|---|---|---|
| Sair | Sign out | `plugin/blocks/account-link/render.php:16` |
| Entrar | Sign in | `plugin/blocks/account-link/render.php:18` |
| Escrever | Write | `plugin/blocks/app-nav/render.php:16` |
| Meus pedidos | My requests | `plugin/blocks/app-nav/render.php:17` |
| Conta | Account | `plugin/blocks/app-nav/render.php:18` |
| Principal | Main | `plugin/blocks/app-nav/render.php:21` |
| Marca | Brand | `theme/patterns/brand.php` |
| Logo com link para a página inicial. O SVG usa currentColor e segue a cor do texto nas versões Noite e Dia. | Logo linking to the home page. The SVG uses currentColor, so it follows the text color in Night and Day. | `theme/patterns/brand.php` |
| EuTeAjudo.org, início | EuTeAjudo.org, home | `theme/patterns/brand.php:14` |

## Criar conta

| Texto no site | Original (inglês) | Onde |
|---|---|---|
| Senha | Password | `plugin/blocks/sign-in/render.php:36` |
| Pode Falar (UNICEF) | Pode Falar (UNICEF) | `plugin/blocks/sign-up/render.php:26` |
| Para quem tem de 13 a 24 anos, por WhatsApp. | For people aged 13 to 24, on WhatsApp. | `plugin/blocks/sign-up/render.php:27` |
| Abrir o Pode Falar | Open Pode Falar | `plugin/blocks/sign-up/render.php:28` |
| CVV | CVV | `plugin/blocks/sign-up/render.php:31` |
| Qualquer idade, a qualquer hora, pelo telefone 188 ou pelo chat. | Any age, any time, by phone at 188 or by chat. | `plugin/blocks/sign-up/render.php:32` |
| Ligar para o 188 | Call 188 | `plugin/blocks/sign-up/render.php:33` |
| Quase lá | Almost there | `plugin/blocks/sign-up/render.php:38` |
| Mandamos um link para o seu e-mail. Toque nele para confirmar que o e-mail é seu. Ele vale por 48 horas. | We sent a link to the email you typed. Open the email and tap the link to confirm it. The link is valid for 48 hours. | `plugin/blocks/sign-up/render.php:39` |
| O assunto é "EuTeAjudo: confirme seu e-mail". Se não aparecer em alguns minutos, vale olhar a pasta de spam. Para receber outro link, é só entrar com seu e-mail e senha. | The subject is "EuTeAjudo: confirm your email". If it does not arrive in a few minutes, check the spam folder. To get a new link, try to sign in. | `plugin/blocks/sign-up/render.php:40` |
| Antes de começar | Before you start | `plugin/blocks/sign-up/render.php:48` |
| Tenho 18 anos ou mais | I am 18 or older | `plugin/blocks/sign-up/render.php:50` |
| Tenho menos de 18 anos | I am under 18 | `plugin/blocks/sign-up/render.php:51` |
| Li a %s. | I have read the %s. | `plugin/blocks/sign-up/render.php:65` |
| Política de privacidade | Privacy policy | `plugin/blocks/sign-up/render.php:60` |
| Criar conta | Create account | `plugin/blocks/sign-up/render.php:80` |
| Já tem conta? Entrar | Already have an account? Sign in | `plugin/blocks/sign-up/render.php:84` |
| Aceito os %1$s e a %2$s. | I accept the %1$s and the %2$s. | `plugin/blocks/sign-up/render.php:58` |
| Termos de uso | Terms of use | `plugin/blocks/sign-up/render.php:59` |
| Use pelo menos %d caracteres. | At least %d characters. | `plugin/blocks/password-reset/render.php:30` |
| Você merece ter com quem falar agora | You deserve someone to talk to right now | `plugin/blocks/sign-up/render.php:22` |
| Este espaço é para quem tem 18 anos ou mais. Estes serviços conversam com quem tem menos de 18, de graça e com pessoas preparadas: | This space is for people 18 and over. These services talk with people under 18, for free and with trained people: | `plugin/blocks/sign-up/render.php:23` |
| Você não precisa dizer seu nome. Aqui chamamos você de Amigo, com um número que é só seu, como Amigo 48213. Ninguém vê o seu e-mail. | You do not need to give your name. Here we call you Friend, with a number that is only yours, like Friend 48213. Nobody sees your email. | `plugin/blocks/sign-up/render.php:44` |
| Concordo que uma inteligência artificial feita para acolher leia o que eu escrever, para poder me responder, inclusive usando serviços fora do Brasil. | I agree that an artificial intelligence made to welcome people reads what I write so it can reply to me, including through services outside Brazil. | `plugin/blocks/sign-up/render.php:53` |
| Seu e-mail nunca vai junto, e telefones e documentos são tirados do texto antes. | Your email is never sent along, and phone numbers and documents are taken out of the text first. | `plugin/blocks/sign-up/render.php:72` |

## Entrar

| Texto no site | Original (inglês) | Onde |
|---|---|---|
| Senha nova guardada. Agora é só entrar com ela. | Your password was changed. Sign in with the new password. | `plugin/blocks/sign-in/render.php:20` |
| Bom ter você de volta. | Good to have you back. | `plugin/blocks/sign-in/render.php:27` |
| Esqueci minha senha | Forgot password | `plugin/blocks/sign-in/render.php:45` |
| Ainda não tem conta? Criar conta | Don't have an account yet? Create account | `plugin/blocks/sign-in/render.php:46` |
| Sua conta foi excluída, como você pediu. Se quiser voltar um dia, é só criar uma conta nova. | Your account was deleted, as you asked. If you want to come back one day, just create a new account. | `plugin/blocks/sign-in/render.php:23` |

## Esqueci minha senha e nova senha

| Texto no site | Original (inglês) | Onde |
|---|---|---|
| Se esse e-mail tiver uma conta aqui, mandamos um link para criar uma senha nova. Vale olhar a caixa de entrada e a pasta de spam. | If this email has an account, we sent a link to create a new password. Check your inbox and the spam folder. | `plugin/blocks/password-request/render.php:18` |
| Escreva o e-mail que você usa para entrar. Se ele tiver uma conta aqui, a gente manda um link para criar uma senha nova. | Type the email of your account. If it is registered, we send a link to create a new password. | `plugin/blocks/password-request/render.php:23` |
| Enviar link | Send link | `plugin/blocks/password-request/render.php:28` |
| Se você entra com o Google, não precisa de senha: volte e use "Continuar com o Google". | Account created with Google? Go back and use "Continue with Google". | `plugin/blocks/password-request/render.php:32` |
| Voltar para Entrar | Back to sign in | `plugin/blocks/password-request/render.php:33` |
| Para criar uma senha nova, abra esta página pelo link que mandamos para o seu e-mail. | Open this page from the link we sent to your email. | `plugin/blocks/password-reset/render.php:21` |
| Pedir um link novo | Ask for a new link | `plugin/blocks/password-reset/render.php:22` |
| Nova senha | New password | `plugin/blocks/password-reset/render.php:30` |
| Salvar senha nova | Save new password | `plugin/blocks/password-reset/render.php:31` |

## Confirmação de e-mail

| Texto no site | Original (inglês) | Onde |
|---|---|---|
| E-mail confirmado. Bom ter você aqui, %s. | Email confirmed. You are signed in as %s. | `plugin/blocks/email-confirmation/render.php:22` |
| Ir para o início | Go to the home page | `plugin/blocks/email-confirmation/render.php:27` |
| Para confirmar seu e-mail, toque no link que mandamos para ele. | Open the link we sent to your email to confirm it. | `plugin/blocks/email-confirmation/render.php:32` |

## Minha conta

| Texto no site | Original (inglês) | Onde |
|---|---|---|
| E-mail | Email | `plugin/blocks/account/render.php:30` |
| Aqui você é %s. | Here you are %s. | `plugin/blocks/account/render.php:24` |
| Só você vê este e-mail. Ele serve para você entrar e para a gente avisar quando chegar uma resposta. | Only you see this email. It is used to sign in and to tell you when a reply arrives. | `plugin/blocks/account/render.php:32` |
| Aparência | Appearance | `plugin/blocks/account/render.php:35` |
| Automática (segue o seu aparelho) | Automatic (follows your device) | `plugin/blocks/account/render.php:36` |
| Noite | Night | `plugin/blocks/account/render.php:37` |
| Dia | Day | `plugin/blocks/account/render.php:38` |
| Excluir conta | Delete account | `plugin/blocks/account/render.php:46` |
| Contas que administram o site não podem ser excluídas aqui. | Accounts that manage the site cannot be deleted here. | `plugin/blocks/account/render.php:48` |
| Se você excluir a conta, a gente apaga o seu e-mail e ninguém mais consegue entrar nela. O que você escreveu fica guardado com o seu Apelido, sem o seu e-mail. Se quiser voltar depois, pode criar uma conta nova, que recebe outro Apelido. | Your email is removed from the site and nobody can sign in to this account anymore. What you wrote stays under your alias, without your email. You can create a new account later; it gets a new alias. | `plugin/blocks/account/render.php:50` |
| Entendo e quero excluir minha conta | I understand and want to delete my account | `plugin/blocks/account/render.php:52` |
| Excluir minha conta | Delete my account | `plugin/blocks/account/render.php:53` |
| A mudança vale na hora e fica guardada só neste navegador. | The change is applied right away and saved only in this browser. | `plugin/blocks/account/render.php:39` |
| Aparência guardada. | Appearance saved. | `plugin/blocks/account/render.php:40` |
| Se outras pessoas usam este aparelho, saia da conta quando terminar. Assim o que você escreveu fica só com você. | If other people use this device, sign out when you finish. | `plugin/blocks/account/render.php:44` |
| Sair neste aparelho | Sign out of this device | `plugin/blocks/account/render.php:44` |

## Escrever

| Texto no site | Original (inglês) | Onde |
|---|---|---|
| Título | Title | `plugin/blocks/write/render.php:30` |
| para você encontrar depois | so you can find it later | `plugin/blocks/write/render.php:30` |
| O que está acontecendo? | What is happening? | `plugin/blocks/write/render.php:33` |
| Enviar pedido | Send request | `plugin/blocks/write/render.php:45` |
| E-mail confirmado. Quando você quiser, conte aqui o que está acontecendo. | Your email is confirmed. Whenever you are ready, write here what is happening. | `plugin/blocks/write/render.php:25` |
| Escreva do seu jeito, não precisa organizar nada. Se não souber por onde começar, estas perguntas podem ajudar: | Write your way; nothing needs to be organised. If you do not know where to start, these questions may help: | `plugin/blocks/write/render.php:35` |
| Há quanto tempo isso acontece? | How long has this been going on? | `plugin/blocks/write/render.php:37` |
| O que você já tentou? | What have you already tried? | `plugin/blocks/write/render.php:38` |
| Do que você sente que precisa agora? | What do you feel you need now? | `plugin/blocks/write/render.php:39` |
| Para proteger você, evite escrever nomes e endereços. Telefones, e-mails e documentos a gente tira do texto antes de guardar. A resposta pode levar algumas horas, e a gente avisa por e-mail. | To protect you, avoid writing names and addresses. We take phone numbers, emails and documents out of the text before saving. The reply may take a few hours, and we let you know by email. | `plugin/blocks/write/render.php:44` |

## Meus pedidos e Conversa

| Texto no site | Original (inglês) | Onde |
|---|---|---|
| Esperando resposta | Awaiting reply | `plugin/src/HelpRequests/HelpRequestType.php:96` |
| Em conversa | In conversation | `plugin/src/HelpRequests/HelpRequestType.php:101` |
| Encerrado | Closed | `plugin/src/HelpRequests/HelpRequestType.php:106` |
| Novo pedido | New request | `plugin/blocks/my-requests/render.php:40` |
| Nada por aqui ainda. Quando quiser escrever, é só começar. | Nothing here yet. When you want to write, just start. | `plugin/blocks/my-requests/render.php:42` |
| Resposta nova | New reply | `plugin/blocks/my-requests/render.php:51` |
| Você escreveu em %s | Written on %s | `plugin/blocks/my-requests/render.php:71` |
| Você | You | `plugin/blocks/my-requests/render.php:87` |
| Isso me ajudou | This helped me | `plugin/blocks/my-requests/render.php:96` |
| Este pedido está encerrado. Você pode reabrir quando quiser continuar a conversa. | This request is closed. You can reopen it whenever you want to keep talking. | `plugin/blocks/my-requests/render.php:104` |
| Reabrir pedido | Reopen request | `plugin/blocks/my-requests/render.php:108` |
| Responder | Reply | `plugin/blocks/my-requests/render.php:120` |
| Escreva quando quiser | Write whenever you want | `plugin/blocks/my-requests/render.php:121` |
| Enviar resposta | Send reply | `plugin/blocks/my-requests/render.php:122` |
| Encerrar pedido | Close request | `plugin/blocks/my-requests/render.php:126` |
| Estou melhor | I feel better | `plugin/blocks/my-requests/render.php:131` |
| Prefiro não dizer | I prefer not to say | `plugin/blocks/my-requests/render.php:133` |
| j \d\e F | F j | `plugin/src/HelpRequests/View.php:40` |
| j \d\e M, H\hi | M j, g:i a | `plugin/src/HelpRequests/View.php:50` |
| Seu pedido chegou e vai ser lido com atenção. A resposta pode levar algumas horas; a gente avisa por e-mail. | Your request arrived and will be read with care. The reply may take a few hours; we let you know by email. | `plugin/blocks/my-requests/render.php:78` |
| Sua mensagem chegou. A resposta pode levar algumas horas; a gente avisa por e-mail. | Your message arrived. The reply may take a few hours; we let you know by email. | `plugin/blocks/my-requests/render.php:115` |
| Se quiser, conte por que está encerrando | If you want, tell us why you are closing | `plugin/blocks/my-requests/render.php:130` |
| Prefiro parar por aqui | I would rather stop here | `plugin/blocks/my-requests/render.php:132` |
| [removido para proteger você] | [removed to protect you] | `plugin/src/HelpRequests/HelpRequests.php:191` |
| As respostas daqui são escritas por uma inteligência artificial feita para acolher, com atenção e com regras de respeito. Se estiver muito difícil agora, ligue 188 (CVV), a qualquer hora. | The replies here are written by an artificial intelligence made to welcome people, with care and rules of respect. If things are very hard right now, call 188 (CVV), at any time. | `plugin/src/HelpRequests/View.php:66` |

## Mensagens de erro e de aviso (todas as telas)

| Texto no site | Original (inglês) | Onde |
|---|---|---|
| Não encontramos uma conta com esse e-mail e essa senha. Confira os dois e tente de novo, ou use "Esqueci minha senha". | Email or password does not match. Check and try again, or use "Forgot password". | `plugin/src/Accounts/Forms/Messages.php:24` |
| Falta só confirmar seu e-mail. Mandamos um link novo para ele; é só tocar no link. | Confirm your email first. We sent a new link to your inbox. | `plugin/src/Accounts/Forms/Messages.php:25` |
| Confira o e-mail. Ele precisa ter o formato nome@exemplo.com. | Type a valid email address. | `plugin/src/Accounts/Forms/Messages.php:29` |
| Ainda não existe uma conta aqui com esse Google. Crie sua conta para começar. | This Google account has no account here yet. Create your account to continue. | `plugin/src/Accounts/Forms/Messages.php:32` |
| Entrar com o Google não está funcionando agora. Você pode usar seu e-mail e senha. | Sign in with Google is not available right now. Use your email and password. | `plugin/src/Accounts/Forms/Messages.php:33` |
| Você saiu da tela do Google antes de terminar. Pode tentar de novo quando quiser. | Sign in with Google was cancelled. | `plugin/src/Accounts/Forms/Messages.php:34` |
| Essa conta do Google ainda não confirmou o e-mail dela. Você pode usar outra conta do Google, ou seu e-mail e senha. | This Google account does not have a verified email. Use another account, or your email and password. | `plugin/src/Accounts/Forms/Messages.php:35` |
| A entrada com o Google não terminou. Tente de novo. | Sign in with Google did not finish. Try again. | `plugin/src/Accounts/Forms/Messages.php:36` |
| Esta página ficou aberta por muito tempo. Tente de novo. | This page stayed open for too long. Try again. | `plugin/src/Accounts/Forms/Messages.php:37` |
| Este link não vale mais. Peça um novo logo abaixo. | This link is invalid or has expired. Ask for a new one below. | `plugin/src/Accounts/Forms/Messages.php:38` |
| Este link de confirmação não vale mais. Entre com seu e-mail e senha para receber um novo. | This confirmation link is invalid or has expired. Sign in to get a new one. | `plugin/src/Accounts/Forms/Messages.php:39` |
| Marque a caixa para confirmar que quer excluir sua conta. | Check the box to confirm that you want to delete your account. | `plugin/src/Accounts/Forms/Messages.php:41` |
| Falta um título. Pode ser qualquer palavra que ajude você a encontrar este pedido depois. | Give your request a title, so you can find it later. | `plugin/src/Accounts/Forms/Messages.php:43` |
| O texto está vazio. Escreva algumas linhas para enviar o pedido. | The text is empty. Write a few lines to send your request. | `plugin/src/Accounts/Forms/Messages.php:44` |
| A resposta está vazia. Escreva algo para enviar. | The reply is empty. Write something to send it. | `plugin/src/Accounts/Forms/Messages.php:45` |
| Este pedido está encerrado. Reabra para continuar a conversa. | This request is closed. Reopen it to keep talking. | `plugin/src/Accounts/Forms/Messages.php:46` |
| Conte para a gente se você tem 18 anos ou mais. | Tell us whether you are 18 or older. | `plugin/src/Accounts/Forms/Messages.php:26` |
| Marque a caixa de concordância em "Antes de começar". | Check the agreement box to continue. | `plugin/src/Accounts/Forms/Messages.php:27` |
| Foram muitas tentativas seguidas. Para proteger sua conta, espere 15 minutos e tente de novo, ou crie uma senha nova em "Esqueci minha senha". | Many attempts in a row. Wait 15 minutes and try again, or create a new password with "Forgot password". | `plugin/src/Accounts/Forms/Messages.php:28` |
| A senha precisa de pelo menos %d caracteres. | The password needs at least %d characters. | `plugin/src/Accounts/Forms/Messages.php:31` |
| Alguma coisa não funcionou do nosso lado. Tente de novo em alguns minutos. | Something did not work on our side. Try again in a few minutes. | `plugin/src/Accounts/Forms/Messages.php:40` |
| Isso não pode ser feito por aqui. Seus pedidos estão em Meus pedidos. | This cannot be done here. Your requests are in My requests. | `plugin/src/Accounts/Forms/Messages.php:47` |
| Não encontramos este pedido. Seus pedidos estão em Meus pedidos. | We could not find this request. Your requests are in My requests. | `plugin/src/Accounts/Forms/Messages.php:48` |

## Partes comuns dos formulários e login com Google

| Texto no site | Original (inglês) | Onde |
|---|---|---|
| Mostrar | Show | `plugin/src/Accounts/Forms/View.php:133` |
| Ocultar | Hide | `plugin/src/Accounts/Forms/View.php:133` |
| Continuar com o Google | Continue with Google | `plugin/src/Accounts/Forms/View.php:149` |
| ou com e-mail | or with email | `plugin/src/Accounts/Forms/View.php:156` |
| Falta pouco: | Before continuing: | `plugin/src/Accounts/Forms/View.php:50` |

## E-mails

| Texto no site | Original (inglês) | Onde |
|---|---|---|
| Amigo %s | Friend %s | `plugin/src/Accounts/Aliases.php:40` |
| EuTeAjudo: faixa de Apelidos ampliada | EuTeAjudo: alias range widened | `plugin/src/Accounts/Aliases.php:226` |
| Os Apelidos de até %1$d dígitos estavam quase todos ocupados, então os novos Apelidos podem ter até %2$d dígitos. Não é preciso fazer mais nada. | Aliases with up to %1$d digits were almost all taken, so new aliases can now have up to %2$d digits. Nothing else needs to be done. | `plugin/src/Accounts/Aliases.php:229` |
| EuTeAjudo: confirme seu e-mail | EuTeAjudo: confirm your email | `plugin/src/Accounts/EmailConfirmation.php:52` |
| Oi, %1$s.<br><br>Falta só confirmar este e-mail para você começar no EuTeAjudo.org. Abra o link abaixo em até 48 horas:<br><br>%2$s<br><br>Se não foi você quem criou a conta, pode ignorar esta mensagem.<br><br>EuTeAjudo.org | Hello, %1$s.<br><br>To confirm this email at EuTeAjudo.org, open the link below within 48 hours:<br><br>%2$s<br><br>If you did not create an account, you can ignore this message. | `plugin/src/Accounts/EmailConfirmation.php:55` |
| EuTeAjudo: acesso à conta | EuTeAjudo: account access | `plugin/src/Accounts/Forms/Notices.php:27` |
| Alguém tentou criar uma conta no EuTeAjudo.org com este e-mail, mas ele já tem uma conta aqui.<br><br>Se foi você, é só entrar:<br>%1$s<br><br>Esqueceu a senha? Crie uma nova aqui:<br>%2$s<br><br>Se não foi você, pode ignorar esta mensagem. Nada foi alterado. | Someone tried to create an account at EuTeAjudo.org with this email, which already has one.<br><br>If it was you, sign in here:<br>%1$s<br><br>Forgot your password? Create a new one here:<br>%2$s<br><br>If it was not you, you can ignore this message. Nothing was changed. | `plugin/src/Accounts/Forms/Notices.php:30` |
| EuTeAjudo: crie uma senha nova | EuTeAjudo: create a new password | `plugin/src/Accounts/Forms/Notices.php:46` |
| Para criar uma senha nova no EuTeAjudo.org, abra o link abaixo:<br><br>%s<br><br>Se você não pediu isso, pode ignorar esta mensagem. Sua senha continua a mesma. | To create a new password at EuTeAjudo.org, open the link below:<br><br>%s<br><br>If you did not ask for this, you can ignore this message. Your password stays the same. | `plugin/src/Accounts/Forms/Notices.php:49` |
| EuTeAjudo: uma Resposta do Agente falhou na verificação | EuTeAjudo: an Agent reply failed verification | `plugin/src/Agents/AgentWork.php:392` |
| O Agente não conseguiu escrever uma Resposta que passasse na verificação. Ele tenta de novo mais tarde, com esperas cada vez maiores.<br><br>Abrir o Pedido: %s | The Agent could not write a reply that passed verification. It will try again later, with longer waits.<br><br>Open the request: %s | `plugin/src/Agents/AgentWork.php:395` |
| EuTeAjudo: você tem uma nova mensagem | EuTeAjudo: you have a new message | `plugin/src/HelpRequests/Notifications.php:42` |
| Oi, %1$s.<br><br>Chegou uma resposta em um dos seus pedidos. Ela fica guardada lá para quando você quiser ler:<br>%2$s<br><br>EuTeAjudo.org | Hi, %1$s.<br><br>There is a new reply in one of your requests. To read it, sign in:<br>%2$s<br><br>EuTeAjudo.org | `plugin/src/HelpRequests/Notifications.php:44` |
| EuTeAjudo: um pedido seu foi encerrado | EuTeAjudo: one of your requests was closed | `plugin/src/HelpRequests/Notifications.php:56` |
| Oi, %1$s.<br><br>Um dos seus pedidos ficou 30 dias sem mensagens novas, então ele foi encerrado. A conversa continua guardada, e você pode reabrir quando quiser:<br>%2$s<br><br>EuTeAjudo.org | Hi, %1$s.<br><br>One of your requests had no new messages for 30 days and was closed. You can reopen it whenever you want:<br>%2$s<br><br>EuTeAjudo.org | `plugin/src/HelpRequests/Notifications.php:58` |
| EuTeAjudo: pedidos esperando há mais de 24 horas | EuTeAjudo: requests waiting more than 24 hours | `plugin/src/HelpRequests/Schedules.php:120` |
| Estes pedidos esperam há mais de 24 horas por uma Resposta do Agente. Confira se o Agente está rodando (Painel) ou responda você mesmo:<br><br>%s | These requests have waited more than 24 hours for an Agent reply. Check whether the Agent is running (Dashboard) or reply yourself:<br><br>%s | `plugin/src/HelpRequests/Schedules.php:123` |

## Nomes de páginas, termos e Serviços de apoio criados na instalação

| Texto no site | Original (inglês) | Onde |
|---|---|---|
| Confirmar e-mail | Confirm email | `plugin/src/Accounts/Pages.php:133` |
| Minha conta | My account | `plugin/src/Accounts/Pages.php:134` |
| Emocional | Emotional | `plugin/src/HelpRequests/TriageTerms.php:26` |
| Relacionamentos | Relationships | `plugin/src/HelpRequests/TriageTerms.php:27` |
| Trabalho e estudo | Work and study | `plugin/src/HelpRequests/TriageTerms.php:28` |
| Financeiro | Financial | `plugin/src/HelpRequests/TriageTerms.php:29` |
| Necessidades materiais | Material needs | `plugin/src/HelpRequests/TriageTerms.php:30` |
| Saúde | Health | `plugin/src/HelpRequests/TriageTerms.php:31` |
| Violência | Violence | `plugin/src/HelpRequests/TriageTerms.php:32` |
| Outros | Other | `plugin/src/HelpRequests/TriageTerms.php:33` |
| Baixa | Low | `plugin/src/HelpRequests/TriageTerms.php:36` |
| Média | Medium | `plugin/src/HelpRequests/TriageTerms.php:37` |
| Alta | High | `plugin/src/HelpRequests/TriageTerms.php:38` |
| Crítica | Critical | `plugin/src/HelpRequests/TriageTerms.php:39` |
| Ideação suicida | Suicidal ideation | `plugin/src/HelpRequests/TriageTerms.php:42` |
| Autolesão | Self-harm | `plugin/src/HelpRequests/TriageTerms.php:43` |
| Violência sofrida | Violence suffered | `plugin/src/HelpRequests/TriageTerms.php:44` |
| Risco a terceiros | Risk to others | `plugin/src/HelpRequests/TriageTerms.php:45` |
| Emergência médica | Medical emergency | `plugin/src/HelpRequests/TriageTerms.php:46` |
| Uso de substâncias em crise | Substance use crisis | `plugin/src/HelpRequests/TriageTerms.php:47` |
| Falta de alimento ou moradia | Lack of food or housing | `plugin/src/HelpRequests/TriageTerms.php:48` |
| Possível menor de idade | Possibly under 18 | `plugin/src/HelpRequests/TriageTerms.php:49` |
| Pedido de dinheiro | Money request | `plugin/src/HelpRequests/TriageTerms.php:50` |
| Dado pessoal no texto | Personal data in the text | `plugin/src/HelpRequests/TriageTerms.php:51` |
| 24 horas, todos os dias | 24 hours, every day | `plugin/src/SupportServices/SupportServices.php:120` |
| Apoio emocional e prevenção do suicídio, gratuito e sigiloso, por telefone, chat e e-mail. | Emotional support and suicide prevention, free and confidential, by phone, chat and email. | `plugin/src/SupportServices/SupportServices.php:124` |
| Serviço de atendimento móvel de urgência. | Mobile emergency medical service. | `plugin/src/SupportServices/SupportServices.php:134` |
| Emergência policial, para violência acontecendo agora ou perigo imediato. | Police emergency, for violence happening now or immediate danger. | `plugin/src/SupportServices/SupportServices.php:144` |
| Orientação e denúncias sobre violência contra a mulher, gratuito e sigiloso. | Guidance and reports about violence against women, free and confidential. | `plugin/src/SupportServices/SupportServices.php:154` |
| Denúncias de violações de direitos humanos, inclusive contra crianças, idosos e pessoas com deficiência. | Reports of human rights violations, including against children, older people and people with disabilities. | `plugin/src/SupportServices/SupportServices.php:164` |
| Apoio emocional on-line para adolescentes e jovens, do UNICEF e parceiros. | Online emotional support for teenagers and young people, by UNICEF and partners. | `plugin/src/SupportServices/SupportServices.php:174` |
| Sobre | About | `plugin/src/Site/ContentPages.php:100` |
| EuTeAjudo: preencha os campos marcados com "[preencher: ...]" antes do lançamento: %s. | EuTeAjudo: fill in the fields marked "[preencher: ...]" before launch: %s. | `plugin/src/Site/ContentPages.php:138` |

## Painel do Administrador e API

| Texto no site | Original (inglês) | Onde |
|---|---|---|
| Você não tem permissão para listar usuários. | Sorry, you are not allowed to list users. | `plugin/src/Privacy/UserDirectory.php:43` |
| Apelido | Alias | `plugin/src/Admin/UsersList.php:46` |
| Pedidos | Help requests | `plugin/src/HelpRequests/HelpRequestType.php:62` |
| Pedido | Help request | `plugin/src/HelpRequests/HelpRequestType.php:63` |
| Aguardando triagem | Awaiting triage | `plugin/src/HelpRequests/HelpRequestType.php:91` |
| Aguardando triagem <span class="count">(%s)</span> / Aguardando triagem <span class="count">(%s)</span> | Awaiting triage <span class="count">(%s)</span> | `plugin/src/HelpRequests/HelpRequestType.php:93` |
| Aguardando resposta <span class="count">(%s)</span> / Aguardando resposta <span class="count">(%s)</span> | Awaiting reply <span class="count">(%s)</span> | `plugin/src/HelpRequests/HelpRequestType.php:98` |
| Em conversa <span class="count">(%s)</span> / Em conversa <span class="count">(%s)</span> | In conversation <span class="count">(%s)</span> | `plugin/src/HelpRequests/HelpRequestType.php:103` |
| Encerrados <span class="count">(%s)</span> / Encerrados <span class="count">(%s)</span> | Closed <span class="count">(%s)</span> | `plugin/src/HelpRequests/HelpRequestType.php:108` |
| Categorias | Categories | `plugin/src/HelpRequests/HelpRequestType.php:128` |
| Gravidade | Severity | `plugin/src/HelpRequests/HelpRequestType.php:129` |
| Sinais de risco | Risk signals | `plugin/src/HelpRequests/HelpRequestType.php:130` |
| Serviços de apoio | Support services | `plugin/src/SupportServices/SupportServiceType.php:61` |
| Serviço de apoio | Support service | `plugin/src/SupportServices/SupportServiceType.php:62` |
| Adicionar Serviço de apoio | Add support service | `plugin/src/SupportServices/SupportServiceType.php:63` |
| Editar Serviço de apoio | Edit support service | `plugin/src/SupportServices/SupportServiceType.php:64` |
| Contato | Contact | `plugin/src/SupportServices/SupportServiceType.php:91` |
| Telefone | Phone | `plugin/src/SupportServices/SupportServiceType.php:103` |
| Site | Website | `plugin/src/SupportServices/SupportServiceType.php:105` |
| Horário de atendimento | Opening hours | `plugin/src/SupportServices/SupportServiceType.php:107` |
| Alcance | Reach | `plugin/src/SupportServices/SupportServiceType.php:109` |
| Nacional | National | `plugin/src/SupportServices/SupportServiceType.php:111` |
| Digital | Digital | `plugin/src/SupportServices/SupportServiceType.php:112` |
| Marque as Categorias, Gravidades e Sinais de risco a que este serviço se aplica. O Agente recebe os serviços que combinam com a Triagem de cada Pedido. | Mark the categories, severities and risk signals this service fits. The Agent receives the services that match the triage of each request. | `plugin/src/SupportServices/SupportServiceType.php:114` |
| EuTeAjudo: Agente | EuTeAjudo: Agent | `plugin/src/Admin/AgentStatus.php:35` |
| Nenhum Agente consultou a API ainda. | No Agent has called the API yet. | `plugin/src/Admin/AgentStatus.php:69` |
| Última consulta de um Agente: há %s. | Last call from an Agent: %s ago. | `plugin/src/Admin/AgentStatus.php:72` |
| %d pedido esperando o Agente / %d pedidos esperando o Agente | %d request waiting for an Agent | `plugin/src/Admin/AgentStatus.php:77` |
| %d esperando há mais de 24h / %d esperando há mais de 24h | %d waiting more than 24h | `plugin/src/Admin/AgentStatus.php:79` |
| %d com falha na verificação / %d com falha na verificação | %d with failed verification | `plugin/src/Admin/AgentStatus.php:81` |
| Autor | Author | `plugin/src/Admin/HelpRequestsAdmin.php:125` |
| Status | Status | `plugin/src/Admin/HelpRequestsAdmin.php:126` |
| Última mensagem do autor | Author last wrote | `plugin/src/Admin/HelpRequestsAdmin.php:127` |
| Respostas | Replies | `plugin/src/Admin/HelpRequestsAdmin.php:128` |
| %d falha na verificação / %d falhas na verificação | %d failed verification | `plugin/src/Admin/HelpRequestsAdmin.php:153` |
| Todas as Gravidades | All severities | `plugin/src/Admin/HelpRequestsAdmin.php:176` |
| Todas as Categorias | All categories | `plugin/src/Admin/HelpRequestsAdmin.php:177` |
| Qualquer situação | Any situation | `plugin/src/Admin/HelpRequestsAdmin.php:192` |
| Sem Resposta do Agente há 24h | No Agent reply for 24h | `plugin/src/Admin/HelpRequestsAdmin.php:194` |
| Falha na verificação | Failed verification | `plugin/src/Admin/HelpRequestsAdmin.php:195` |
| Pedido | Request | `plugin/src/Admin/HelpRequestsAdmin.php:242` |
| Resumo (o autor não vê) | Summary (not shown to the author) | `plugin/src/Admin/HelpRequestsAdmin.php:243` |
| Conversa | Conversation | `plugin/src/Admin/HelpRequestsAdmin.php:244` |
| Apelido do Agente | Agent alias | `plugin/src/Admin/HelpRequestsAdmin.php:257` |
| Escrito em | Written on | `plugin/src/Admin/HelpRequestsAdmin.php:258` |
| Falhas na verificação | Failed verifications | `plugin/src/Admin/HelpRequestsAdmin.php:259` |
| Encerrado por | Closed by | `plugin/src/Admin/HelpRequestsAdmin.php:263` |
| Editado por %1$s em %2$s | Edited by %1$s on %2$s | `plugin/src/Admin/HelpRequestsAdmin.php:273` |
| Texto original | Original text | `plugin/src/Admin/HelpRequestsAdmin.php:277` |
| O status muda com a conversa. Salvar mantém o status e registra a edição. | The status changes with the conversation. Saving keeps the status and records the edit. | `plugin/src/Admin/HelpRequestsAdmin.php:279` |
| Salvar alterações | Save changes | `plugin/src/Admin/HelpRequestsAdmin.php:280` |
| autor | author | `plugin/src/Admin/HelpRequestsAdmin.php:292` |
| marcada como "isso me ajudou" | marked "this helped me" | `plugin/src/Admin/HelpRequestsAdmin.php:296` |
| Editar | Edit | `plugin/src/Admin/HelpRequestsAdmin.php:298` |
| Este Pedido está encerrado. Só o autor pode reabrir. | This request is closed. Only the author can reopen it. | `plugin/src/Admin/HelpRequestsAdmin.php:307` |
| Responder como | Reply as | `plugin/src/Admin/HelpRequestsAdmin.php:314` |
| Responder como %s (você) | Reply as %s (you) | `plugin/src/Admin/HelpRequestsAdmin.php:316` |
| Responder como %s (Apelido do Agente neste Pedido) | Reply as %s (Agent alias in this request) | `plugin/src/Admin/HelpRequestsAdmin.php:318` |
| Resposta enviada. | Reply sent. | `plugin/src/Admin/HelpRequestsAdmin.php:379` |
| A Resposta não foi enviada. Confira se ela não está vazia e se o Pedido está aberto. | The reply was not sent. Check that it is not empty and that the request is open. | `plugin/src/Admin/HelpRequestsAdmin.php:379` |
| Todos os Pedidos | All help requests | `plugin/src/HelpRequests/HelpRequestType.php:65` |
| Buscar Pedidos | Search help requests | `plugin/src/HelpRequests/HelpRequestType.php:66` |
| Nenhum Pedido encontrado. | No help requests found. | `plugin/src/HelpRequests/HelpRequestType.php:67` |
| Nenhum Pedido na lixeira. | No help requests found in Trash. | `plugin/src/HelpRequests/HelpRequestType.php:68` |
| Pedido salvo. | Help request saved. | `plugin/src/Admin/HelpRequestsAdmin.php:361` |

## Outros

| Texto no site | Original (inglês) | Onde |
|---|---|---|
| EuTeAjudo: sua conta foi excluída | EuTeAjudo: your account was deleted | `plugin/src/Accounts/AccountDeletion.php:65` |
| Oi.<br><br>Sua conta no EuTeAjudo.org foi excluída, como você pediu.<br><br>EuTeAjudo.org | Hi.<br><br>Your account at EuTeAjudo.org was deleted, as you asked.<br><br>EuTeAjudo.org | `plugin/src/Accounts/AccountDeletion.php:66` |
| **(sem tradução)** | EuTeAjudo | `theme/functions.php:85` |
| Link | Link | `theme/functions.php:90` |
| Início | Home | `theme/functions.php:127` |
| Links do rodapé | Footer links | `theme/patterns/footer.php` |
| Sobre, Termos de uso e Política de privacidade, e a linha de crise. | About, Terms of use and Privacy policy, and the crisis line. | `theme/patterns/footer.php` |
| Sobre | About | `theme/patterns/footer.php:13` |
| Termos de uso | Terms of use | `theme/patterns/footer.php:14` |
| Política de privacidade | Privacy policy | `theme/patterns/footer.php:15` |
| Sobre o site | About the site | `theme/patterns/footer.php:19` |
| Se estiver difícil agora, ligue 188 (CVV). É de graça, a qualquer hora. | If things are hard right now, call 188 (CVV). It is free, at any time. | `theme/patterns/footer.php:27` |
| Início | Home | `theme/patterns/home.php` |
| Conteúdo da página Início: boas-vindas, a janela acesa, como funciona e ajuda agora. O tema cria a página Início a partir dele uma vez; depois, a página é editada em Páginas. | Content of the Home page: welcome, the lit window, how it works and help right now. The theme creates the Home page from it once; after that, the page is edited in Pages. | `theme/patterns/home.php` |
| Tem uma luz acesa aqui. | There is a light on here. | `theme/patterns/home.php:16` |
| Pode escrever a qualquer hora, do seu jeito, sem dizer quem você é. O que você escrever é lido com atenção. | Write at any time, your way, without saying who you are. What you write is read with care. | `theme/patterns/home.php:20` |
| Começar a escrever | Start writing | `theme/patterns/home.php:25` |
| Já tenho conta | I already have an account | `theme/patterns/home.php:29` |
| Como funciona | How it works | `theme/patterns/home.php:38` |
| Crie uma conta só com e-mail. | Create an account with just an email. | `theme/patterns/home.php:44` |
| Você não precisa dizer seu nome. Aqui chamamos você de Amigo, com um número que é só seu, como Amigo 48213. | You do not need to give your name. Here we call you Friend, with a number that is only yours, like Friend 48213. | `theme/patterns/home.php:48` |
| Conte o que está acontecendo. | Tell what is happening. | `theme/patterns/home.php:54` |
| Do seu jeito, no seu tempo. Não precisa organizar nada. | Your way, in your time. Nothing needs to be organised. | `theme/patterns/home.php:58` |
| Receba uma resposta escrita com cuidado. | Get a reply written with care. | `theme/patterns/home.php:64` |
| As respostas são escritas por uma inteligência artificial feita para acolher: ela lê com atenção, não julga e segue regras de respeito. Quando chegar uma resposta, a gente avisa por e-mail, sem mostrar o que você escreveu. | Replies are written by an artificial intelligence made to welcome people: it reads with care, does not judge and follows rules of respect. When a reply arrives, we let you know by email, without showing what you wrote. | `theme/patterns/home.php:68` |
| Mais sobre como funciona | More about how it works | `theme/patterns/home.php:74` |
| Se estiver muito difícil agora | If things are very hard right now | `theme/patterns/home.php:80` |
| O CVV conversa com você de graça, a qualquer hora, pelo telefone 188 ou pelo chat em cvv.org.br. | CVV talks with you for free, at any time, by phone at 188 or by chat at cvv.org.br. | `theme/patterns/home.php:84` |
| Ligar para o 188 | Call 188 | `theme/patterns/home.php:89` |
| Janela acesa | Lit window | `theme/patterns/lit-window.php` |
| Ilustração da identidade: uma fachada com uma janela acesa no celular e uma rua à noite de ponta a ponta em telas largas. A janela acende uma vez, a única animação automática do site. | The identity's illustration: a facade with one lit window on phones, a night street across the page on wide screens. The lit window turns on once, the only automatic animation of the site. | `theme/patterns/lit-window.php` |
