# UC-USR-12: Visualizar Detalhe do Usuário

## Metadados
- **Módulo:** Usuários
- **Dependências:**
  - `docs/referencias/permissoes-sistema.md` — permissões `usuarios.visualizar.proprio` e `usuarios.visualizar.qualquer`.
  - `_estados-usuario.md` — o status do cadastro é exibido no detalhe.
- **UCs relacionados:**
  - UC-USR-07 (Listar e Pesquisar Usuários) — ponto de partida para abrir o detalhe.
  - UC-USR-11 (Editar Usuário) — ação possível a partir do detalhe, conforme permissão.
- **Status:** Rascunho

## Informações Gerais
- **Ator Principal:** Usuário autenticado (o próprio) ou Administrador (qualquer usuário)
- **Atores Secundários:** Nenhum
- **Pré-condições:** O ator está autenticado. Para ver terceiros, possui `usuarios.visualizar.qualquer`; para ver a si, `usuarios.visualizar.proprio`.
- **Pós-condições:** Nenhuma alteração de dados (apenas consulta).

---

## Fluxo Principal

1. O ator abre o detalhe de um usuário: o **próprio** cadastro (tela "Meus Dados", com `usuarios.visualizar.proprio`) ou o de **outro** usuário a partir da listagem (UC-USR-07, com `usuarios.visualizar.qualquer`).
2. O sistema verifica o escopo da permissão (**RN01**):
   - `usuarios.visualizar.proprio` — permite ver apenas o próprio cadastro;
   - `usuarios.visualizar.qualquer` — permite ver o cadastro de qualquer usuário.
3. O sistema exibe os dados do usuário conforme o perfil (**RN02**):
   - Comuns: identificação funcional (Matrícula/SIAPE), nome, e-mail, telefone, status do cadastro.
   - Aluno: curso e situação do comprovante (quando houver — ver assimetria na RN04 do UC-USR-09).
   - Técnico/Professor: cargo e vínculo.
4. O sistema oferece ações a partir do detalhe, cada uma condicionada à respectiva permissão (**RN03**):
   - Na própria tela ("Meus Dados"): **"Alterar senha"** (autoatendimento — UC-USR-05, C2). Para ajustar qualquer outro dado, o sistema orienta a procurar a sala de apoio (UC-USR-11).
   - No detalhe de terceiros (ator com permissão): editar dados (UC-USR-11), alterar perfil (UC-USR-09), gerenciar permissões (UC-USR-08), resetar senha (UC-USR-05, C3), desativar/reativar (UC-USR-13).

---

## Fluxos Alternativos e Exceções

### E01: Sem Permissão para Ver Terceiros
- **No passo 2:** Se o ator possui apenas `usuarios.visualizar.proprio` e tenta abrir o cadastro de outro usuário:
  1. O sistema recusa o acesso e informa que ele só pode visualizar o próprio cadastro.

### E02: Usuário Inexistente
- **No passo 1:** Se o usuário-alvo não existir (ex: link antigo de um cadastro removido):
  1. O sistema informa que o usuário não foi encontrado.

---

## Regras de Negócio (RN)

- **RN01 - Escopo de Visualização:** `usuarios.visualizar.proprio` limita a consulta ao próprio cadastro; `usuarios.visualizar.qualquer` permite consultar qualquer usuário.
- **RN02 - Dados por Perfil:** O detalhe exibe os campos conforme o perfil do usuário. Dados sensíveis (senha/hash) nunca são exibidos.
- **RN03 - Ações Condicionadas:** As ações oferecidas no detalhe (editar, alterar perfil, gerenciar permissões, desativar/reativar) aparecem apenas para atores com as permissões correspondentes.
