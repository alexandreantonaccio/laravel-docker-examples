# UC-USR-07: Listar e Pesquisar Usuários

## Metadados
- **Módulo:** Usuários
- **Dependências:**
  - `_estados-usuario.md` — os status exibidos/filtrados seguem o modelo de estados do usuário.
  - `docs/referencias/permissoes-sistema.md` — a permissão `usuarios.listar` que habilita este UC é concedida via grupos (UC-GRP-01/02) ou ajuste individual (UC-USR-08).
- **UCs relacionados:**
  - UC-USR-04 (Login/Autenticação) — o ator precisa estar autenticado.
  - UC-USR-03 (Aprovação/Rejeição de Cadastro) — a listagem é ponto de partida comum para localizar cadastros a analisar.
- **Status:** Rascunho

## Informações Gerais
- **Ator Principal:** Usuário autenticado com a permissão `usuarios.listar` (ex: Administrador)
- **Atores Secundários:** Nenhum
- **Pré-condições:**
  - O ator está autenticado (UC-USR-04).
  - O ator possui a permissão `usuarios.listar` (**RN01**).
- **Pós-condições:** Nenhuma alteração de dados. A ação é apenas de consulta/visualização.

---

## Fluxo Principal

1. O ator autenticado acessa a área de **"Usuários"**.
2. O sistema verifica se o ator possui a permissão `usuarios.listar` (**RN01**).
3. O sistema exibe a lista de usuários, apresentando por usuário (**RN02**):
   - Nome Completo
   - E-mail
   - Perfil (Aluno / Professor / Técnico)
   - Identificação Funcional (Matrícula/SIAPE, conforme o perfil)
   - Status do cadastro (Pendente de Confirmação de E-mail / E-mail Confirmado / Aprovado / Rejeitado)
4. O sistema oferece controles de **filtro** por Status e por Perfil (**RN03**) e um campo de **pesquisa textual** (**RN04**).
5. O ator, opcionalmente, aplica filtros e/ou digita um termo de pesquisa.
6. O sistema atualiza a lista exibindo apenas os usuários que atendem aos filtros e ao termo pesquisado (**RN03**, **RN04**).
7. Quando a quantidade de resultados excede o tamanho da página, o sistema pagina a listagem (**RN05**).

---

## Fluxos Alternativos e Exceções

### E01: Ator sem Permissão
- **No passo 2:** Se o ator não possuir a permissão `usuarios.listar`:
  1. O sistema não exibe a listagem.
  2. O sistema informa que o ator não tem permissão para acessar essa área.

### E02: Nenhum Resultado Encontrado
- **No passo 6:** Se nenhum usuário atender aos filtros/termo pesquisado:
  1. O sistema exibe uma mensagem informando que nenhum usuário foi encontrado, mantendo os filtros aplicados visíveis para ajuste.

### E03: Termo de Pesquisa Vazio
- **No passo 5:** Se o ator limpar o campo de pesquisa:
  1. O sistema retorna à listagem completa (respeitando os filtros de Status/Perfil ainda aplicados).

---

## Regras de Negócio (RN)

- **RN01 - Controle por Permissão:** A ação de listar/pesquisar usuários só é disponibilizada a atores autenticados que possuam a permissão `usuarios.listar` (ver `docs/referencias/permissoes-sistema.md`). Essa permissão é concedida via grupos (UC-GRP-01/02) ou ajuste individual (UC-USR-08).
- **RN02 - Dados Exibidos:** A listagem exibe, por usuário: nome completo, e-mail, perfil, identificação funcional (Matrícula/SIAPE conforme o perfil) e status do cadastro. Dados sensíveis (ex: senha/hash) nunca são exibidos.
- **RN03 - Filtros:** A listagem pode ser filtrada por Status (conforme `_estados-usuario.md`) e por Perfil (Aluno/Professor/Técnico). Os filtros são combináveis entre si e com a pesquisa textual.
- **RN04 - Pesquisa Textual:** A pesquisa localiza usuários por correspondência **parcial** (não exige termo exato) nos campos nome, e-mail e identificação funcional. A busca é combinada com os filtros ativos.
- **RN05 - Paginação:** Quando o número de resultados excede o tamanho da página, a listagem é paginada, preservando os filtros e o termo de pesquisa aplicados durante a navegação entre páginas.
