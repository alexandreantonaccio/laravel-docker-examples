# UC-VIN-01: Gerenciar Vínculos

## Metadados
- **Módulo:** Vínculos
- **Dependências:**
  - `docs/referencias/permissoes-sistema.md` — permissões `vinculos.*`.
- **UCs relacionados:**
  - UC-USR-01 (Cadastro de Usuário) — Técnico e Professor têm o campo Vínculo, escolhido da lista de vínculos ativos, incluindo a opção "OUTRO" (RN14 do UC-USR-01).
  - UC-USR-11 (Editar Usuário) — a edição de vínculo de um usuário pelo Administrador usa a lista de vínculos ativos.
- **Status:** Rascunho

## Informações Gerais
- **Ator Principal:** Administrador (ator com `vinculos.criar`/`editar`/`desativar`/`visualizar`/`listar`)
- **Atores Secundários:** Nenhum
- **Pré-condições:** O ator está autenticado e possui as permissões de gestão de vínculos.
- **Pós-condições:** Lista de vínculos atualizada (vínculo criado, editado, ativado/desativado), refletindo imediatamente na lista de vínculos disponíveis no cadastro/edição de usuário.

---

## Conceito

Vínculos são usados no cadastro dos perfis **Técnico** e **Professor**
(UC-USR-01, RN14). No cadastro, o usuário seleciona o vínculo de uma lista
de vínculos **ativos**, sem digitação livre. Quando não encontrar o seu
vínculo, seleciona a opção **"OUTRO"** (um vínculo cadastrado como
qualquer outro).

> **Sem inicialização automática:** a tabela de vínculos **não** é
> populada na instalação do sistema. Os vínculos (incluindo o registro
> "OUTRO") são cadastrados manualmente pelo Administrador quando o
> sistema estiver em operação (**RN07**).

> **Desacoplamento (consistente com Cargos, Cursos e Domínios):** ao
> salvar o cadastro/edição, o sistema grava no usuário o **texto do
> vínculo** escolhido (cópia), sem chave estrangeira para o registro do
> vínculo. Editar ou desativar um vínculo **não** altera o vínculo de
> usuários já cadastrados (ver **RN06**).

---

## Fluxo Principal

1. O ator acessa a área de **"Vínculos"**.
2. O sistema lista os vínculos cadastrados, indicando quais estão **ativos** e **desativados**, com filtro por status (todos / ativos / desativados) e a **quantidade de usuários** que usam cada vínculo (**RN05**).
3. O ator cria um novo vínculo informando o **nome** (ex: "Efetivo", "Terceirizado", "Bolsista") (**RN01**).
4. O sistema valida o nome e a unicidade (**RN01**, **RN02**) e persiste o vínculo como **ativo**.
5. A partir daí, o vínculo ativo passa a aparecer na lista de vínculos disponíveis no cadastro (UC-USR-01) e edição (UC-USR-11) (**RN04**).

---

## Fluxos Alternativos e Exceções

### E01: Editar Vínculo
- **No passo 3:** O ator seleciona um vínculo existente e altera o nome.
  1. O sistema revalida nome e unicidade (**RN01**, **RN02**) e persiste.
  2. A mudança vale para **novas** seleções; usuários já cadastrados com o vínculo **não** são afetados (**RN06**).

### E02: Desativar Vínculo
- O ator desativa um vínculo.
  1. O sistema marca o vínculo como **desativado**; ele deixa de aparecer na lista de seleção do cadastro/edição (**RN03**).
  2. Usuários já cadastrados com esse vínculo **permanecem inalterados** (**RN06**).
  3. O sistema informa quantos usuários usam esse vínculo, para ciência do ator (**RN05**).

### E03: Reativar Vínculo
- O ator reativa um vínculo desativado, que volta a aparecer na lista de seleção.

### E04: Nome Inválido
- **No passo 4:** Se o nome for vazio ou fora do formato esperado:
  1. O sistema recusa e orienta o preenchimento correto (**RN01**).

### E05: Vínculo Duplicado
- **No passo 4:** Se já existir um vínculo com o mesmo nome:
  1. O sistema interrompe e informa a duplicidade (**RN02**).

---

## Regras de Negócio (RN)

- **RN01 - Nome do Vínculo:** O nome do vínculo é obrigatório (ex: "Efetivo", "Terceirizado", "Bolsista", "OUTRO").
- **RN02 - Unicidade:** Não pode haver dois vínculos com o mesmo nome.
- **RN03 - Desativação:** Desativar um vínculo o remove da lista de seleção de novos cadastros/edições, mas não o exclui. O sistema não exclui vínculos (preserva histórico); a medida disponível é a desativação.
- **RN04 - Efeito Imediato:** Alterações na lista (criação, edição, ativação/desativação) refletem imediatamente na lista de vínculos disponíveis nos fluxos de cadastro/edição.
- **RN05 - Filtro e Contagem de Uso:** A listagem permite filtrar por status (todos / ativos / desativados) e exibe, para cada vínculo, a quantidade de usuários que o utilizam. A contagem é obtida por correspondência textual do nome do vínculo nos usuários, sem relação (FK) entre as tabelas — ver RN06.
- **RN06 - Sem Relação (FK) entre Usuário e Registro de Vínculo:** No cadastro/edição, o sistema grava no usuário o texto do vínculo escolhido, sem chave estrangeira para o registro correspondente na lista de vínculos. Editar ou desativar um vínculo da lista não afeta o vínculo já salvo em usuários cadastrados.
- **RN07 - Sem Inicialização Automática:** A tabela de vínculos não é populada na instalação. Os vínculos, incluindo o registro **"OUTRO"** usado como opção de fallback no cadastro de Técnico/Professor (UC-USR-01, RN14), são cadastrados manualmente pelo Administrador. Enquanto não houver vínculos ativos (ou o "OUTRO"), o cadastro de Técnico/Professor depende desse cadastro prévio.
- **RN08 - Leitura no Cadastro não é Permissão Administrativa:** A exibição da lista de vínculos ativos no cadastro/edição de usuário faz parte desses fluxos e não exige do usuário as permissões `vinculos.*` (que são para a gestão administrativa de vínculos).
