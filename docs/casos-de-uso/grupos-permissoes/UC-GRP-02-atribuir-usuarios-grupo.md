# UC-GRP-02: Atribuir Usuários a um Grupo

## Metadados
- **Módulo:** Grupos de Permissões
- **Dependências:**
  - `docs/referencias/permissoes-sistema.md` — modelo de atribuição.
  - UC-GRP-01 (Gerenciar Grupos de Permissões) — os grupos precisam existir.
- **UCs relacionados:**
  - UC-USR-08 (Gerenciar Permissões Individuais do Usuário) — ajuste individual complementar.
  - UC-USR-07 (Listar e Pesquisar Usuários) — ponto de partida para selecionar usuários.
- **Status:** Rascunho

## Informações Gerais
- **Ator Principal:** Administrador (ator com `grupos_permissoes.atribuir_usuarios`)
- **Atores Secundários:** Nenhum
- **Pré-condições:** Existem grupos (UC-GRP-01) e usuários cadastrados.
- **Pós-condições:** Usuários associados/desassociados ao grupo; permissão efetiva recalculada.

---

## Fluxo Principal

1. O ator acessa um grupo e visualiza os usuários já associados (**RN01**).
2. O ator seleciona **um ou vários** usuários para associar ao grupo (atribuição em conjunto — **RN02**).
3. O ator confirma. O sistema associa os usuários ao grupo.
4. Cada usuário associado passa a herdar as permissões do grupo, somadas às que já possui de outros grupos e de ajustes individuais (**RN03**).

---

## Fluxos Alternativos e Exceções

### E01: Desassociar Usuário
- **No passo 2:** O ator remove um ou mais usuários do grupo.
  1. O sistema remove a associação.
  2. As permissões herdadas daquele grupo deixam de valer para esses usuários (mantendo as de outros grupos e as individuais — **RN03**).

### E02: Usuário em Vários Grupos
- Um usuário pode pertencer a mais de um grupo simultaneamente; sua permissão efetiva é a **união** das permissões de todos os grupos (**RN03**).

### E03: Associação na Aprovação do Cadastro
- Na aprovação de um novo cadastro (UC-USR-03), o Administrador escolhe manualmente o(s) grupo(s) do usuário. Não há associação automática a um grupo padrão (**RN04**).

---

## Regras de Negócio (RN)

- **RN01 - Visibilidade dos Membros:** O sistema exibe quais usuários pertencem a cada grupo.
- **RN02 - Atribuição em Conjunto:** É possível associar múltiplos usuários a um grupo em uma única operação.
- **RN03 - Permissão Efetiva:** A permissão efetiva de um usuário é a união das permissões de todos os seus grupos, mais as adições individuais e menos as negações individuais (ver `permissoes-sistema.md`).
- **RN04 - Associação na Aprovação:** Na aprovação de um cadastro (UC-USR-03), o Administrador escolhe manualmente o(s) grupo(s) do usuário; não há grupo padrão associado automaticamente.
