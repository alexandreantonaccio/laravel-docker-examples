# UC-CAR-01: Gerenciar Cargos

## Metadados
- **Módulo:** Cargos
- **Dependências:**
  - `docs/referencias/permissoes-sistema.md` — permissões `cargos.*`.
- **UCs relacionados:**
  - UC-USR-01 (Cadastro de Usuário) — Técnico e Professor têm o campo Cargo, escolhido da lista de cargos ativos (RN13 do UC-USR-01).
  - UC-USR-11 (Editar Usuário) — a edição de cargo de um usuário pelo Administrador usa a lista de cargos ativos.
- **Status:** Rascunho

## Informações Gerais
- **Ator Principal:** Administrador (ator com `cargos.criar`/`editar`/`desativar`/`visualizar`/`listar`)
- **Atores Secundários:** Nenhum
- **Pré-condições:** O ator está autenticado e possui as permissões de gestão de cargos.
- **Pós-condições:** Lista de cargos atualizada (cargo criado, editado, ativado/desativado), refletindo imediatamente na lista de cargos disponíveis nos fluxos de cadastro/edição de usuário.

---

## Conceito

Cargos são usados no cadastro dos perfis **Técnico** e **Professor**
(UC-USR-01, RN13). No cadastro, o usuário seleciona o cargo de uma lista
de cargos **ativos**. Para o perfil **Professor**, o cargo é
pré-preenchido automaticamente como **"Professor do Magistério Superior"**.

> **Desacoplamento (consistente com Domínios de E-mail):** ao salvar o
> cadastro/edição, o sistema grava no usuário o **texto do cargo**
> escolhido (cópia), sem manter chave estrangeira para o registro do
> cargo. Editar ou desativar um cargo **não** altera o cargo de usuários
> já cadastrados (ver **RN06**).

O sistema já nasce (na inicialização) com o cargo **"Professor do
Magistério Superior"** cadastrado e ativo, pois o cadastro de Professor
depende dele (**RN07**).

---

## Fluxo Principal

1. O ator acessa a área de **"Cargos"**.
2. O sistema lista os cargos cadastrados, indicando quais estão **ativos** e **desativados**, com filtro por status (todos / ativos / desativados) e a **quantidade de usuários** que usam cada cargo (**RN05**).
3. O ator cria um novo cargo informando o **nome** (**RN01**).
4. O sistema valida o nome e a unicidade (**RN01**, **RN02**) e persiste o cargo como **ativo**.
5. A partir daí, o cargo ativo passa a aparecer na lista de cargos disponíveis no cadastro (UC-USR-01) e edição (UC-USR-11) (**RN04**).

---

## Fluxos Alternativos e Exceções

### E01: Editar Cargo
- **No passo 3:** O ator seleciona um cargo existente e altera o nome.
  1. O sistema revalida nome e unicidade (**RN01**, **RN02**) e persiste.
  2. A mudança vale para **novas** seleções; usuários já cadastrados com o cargo **não** são afetados (**RN06**).

### E02: Desativar Cargo
- O ator desativa um cargo.
  1. O sistema marca o cargo como **desativado**; ele deixa de aparecer na lista de seleção do cadastro/edição (**RN03**).
  2. Usuários já cadastrados com esse cargo **permanecem inalterados** (**RN06**).
  3. O sistema informa quantos usuários usam esse cargo, para ciência do ator (**RN05**).

### E03: Reativar Cargo
- O ator reativa um cargo desativado, que volta a aparecer na lista de seleção.

### E04: Nome Inválido
- **No passo 4:** Se o nome for vazio ou fora do formato esperado:
  1. O sistema recusa e orienta o preenchimento correto (**RN01**).

### E05: Cargo Duplicado
- **No passo 4:** Se já existir um cargo com o mesmo nome:
  1. O sistema interrompe e informa a duplicidade (**RN02**).

---

## Regras de Negócio (RN)

- **RN01 - Nome do Cargo:** O nome do cargo é obrigatório e segue o padrão de nomenclatura adotado (ex: caixa alta, conforme convenção do módulo). Ex: "PROFESSOR DO MAGISTÉRIO SUPERIOR", "TÉCNICO DE LABORATÓRIO".
- **RN02 - Unicidade:** Não pode haver dois cargos com o mesmo nome.
- **RN03 - Desativação:** Desativar um cargo o remove da lista de seleção de novos cadastros/edições, mas não o exclui. O sistema não exclui cargos (preserva histórico); a medida disponível é a desativação.
- **RN04 - Efeito Imediato:** Alterações na lista (criação, edição, ativação/desativação) refletem imediatamente na lista de cargos disponíveis nos fluxos de cadastro/edição.
- **RN05 - Filtro e Contagem de Uso:** A listagem permite filtrar por status (todos / ativos / desativados) e exibe, para cada cargo, a quantidade de usuários que o utilizam. A contagem é obtida por correspondência textual do cargo nos usuários (não há vínculo relacional — ver RN06).
- **RN06 - Cargo Desacoplado do Usuário (cópia de texto):** No cadastro/edição, o sistema grava no usuário o texto do cargo escolhido, sem chave estrangeira para o registro do cargo. Editar ou desativar um cargo não afeta o cargo de usuários já cadastrados.
- **RN07 - Cargo de Inicialização (Professor):** O sistema é inicializado com o cargo "Professor do Magistério Superior" ativo, pois o cadastro do perfil Professor o atribui automaticamente (UC-USR-01, RN13).
- **RN08 - Leitura no Cadastro não é Permissão Administrativa:** A exibição da lista de cargos ativos no cadastro/edição de usuário faz parte desses fluxos e não exige do usuário as permissões `cargos.*` (que são para a gestão administrativa de cargos).
