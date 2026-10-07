# Backlog de Módulos

Lista provisória dos módulos planejados para o sistema. Cada módulo tem sua
própria pasta em `docs/casos-de-uso/` e um prefixo de ID usado na numeração
dos casos de uso (`UC-<PREFIXO>-NN`). Referências cruzadas entre módulos
citam o ID completo (ex: `UC-DOM-01`).

Esta lista é provisória e será atualizada conforme os casos de uso forem
escritos e novos módulos forem identificados.

| Módulo | Pasta | Prefixo | UCs escritos | Status |
|---|---|---|---|---|
| Domínios de e-mail | `dominios-email/` | `DOM` | 1 | Em andamento |
| Usuários | `usuarios/` | `USR` | 13 | Em andamento |
| Ambientes | `ambientes/` | `AMB` | 1 | Em andamento |
| Inventário | `inventario/` | `INV` | 0 | Não iniciado |
| Empréstimo | `emprestimo/` | `EMP` | 0 | Não iniciado (permissão liberada pela aprovação no UC-USR-01) |
| Agendamento | `agendamento/` | `AGE` | 8 | Em andamento |
| Perfis de usuários | `perfis-usuario/` | `PRF` | — | **Não terá UC** — perfis são enum fixo no código (ver ADR-002 em `decisoes-tecnicas.md`) |
| Grupos de permissões | `grupos-permissoes/` | `GRP` | 2 | Em andamento |
| Grupos de Ambientes | `grupos-ambientes/` | `GAM` | 1 | Em andamento |
| Cursos | `cursos/` | `CUR` | 1 | Em andamento |
| Cargos | `cargos/` | `CAR` | 1 | Em andamento |
| Vínculos | `vinculos/` | `VIN` | 1 | Em andamento |

## Legenda de Status
- **Não iniciado:** nenhum caso de uso escrito ainda.
- **Em andamento:** pelo menos um caso de uso escrito, módulo ainda incompleto.
- **Completo:** todos os casos de uso previstos para o módulo foram escritos e revisados.

## Casos de uso escritos por módulo

### Usuários (`usuarios/`)
- **UC-USR-01** — Cadastro de Usuário
- **UC-USR-02** — Confirmação de E-mail
- **UC-USR-03** — Aprovação/Rejeição de Cadastro
- **UC-USR-04** — Login / Autenticação
- **UC-USR-05** — Gestão de Senha (esqueci / alterar logado / reset pelo admin)
- **UC-USR-06** — Reenvio de Confirmação de E-mail
- **UC-USR-07** — Listar e Pesquisar Usuários
- **UC-USR-08** — Gerenciar Permissões Individuais do Usuário
- **UC-USR-09** — Alterar Perfil do Usuário
- **UC-USR-10** — Gestão de Sessão (logout, expiração, encerrar sessões)
- **UC-USR-11** — Editar Usuário (próprio: só via sala de apoio, exceto senha que é o UC-USR-05; admin: todos os campos exceto perfil e senha)
- **UC-USR-12** — Visualizar Detalhe do Usuário
- **UC-USR-13** — Desativar / Reativar Usuário
- Documento de apoio: `_estados-usuario.md` (modelo de estados + dimensão ativo/desativado).
- Referências: `docs/referencias/permissoes-sistema.md`, `docs/referencias/notificacoes-sistema.md`.

### Grupos de Permissões (`grupos-permissoes/`)
- **UC-GRP-01** — Gerenciar Grupos de Permissões
- **UC-GRP-02** — Atribuir Usuários a um Grupo
- Referência: `docs/referencias/permissoes-sistema.md` (catálogo, modelo grupos + ajuste individual com adição/negação).

### Domínios de E-mail (`dominios-email/`)
- **UC-DOM-01** — Gerenciar Domínios de E-mail Permitidos

### Cargos (`cargos/`)
- **UC-CAR-01** — Gerenciar Cargos

### Cursos (`cursos/`)
- **UC-CUR-01** — Gerenciar Cursos

### Vínculos (`vinculos/`)
- **UC-VIN-01** — Gerenciar Vínculos

### Ambientes (`ambientes/`)
- **UC-AMB-01** — Gerenciar Ambientes (referenciado por FK no Agendamento — ver ADR-003 em `decisoes-tecnicas.md`)

### Grupos de Ambientes (`grupos-ambientes/`)
- **UC-GAM-01** — Gerenciar Grupos de Ambientes

### Agendamento (`agendamento/`)
- **UC-AGE-01** — Gerenciar Tipos de Agendamento (com cor)
- **UC-AGE-02** — Gerenciar Docentes
- **UC-AGE-03** — Configurar Regras de Agendamento (global + por ambiente + bloqueios)
- **UC-AGE-04** — Visualizar Agenda (calendário + lista administrativa)
- **UC-AGE-05** — Solicitar Agendamento
- **UC-AGE-06** — Aprovar / Rejeitar Solicitação
- **UC-AGE-07** — Gerenciar Agendamentos Fixos / Recorrentes
- **UC-AGE-08** — Cancelar / Editar Agendamento

### UCs previstos (a escrever conforme necessidade)
- **Usuários:** o ciclo de vida da conta e a gestão de usuários estão cobertos (UC-USR-01 a 13). Novos UCs conforme surgirem necessidades.
- **Agendamento:** módulo coberto com 8 UCs. Novos UCs conforme surgirem necessidades (ex: relatórios de utilização por ambiente).

## Observações
- A numeração de UC é independente por módulo (cada módulo começa em `01`).
- Dependências entre módulos devem ser explicitadas na seção de Metadados de cada UC.
- `UC-USR-01` (Cadastro e Aprovação de Usuário) possui as seguintes dependências pendentes, a resolver ao iniciar os respectivos módulos:
  - **Domínios de e-mail:** ✅ resolvido — validação de domínio documentada em UC-DOM-01.
  - **Cursos:** ✅ resolvido — gestão de cursos documentada em UC-CUR-01 (inclui "OUTRO" inicializado e caixa alta).
  - **Perfis de usuário:** ✅ resolvido — enum fixo no código, sem módulo/UC de gerenciamento (ADR-002 em `decisoes-tecnicas.md`).
  - **Cargos:** ✅ resolvido — gestão de cargos documentada em UC-CAR-01.
  - **Vínculos:** ✅ resolvido — gestão de vínculos documentada em UC-VIN-01 (inclui "OUTRO" inicializado).
  - **Grupos de permissões:** modelo definido (grupos + ajuste individual com adição/negação) em `docs/referencias/permissoes-sistema.md`; UCs em UC-GRP-01/02 e UC-USR-08. Na aprovação (UC-USR-03), o Administrador associa o usuário aos grupos.
