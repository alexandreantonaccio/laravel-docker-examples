# UC-GAM-01: Gerenciar Grupos de Ambientes

## Metadados
- **Módulo:** Grupos de Ambientes
- **Dependências:**
  - `docs/referencias/permissoes-sistema.md` — permissões `grupos_ambientes.*`.
  - UC-AMB-01 (Gerenciar Ambientes) — ambientes são associados a grupos aqui.
- **UCs relacionados:**
  - UC-AGE-05 (Solicitar Agendamento) — ao solicitar, o sistema usa os e-mails do grupo do ambiente para notificação.
  - UC-AGE-06 (Aprovar/Rejeitar) — idem.
  - UC-AGE-08 (Cancelar) — idem.
- **Status:** Rascunho

## Informações Gerais
- **Ator Principal:** Administrador (ator com `grupos_ambientes.criar`/`editar`/`desativar`/`visualizar`/`listar`)
- **Atores Secundários:** Nenhum
- **Pré-condições:** O ator está autenticado e possui as permissões de gestão de grupos de ambientes.
- **Pós-condições:** Grupo de ambientes criado/atualizado, com e-mails de notificação configurados e ambientes associados.

---

## Conceito

Um **Grupo de Ambientes** reúne um conjunto de ambientes sob a
responsabilidade de um mesmo grupo de pessoas (ex: técnicos de um bloco),
centralizando os **e-mails de notificação** dessas solicitações. Quando
uma solicitação de agendamento for feita para um ambiente pertencente a um
grupo, o sistema dispara notificações para todos os e-mails configurados
no grupo.

- Um ambiente pertence a **no máximo um grupo** (opcional — ambiente sem
  grupo recebe agendamentos normalmente, apenas sem notificação de e-mail).
- Um grupo pode ter **múltiplos e-mails** de notificação.
- Um grupo pode conter **múltiplos ambientes**.

---

## Fluxo Principal

1. O ator acessa a área de **"Grupos de Ambientes"**.
2. O sistema lista os grupos existentes, indicando nome, quantidade de ambientes e e-mails configurados.
3. O ator cria um novo grupo informando:
   - **Nome** do grupo (ex: "Bloco A — Labs 01, 02 e 03") (**RN01**)
   - **E-mails de notificação** (um ou mais) (**RN02**)
4. O sistema valida e persiste o grupo.
5. O ator associa ambientes ao grupo (via edição do grupo ou do próprio ambiente no UC-AMB-01) (**RN03**).

---

## Fluxos Alternativos e Exceções

### E01: Editar Grupo
- O ator seleciona um grupo existente e altera nome, e-mails ou ambientes associados.
  1. Mudanças nos e-mails passam a valer imediatamente para novas notificações.
  2. Remover um ambiente do grupo não afeta agendamentos existentes daquele ambiente.

### E02: Desativar Grupo
- O ator desativa um grupo.
  1. Os ambientes do grupo deixam de ter e-mails de notificação associados (ficam como "sem grupo").
  2. O sistema informa quantos ambientes serão afetados antes de confirmar (**RN04**).
  3. Agendamentos existentes não são afetados.

### E03: Nome Duplicado
- **No passo 4:** Se já existir um grupo com o mesmo nome:
  1. O sistema interrompe e informa a duplicidade (**RN01**).

### E04: E-mail Inválido
- **No passo 4:** Se algum e-mail informado não tiver formato válido:
  1. O sistema recusa e orienta o formato correto (**RN02**).

---

## Regras de Negócio (RN)

- **RN01 - Nome Único:** O nome do grupo é obrigatório e único.
- **RN02 - E-mails de Notificação:** O grupo deve ter ao menos um e-mail de notificação. Múltiplos e-mails são permitidos; todos receberão as notificações de agendamento dos ambientes do grupo.
- **RN03 - Um Grupo por Ambiente:** Cada ambiente pertence a no máximo um grupo. Ao associar um ambiente a um grupo, o sistema verifica se ele já pertence a outro; se pertencer, informa o conflito antes de prosseguir.
- **RN04 - Aviso de Impacto na Desativação:** Antes de desativar um grupo, o sistema informa quantos ambientes ficarão sem grupo de notificação.
- **RN05 - Ambiente Sem Grupo:** Ambientes não associados a nenhum grupo podem ser agendados normalmente; simplesmente não disparam notificações de e-mail.
