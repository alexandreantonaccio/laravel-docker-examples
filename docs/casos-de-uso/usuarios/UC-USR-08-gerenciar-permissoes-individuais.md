# UC-USR-08: Gerenciar Permissões Individuais do Usuário

## Metadados
- **Módulo:** Usuários
- **Dependências:**
  - `docs/referencias/permissoes-sistema.md` — catálogo e modelo de atribuição (ajuste individual de adição e remoção; negação individual vence).
  - UC-GRP-02 (Atribuir Usuários a um Grupo) — a base de permissões vem dos grupos; este UC complementa e pode revogar pontualmente.
- **UCs relacionados:**
  - UC-USR-07 (Listar e Pesquisar Usuários) — ponto de partida para localizar o usuário.
- **Status:** Rascunho

## Informações Gerais
- **Ator Principal:** Administrador (ator com `usuarios.gerenciar_permissoes`)
- **Atores Secundários:** Nenhum
- **Pré-condições:** O usuário-alvo existe. O ator possui a permissão de gerenciar permissões.
- **Pós-condições:** Ajustes individuais (adições e/ou negações) aplicados; permissão efetiva recalculada.

---

## Fluxo Principal

1. O ator localiza e seleciona um usuário (via UC-USR-07).
2. O sistema exibe a **permissão efetiva** do usuário, distinguindo claramente a origem de cada item (**RN01**):
   - herdada **de grupo** (e de qual grupo);
   - **adicionada** individualmente;
   - **negada** individualmente (herdada de grupo, porém revogada para este usuário).
3. O ator aplica ajustes individuais a partir do catálogo (**RN02**):
   - **adicionar** uma permissão extra que o usuário não possui; ou
   - **negar** uma permissão que o usuário herda de um grupo, revogando-a apenas para ele.
4. O ator salva. O sistema recalcula a permissão efetiva aplicando a regra de precedência (**RN03**).

---

## Fluxos Alternativos e Exceções

### E01: Desfazer um Ajuste Individual
- **No passo 3:** O ator remove um ajuste individual previamente feito (uma adição ou uma negação).
  1. Ao desfazer uma **adição**, a permissão extra deixa de valer (mantendo o que vier de grupo).
  2. Ao desfazer uma **negação**, a permissão volta a valer se algum grupo do usuário a conceder (**RN04**).

### E02: Adicionar Permissão Já Herdada de Grupo
- **No passo 3:** Se o ator tentar **adicionar** individualmente uma permissão que o usuário já herda de um grupo:
  1. O sistema informa que a permissão já é concedida via grupo e evita duplicidade. (Para retirá-la deste usuário, use a **negação individual**, não a remoção da adição.)

### E03: Conflito Grupo Concede × Indivíduo Nega
- Se um grupo do usuário concede uma permissão e existe uma negação individual para a mesma permissão:
  1. Prevalece a **negação individual** — a permissão **não** vale para o usuário (**RN03**).

---

## Regras de Negócio (RN)

- **RN01 - Origem Transparente:** Para cada permissão exibida, o sistema deixa claro se ela vem de grupo (qual), de adição individual, ou se está negada individualmente.
- **RN02 - Ajuste Individual (Adição e Remoção):** O ajuste individual pode **conceder** permissões extras e também **negar** (revogar) permissões herdadas de grupos, aplicando-se apenas ao usuário em questão (ver `permissoes-sistema.md`).
- **RN03 - Precedência (Negação Vence):** No cálculo da permissão efetiva, a negação individual prevalece sobre a concessão de grupo. Permissão efetiva = (união das permissões dos grupos) + (adições individuais) − (negações individuais).
- **RN04 - Reversibilidade:** Desfazer uma negação individual restabelece a permissão caso ela ainda seja concedida por algum grupo; desfazer uma adição remove apenas a permissão extra.
- **RN05 - Proteção do SuperAdmin:** As permissões da conta **SuperAdmin** não podem ser alteradas por nenhum outro usuário, independentemente das permissões (ver ADR-004 em `docs/referencias/decisoes-tecnicas.md`). O sistema recusa qualquer ajuste de permissões que tenha o SuperAdmin como alvo, exceto pelo próprio SuperAdmin.
