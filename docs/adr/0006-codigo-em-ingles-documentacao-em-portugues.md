# Código em inglês, documentação do domínio em português

Todo código é escrito em inglês: identificadores, comentários, mensagens de commit e os textos-fonte da interface, que recebem tradução `pt_BR` pelo text domain `euteajudo`. A documentação do domínio (glossário, ADRs, plano, guia de identidade) continua em português, porque descreve o domínio para o mantenedor. O glossário tem uma linha _Código_ em cada termo com a tradução oficial, para que nenhum termo ganhe sinônimos no código. Tudo o que é registrado no WordPress leva o prefixo `eta_`, o namespace PHP é `EuTeAjudo\`.

## Considered Options

- Textos-fonte da interface em português: descartado porque o projeto prevê vários idiomas e a prática do WordPress é texto-fonte em inglês com arquivos de tradução.
