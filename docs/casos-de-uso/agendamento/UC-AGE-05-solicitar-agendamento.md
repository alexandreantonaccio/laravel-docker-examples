# UC-AGE-05: Solicitar Agendamento

## Metadados
- **Módulo:** Agendamento
- **Dependências:**
  - `docs/referencias/permissoes-sistema.md` — permissão `agendamentos.solicitar.proprio` ou `agendamentos.solicitar.qualquer`.
  - UC-AGE-01 (Tipos de Agendamento) — tipo selecionado na solicitação.
  - UC-AGE-03 (Configurar Regras) — regras verificadas ao submeter.
  - UC-AMB-01 (Ambientes) — ambiente selecionado na solicitação (FK).
  - UC-GAM-01 (Grupos de Ambientes) — e-mails de notificação obtidos pelo grupo do ambiente.
  - `docs/referencias/notificacoes-sistema.md` — notificações N12.
- **UCs relacionados:**
  - UC-AGE-06 (Aprovar/Rejeitar) — passo seguinte após a solicitação.
  - UC-AGE-08 (Cancelar) — solicitante pode cancelar enquanto Pendente.
- **Status:** Rascunho

## Informações Gerais
- **Ator Principal:** Usuário autenticado com `agendamentos.solicitar.proprio` (para si) ou `agendamentos.solicitar.qualquer` (em nome de outro)
- **Atores Secundários:** Serviço de E-mail
- **Pré-condições:**
  - O ator está autenticado e possui a permissão de solicitação.
  - Existem tipos de agendamento e ambientes cadastrados e ativos.
  - As regras de agendamento estão configuradas (UC-AGE-03).
- **Pós-condições:** Agendamento criado com status **"Pendente"**; notificações N12 disparadas.

---

## Fluxo Principal

1. O ator acessa **"Solicitar Agendamento"** (disponível no calendário ou menu).
2. O sistema exibe o formulário de solicitação.
3. O ator preenche os campos obrigatórios (**RN01**):
   - **Solicitante:** o próprio ator (pré-preenchido) ou outro usuário, se possuir `agendamentos.solicitar.qualquer` (**RN02**)
   - **Ambiente:** selecionado da lista de ambientes ativos (FK — **RN03**)
   - **Tipo:** selecionado da lista de tipos ativos (FK)
   - **Motivo:** texto descritivo (obrigatório)
   - **Data:** data do agendamento
   - **Hora de início** e **Hora de fim**
4. O sistema realiza as validações (**RN04**–**RN07**) ao submeter.
5. O sistema salva o agendamento com status **"Pendente"**.
6. O sistema dispara as notificações (**RN08**):
   - E-mail ao solicitante confirmando a solicitação e informando que está pendente de aprovação (**N12a**).
   - E-mail(s) para os responsáveis do grupo do ambiente selecionado, se o ambiente pertencer a algum grupo (**N12b**).
7. O sistema exibe confirmação ao ator e o redireciona para o calendário.

---

## Fluxos Alternativos e Exceções

### E01: Período Bloqueado
- **No passo 4:** Se a data/horário selecionado estiver bloqueado (UC-AGE-03):
  1. O sistema recusa a submissão e informa o bloqueio (**RN04**).

### E02: Fora das Regras de Agendamento
- **No passo 4:** Se o dia da semana ou o horário não forem permitidos pelas regras do ambiente ou globais (UC-AGE-03):
  1. O sistema recusa e informa a restrição aplicável (**RN05**).

### E03: Conflito com Agendamento Aprovado
- **No passo 4:** Se já existir um agendamento **Aprovado** para o mesmo ambiente, data e horário (sobreposição parcial ou total):
  1. O sistema recusa a submissão e informa o conflito (**RN06**).
  2. Múltiplas solicitações **Pendentes** para o mesmo horário/ambiente são permitidas; o conflito só é verificado contra aprovados.

### E04: Hora de Fim Anterior ou Igual à de Início
- **No passo 4:** Se a hora de fim não for posterior à de início:
  1. O sistema recusa e informa o erro de horário (**RN07**).

### E05: Sem Ambiente Ativo Disponível
- **No passo 3:** Se não houver nenhum ambiente ativo cadastrado:
  1. O sistema informa que não há ambientes disponíveis para agendamento.

---

## Regras de Negócio (RN)

- **RN01 - Campos Obrigatórios:** Solicitante, Ambiente, Tipo, Motivo, Data, Hora de início e Hora de fim são todos obrigatórios.
- **RN02 - Solicitante:** Com `agendamentos.solicitar.proprio`, o solicitante é sempre o próprio ator (pré-preenchido, não editável). Com `agendamentos.solicitar.qualquer`, o ator pode selecionar outro usuário do sistema como solicitante.
- **RN03 - Ambiente por FK:** O ambiente é selecionado da lista de ativos e referenciado por chave estrangeira (não é cópia de texto — ver ADR-003).
- **RN04 - Bloqueios:** Data/horário bloqueados por regra de bloqueio (UC-AGE-03) rejeitam a solicitação.
- **RN05 - Regras de Agendamento:** A solicitação é validada contra as regras do ambiente específico (se houver) e, caso contrário, contra a regra global. Dias/horários fora das regras são rejeitados.
- **RN06 - Conflito com Aprovado:** Não é possível solicitar agendamento para horário e ambiente que já tenha agendamento **Aprovado** com sobreposição de horário. Múltiplas solicitações **Pendentes** para o mesmo slot são permitidas.
- **RN07 - Horário Válido:** A hora de fim deve ser posterior à hora de início.
- **RN08 - Notificações:** Ao criar uma solicitação, o sistema dispara: (a) e-mail ao solicitante confirmando o recebimento e status Pendente; (b) e-mail para os responsáveis do grupo do ambiente, se o ambiente pertencer a um grupo. Ambientes sem grupo não disparam o e-mail (b).
