# UC-AGE-08: Cancelar / Editar Agendamento

## Metadados
- **Módulo:** Agendamento
- **Dependências:**
  - `docs/referencias/permissoes-sistema.md` — permissões `agendamentos.cancelar.proprio`, `agendamentos.cancelar.qualquer`, `agendamentos.editar.proprio`, `agendamentos.editar.qualquer`.
  - `docs/referencias/notificacoes-sistema.md` — notificação N15.
  - UC-AGE-03 (Configurar Regras) — regras verificadas ao editar horário/ambiente.
- **UCs relacionados:**
  - UC-AGE-04 (Visualizar Agenda) — ponto de partida para acessar um agendamento.
  - UC-AGE-06 (Aprovar/Rejeitar) — o ator pode cancelar em vez de rejeitar.
  - UC-AGE-07 (Agendamentos Fixos) — série pode ser cancelada/editada aqui.
- **Status:** Rascunho

## Informações Gerais
- **Ator Principal:** Solicitante (cancelamento próprio) ou Administrador (cancelamento/edição de qualquer agendamento)
- **Atores Secundários:** Serviço de E-mail
- **Pré-condições:** O ator está autenticado. O agendamento existe.
- **Pós-condições:** Agendamento cancelado (status "Cancelado") ou dados atualizados, conforme a ação realizada.

---

## Conceito — Escopo da Operação

Para agendamentos pertencentes a uma série (fixos/recorrentes), as
operações de cancelamento e edição podem ser aplicadas em dois escopos:

- **Ocorrência específica:** afeta apenas aquela data individual.
- **Série inteira (esta e as seguintes):** afeta a ocorrência atual e
  todas as futuras da série.

---

## Fluxo Principal — Cancelar (Solicitante)

1. O solicitante acessa o detalhe do próprio agendamento (UC-AGE-04).
2. O sistema verifica que o agendamento está **Pendente** e pertence ao solicitante (**RN01**).
3. O solicitante clica em **"Cancelar"** e informa o **motivo** (obrigatório — **RN02**).
4. O sistema altera o status para **"Cancelado"** e registra o motivo.
5. O sistema notifica os responsáveis do grupo do ambiente, se houver (**N15**).

---

## Fluxo Alternativo — Cancelar pelo Administrador

A1. O ator com `agendamentos.cancelar.qualquer` acessa o detalhe de qualquer agendamento.
A2. O agendamento pode estar em qualquer status (**RN03**).
A3. Se pertencer a uma série, o sistema pergunta o escopo: **"Apenas esta ocorrência"** ou **"Esta e todas as seguintes"** (**RN04**).
A4. O ator informa o **motivo** (obrigatório — **RN02**).
A5. O sistema cancela a(s) ocorrência(s) selecionada(s), registra o motivo e notifica (**N15**).

---

## Fluxo Alternativo — Editar Agendamento

B1. O ator com `agendamentos.editar.proprio` (próprio) ou `agendamentos.editar.qualquer` (qualquer) acessa o detalhe.
B2. O ator clica em **"Editar"**.
B3. Se pertencer a uma série, o sistema pergunta o escopo: **"Apenas esta ocorrência"** ou **"Esta e todas as seguintes"** (**RN04**).
B4. O ator altera os campos desejados (ambiente, tipo, motivo, horário etc.) (**RN05**).
B5. O sistema revalida regras (bloqueios, horários, conflito com aprovados) para os campos alterados (**RN06**).
B6. O sistema persiste as alterações.

---

## Fluxos Alternativos e Exceções

### E01: Cancelamento Não Permitido (Solicitante)
- **No fluxo principal, passo 2:** Se o agendamento não estiver Pendente ou não pertencer ao solicitante:
  1. O sistema recusa e informa o motivo (**RN01**).

### E02: Conflito ao Editar Horário/Ambiente
- **No fluxo B, passo B5:** Se a nova combinação de horário/ambiente conflitar com agendamento Aprovado:
  1. O sistema recusa e informa o conflito (**RN06**).

### E03: Editar Ocorrência de Série — Desvinculamento
- **No fluxo B, passo B3:** Ao editar apenas uma ocorrência de uma série:
  1. Essa ocorrência é **desvinculada da série** para aquele campo editado — mantém o vínculo para exibir o contexto de série, mas passa a ter configurações independentes (**RN07**).

---

## Regras de Negócio (RN)

- **RN01 - Cancelamento pelo Solicitante:** O solicitante só pode cancelar o próprio agendamento enquanto estiver com status **"Pendente"**. Agendamentos Aprovados, Rejeitados ou já Cancelados não podem ser cancelados pelo solicitante.
- **RN02 - Motivo Obrigatório:** O motivo é obrigatório tanto no cancelamento (solicitante e admin) quanto na edição pelo admin.
- **RN03 - Cancelamento pelo Administrador:** O administrador (com `agendamentos.cancelar.qualquer`) pode cancelar qualquer agendamento, em qualquer status (exceto já Cancelado).
- **RN04 - Escopo em Série:** Para agendamentos pertencentes a uma série, as operações de cancelamento e edição oferecem dois escopos: (a) apenas a ocorrência selecionada; (b) esta ocorrência e todas as futuras da série. O ator escolhe antes de confirmar.
- **RN05 - Campos Editáveis:** O ator pode editar: ambiente, tipo, motivo, data, hora de início, hora de fim. O solicitante e o vínculo com a série não são editáveis por este fluxo.
- **RN06 - Revalidação na Edição:** Ao alterar horário, data ou ambiente, o sistema revalida regras de agendamento (UC-AGE-03) e conflito com aprovados existentes.
- **RN07 - Desvinculamento de Ocorrência:** Editar apenas uma ocorrência de uma série não altera as demais ocorrências. A ocorrência editada mantém o vínculo com a série para fins de exibição de contexto, mas suas configurações passam a ser independentes.
- **RN08 - Notificação de Cancelamento:** O cancelamento dispara notificação N15 para os responsáveis do grupo do ambiente, se houver.
