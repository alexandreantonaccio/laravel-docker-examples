# Catálogo de Notificações do Sistema

Documento de referência que cataloga **quais notificações** o sistema
envia, o **evento** que as dispara, **quem recebe** e o **canal**. Serve
de fonte única para os casos de uso, que referenciam as notificações por
aqui em vez de repetir a descrição.

> **Escopo:** este documento lista as notificações. O conteúdo exato
> (texto) das mensagens e o momento técnico de envio (síncrono/assíncrono
> via fila) são detalhes de implementação. A stack de referência
> (SIGEA/SIGEB) usa notificações Laravel via e-mail (SMTP) e um dispatcher
> dedicado — ver `stack-tecnica-sigea-sigeb.md`.

## Destinatários

- **Usuário:** o titular do cadastro (solicitante).
- **Administração:** o(s) usuário(s) responsáveis pela análise
  (endereço/gestão administrativa).

## Canais

- **E-mail** — canal principal (institucional na maioria dos casos).

## Catálogo (evento → destinatário → canal)

| # | Evento | UC | Destinatário | Canal | Observação |
|---|---|---|---|---|---|
| N01 | Cadastro iniciado — confirmação de e-mail | UC-USR-01 | Usuário | E-mail | Contém o link/token de confirmação (validade 24h). |
| N02 | Reenvio de confirmação de e-mail | UC-USR-06 | Usuário | E-mail | Novo link; invalida o anterior. |
| N03 | Novo cadastro aguardando análise | UC-USR-02 | Administração | E-mail | Disparado após o usuário confirmar o e-mail. |
| N04 | Cadastro aprovado | UC-USR-03 | Usuário | E-mail | Informa liberação de acesso. |
| N05 | Cadastro rejeitado | UC-USR-03 | Usuário | E-mail | Inclui a justificativa da rejeição. |
| N06 | Redefinição de senha por link (esqueci — C1, ou reset pelo Administrador — C3) | UC-USR-05 | Usuário | E-mail | Link de redefinição (token curto, uso único). A senha nunca vai por e-mail. |
| N07 | E-mail de acesso alterado pelo Administrador | UC-USR-11 | Usuário | E-mail | Enviado ao e-mail anterior e ao novo, por segurança. |
| N08 | Conta desativada | UC-USR-13 | Usuário | E-mail | Orienta procurar a sala de apoio para reativação. |
| N09 | Conta reativada | UC-USR-13 | Usuário | E-mail | Informa restabelecimento do acesso. |
| N10 | Perfil alterado pelo Administrador | UC-USR-09 | Usuário | E-mail | Informa a mudança de perfil (ex: Técnico → Professor). |
| N11 | Senha alterada (troca logado — C2) | UC-USR-05 | Usuário | E-mail | Aviso de segurança ("sua senha foi alterada"). |
| N12a | Agendamento solicitado — confirmação ao solicitante | UC-AGE-05 | Solicitante | E-mail | Confirma recebimento e informa status Pendente. |
| N12b | Agendamento solicitado — aviso ao grupo do ambiente | UC-AGE-05 / UC-AGE-07 | E-mails do grupo do ambiente | E-mail | Disparado para os responsáveis do grupo do ambiente, se o ambiente pertencer a um grupo. Para série recorrente (UC-AGE-07), uma única notificação resumida. |
| N13 | Agendamento aprovado | UC-AGE-06 | Solicitante | E-mail | Informa a aprovação do agendamento. |
| N14 | Agendamento rejeitado | UC-AGE-06 | Solicitante | E-mail | Informa a rejeição e apresenta o motivo. |
| N15 | Agendamento cancelado | UC-AGE-08 | E-mails do grupo do ambiente | E-mail | Disparado para os responsáveis do grupo do ambiente quando um agendamento é cancelado (pelo solicitante ou admin). |

## Observações e pendências

- **Não revelação de e-mail:** nos fluxos de recuperação de senha
  (UC-USR-05) e reenvio de confirmação (UC-USR-06), a mensagem exibida na
  tela é genérica para não revelar se o e-mail existe; a notificação por
  e-mail só é efetivamente enviada quando o cadastro se aplica.
- **Mudança de permissões/grupos NÃO notifica:** por decisão de projeto,
  alterações de permissões individuais (UC-USR-08) ou de grupos
  (UC-GRP-02) **não** geram notificação ao usuário — evita ruído, já que
  permissões podem mudar com frequência.
- **Módulos futuros:** Empréstimo terá suas próprias notificações. Serão adicionadas quando esse módulo for documentado.
