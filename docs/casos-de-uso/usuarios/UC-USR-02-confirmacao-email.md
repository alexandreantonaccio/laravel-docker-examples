# UC-USR-02: Confirmação de E-mail

## Metadados
- **Módulo:** Usuários
- **Dependências:**
  - `_estados-usuario.md` — modelo de estados do usuário.
- **UCs relacionados:**
  - UC-USR-01 (Cadastro de Usuário) — origina o cadastro e o envio do link.
  - UC-USR-03 (Aprovação/Rejeição de Cadastro) — passo seguinte, pelo Administrador.
  - UC-USR-06 (Reenvio de Confirmação de E-mail) — quando o link expira ou não chega.
- **Status:** Rascunho

## Informações Gerais
- **Ator Principal:** Aluno / Professor / Técnico (usuário recém-cadastrado)
- **Atores Secundários:** Administrador do Sistema, Serviço de E-mail
- **Pré-condições:** Existe um cadastro no status **"Pendente de Confirmação de E-mail"** (criado em UC-USR-01) e o link/token de confirmação ainda é válido.
- **Pós-condições:** Cadastro no status **"E-mail Confirmado"**; o usuário pode efetuar login (ainda sem grupos atribuídos); o Administrador é notificado do novo cadastro aguardando análise.

---

## Fluxo Principal

1. O usuário acessa seu e-mail e clica no link de confirmação recebido no cadastro.
2. O sistema valida o token do link (**RN01**).
3. O sistema altera o status do usuário para **"E-mail Confirmado"**.
4. A partir deste ponto o usuário já pode efetuar login. Como ainda não foi associado a nenhum grupo, seu acesso se limita ao que o sistema libera por padrão, até a decisão do Administrador (ver `_estados-usuario.md` e `docs/referencias/permissoes-sistema.md`).
5. O sistema envia automaticamente um e-mail de notificação à Administração do Sistema informando sobre o novo cadastro aguardando análise.
6. O sistema exibe ao usuário uma confirmação de que o e-mail foi verificado.

> Continuação: a decisão de liberação de permissões ocorre em **UC-USR-03**.

---

## Fluxos Alternativos e Exceções

### E01: Link de Confirmação Expirado
- **No passo 2:** Se o token estiver expirado (**RN02**):
  1. O sistema informa que o link expirou.
  2. O sistema oferece a opção de solicitar um novo link (UC-USR-06).

### E02: Token Inválido ou Já Utilizado
- **No passo 2:** Se o token for inválido, adulterado ou já tiver sido utilizado:
  1. O sistema recusa a confirmação e exibe mensagem apropriada.
  2. Se o e-mail já estava confirmado, orienta o usuário a efetuar login.

### E03: Cadastro Expirado/Removido
- **No passo 2:** Se o cadastro correspondente já tiver sido removido por expiração (UC-USR-01, RN12):
  1. O sistema informa que o cadastro não está mais disponível e orienta o usuário a realizar um novo cadastro.

---

## Regras de Negócio (RN)

- **RN01 - Validação do Token:** O sistema só confirma o e-mail se o token for válido, não expirado e ainda não utilizado, e corresponder a um cadastro existente.
- **RN02 - Expiração do Link:** O link de confirmação expira após 24 horas (consistente com UC-USR-01, RN05). Após expirar, é necessário solicitar reenvio (UC-USR-06).
- **RN03 - Notificação ao Administrador:** Ao confirmar o e-mail, o sistema notifica a Administração de que há um novo cadastro aguardando análise, para viabilizar o UC-USR-03.
