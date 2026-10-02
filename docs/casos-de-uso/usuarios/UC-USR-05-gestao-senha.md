# UC-USR-05: Gestão de Senha

## Metadados
- **Módulo:** Usuários
- **Dependências:**
  - `_estados-usuario.md` — os fluxos por link exigem e-mail já confirmado (**RN06**).
  - UC-USR-01 (Cadastro de Usuário) — regra de força de senha (RN11).
  - `docs/referencias/permissoes-sistema.md` — permissão `usuarios.resetar_senha` (reset pelo Administrador).
  - `docs/referencias/notificacoes-sistema.md` — notificações de senha.
- **UCs relacionados:**
  - UC-USR-04 (Login/Autenticação) — origem do "Esqueci minha senha" e destino após redefinir.
  - UC-USR-10 (Gestão de Sessão) — a troca de senha pode encerrar sessões ativas.
  - UC-USR-02 / UC-USR-06 — caminho correto quando o e-mail ainda não foi confirmado.
- **Status:** Rascunho

## Informações Gerais
- **Ator Principal:** Usuário (Aluno / Professor / Técnico) — cenários C1 e C2; Administrador — cenário C3.
- **Atores Secundários:** Serviço de E-mail
- **Pré-condições:** Existe um cadastro. Para os fluxos por link (C1, C3), o e-mail já deve estar confirmado (**RN06**).
- **Pós-condições:** Senha redefinida; a senha anterior deixa de ser válida; token utilizado é invalidado.

---

## Visão Geral dos Cenários

Este UC reúne todos os caminhos de definição/troca de senha:

- **C1 — Esqueci minha senha (usuário não logado):** o usuário solicita, recebe um **link com token** por e-mail e define a nova senha.
- **C2 — Alterar senha (usuário logado):** o usuário, autenticado, informa a **senha atual** e a nova.
- **C3 — Reset pelo Administrador:** o Administrador dispara o reset; o usuário recebe um **link com token** por e-mail e define a nova senha (o Administrador não define nem vê a senha — **RN07**).

> Princípio de segurança comum a C1 e C3: a senha **nunca** trafega por
> e-mail. O e-mail carrega apenas um link com token de **uso único** e
> **validade curta**; a senha final é sempre definida pelo próprio usuário.

---

## Fluxo C1 — Esqueci minha senha (não logado)

1. Na tela de Login, o usuário clica em **"Esqueci minha senha"**.
2. O sistema solicita o **e-mail** cadastrado.
3. O sistema gera um token de redefinição (uso único, validade curta) e envia um **link** por e-mail (**RN01**, **RN02**); e-mail confirmado é pré-requisito (**RN06**).
4. O sistema exibe uma mensagem genérica, sem revelar se o e-mail existe (**RN03**).
5. O usuário acessa o e-mail, clica no link; o sistema valida o token (**RN02**) e exibe o formulário de nova senha.
6. O usuário define a nova senha respeitando a força de senha (**RN04**); o sistema salva (hash), invalida o token e pode encerrar sessões ativas (**RN05**, UC-USR-10).
7. O sistema confirma e orienta o login (UC-USR-04).

## Fluxo C2 — Alterar senha (logado)

1. O usuário autenticado acessa "Meus Dados" (UC-USR-12) e aciona **"Alterar senha"**.
2. O usuário informa **senha atual** e **nova senha** (com confirmação).
3. O sistema valida a senha atual e a força da nova (**RN04**), salva (hash), pode encerrar as demais sessões (**RN05**, UC-USR-10) e notifica o usuário (**N11** — ver `docs/referencias/notificacoes-sistema.md`).

## Fluxo C3 — Reset pelo Administrador

1. O Administrador (ator com `usuarios.resetar_senha`) localiza o usuário (UC-USR-07/12) e aciona **"Resetar senha"**.
2. O sistema gera um token de redefinição e envia um **link** por e-mail ao usuário (mesmo mecanismo de C1 — **RN01**, **RN02**, **RN07**).
3. O usuário segue os passos 5–7 do fluxo C1 para definir a nova senha.
4. A ação de reset é registrada para auditoria (autor, data/hora).

---

## Fluxos Alternativos e Exceções

### E01: Link de Redefinição Expirado ou Inválido
- **Em C1/C3, ao abrir o link:** se o token estiver expirado, já utilizado ou inválido:
  1. O sistema recusa a redefinição e oferece a opção de solicitar um novo link.

### E02: Nova Senha Fraca ou Confirmação Divergente
- **Em qualquer cenário:** se a nova senha não atender à força (**RN04**) ou não coincidir com a confirmação:
  1. O sistema interrompe e informa o requisito não atendido.

### E03: Senha Atual Incorreta (C2)
- **No C2:** se a senha atual informada não conferir:
  1. O sistema recusa a troca, sem alterar a senha.

### E04: E-mail Não Cadastrado (C1)
- **No C1:** se o e-mail não existir na base:
  1. O sistema não envia link, mas exibe a mesma mensagem genérica (**RN03**), para não revelar a existência do e-mail.

### E05: E-mail Ainda Não Confirmado (C1/C3)
- Se o cadastro estiver "Pendente de Confirmação de E-mail" (**RN06**):
  1. O sistema não envia link de redefinição de senha (o caminho correto é confirmar o e-mail — UC-USR-02 — ou reenviar — UC-USR-06). Em C1, por não revelação (**RN03**), exibe a mesma mensagem genérica.

---

## Regras de Negócio (RN)

- **RN01 - Envio por Link:** Os fluxos C1 e C3 enviam um **link com token** ao e-mail; a senha nunca é enviada por e-mail.
- **RN02 - Token de Uso Único e Validade Curta:** O token de redefinição expira após um prazo curto (ex: 1 hora) e só pode ser utilizado uma vez.
- **RN03 - Não Revelação de E-mail:** Nas mensagens de tela, o sistema não revela se um e-mail existe na base, evitando enumeração de usuários.
- **RN04 - Força de Senha:** A nova senha segue a regra do cadastro (mínimo 8 caracteres, ao menos uma letra e um número — RN11 do UC-USR-01) e exige confirmação.
- **RN05 - Invalidação após Troca:** Ao definir a nova senha, o token utilizado (quando houver) é invalidado, a senha anterior deixa de ser válida e as sessões ativas podem ser encerradas (UC-USR-10).
- **RN06 - Fluxos por Link Exigem E-mail Confirmado:** C1 e C3 só se aplicam a cadastros cujo e-mail já foi confirmado. Cadastro "Pendente de Confirmação de E-mail" não recupera/reseta senha (deve confirmar o e-mail primeiro).
- **RN07 - Reset pelo Administrador Não Expõe Senha:** No C3, o Administrador apenas dispara o reset; ele **não** define nem visualiza a senha do usuário. A senha é definida pelo próprio usuário via link, preservando a confidencialidade.
