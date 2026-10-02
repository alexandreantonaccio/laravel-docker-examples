# UC-AGE-01: Gerenciar Tipos de Agendamento

## Metadados
- **Módulo:** Agendamento
- **Dependências:**
  - `docs/referencias/permissoes-sistema.md` — permissões `agendamentos.*`.
- **UCs relacionados:**
  - UC-AGE-05 (Solicitar Agendamento) — o tipo é informado na solicitação.
  - UC-AGE-07 (Agendamentos Fixos/Recorrentes) — idem.
  - UC-AGE-04 (Visualizar Agenda) — a cor do tipo é usada no calendário.
- **Status:** Rascunho

## Informações Gerais
- **Ator Principal:** Administrador (ator com `agendamentos.tipo.criar`/`editar`/`desativar`)
- **Atores Secundários:** Nenhum
- **Pré-condições:** O ator está autenticado e possui as permissões de gestão de tipos.
- **Pós-condições:** Lista de tipos de agendamento atualizada, refletindo imediatamente no calendário e nos formulários de solicitação.

---

## Conceito

Tipos de agendamento categorizam as solicitações (ex: Aula, Experimento,
Palestra, Evento, Monitoria) e definem a **cor** de exibição no
calendário. Cada agendamento deve ter um tipo, e o calendário exibe os
agendamentos com a cor do respectivo tipo para facilitar a identificação
visual.

---

## Fluxo Principal

1. O ator acessa a área de **"Tipos de Agendamento"**.
2. O sistema lista os tipos cadastrados, indicando nome, cor e status (ativo/desativado).
3. O ator cria um novo tipo informando:
   - **Nome** (ex: "Aula", "Experimento") (**RN01**)
   - **Cor** (selecionada em um seletor de cor — **RN02**)
4. O sistema valida e persiste o tipo como **ativo**.
5. O tipo passa a estar disponível nos formulários de solicitação de agendamento e sua cor é aplicada no calendário.

---

## Fluxos Alternativos e Exceções

### E01: Editar Tipo
- O ator seleciona um tipo existente e altera nome ou cor.
  1. A mudança de cor reflete imediatamente no calendário para todos os agendamentos daquele tipo (**RN03**).

### E02: Desativar Tipo
- O ator desativa um tipo.
  1. O tipo deixa de aparecer nas opções de novos agendamentos.
  2. Agendamentos existentes com esse tipo **não** são afetados (**RN04**).

### E03: Reativar Tipo
- O ator reativa um tipo desativado, que volta a aparecer nos formulários.

### E04: Nome Duplicado
- Se já existir um tipo com o mesmo nome:
  1. O sistema interrompe e informa a duplicidade (**RN01**).

---

## Regras de Negócio (RN)

- **RN01 - Nome Único:** O nome do tipo é obrigatório e único.
- **RN02 - Cor Obrigatória:** Cada tipo deve ter uma cor configurada (usada no calendário). A cor é definida pelo ator no momento da criação/edição.
- **RN03 - Cor Reflete Imediatamente:** Alterar a cor de um tipo atualiza imediatamente a exibição de todos os agendamentos daquele tipo no calendário.
- **RN04 - Desativação Não Afeta Existentes:** Desativar um tipo o remove das opções de novos agendamentos, mas não altera agendamentos já criados com ele.
- **RN05 - Sem Exclusão:** O sistema não exclui tipos (preserva histórico); a medida disponível é a desativação.
