# O plugin cuida do funcionamento, o tema cuida da aparência

O plugin `euteajudo` guarda o que o site faz: contas, Pedidos, Respostas, API dos Agentes, regras, e-mails e as telas que dependem disso (formulários, conversa, Meus pedidos, Minha conta, o link do Apelido e a navegação do Amigo). O tema `euteajudo-theme` guarda como o site aparece: identidade, cores, fontes, templates, o Início, a ilustração da janela acesa e o rodapé. Decidimos assim depois que o Início foi feito como bloco do plugin: os textos não podiam ser editados no painel e o visual ficou dividido em dois lugares.

## Regra prática

- Se o conteúdo é igual para todo visitante e não depende de dado do plugin, ele vai para o tema, como pattern com blocos nativos.
- Se depende de quem está conectado, de um Pedido ou de uma regra, é bloco dinâmico do plugin. O estilo desse bloco vai junto com ele, para o bloco funcionar com qualquer tema, mas usa sempre as variáveis de cor e tamanho do tema.
- Links do tema para páginas do plugin usam os endereços públicos (`/criar-conta/`, `/entrar/`). O plugin redireciona quem já está conectado, então o tema não precisa saber do login.

## Consequências

- O Início é uma Página do WordPress ("Início"), criada uma vez pelo tema a partir do pattern `euteajudo/home` e definida como página inicial estática. Depois disso é editada em Páginas como qualquer outra. A ilustração (`euteajudo/lit-window`) entra por referência ao pattern, então o desenho continua no tema.
- O rodapé é o pattern `euteajudo/footer-links` dentro do template part.
- O WordPress guarda em cache a lista de patterns do tema. O tema limpa esse cache uma vez por versão (`VERSION` em `functions.php` e `Version` em `style.css`), porque a publicação por FTP troca os arquivos sem avisar o WordPress. Subir a versão do tema sempre que um pattern for criado ou renomeado.
- Os textos do tema ficam em `theme/languages/euteajudo-theme-pt_BR.po`.
