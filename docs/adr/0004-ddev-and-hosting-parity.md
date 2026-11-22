# Ambiente local com DDEV, nas mesmas versões da hospedagem

O ambiente local é o DDEV, configurado em `.ddev/` e versionado no repositório, com as mesmas versões da hospedagem compartilhada (cPanel): PHP 8.5, MySQL 5.7 e Apache (`apache-fpm`), e os mesmos limites do `php.ini` (`memory_limit` 512M, `max_execution_time` 60, `max_input_vars` 1000, `post_max_size` e `upload_max_filesize` 64M). O pacote próprio do mantenedor (`wp-docker-package`) foi descartado porque o MySQL não inicializava com os dados num disco NTFS e porque as versões não eram fixas. O DDEV guarda o banco num volume do Docker, dá HTTPS local (exigido pela Application Password dos Agentes) e roda igual no CI.

## Consequências

- **MySQL 5.7 em produção**, sem suporte desde 2023 e impossível de trocar na hospedagem compartilhada. Nenhum SQL próprio pode usar recursos do MySQL 8 (CTEs, funções de janela, `JSON_TABLE`). Usar sempre as APIs do WordPress.
- Composer fixado na versão de PHP da hospedagem (`config.platform.php`) e PHPCS com PHPCompatibility apontado para a mesma versão.
- **Sem SSH em produção**: publicação por FTPS e nenhum WP-CLI no servidor. A publicação por FTP não executa o hook de ativação, então o plugin guarda a própria versão numa opção e aplica migrações ao detectar versão nova.
- O código fica num disco NTFS (`/mnt/dados`), visível também pelo Windows. Por isso cada repositório usa `core.fileMode=false`, está em `safe.directory` e tem `.gitattributes` com `eol=lf`. O banco fica no volume do Docker; cópias vão para `backups/` (ignorada pelo git) com `ddev export-db`.
- A hospedagem tem ModSecurity e Monarx. Um comportamento que funciona localmente e falha só em produção deve ser investigado primeiro neles.
