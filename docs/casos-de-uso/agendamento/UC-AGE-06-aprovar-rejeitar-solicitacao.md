# UC-AGE-06: Aprovar / Rejeitar Solicitação de Agendamento

## Metadados
- **Módulo:** Agendamento
- **Dependências:**
  - `docs/referencias/permissoes-sistema.md` — permissão `agendamentos.aprovar.qualquer`.
  - `docs/referencias/notificacoes-sistema.md` — notificações N13 e N14.
- **UCs relacionados:**
  - UC-AGE-04 (Visualizar Agenda) — ponto de partida para acessar as solicitações.
  - UC-AGE-05 (Solicitar Agendamento) — origina as solicitações analisadas aqui.
  - UC-AGE-08 (Cancelar/Editar) — o ator pode cancelar em vez de rejeitar.
- **Status:** Rascunho

## Informações Gerais
- **Ator Principal:** Administrador (ator com `agendamentos.aprovar.qualquer`)
- **Atores Secundários:** Serviço de E-mail
- **Pré-condições:**
  - O ator está autenticado e possui `agendamentos.aprovar.qualquer`.
  - Existe ao menos uma solicitação no status **"Pendente"**.
- **Pós-condições:** Solicitação no status **"Aprovado"** ou **"Rejeitado"**; solicitante notificado.

---

## Fluxo Principal — Aprovar

1. O ator acessa a lista administrativa (UC-AGE-04, visão restrita) e visualiza as solicitações Pendentes.
2. O ator seleciona uma solicitação e visualiza o detalhe completo (solicitante, ambiente, tipo, motivo, data, horário).
3. O ator clica em **"Aprovar"**.
4. O sistema valida que não existe agendamento Aprovado em conflito de horário/ambiente (**RN01**).
5. O sistema altera o status para **"Aprovado"**.
6. O sistema notifica o solicitante por e-mail informando a aprovação (**N13**).

---

## Fluxo Alternativo — Rejeitar

A1. O ator seleciona uma solicitação Pendente e clica em **"Rejeitar"**.
A2. O sistema exige que o ator informe o **motivo da rejeição** (obrigatório — **RN02**).
A3. O sistema altera o status para **"Rejeitado"** e registra o motivo.
A4. O sistema notifica o solicitante por e-mail com a informação da rejeição e o motivo (**N14**).

---

## Fluxos Alternativos e Exceções

### E01: Conflito com Aprovado ao Aprovar
- **No passo 4:** Se, entre o momento da solicitação e o momento da aprovação, outro agendamento para o mesmo ambiente/horário já tiver sido aprovado:
  1. O sistema impede a aprovação e informa o conflito (**RN01**).
  2. O ator pode rejeitar a solicitação atual ou cancelar o agendamento aprovado conflitante (UC-AGE-08) antes de prosseguir.

### E02: Solicitação Já Decidida
- Se a solicitação não estiver mais Pendente (já foi aprovada, rejeitada ou cancelada):
  1. O sistema informa que a solicitação não está mais disponível para decisão.

---

## Regras de Negócio (RN)

- **RN01 - Revalidação de Conflito:** No momento da aprovação, o sistema revalida se existe agendamento Aprovado em conflito de horário/ambiente. Isso é necessário pois múltiplas solicitações Pendentes para o mesmo slot são permitidas — apenas uma pode ser aprovada.
- **RN02 - Motivo Obrigatório na Rejeição:** A rejeição exige motivo, registrado permanentemente para referência do solicitante e auditoria.
- **RN03 - Notificação ao Solicitante:** Tanto aprovação (N13) quanto rejeição (N14) geram notificação por e-mail ao solicitante.
- **RN04 - Apenas Pendentes:** Só é possível aprovar ou rejeitar solicitações no status Pendente.
