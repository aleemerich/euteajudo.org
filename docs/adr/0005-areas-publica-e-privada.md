# Áreas pública e privada, endereços e acesso aos Pedidos

O site tem quatro áreas. A pública indexável usa páginas comuns do WordPress (Início, Como funciona, Sobre, Ajuda imediata, Termos, Privacidade). A pública não indexável tem páginas próprias com blocos para Entrar, Cadastrar, Recuperar senha e Confirmar e-mail; o `wp-login.php` fica só para o Administrador. A privada (Amigo logado: Desabafar, Meus Pedidos, Pedido, Minha conta) é fora dos buscadores, sem cache e redireciona para Entrar quem não está logado. O `wp-admin` é só do Administrador; Amigos e Agentes são redirecionados e não veem a barra de administração. Agentes usam só a REST.

O Pedido é acessado em `/meus-pedidos/{código}/`, onde o código é aleatório e fica no `post_name`, nunca o id do post. Quem não é o autor nem tem a capability recebe 404, e não 403. Decidimos assim porque o id sequencial revelaria quantos Pedidos existem e convidaria a testar números vizinhos, e um 403 confirmaria que o Pedido existe.

## Consequências

- O CPT do Pedido não usa o template `single` nativo. A página Meus Pedidos resolve o código e o bloco Conversa faz a checagem de acesso.
- Todas as páginas privadas enviam `noindex` e cabeçalhos que impedem cache, inclusive em plugins de cache da hospedagem.
