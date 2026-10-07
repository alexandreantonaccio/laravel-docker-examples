# UC-CUR-01: Gerenciar Cursos

## Metadados
- **Módulo:** Cursos
- **Dependências:**
  - `docs/referencias/permissoes-sistema.md` — permissões `cursos.*`.
- **UCs relacionados:**
  - UC-USR-01 (Cadastro de Usuário) — o perfil Aluno tem o campo Curso, escolhido da lista de cursos ativos, incluindo a opção "OUTRO" (RN08 do UC-USR-01).
  - UC-USR-11 (Editar Usuário) — a edição de curso de um usuário pelo Administrador usa a lista de cursos ativos.
- **Status:** Rascunho

## Informações Gerais
- **Ator Principal:** Administrador (ator com `cursos.criar`/`editar`/`desativar`/`visualizar`/`listar`)
- **Atores Secundários:** Nenhum
- **Pré-condições:** O ator está autenticado e possui as permissões de gestão de cursos.
- **Pós-condições:** Lista de cursos atualizada (curso criado, editado, ativado/desativado), refletindo imediatamente na lista de cursos disponíveis no cadastro/edição de usuário.

---

## Conceito

Cursos são usados no cadastro do perfil **Aluno** (UC-USR-01, RN08). No
cadastro, o aluno seleciona o curso de uma lista de cursos **ativos**, sem
digitação livre. Quando não encontrar o seu curso, seleciona a opção
**"OUTRO"** (um curso cadastrado como qualquer outro).

> **Inicialização:** o registro "OUTRO" é criado ativo na instalação para
> permitir o cadastro de Alunos mesmo antes da configuração dos cursos.
> Os demais cursos são cadastrados pelo Administrador quando o sistema
> estiver em operação (**RN07**).

> **Desacoplamento (consistente com Cargos e Domínios):** ao salvar o
> cadastro/edição, o sistema grava no usuário o **texto do curso**
> escolhido (cópia), sem chave estrangeira para o registro do curso.
> Editar ou desativar um curso **não** altera o curso de usuários já
> cadastrados (ver **RN06**).

---

## Fluxo Principal

1. O ator acessa a área de **"Cursos"**.
2. O sistema lista os cursos cadastrados, indicando quais estão **ativos** e **desativados**, com filtro por status (todos / ativos / desativados) e a **quantidade de usuários** que usam cada curso (**RN05**).
3. O ator cria um novo curso informando o **nome** (**RN01**).
4. O sistema normaliza o nome para **caixa alta** (**RN08**), valida e verifica a unicidade (**RN01**, **RN02**) e persiste o curso como **ativo**.
5. A partir daí, o curso ativo passa a aparecer na lista de cursos disponíveis no cadastro (UC-USR-01) e edição (UC-USR-11) (**RN04**).

---

## Fluxos Alternativos e Exceções

### E01: Editar Curso
- **No passo 3:** O ator seleciona um curso existente e altera o nome.
  1. O sistema normaliza para caixa alta (**RN08**), revalida nome e unicidade (**RN01**, **RN02**) e persiste.
  2. A mudança vale para **novas** seleções; usuários já cadastrados com o curso **não** são afetados (**RN06**).

### E02: Desativar Curso
- O ator desativa um curso.
  1. O sistema marca o curso como **desativado**; ele deixa de aparecer na lista de seleção do cadastro/edição (**RN03**).
  2. Usuários já cadastrados com esse curso **permanecem inalterados** (**RN06**).
  3. O sistema informa quantos usuários usam esse curso, para ciência do ator (**RN05**).

### E03: Reativar Curso
- O ator reativa um curso desativado, que volta a aparecer na lista de seleção.

### E04: Nome Inválido
- **No passo 4:** Se o nome for vazio ou fora do formato esperado:
  1. O sistema recusa e orienta o preenchimento correto (**RN01**).

### E05: Curso Duplicado
- **No passo 4:** Se já existir um curso com o mesmo nome (após normalização para caixa alta):
  1. O sistema interrompe e informa a duplicidade (**RN02**).

---

## Regras de Negócio (RN)

- **RN01 - Nome do Curso:** O nome do curso é obrigatório.
- **RN02 - Unicidade:** Não pode haver dois cursos com o mesmo nome (comparação após normalização para caixa alta).
- **RN03 - Desativação:** Desativar um curso o remove da lista de seleção de novos cadastros/edições, mas não o exclui. O sistema não exclui cursos (preserva histórico); a medida disponível é a desativação.
- **RN04 - Efeito Imediato:** Alterações na lista (criação, edição, ativação/desativação) refletem imediatamente na lista de cursos disponíveis nos fluxos de cadastro/edição.
- **RN05 - Filtro e Contagem de Uso:** A listagem permite filtrar por status (todos / ativos / desativados) e exibe, para cada curso, a quantidade de usuários que o utilizam. A contagem é obtida por correspondência textual do curso nos usuários (não há vínculo relacional — ver RN06).
- **RN06 - Curso Desacoplado do Usuário (cópia de texto):** No cadastro/edição, o sistema grava no usuário o texto do curso escolhido, sem chave estrangeira para o registro do curso. Editar ou desativar um curso não afeta o curso de usuários já cadastrados.
- **RN07 - Inicialização da Opção de Fallback:** O registro **"OUTRO"** é criado ativo na inicialização para uso como opção de fallback no cadastro de Aluno (UC-USR-01, RN08). Os demais cursos são cadastrados pelo Administrador.
- **RN08 - Caixa Alta:** Os nomes de curso são armazenados e exibidos em **caixa alta** (o sistema normaliza a entrada ao salvar), garantindo padronização e evitando duplicidades por diferença de capitalização.
- **RN09 - Leitura no Cadastro não é Permissão Administrativa:** A exibição da lista de cursos ativos no cadastro/edição de usuário faz parte desses fluxos e não exige do usuário as permissões `cursos.*` (que são para a gestão administrativa de cursos).
