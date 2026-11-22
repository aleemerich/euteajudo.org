# Agentes de IA rodam fora da plataforma e buscam trabalho pela API

A plataforma (código aberto) não contém nenhuma IA. Toda Resposta automática vem de um Agente: um sistema externo, de código fechado, que consulta periodicamente a fila de pendências da plataforma e publica Triagens e Respostas pela API, autenticado por uma credencial própria. Decidimos assim para isolar a plataforma pública dos sistemas de apoio e permitir, no futuro, vários Agentes, inclusive de terceiros, sem mudar a plataforma.

## Considered Options

- Webhook da plataforma para o Agente: descartado no MVP porque o Agente roda primeiro numa máquina doméstica, inalcançável a partir da hospedagem compartilhada e nem sempre ligada.

## Consequências

- A plataforma trata todo Agente como um cliente igual aos outros. Nenhuma regra de negócio da IA (prompts, triagem, escolha de modelo) fica no repositório aberto.
- Quando o Agente cai, os Pedidos esperam na fila e o trabalho continua quando ele volta. Não há compromisso de prazo com o Amigo.
- Os Apelidos que os Agentes usam ficam guardados num banco gerado aleatoriamente na instalação e armazenado só no banco de dados, nunca no código.
