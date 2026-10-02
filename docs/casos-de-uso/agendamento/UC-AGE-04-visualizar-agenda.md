# UC-AGE-04: Visualizar Agenda

## Metadados
- **Módulo:** Agendamento
- **Dependências:**
  - `docs/referencias/permissoes-sistema.md` — o calendário (visão geral) é acessível a qualquer usuário autenticado; a lista administrativa exige `agendamentos.listar.qualquer`. As ações a partir da lista são condicionadas às suas próprias permissões (`agendamentos.aprovar.qualquer`, `agendamentos.editar.*`, `agendamentos.cancelar.*`).
  - UC-AGE-01 (Tipos de Agendamento) — cores dos tipos usadas no calendário.
  - UC-AMB-01 (Ambientes) — filtro por ambiente.
  - `_estados-agendamento.md` — modelo de estados dos agendamentos.
- **UCs relacionados:**
  - UC-AGE-05 (Solicitar Agendamento) — acionado a partir da agenda.
  - UC-AGE-06 (Aprovar/Rejeitar) — acionado a partir da lista administrativa.
  - UC-AGE-08 (Cancelar/Editar) — acionado a partir do detalhe.
- **Status:** Rascunho

## Informações Gerais
- **Ator Principal:** Usuário autenticado (visualização geral) / Administrador (visualização administrativa)
- **Atores Secundários:** Nenhum
- **Pré-condições:** O ator está autenticado. Para a visualização geral (calendário): qualquer usuário logado. Para a lista administrativa: permissão `agendamentos.listar.qualquer`.
- **Pós-condições:** Nenhuma alteração de dados (apenas consulta/visualização).

---

## Conceito — Duas Visões

Este UC cobre duas visões distintas da agenda:

1. **Calendário (visão geral):** acessível a todos os usuários autenticados.
   Mostra todos os agendamentos num formato de calendário com cores por
   tipo, sem separar fixos de normais. Ideal para verificar
   disponibilidade.

2. **Lista administrativa (visão restrita):** acessível a quem tem
   `agendamentos.listar.qualquer`. Exibe os agendamentos em formato de
   lista tabular, com separação entre **Agendamentos Normais** e
   **Agendamentos Fixos/Recorrentes**. Ideal para gestão. As ações
   disponíveis a partir da lista (aprovar, rejeitar, editar, cancelar)
   dependem de permissões próprias e independentes (**RN08**).

---

## Fluxo Principal — Calendário (visão geral)

1. O usuário autenticado acessa a área de **"Agenda"**.
2. O sistema exibe o calendário no período padrão: **dia atual** (**RN01**).
3. Cada agendamento é exibido com a **cor do seu tipo** (UC-AGE-01) e indicação visual de status (ex: Pendente, Aprovado) (**RN02**).
4. O usuário pode navegar pelos períodos e aplicar filtros:
   - **Período:** dia / semana / mês / ano (**RN03**)
   - **Ambiente:** exibir apenas agendamentos de um ambiente específico (**RN04**)
5. O usuário clica em um agendamento para ver o **detalhe** (**RN05**).

## Fluxo Alternativo — Lista Administrativa (visão restrita)

A1. O ator com `agendamentos.listar.qualquer` acessa a aba **"Solicitações / Lista"**.
A2. O sistema exibe **duas seções separadas**:
   - **Agendamentos Normais** (solicitações comuns)
   - **Agendamentos Fixos/Recorrentes**
A3. Cada seção exibe: solicitante, ambiente, tipo, data/horário, status (**RN06**).
A4. O ator pode filtrar por status, ambiente, período e tipo em cada seção.
A5. O ator clica num agendamento para ver o detalhe. As ações oferecidas dependem de permissões próprias e independentes (**RN08**): aprovar/rejeitar (`agendamentos.aprovar.qualquer`), editar (`agendamentos.editar.qualquer`), cancelar (`agendamentos.cancelar.qualquer`). Um ator pode, por exemplo, ter apenas `listar.qualquer` (vê a lista, mas não decide nada).

---

## Fluxos Alternativos e Exceções

### E01: Sem Agendamentos no Período
- Se não houver agendamentos no período/filtro selecionado:
  1. O sistema exibe o calendário/lista vazio com mensagem informativa.

### E02: Usuário sem Permissão para Lista Administrativa
- Se um usuário sem `agendamentos.listar.qualquer` tentar acessar a lista administrativa:
  1. O sistema não exibe a aba/seção e recusa o acesso.

### E03: Ator vê a Lista mas não tem Permissão de Ação
- **No passo A5:** Se o ator tem `agendamentos.listar.qualquer` mas não tem a permissão de uma ação específica (aprovar/editar/cancelar):
  1. O sistema exibe a lista e o detalhe normalmente, mas não oferece (ou desabilita) os botões das ações para as quais o ator não tem permissão (**RN08**).

---

## Detalhe do Agendamento (RN05)

Ao clicar num agendamento (calendário ou lista), o sistema exibe:

- **Solicitante:** nome do usuário ou docente que solicitou
- **Ambiente:** nome completo do ambiente
- **Tipo:** tipo do agendamento (com cor)
- **Motivo:** descrição informada na solicitação
- **Data:** data e dia da semana (ex: "01/10/2026 — Quinta-Feira")
- **Horário:** início e fim (ex: "15:30 – 16:00")
- **Status:** estado atual (Pendente / Aprovado / Rejeitado / Cancelado)
- **Contexto de série** (quando aplicável — **RN07**):
  - Indica que o agendamento pertence a uma série recorrente
  - Exibe: "Ocorrência X de Y da série [nome/identificador da série]"
  - Exibe as configurações originais da série (dias, horário, período)

---

## Regras de Negócio (RN)

- **RN01 - Período Padrão:** O calendário abre no dia atual como visão padrão.
- **RN02 - Cores por Tipo:** Cada agendamento é exibido com a cor configurada no seu tipo (UC-AGE-01). Agendamentos fixos/recorrentes recebem diferenciação visual adicional (ex: ícone ou borda) para distingui-los dos normais no calendário.
- **RN03 - Filtro de Período:** O usuário pode alternar entre as visões: dia, semana, mês e ano.
- **RN04 - Filtro por Ambiente:** O usuário pode filtrar para ver apenas os agendamentos de um ambiente específico.
- **RN05 - Detalhe do Agendamento:** Qualquer usuário autenticado pode visualizar o detalhe completo de qualquer agendamento.
- **RN06 - Separação na Lista Administrativa:** Na visão administrativa, agendamentos normais e fixos/recorrentes são exibidos em seções separadas para não misturá-los.
- **RN07 - Contexto de Série:** Agendamentos que pertencem a uma série recorrente (fixos) exibem no detalhe as informações da série: número da ocorrência, total de ocorrências, configuração original e identificador da série.
- **RN08 - Permissões Independentes por Ação:** Ver a lista administrativa, aprovar/rejeitar, editar e cancelar são permissões **independentes**: `agendamentos.listar.qualquer` (ver a lista), `agendamentos.aprovar.qualquer` (decidir), `agendamentos.editar.qualquer` (editar), `agendamentos.cancelar.qualquer` (cancelar). Um ator pode ter qualquer combinação delas — por exemplo, apenas visualizar a lista sem poder decidir. Cada botão/ação na lista é exibido somente para quem tem a permissão correspondente.
