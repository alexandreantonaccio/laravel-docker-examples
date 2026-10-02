# UC-AGE-02: Gerenciar Docentes

## Metadados
- **Módulo:** Agendamento
- **Dependências:**
  - `docs/referencias/permissoes-sistema.md` — permissões `agendamentos.docente.*`.
- **UCs relacionados:**
  - UC-AGE-07 (Agendamentos Fixos/Recorrentes) — o solicitante de um agendamento fixo pode ser um docente desta lista.
- **Status:** Rascunho

## Informações Gerais
- **Ator Principal:** Administrador (ator com `agendamentos.docente.criar`/`editar`/`desativar`)
- **Atores Secundários:** Nenhum
- **Pré-condições:** O ator está autenticado e possui as permissões de gestão de docentes.
- **Pós-condições:** Lista de docentes atualizada, disponível para seleção nos agendamentos fixos.

---

## Conceito

A lista de **Docentes** é um cadastro simples de nomes de professores
usados como **referência de solicitante** nos agendamentos fixos
(UC-AGE-07). Serve para os casos em que o professor não possui acesso ao
sistema ou não realizou cadastro — o administrador registra o nome do
docente para identificação nos agendamentos.

> **Distinção importante:** "Docente" aqui é apenas um nome de referência
> (ex: "Prof. Ricardo Melo"), sem vínculo com a conta de usuário do
> sistema. Quando o solicitante de um agendamento fixo for um usuário
> cadastrado, ele é selecionado diretamente pelo sistema de usuários, não
> desta lista.

---

## Fluxo Principal

1. O ator acessa a área de **"Docentes"**.
2. O sistema lista os docentes cadastrados, indicando nome e status.
3. O ator cria um novo docente informando o **nome** (ex: "Prof. Ricardo Melo") (**RN01**).
4. O sistema valida e persiste o docente como **ativo**.
5. O docente passa a estar disponível para seleção nos agendamentos fixos.

---

## Fluxos Alternativos e Exceções

### E01: Editar Docente
- O ator seleciona um docente e altera o nome.
  1. O sistema persiste. Agendamentos existentes com esse docente refletem o novo nome (**RN02** — FK).

### E02: Desativar Docente
- O ator desativa um docente.
  1. O docente deixa de aparecer nas opções de novos agendamentos fixos.
  2. Agendamentos existentes com esse docente **não** são afetados.

### E03: Reativar Docente
- O ator reativa um docente desativado.

### E04: Nome Duplicado
- Se já existir um docente com o mesmo nome:
  1. O sistema informa a duplicidade (**RN01**).

---

## Regras de Negócio (RN)

- **RN01 - Nome Obrigatório e Único:** O nome do docente é obrigatório e único na lista.
- **RN02 - Referenciado por FK:** Diferente das listas desacopladas do sistema, o Docente é referenciado por chave estrangeira nos agendamentos fixos. Editar o nome do docente reflete nos agendamentos associados. Isso é intencional: o nome é uma referência de identificação, não uma cópia histórica.
- **RN03 - Sem Exclusão:** O sistema não exclui docentes (preserva histórico); a medida disponível é a desativação.
