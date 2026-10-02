# UC-GRP-01: Gerenciar Grupos de Permissões

## Metadados
- **Módulo:** Grupos de Permissões
- **Dependências:**
  - `docs/referencias/permissoes-sistema.md` — catálogo de permissões e modelo de atribuição.
- **UCs relacionados:**
  - UC-GRP-02 (Atribuir Usuários a um Grupo) — associa usuários aos grupos aqui definidos.
  - UC-USR-03 (Aprovação/Rejeição de Cadastro) — na aprovação, o Administrador associa o usuário a grupos criados aqui.
- **Status:** Rascunho

## Informações Gerais
- **Ator Principal:** Administrador (ator com `grupos_permissoes.criar`/`editar`/`excluir`)
- **Atores Secundários:** Nenhum
- **Pré-condições:** O ator está autenticado e possui as permissões de gestão de grupos.
- **Pós-condições:** Grupo criado/alterado/removido, com seu conjunto de permissões definido.

---

## Fluxo Principal

1. O ator acessa a área de **"Grupos de Permissões"**.
2. O sistema lista os grupos existentes (todos criados manualmente pelo Administrador).
3. O ator cria um novo grupo, informando **nome** e **descrição** (**RN01**).
4. O ator seleciona, a partir do catálogo de permissões, as permissões que o grupo concede (**RN02**).
5. O ator salva. O sistema valida a unicidade do nome e persiste o grupo com suas permissões.
6. As permissões passam a valer para todos os usuários associados ao grupo (**RN03**).

---

## Fluxos Alternativos e Exceções

### E01: Editar Grupo Existente
- **No passo 3:** O ator seleciona um grupo existente e altera nome, descrição ou o conjunto de permissões. As mudanças passam a valer para todos os usuários do grupo imediatamente (**RN03**).

### E02: Excluir Grupo
- O ator solicita a exclusão de um grupo.
  1. O sistema alerta quantos usuários serão afetados antes de confirmar (**RN04**).
  2. Após a exclusão, os usuários que pertenciam ao grupo perdem as permissões herdadas dele (mantendo as de outros grupos e os ajustes individuais).

### E03: Nome de Grupo Duplicado
- **No passo 5:** Se já existir um grupo com o mesmo nome, o sistema interrompe e solicita outro nome (**RN01**).

---

## Regras de Negócio (RN)

- **RN01 - Nome Único:** O nome do grupo é obrigatório e único.
- **RN02 - Permissões a partir do Catálogo:** As permissões atribuíveis a um grupo são as definidas em `permissoes-sistema.md`, respeitando o escopo (`.proprio`/`.qualquer`) quando aplicável.
- **RN03 - Herança Imediata:** Alterações no conjunto de permissões de um grupo refletem imediatamente na permissão efetiva de todos os usuários associados.
- **RN04 - Aviso de Impacto na Exclusão:** Antes de excluir um grupo, o sistema informa quantos usuários perderão as permissões herdadas dele. Não há grupos "de sistema": todos os grupos são criados manualmente e podem ser editados/excluídos.
