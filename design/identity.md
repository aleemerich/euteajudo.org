# Identidade: Luz acesa

Aprovada em 22/11/2026, entre três propostas (as outras duas ficam em `proposals/` como registro). Os valores exatos estão em [tokens.json](tokens.json); este guia explica o conceito e as regras de uso.

## Conceito

Muita gente só consegue escrever de madrugada, quando a casa dorme e a cabeça não para. A identidade é a de uma janela acesa numa rua escura: alguém está acordado e disponível. O site tem pouco brilho, nada que acelere, e uma única luz quente.

**A luz âmbar é a marca.** Ela aparece na janela do logo, nos botões e no que pede atenção (Resposta nova, aba ativa). Nenhum outro elemento brilha.

## Logo

Arquivos em [logo/](logo/). O texto do logo está convertido em desenho; não depende de fonte instalada.

| Arquivo | Uso |
|---|---|
| `logo-night.svg` | Logo completo sobre fundo escuro (com o brilho da janela) |
| `logo-day.svg` | Logo completo sobre fundo claro |
| `logo-mono.svg` | Logo em uma cor (`currentColor`), usado no tema para seguir a cor do texto |
| `symbol-night.svg`, `symbol-day.svg`, `symbol-mono.svg` | Só a janela, para espaços pequenos |
| `favicon.svg` | Ícone do navegador (janela sobre quadrado noite) |
| `social-avatar.svg` | Foto de perfil nas redes sociais (quadrado; as redes recortam em círculo) |

Regras:
- O vidro aceso fica sempre no canto superior esquerdo e sempre em âmbar `#F2B13B`, em qualquer versão.
- Não girar, não trocar a cor do âmbar, não acender outros vidros, não colocar o logo sobre fotos.
- Espaço livre em volta: pelo menos a largura de um vidro da janela.
- O nome se escreve "eu te ajudo" em minúsculas no logo. Em texto corrido, o nome do projeto é EuTeAjudo.org.

## Cores

Duas versões com os mesmos papéis. Contraste medido pelo WCAG 2.2; todo texto passa de 4,5:1 (nível AA).

| Papel | Noite | Dia |
|---|---|---|
| Fundo | `#0F2529` | `#EEF3F1` |
| Cartões e Respostas | `#16343B` | `#FFFFFF` |
| Campos de texto | `#1B3D44` | `#FFFFFF` |
| Borda dos campos | `#7A9692` | `#738884` |
| Texto | `#E9EFEC` (13,7:1) | `#10262A` (14,1:1) |
| Texto secundário | `#9BB1AD` (7,1:1) | `#4A625E` (5,9:1) |
| Linhas | `#24444B` | `#D3DEDB` |
| Botões (âmbar com texto escuro) | `#F2B13B` / `#1E1503` (9,6:1) | igual |
| Foco do teclado | `#F2B13B` (8,5:1) | `#0F2529` (14,2:1) |
| Aviso "Resposta nova" | `#F6CD78` (10,6:1) | `#7A4E00` (7,2:1) |

**Qual versão aparece:**
1. Se o visitante escolheu uma versão (botão em Conta e no rodapé), vale a escolha dele. A escolha fica só no navegador, não na conta.
2. Senão, segue a preferência do aparelho (modo claro ou escuro do sistema).
3. Se o aparelho não informa, Noite.

Na versão Dia, o âmbar tem pouco contraste com o fundo (1,7:1). Por isso o foco do teclado vira verde-noite, e os botões ganham uma borda de 1 px em `#B7790F`.

## Tipografia

Uma família só: **Atkinson Hyperlegible Next**, do Braille Institute, criada para máxima legibilidade. As letras que se confundem (I, l e 1; O e 0) têm formas distintas, o que ajuda quem lê cansado, com a vista turva ou com baixa visão. Licença OFL.

- Arquivos em [fonts/](fonts/): os originais (`.ttf`) e as versões para o site em `fonts/web/` (`.woff2`, variáveis, latim e latim estendido).
- **O site serve as fontes pelo próprio tema.** Nunca carregar do Google Fonts: cada acesso entregaria o IP do Amigo a um terceiro.
- Pesos: 400 no texto, 600 em rótulos e destaques, 700 em títulos.
- Texto com 17 px e entrelinha 1,55. Títulos com entrelinha 1,15.
- Tamanhos: veja `font.size` em `tokens.json`.

## Tom de voz

Voz baixa de madrugada: frases curtas, nada que acelere. Reconhece o momento sem dramatizar. Vale para o site, para as redes sociais, para os vídeos e para a persona do Agente.

- **Botões dizem o que acontece**: "Começar a escrever", "Enviar pedido", "Encerrar pedido". A mesma ação mantém o mesmo nome no fluxo inteiro.
- **Espera**: "Enviado. A resposta pode levar algumas horas; avisamos por e-mail."
- **Erro explica e orienta**: "O texto está vazio. Escreva algumas linhas para enviar." Erros não pedem desculpa e não são vagos.
- **Lista vazia convida**: "Nada por aqui ainda. Quando quiser escrever, é só começar."
- **Evitar**: promessas ("vai ficar tudo bem"), frases motivacionais, pontos de exclamação, emojis, termos técnicos, letras maiúsculas em rótulos.
- **Tratamento**: "você". O Amigo é chamado pelo Apelido quando faz sentido ("Oi, Amigo 48213").

## Elementos

- **A janela acesa** é o único elemento ilustrativo. Na página inicial, uma fachada com uma janela que acende uma vez ao abrir a página. É a única animação automática do site, e some para quem pede menos movimento no sistema.
- Cantos arredondados: 12 px em campos e botões, 16 px em cartões.
- Alvos de toque com pelo menos 48 px de altura.
- Sem sombras decorativas. O único brilho é o da janela.
- Ícones de traço (1,8 px), cantos arredondados, na cor do texto.

## Redes sociais e vídeos

Modelos em [social-kit/](social-kit/).

- Fundo Noite com texto Luar é o padrão. Dia só para conteúdo informativo longo.
- Uma ideia por peça, em frases curtas, no mesmo tom do site.
- Vídeos: legenda sempre presente, em Atkinson Hyperlegible Next 600 sobre faixa Noite com 85% de opacidade; símbolo da janela no canto inferior direito.
- Nunca usar depoimentos ou textos reais de Pedidos, mesmo anônimos.
