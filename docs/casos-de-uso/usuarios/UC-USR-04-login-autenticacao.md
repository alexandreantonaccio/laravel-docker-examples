# UC-USR-04: Login / Autenticação

## Metadados
- **Módulo:** Usuários
- **Dependências:**
  - `_estados-usuario.md` — modelo de estados do usuário (define o acesso permitido por estado).
- **UCs relacionados:**
  - UC-USR-02 (Confirmação de E-mail) — pré-requisito: login exige e-mail confirmado.
  - UC-USR-05 (Gestão de Senha) — quando o usuário esqueceu a senha.
  - UC-USR-06 (Reenvio de Confirmação de E-mail) — quando o e-mail ainda não foi confirmado.
- **Status:** Rascunho

## Informações Gerais
- **Ator Principal:** Usuário (Aluno / Professor / Técnico)
- **Atores Secundários:** Administrador do Sistema (também autentica por este fluxo)
- **Pré-condições:** O usuário possui um cadastro cujo status permite login (**"E-mail Confirmado"**, **"Aprovado"** ou **"Rejeitado"** — ver `_estados-usuario.md`).
- **Pós-condições:** Sessão autenticada iniciada; as funcionalidades disponíveis correspondem às permissões efetivas do usuário (grupos + ajuste individual).

---

## Fluxo Principal

1. O usuário acessa a tela de **Login**.
2. O usuário informa **e-mail** e **senha** e clica em **"Entrar"**.
3. O sistema valida as credenciais (**RN01**).
4. O sistema verifica se o status do usuário permite login (**RN02**).
5. O sistema inicia a sessão autenticada e redireciona o usuário para a área inicial.
6. O sistema disponibiliza as funcionalidades conforme as **permissões efetivas** do usuário (grupos + ajuste individual — ver `docs/referencias/permissoes-sistema.md`). Um usuário sem grupos atribuídos acessa apenas o que estiver liberado por padrão.

---

## Fluxos Alternativos e Exceções

### E01: Credenciais Inválidas
- **No passo 3:** Se o e-mail não existir ou a senha não conferir:
  1. O sistema recusa o login com mensagem genérica (**RN03**): *"E-mail ou senha inválidos."*
  2. Não revela se o problema é o e-mail ou a senha.

### E02: E-mail Ainda Não Confirmado
- **No passo 4:** Se o status for **"Pendente de Confirmação de E-mail"**:
  1. O sistema recusa o login e informa que o e-mail ainda não foi confirmado.
  2. O sistema oferece a opção de reenviar o link de confirmação (UC-USR-06).

### E05: Usuário Desativado
- **No passo 4:** Se o usuário estiver **desativado** (ver UC-USR-13 e `_estados-usuario.md`):
  1. O sistema recusa o login.
  2. Diferentemente da falha genérica de credenciais, aqui o sistema informa especificamente que a **conta está desativada** e orienta o usuário a procurar a **sala de apoio** para reativação (**RN05**).

### E03: Esqueci a Senha
- **No passo 2:** O usuário pode acionar a opção **"Esqueci minha senha"**, iniciando o UC-USR-05.

### E04: Excesso de Tentativas
- **No passo 3:** Após múltiplas tentativas malsucedidas consecutivas (**RN04**):
  1. O sistema aplica proteção contra força bruta (ex: atraso progressivo ou bloqueio temporário).

---

## Regras de Negócio (RN)

- **RN01 - Autenticação por E-mail e Senha:** As credenciais são o e-mail e a senha definidos no cadastro. A senha é verificada contra o hash armazenado (nunca em texto puro).
- **RN02 - Login Condicionado ao Status e à Ativação:** Só podem efetuar login usuários que (a) estejam nos status "E-mail Confirmado", "Aprovado" ou "Rejeitado" — "Pendente de Confirmação de E-mail" não loga — **e** (b) estejam **ativos** (não desativados — ver UC-USR-13 e `_estados-usuario.md`). O status/ativação controla o **acesso** (poder ou não logar); as **ações** disponíveis após o login dependem das permissões efetivas do usuário (grupos + ajuste individual — ver `docs/referencias/permissoes-sistema.md`).
- **RN03 - Mensagem Genérica de Falha:** Em falha de credenciais, a mensagem não deve revelar se o e-mail existe ou não, para não expor a base de usuários.
- **RN04 - Proteção contra Força Bruta:** O sistema deve limitar tentativas consecutivas de login malsucedidas (ex: atraso progressivo ou bloqueio temporário), como medida de segurança.
- **RN05 - Mensagem Específica para Conta Desativada:** Como exceção à mensagem genérica (RN03), quando o motivo da recusa é conta **desativada**, o sistema informa explicitamente essa condição e orienta a procurar a sala de apoio — pois o usuário precisa saber que deve solicitar reativação.
