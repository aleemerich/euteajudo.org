# Publicação e operação

Roteiro para pôr o site no ar na hospedagem compartilhada (cPanel, PHP 8.5, MySQL 5.7, sem SSH) e mantê-lo. A hospedagem recebe só o plugin e o tema; o WordPress é instalado pelo cPanel. Nenhum segredo vai para o repositório: tudo fica no `wp-config.php` do servidor e nos segredos do GitHub.

## Primeira vez

1. **Banco**: no cPanel, criar um banco e um usuário exclusivos do EuTeAjudo, com todos os privilégios só nesse banco.
2. **WordPress**: instalar pelo instalador do cPanel no domínio, com PHP 8.5 (`ea-php85`). Usuário administrador com senha forte; nunca `admin`.
3. **Idioma**: em Configurações > Geral, escolher Português do Brasil **antes** de ativar o plugin (as páginas e os termos são criados no idioma do site).
4. **`wp-config.php`**: acrescentar antes de `/* That's all, stop editing! */`:

   ```php
   define( 'WP_ENVIRONMENT_TYPE', 'production' );
   define( 'DISALLOW_FILE_EDIT', true );
   define( 'FORCE_SSL_ADMIN', true );
   define( 'DISABLE_WP_CRON', true );          // o cron do cPanel chama o wp-cron.php
   define( 'WP_DEBUG', true );
   define( 'WP_DEBUG_DISPLAY', false );
   define( 'WP_DEBUG_LOG', '/home/USUARIO/logs/euteajudo-debug.log' ); // fora do public_html

   // Login com Google (Google Cloud Console > Credenciais)
   define( 'EUTEAJUDO_GOOGLE_CLIENT_ID', '...' );
   define( 'EUTEAJUDO_GOOGLE_CLIENT_SECRET', '...' );

   // E-mail pelo serviço transacional
   define( 'EUTEAJUDO_SMTP_HOST', 'smtp.exemplo.com' );
   define( 'EUTEAJUDO_SMTP_PORT', 587 );        // 465 usa SSL; os outros, STARTTLS
   define( 'EUTEAJUDO_SMTP_USER', '...' );
   define( 'EUTEAJUDO_SMTP_PASSWORD', '...' );
   define( 'EUTEAJUDO_MAIL_FROM', 'nao-responda@euteajudo.org' );
   ```

   No Google Cloud, a URI de redirecionamento autorizada passa a ser `https://euteajudo.org/wp-json/euteajudo/v1/auth/google/callback` (a mesma rota do ambiente local, com o domínio de produção).
5. **Domínio de e-mail**: configurar SPF, DKIM e DMARC conforme o serviço transacional, e conferir com um e-mail de teste.
6. **`.htaccess`** na raiz do site, acima das regras do WordPress:

   ```apache
   <FilesMatch "^(error_log|debug\.log|wp-config\.php|readme\.html|license\.txt)$">
     Require all denied
   </FilesMatch>
   ```

7. **Cron**: no cPanel > Cron Jobs, a cada 5 minutos:

   ```
   */5 * * * * /usr/local/bin/ea-php85 /home/USUARIO/public_html/wp-cron.php >/dev/null 2>&1
   ```

   O caminho do `ea-php85` pode variar; o cPanel mostra o correto em "MultiPHP Manager".
8. **FTP**: criar uma conta de FTP com diretório raiz no `wp-content` do site. No GitHub (Settings > Environments > `production`), cadastrar os segredos `FTP_HOST`, `FTP_USER` e `FTP_PASSWORD`, e as variáveis `FTP_WP_CONTENT` (normalmente `.`) e `SITE_URL` (`https://euteajudo.org`).
9. **Primeira publicação**: em Actions > Deploy > Run workflow (branch `main`). Depois, em Plugins, ativar "EuTeAjudo" e, em Aparência > Temas, ativar "EuTeAjudo". A primeira visita roda as migrações: papéis, páginas, termos de Triagem, Serviços de apoio, banco de Apelidos de Agente e links permanentes `/%postname%/`.
10. **Agente**: em Usuários > Adicionar novo, criar um usuário com a função "Agent" (e-mail qualquer, sem enviar notificação). O login vira o Apelido. Editar esse usuário e, em "Senhas de aplicativo", criar uma senha chamada `agente`. Login, senha e `https://euteajudo.org/wp-json/euteajudo/v1` vão para o `.env` do Agente. Contrato da API: [agent-api.md](agent-api.md).
11. **Backup**: conferir no cPanel que o backup diário inclui o banco e o `wp-content`.

## Cada publicação

1. PR com CI verde, merge em `main`.
2. Actions > Deploy > Run workflow. O workflow envia `plugin/` e `theme/` por FTPS e visita o site para rodar as migrações.
3. Conferir no Painel o widget "EuTeAjudo: Agente" e o `euteajudo-debug.log`.

## Operação

- **Agente parado**: o widget do Painel fica vermelho depois de 30 minutos sem consulta, e um e-mail chega quando um Pedido espera mais de 24 horas.
- **Falha na verificação**: um e-mail por Pedido. Abrir o Pedido no painel e, se for o caso, responder com o Apelido do Agente.
- **Logs**: `euteajudo-debug.log` fora do `public_html` e o `error_log` do cPanel.
