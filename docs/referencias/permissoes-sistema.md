# Catálogo de Permissões do Sistema

Documento de referência que cataloga **quais permissões existem** no
sistema e **como elas são atribuídas** (modelo). Serve de fonte única
para os casos de uso, que referenciam as permissões pelo seu
identificador (ex: `agendamentos.solicitar`).

## Modelo de atribuição

As permissões chegam ao usuário por duas camadas combinadas:

1. **Grupos de permissões (atribuição em conjunto):** um grupo define um
   conjunto de permissões uma vez; todos os usuários associados ao grupo
   as herdam. Gerido no módulo Grupos de Permissões (UC-GRP-01, UC-GRP-02).
2. **Permissões individuais (ajuste individual — adição e remoção):** um
   usuário pode receber permissões **extras** além das que herda dos
   grupos, e também pode ter uma permissão herdada de grupo **revogada**
   individualmente (UC-USR-08).

**Regra de precedência:** a **negação individual vence** a concessão de
grupo. Uma permissão negada individualmente não vale para o usuário, mesmo
que algum grupo dele a conceda.

**Permissão efetiva do usuário =**
`(união das permissões de todos os seus grupos) + (adições individuais) −
(remoções/negações individuais)`.

## Escopo das permissões (próprio × qualquer)

Algumas ações têm alcance diferente conforme o alvo. O escopo é indicado
por sufixo:

- `.proprio` — a ação alcança apenas os registros do próprio usuário
  (ex: editar o meu agendamento).
- `.qualquer` — a ação alcança registros de qualquer usuário
  (ex: excluir o agendamento de outra pessoa — poder administrativo).

Ações sem alcance sobre "dono" (ex: cadastrar um curso, aprovar uma
solicitação) **não usam** sufixo de escopo.

## Ações

| Ação | Significado |
|---|---|
| `visualizar` | Consultar/ver registros do recurso. |
| `listar` | Listar/pesquisar registros. |
| `criar` | Cadastrar um novo registro. |
| `editar` | Alterar um registro existente. |
| `excluir` | Remover (apagar) um registro. |
| `cancelar` | Mudar o status de um registro para "cancelado", preservando o histórico (alternativa não destrutiva à exclusão). |
| `solicitar` | Abrir uma solicitação sobre o recurso (agendamento/empréstimo). |
| `aprovar` | Aprovar/decidir sobre uma solicitação. |

## Grupos

Não há grupos "de sistema" que nasçam com a aplicação. **Todos os grupos
são criados manualmente** pelo Administrador (UC-GRP-01) e têm suas
permissões configuradas caso a caso.

> Exemplo comum: o Administrador cria um grupo "Comum" e o configura com
> as permissões básicas de um usuário aprovado (visualizar/solicitar/
> editar/cancelar/listar os próprios agendamentos e empréstimos etc.).
> Esse grupo "Comum" é apenas um exemplo de grupo criado pelo admin — não
> é um registro especial do sistema. Na aprovação de um cadastro
> (UC-USR-03), é o Administrador quem escolhe a qual(is) grupo(s) associar
> o novo usuário; não há associação automática a um grupo padrão.

### Grupo e usuário de inicialização

O sistema nasce (via seeder) com:
- Um grupo chamado **"Administradores"**, contendo **todas** as
  permissões do catálogo (incluindo as de escopo `.qualquer`).
- Um usuário **"SuperAdmin"** inicial, já associado ao grupo
  "Administradores", para viabilizar a primeira operação do sistema
  (aprovar cadastros, criar outros grupos etc.).

Diferente do "Comum", esse usuário/grupo de inicialização é criado
automaticamente na instalação do sistema, não manualmente. A partir daí,
novos administradores são formados associando usuários ao grupo
"Administradores" (UC-GRP-02).

#### SuperAdmin — hierarquia especial

O **SuperAdmin** é uma **conta única** de nível hierárquico superior aos
demais administradores. Regras especiais (que se sobrepõem ao modelo
normal de permissões — ver **ADR-004**):

- **Intocável por terceiros:** nenhum outro usuário — nem membros do
  grupo "Administradores", nem de qualquer outro grupo, independentemente
  de suas permissões — pode alterar as informações, a senha, o perfil, os
  grupos ou as permissões do SuperAdmin, nem desativá-lo.
- **Poder supremo:** o SuperAdmin pode alterar informações, permissões,
  grupos e status de **qualquer** usuário, incluindo os administradores do
  grupo "Administradores".
- **Autogestão preservada:** o próprio SuperAdmin gerencia a si mesmo
  (ex: troca a própria senha, recupera senha pelos fluxos normais),
  evitando bloqueio de acesso (lockout).

> **Distinção de vocabulário:** "SuperAdmin" é essa conta única de
> inicialização. Já "Administrador" (usado como ator nos casos de uso)
> continua significando *qualquer usuário com permissões administrativas*
> (tipicamente, membros do grupo "Administradores"). São conceitos
> diferentes: todo SuperAdmin é administrador, mas nem todo administrador
> é o SuperAdmin.

> Outros dados de inicialização do sistema também são criados na
> instalação, para viabilizar os primeiros cadastros:
> - o domínio de e-mail `ufam.edu.br`, ativo e padrão (UC-DOM-01, RN09);
> - o cargo "Professor do Magistério Superior", ativo (UC-CAR-01, RN07),
>   necessário ao cadastro do perfil Professor.

## Lista de Permissões por Recurso

### Agendamentos
- `agendamentos.visualizar.proprio` / `agendamentos.visualizar.qualquer`
- `agendamentos.listar.proprio` — listar/consultar os próprios agendamentos.
- `agendamentos.listar.qualquer` — acessar a **lista administrativa** (todos os agendamentos, com solicitante e detalhes — ver UC-AGE-04). É independente de aprovar/editar/cancelar: ter esta permissão não concede nenhuma ação, apenas a visualização da lista.
- `agendamentos.solicitar.proprio` — solicitar um agendamento para si.
- `agendamentos.solicitar.qualquer` — solicitar um agendamento **em nome de outro usuário**.
- `agendamentos.fixo.criar` — criar agendamento fixo/recorrente (nasce Aprovado — ver UC-AGE-07).
- `agendamentos.editar.proprio` / `agendamentos.editar.qualquer`
- `agendamentos.cancelar.proprio` / `agendamentos.cancelar.qualquer` — cancelar é a medida máxima (status "Cancelado"); o sistema não exclui agendamentos (histórico preservado). Exclusão definitiva, se necessária, é manual no banco.
- `agendamentos.aprovar.qualquer` — aprovar/decidir solicitações (inclui as próprias, já que "qualquer" abrange qualquer solicitante). Não há `aprovar.proprio` — evita que alguém sem poder de aprovar valide o próprio pedido (segregação de função).
- `agendamentos.configurar` — configurar regras de agendamento (dias, horários, bloqueios — ver UC-AGE-03).
- `agendamentos.tipo.criar` · `agendamentos.tipo.editar` · `agendamentos.tipo.desativar` — gestão de tipos de agendamento (UC-AGE-01).
- `agendamentos.docente.criar` · `agendamentos.docente.editar` · `agendamentos.docente.desativar` — gestão de docentes (UC-AGE-02).

### Grupos de Ambientes
- `grupos_ambientes.visualizar` · `grupos_ambientes.listar` · `grupos_ambientes.criar` · `grupos_ambientes.editar` · `grupos_ambientes.desativar` — gestão de grupos de ambientes e e-mails de notificação (UC-GAM-01). Sem exclusão (só desativação).

### Empréstimos
- `emprestimos.visualizar.proprio` / `emprestimos.visualizar.qualquer`
- `emprestimos.listar.proprio` / `emprestimos.listar.qualquer`
- `emprestimos.solicitar.proprio` — solicitar um empréstimo para si.
- `emprestimos.solicitar.qualquer` — solicitar um empréstimo **em nome de outro usuário**.
- `emprestimos.editar.proprio` / `emprestimos.editar.qualquer`
- `emprestimos.cancelar.proprio` / `emprestimos.cancelar.qualquer`
- `emprestimos.excluir.qualquer` — exclusão definitiva (administrativo)
- `emprestimos.aprovar.qualquer` — aprovar/decidir solicitações (inclui as próprias). Não há `aprovar.proprio` (segregação de função).

### Usuários
- `usuarios.visualizar.proprio` — ver o próprio cadastro.
- `usuarios.visualizar.qualquer` — ver o cadastro de qualquer usuário (ver UC-USR-12).
- `usuarios.listar` — listar/pesquisar usuários (ver UC-USR-07).
- `usuarios.criar` — cadastrar um usuário (cadastro administrativo).
- `usuarios.editar.proprio` — editar os próprios dados. Restrito à **troca de senha** (estando logado). Demais ajustes de cadastro não são autoatendimento (o sistema orienta a comparecer à sala de apoio). Ver UC-USR-11.
- `usuarios.editar.qualquer` — editar todos os dados de qualquer usuário (ver UC-USR-11).
- `usuarios.alterar_perfil` — alterar o perfil de um usuário (Aluno/Professor/Técnico), operação mais sensível que a edição comum (ver UC-USR-09).
- `usuarios.desativar` — desativar/reativar um usuário, preservando o registro (ver UC-USR-13).
- `usuarios.resetar_senha` — disparar o reset de senha de um usuário (envia link ao e-mail dele; não define/vê a senha — ver UC-USR-05, C3).
- `usuarios.aprovar` — aprovar/rejeitar cadastro (ver UC-USR-03).
- `usuarios.gerenciar_permissoes` — gerir permissões individuais (UC-USR-08).

### Grupos de Permissões
- `grupos_permissoes.visualizar`
- `grupos_permissoes.listar`
- `grupos_permissoes.criar`
- `grupos_permissoes.editar`
- `grupos_permissoes.excluir`
- `grupos_permissoes.atribuir_usuarios` — associar/desassociar usuários (UC-GRP-02).

### Inventário
- `inventario.visualizar` · `inventario.listar` · `inventario.criar` · `inventario.editar` · `inventario.excluir`

### Ambientes
- `ambientes.visualizar` · `ambientes.listar` · `ambientes.criar` · `ambientes.editar` · `ambientes.desativar` — gestão de ambientes (UC-AMB-01). Sem exclusão (só desativação).

### Cursos
- `cursos.visualizar` · `cursos.listar` · `cursos.criar` · `cursos.editar` · `cursos.desativar` — gestão de cursos (UC-CUR-01). Sem exclusão (só desativação). A leitura da lista no cadastro de usuário não exige essas permissões.

### Cargos
- `cargos.visualizar` · `cargos.listar` · `cargos.criar` · `cargos.editar` · `cargos.desativar` — gestão de cargos (UC-CAR-01). Sem exclusão (só desativação). A leitura da lista no cadastro de usuário não exige essas permissões.

### Vínculos
- `vinculos.visualizar` · `vinculos.listar` · `vinculos.criar` · `vinculos.editar` · `vinculos.desativar` — gestão de vínculos (UC-VIN-01). Sem exclusão (só desativação). A leitura da lista no cadastro de usuário não exige essas permissões.

### Domínios de E-mail
- `dominios_email.visualizar` · `dominios_email.listar` · `dominios_email.criar` · `dominios_email.editar` · `dominios_email.excluir`

### Perfis
- `perfis.visualizar` · `perfis.listar` · `perfis.criar` · `perfis.editar` · `perfis.excluir`

## Observações e pendências

- **Perfil × grupo (esclarecido):** o *perfil* (Aluno/Professor/Técnico)
  define os campos do cadastro; as *permissões* vêm de grupos + ajuste
  individual. São conceitos distintos. O perfil não concede permissões: na
  aprovação do cadastro, o Administrador escolhe manualmente a qual(is)
  grupo(s) associar o usuário (UC-USR-03), não há grupo automático.
- **Escopo do "editar":** a permissão `*.editar.proprio` diz *que* o
  usuário pode editar os próprios registros; *quando* isso é permitido
  (ex: só antes da aprovação, ou até X horas antes) é regra de negócio dos
  módulos Agendamento/Empréstimo — pendente até esses UCs.
- A matriz de recursos ainda não detalhados (inventário, ambientes,
  cursos, domínios de e-mail, perfis) é **provisória** e será revisada ao
  escrever os UCs de cada módulo.
