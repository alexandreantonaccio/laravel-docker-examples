# UC-AGE-03: Configurar Regras de Agendamento

## Metadados
- **Módulo:** Agendamento
- **Dependências:**
  - `docs/referencias/permissoes-sistema.md` — permissões `agendamentos.configurar`.
  - UC-AMB-01 (Gerenciar Ambientes) — regras por ambiente referenciam ambientes cadastrados.
- **UCs relacionados:**
  - UC-AGE-05 (Solicitar Agendamento) — as regras são verificadas ao solicitar.
  - UC-AGE-07 (Agendamentos Fixos/Recorrentes) — as regras são verificadas ao criar recorrências.
- **Status:** Rascunho

## Informações Gerais
- **Ator Principal:** Administrador (ator com `agendamentos.configurar`)
- **Atores Secundários:** Nenhum
- **Pré-condições:** O ator está autenticado e possui a permissão de configuração de agendamentos.
- **Pós-condições:** Regras de agendamento atualizadas, passando a valer imediatamente para novas solicitações.

---

## Conceito e Hierarquia de Regras

As regras de agendamento determinam **quando** é permitido solicitar um
agendamento. Funcionam em três camadas, aplicadas nesta ordem de
precedência (mais específica prevalece):

```
1. REGRA GLOBAL     — padrão do sistema (dias/horários permitidos)
2. REGRA POR AMBIENTE — sobrepõe/restringe a global para um ambiente
3. BLOQUEIO         — sobrepõe tudo; bloqueia períodos específicos
```

> **Efeito prospectivo:** todas as regras e bloqueios se aplicam apenas a
> **novas** solicitações. Agendamentos já aprovados não são afetados por
> alterações de regras ou novos bloqueios.

---

## Fluxo Principal — Regra Global

1. O ator acessa **"Configurações de Agendamento"** → aba **"Regra Global"**.
2. O sistema exibe a configuração atual (dias da semana habilitados e faixas de horário permitidas).
3. O ator define:
   - **Dias da semana habilitados** (ex: segunda a sexta) (**RN01**)
   - **Faixas de horário permitidas** (ex: 07:00–12:00 e 13:00–22:00) (**RN02**)
4. O sistema persiste e passa a aplicar a regra global a todos os ambientes que não têm regra própria.

---

## Fluxo Alternativo — Regra por Ambiente

A1. O ator acessa **"Configurações de Agendamento"** → aba **"Por Ambiente"**.
A2. O ator seleciona um ambiente específico e define:
   - **Dias da semana habilitados** para aquele ambiente (**RN03**)
   - **Faixas de horário permitidas** para aquele ambiente (**RN03**)
A3. O sistema persiste. A regra do ambiente sobrepõe a regra global para ele (**RN04**).
A4. O ator pode remover a regra específica de um ambiente (voltando a usar a global) (**RN05**).

---

## Fluxo Alternativo — Bloqueios

B1. O ator acessa **"Configurações de Agendamento"** → aba **"Bloqueios"**.
B2. O sistema lista os bloqueios existentes (ativos e encerrados).
B3. O ator cria um novo bloqueio definindo (**RN06**):
   - **Tipo de bloqueio** (ver RN07)
   - **Escopo** (global ou ambiente específico — **RN08**)
   - **Motivo** (opcional, ex: "Feriado Nacional", "Recesso")
B4. O sistema valida e persiste o bloqueio, que passa a impedir novas solicitações no período bloqueado.

---

## Fluxos Alternativos e Exceções (Bloqueios)

### E01: Editar Bloqueio
- O ator seleciona um bloqueio existente e altera suas configurações.
  1. A mudança vale prospectivamente.

### E02: Remover Bloqueio
- O ator remove um bloqueio.
  1. O período volta a aceitar solicitações conforme as regras ativas.

### E03: Conflito com Agendamento Existente (informativo)
- Ao criar um bloqueio, se existirem agendamentos **aprovados** no período afetado:
  1. O sistema informa quais agendamentos já aprovados existem naquele período (**informativo apenas** — o bloqueio não os cancela, ver RN09).
  2. O ator decide se continua com o bloqueio sabendo do impacto.

---

## Regras de Negócio (RN)

- **RN01 - Dias Habilitados (Global):** A regra global define quais dias da semana aceitam solicitações de agendamento. Dias não habilitados globalmente rejeitam toda nova solicitação, exceto se houver regra específica por ambiente.
- **RN02 - Faixas de Horário (Global):** A regra global define as faixas de horário aceitas. Solicitações com horário fora das faixas são rejeitadas, exceto se houver regra específica por ambiente.
- **RN03 - Regra por Ambiente:** Cada ambiente pode ter sua própria configuração de dias e horários, definida independentemente da global. A regra do ambiente é sempre mais restritiva ou equivalente à global — ela restringe, não amplia além do que o ambiente em si precisa.
- **RN04 - Precedência:** A regra por ambiente prevalece sobre a global para aquele ambiente. Um bloqueio prevalece sobre tudo.
- **RN05 - Remover Regra por Ambiente:** Ao remover a regra específica de um ambiente, ele passa a seguir a regra global.
- **RN06 - Tipos de Bloqueio:** Um bloqueio pode ser de um dos seguintes tipos:
  - **Dia específico inteiro** (ex: 25/12/2026 bloqueado)
  - **Dia específico + faixa de horário** (ex: 25/12/2026 das 08:00 às 12:00)
  - **Dia da semana recorrente inteiro** (ex: todos os sábados)
  - **Dia da semana recorrente + faixa de horário** (ex: toda segunda das 07:00 às 08:00)
- **RN07 - Bloqueio é Prospectivo:** Bloqueios afetam apenas **novas** solicitações. Agendamentos já aprovados no período bloqueado permanecem válidos.
- **RN08 - Escopo do Bloqueio:** Um bloqueio pode ser **global** (afeta todos os ambientes) ou restrito a um **ambiente específico** (afeta apenas aquele).
- **RN09 - Aviso de Impacto (informativo):** Ao criar um bloqueio em período com agendamentos aprovados, o sistema informa o ator sobre esses agendamentos, sem cancelá-los automaticamente. O ator pode manualmente cancelar os agendamentos afetados se necessário (UC-AGE-08).
