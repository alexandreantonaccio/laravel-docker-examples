# UC-USR-05: Recuperação de Senha

## Metadados
- **Módulo:** Usuários
- **Dependências:**
  - `_estados-usuario.md` — modelo de estados do usuário.
  - UC-USR-01 (Cadastro de Usuário) — reutiliza a regra de força de senha (RN11 do UC-USR-01).
- **UCs relacionados:**
  - UC-USR-04 (Login/Autenticação) — origem do acesso "Esqueci minha senha".
- **Status:** Rascunho

## Informações Gerais
- **Ator Principal:** Usuário (Aluno / Professor / Técnico)
- **Atores Secundários:** Serviço de E-mail
- **Pré-condições:** O usuário possui um cadastro com e-mail associado.
- **Pós-condições:** Senha redefinida; a senha anterior deixa de ser válida.

---

## Fluxo Principal

1. Na tela de Login, o usuário clica em **"Esqueci minha senha"**.
2. O sistema exibe um campo para o usuário informar o **e-mail** cadastrado.
3. O usuário informa o e-mail e confirma.
4. O sistema gera um token de redefinição com validade temporária e dispara um e-mail com o link de redefinição (**RN01**, **RN02**).
5. O sistema exibe uma mensagem genérica confirmando o envio, sem revelar se o e-mail existe na base (**RN03**).
6. O usuário acessa o e-mail e clica no link de redefinição.
7. O sistema valida o token (**RN02**) e exibe o formulário de nova senha (senha e confirmação).
8. O usuário define a nova senha respeitando a regra de força de senha (**RN04**).
9. O sistema valida e salva a nova senha (armazenada como hash), invalida o token utilizado e encerra eventuais sessões ativas conforme política de segurança (**RN05**).
10. O sistema confirma a redefinição e orienta o usuário a efetuar login (UC-USR-04).

---

## Fluxos Alternativos e Exceções

### E01: Link de Redefinição Expirado ou Inválido
- **No passo 7:** Se o token estiver expirado, já utilizado ou inválido:
  1. O sistema recusa a redefinição e informa a situação.
  2. O sistema oferece a opção de solicitar um novo link.

### E02: Nova Senha Fraca ou Confirmação Divergente
- **No passo 8:** Se a nova senha não atender à regra de força (**RN04**) ou não coincidir com a confirmação:
  1. O sistema interrompe a operação e informa o requisito não atendido.

### E03: E-mail Não Cadastrado
- **No passo 4:** Se o e-mail informado não existir na base:
  1. O sistema não envia link, mas exibe a **mesma** mensagem genérica do passo 5 (**RN03**), para não revelar a existência do e-mail.

---

## Regras de Negócio (RN)

- **RN01 - Envio do Link:** A redefinição é iniciada pelo envio de um link com token para o e-mail cadastrado.
- **RN02 - Expiração do Token de Redefinição:** O token de redefinição expira após um prazo curto (ex: 1 hora) e só pode ser utilizado uma vez.
- **RN03 - Não Revelação de E-mail:** As mensagens exibidas não devem revelar se o e-mail informado existe ou não na base, evitando enumeração de usuários.
- **RN04 - Força da Nova Senha:** A nova senha deve atender à mesma regra de força do cadastro (mínimo 8 caracteres, ao menos uma letra e um número — RN11 do UC-USR-01).
- **RN05 - Invalidação após Redefinição:** Ao redefinir, o token utilizado é invalidado e a senha anterior deixa de ser válida; sessões ativas podem ser encerradas conforme política de segurança.
