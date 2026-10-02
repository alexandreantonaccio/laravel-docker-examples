# UC-AGE-07: Gerenciar Agendamentos Fixos / Recorrentes

## Metadados
- **Módulo:** Agendamento
- **Dependências:**
  - `docs/referencias/permissoes-sistema.md` — permissão `agendamentos.fixo.criar`.
  - UC-AGE-01 (Tipos de Agendamento) — tipo selecionado na criação.
  - UC-AGE-02 (Docentes) — docente selecionado como solicitante (opcional).
  - UC-AGE-03 (Configurar Regras) — regras verificadas na geração das ocorrências.
  - UC-AMB-01 (Ambientes) — ambiente selecionado (FK).
  - UC-GAM-01 (Grupos de Ambientes) — e-mails de notificação do grupo do ambiente.
  - `docs/referencias/notificacoes-sistema.md` — notificações N12.
- **UCs relacionados:**
  - UC-AGE-04 (Visualizar Agenda) — série exibida no calendário e na lista administrativa.
  - UC-AGE-08 (Cancelar/Editar) — edição e cancelamento de ocorrências ou da série inteira.
- **Status:** Rascunho

## Informações Gerais
- **Ator Principal:** Administrador ou usuário com `agendamentos.fixo.criar`
- **Atores Secundários:** Serviço de E-mail
- **Pré-condições:**
  - O ator está autenticado e possui `agendamentos.fixo.criar`.
  - Existem ambientes, tipos de agendamento ativos e regras configuradas.
- **Pós-condições:** Série criada e todas as ocorrências geradas com status **"Aprovado"**; notificações N12 disparadas.

---

## Conceito — Série e Ocorrências

Um agendamento fixo/recorrente é composto de:

- **Série:** o registro-mãe que define as configurações da recorrência
  (dias da semana, horário, período, solicitante, ambiente, tipo, motivo).
- **Ocorrências:** os registros individuais gerados a partir da série,
  cada um correspondendo a um dia específico de realização.

Cada ocorrência é um agendamento independente (com sua própria data e
status), mas mantém **vínculo por FK com a série** de origem — permitindo
exibir o contexto de série no detalhe (UC-AGE-04, RN07) e operar sobre
toda a série ou ocorrência específica (UC-AGE-08).

**Agendamentos fixos nascem com status "Aprovado"** — não passam pelo
fluxo de solicitação/aprovação, pois são criados diretamente por quem tem
a permissão `agendamentos.fixo.criar`.

---

## Fluxo Principal — Criar Série

1. O ator acessa **"Agendamentos Fixos"** → **"Nova Série"**.
2. O ator preenche as configurações da série (**RN01**):
   - **Nome/identificador da série** (ex: "Circuitos Elétricos I — 2026.2")
   - **Solicitante:** usuário do sistema (seleção) OU docente da lista (UC-AGE-02) (**RN02**)
   - **Ambiente** (FK — UC-AMB-01)
   - **Tipo** (FK — UC-AGE-01)
   - **Motivo/Descrição**
   - **Dias da semana de recorrência** (ex: segunda e quarta)
   - **Hora de início** e **Hora de fim**
   - **Data de início** e **Data de fim** do período
3. O sistema calcula todas as ocorrências no período definido (combinando dias da semana com o intervalo de datas) (**RN03**).
4. O sistema verifica, para cada ocorrência gerada, se o dia/horário está bloqueado ou fora das regras (**RN04**).
5. O sistema exibe um resumo das ocorrências a serem criadas, destacando quais foram **bloqueadas** (não serão criadas) (**RN05**).
6. O ator confirma.
7. O sistema cria a série e todas as ocorrências válidas com status **"Aprovado"**.
8. O sistema notifica os responsáveis do grupo do ambiente (se houver) sobre a criação da série (**N12b** — uma única notificação resumida, não uma por ocorrência).

---

## Fluxos Alternativos e Exceções

### E01: Conflito com Agendamento Aprovado
- **No passo 4:** Se alguma ocorrência conflitar com agendamento Aprovado já existente no mesmo ambiente/horário:
  1. O sistema destaca as ocorrências em conflito no resumo do passo 5 (**RN06**).
  2. O ator pode ajustar o período/horário ou confirmar aceitando que as ocorrências em conflito serão ignoradas na criação.

### E02: Todas as Ocorrências Bloqueadas ou em Conflito
- Se o período/horário configurado não gerar nenhuma ocorrência válida:
  1. O sistema informa que não foi possível gerar ocorrências e solicita ajuste das configurações.

### E03: Data de Fim Anterior à de Início
- Se a data de fim for anterior ou igual à data de início:
  1. O sistema recusa e informa o erro (**RN01**).

---

## Regras de Negócio (RN)

- **RN01 - Campos Obrigatórios da Série:** Nome, Solicitante, Ambiente, Tipo, Motivo, dias de recorrência, hora de início, hora de fim, data de início e data de fim são obrigatórios. A data de fim deve ser posterior à de início.
- **RN02 - Solicitante Flexível:** O solicitante de um agendamento fixo pode ser (a) um usuário do sistema selecionado pelo ator, ou (b) um docente da lista de docentes (UC-AGE-02), para os casos em que o professor não possui conta no sistema.
- **RN03 - Geração de Ocorrências:** O sistema gera automaticamente uma ocorrência para cada combinação de (dia da semana selecionado × data dentro do período) que se encaixar na configuração.
- **RN04 - Verificação de Bloqueios e Regras:** Cada ocorrência é verificada contra bloqueios (UC-AGE-03) e regras de agendamento. Ocorrências que caem em dia/horário bloqueado ou fora das regras são marcadas como não-geráveis e exibidas no resumo.
- **RN05 - Resumo Antes de Confirmar:** Antes de criar, o sistema exibe o resumo das ocorrências válidas e das bloqueadas/em-conflito, para o ator ter ciência e decidir.
- **RN06 - Conflito com Aprovado:** Ocorrências que conflitam com agendamento Aprovado existente são destacadas no resumo. O ator pode optar por não criar essas ocorrências específicas.
- **RN07 - Status das Ocorrências:** Todas as ocorrências válidas nascem com status **"Aprovado"**, sem passar pelo fluxo de aprovação (UC-AGE-06).
- **RN08 - Notificação Resumida:** A criação de uma série dispara uma única notificação para os responsáveis do grupo do ambiente (não uma por ocorrência), informando os dados gerais da série.
