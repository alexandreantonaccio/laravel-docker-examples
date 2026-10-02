# Modelo de Estados do Usuário

Documento de referência compartilhado pelos casos de uso do módulo de
Usuários. Define os estados possíveis de um cadastro e o que cada estado
permite. Os UCs do módulo (cadastro, confirmação de e-mail, aprovação,
login etc.) referenciam este documento em vez de repetir a descrição.

O status controla **se e como** o usuário acessa o sistema. As **ações**
que ele pode realizar (solicitar agendamento/empréstimo etc.) dependem das
suas **permissões efetivas** (grupos + ajuste individual — ver
`docs/referencias/permissoes-sistema.md`), não do status em si. Um usuário
só recebe permissões após ser associado a algum grupo pelo Administrador
(o que costuma ocorrer na aprovação — UC-USR-03).

## Estados

| Estado | Login? | Acesso ao sistema | Recebe permissões (grupos)? |
|---|---|---|---|
| **Pendente de Confirmação de E-mail** | Não | Nenhum | Não |
| **E-mail Confirmado** | Sim | Autenticado, sem grupos ainda | Ainda não (aguardando decisão do Administrador) |
| **Aprovado** | Sim | Autenticado | Sim — nos grupos escolhidos pelo Administrador |
| **Rejeitado** | Sim | Autenticado | Não recebe grupos por este fluxo |

## Descrição dos estados

- **Pendente de Confirmação de E-mail:** cadastro iniciado, e-mail ainda
  não confirmado. O usuário não consegue efetuar login. Cadastros que
  permanecem neste estado além do prazo são expirados e removidos,
  liberando e-mail e identificação funcional para novo cadastro
  (ver UC-USR-01, regra de expiração de cadastro não confirmado).
- **E-mail Confirmado:** e-mail verificado, aguardando decisão do
  Administrador. O usuário pode efetuar login, mas ainda não foi associado
  a nenhum grupo — portanto suas ações se limitam ao que o sistema
  disponibiliza a um usuário sem permissões específicas (ex: consultas
  liberadas por padrão), até que o Administrador decida.
- **Aprovado:** o Administrador aprovou o cadastro e, no mesmo ato,
  associou o usuário a um ou mais grupos de permissões (UC-USR-03 e
  UC-GRP-02). As ações disponíveis passam a ser as permissões efetivas
  resultantes desses grupos (mais eventuais ajustes individuais).
- **Rejeitado:** o Administrador rejeitou o cadastro. O usuário mantém o
  acesso de login, mas não é associado a grupos por este fluxo. A decisão
  negativa fica registrada com a justificativa informada pelo Administrador.

## Transições

```
[novo cadastro]
      │
      ▼
Pendente de Confirmação de E-mail
      │  (usuário confirma o e-mail — UC-USR-02)
      ▼
   E-mail Confirmado
      │  (Administrador decide — UC-USR-03)
      ├───────────────► Aprovado
      └───────────────► Rejeitado
```

> Observação: o status não concede ações por si. As permissões vêm de
> grupos + ajuste individual (ver `docs/referencias/permissoes-sistema.md`,
> UC-GRP-01/02 e UC-USR-08). A aprovação (UC-USR-03) é o momento em que o
> Administrador normalmente associa o usuário aos grupos.

## Dimensão Ativo / Desativado (separada do status)

Além do **status do cadastro** (os 4 estados acima), todo usuário tem uma
segunda dimensão independente: estar **ativo** ou **desativado**.

- **Ativo:** condição normal; o acesso depende apenas do status do cadastro
  e das permissões.
- **Desativado:** o Administrador desativou o usuário (UC-USR-13). Um
  usuário desativado **não consegue efetuar login**, independentemente do
  status do cadastro, e suas sessões ativas são encerradas. A reativação
  restaura o acesso ao estado anterior.

As duas dimensões são ortogonais: um usuário "Aprovado" pode estar ativo
ou desativado. A desativação preserva todo o registro (o sistema não
exclui usuários — ver UC-USR-13, RN05). A verificação de login considera
**ambas** as dimensões (ver UC-USR-04): só loga quem tem status que
permite **e** está ativo.
