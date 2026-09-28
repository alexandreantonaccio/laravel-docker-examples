# UC-USR-06: Reenvio de Confirmação de E-mail

## Metadados
- **Módulo:** Usuários
- **Dependências:**
  - `_estados-usuario.md` — modelo de estados do usuário.
  - UC-USR-01 (Cadastro de Usuário) — regra de expiração do link (RN05) e de cadastro não confirmado (RN12).
- **UCs relacionados:**
  - UC-USR-02 (Confirmação de E-mail) — o link reenviado é usado neste fluxo.
  - UC-USR-04 (Login/Autenticação) — origem quando o login é recusado por e-mail não confirmado.
- **Status:** Rascunho

## Informações Gerais
- **Ator Principal:** Usuário (Aluno / Professor / Técnico) com cadastro não confirmado
- **Atores Secundários:** Serviço de E-mail
- **Pré-condições:** Existe um cadastro no status **"Pendente de Confirmação de E-mail"** ainda não expirado (**RN03**).
- **Pós-condições:** Novo link/token de confirmação gerado e enviado; o link anterior é invalidado.

---

## Fluxo Principal

1. O usuário aciona a opção de **reenviar confirmação** (a partir da tela de login recusado por e-mail não confirmado, da tela de link expirado, ou de uma tela dedicada).
2. O usuário informa o **e-mail** cadastrado (quando o contexto não o fornece automaticamente).
3. O sistema verifica que existe um cadastro "Pendente de Confirmação de E-mail" para o e-mail e que não está expirado (**RN03**).
4. O sistema gera um novo token de confirmação, invalida o token anterior e dispara um novo e-mail com o link (**RN01**, **RN02**).
5. O sistema exibe uma mensagem genérica confirmando o envio, sem revelar se o e-mail existe (**RN04**).
6. O usuário prossegue com a confirmação em UC-USR-02.

---

## Fluxos Alternativos e Exceções

### E01: Cadastro Já Confirmado
- **No passo 3:** Se o cadastro correspondente já estiver com o e-mail confirmado:
  1. O sistema não reenvia link e orienta o usuário a efetuar login (UC-USR-04).

### E02: Cadastro Expirado ou Inexistente
- **No passo 3:** Se não houver cadastro pendente para o e-mail, ou se ele já tiver expirado e sido removido (**RN12 do UC-USR-01**):
  1. O sistema exibe a mesma mensagem genérica do passo 5 (**RN04**) e orienta a realizar um novo cadastro.

### E03: Limite de Reenvios
- **No passo 4:** Se o usuário exceder o número/frequência de reenvios permitidos (**RN05**):
  1. O sistema recusa o reenvio temporariamente e informa quando poderá tentar novamente.

---

## Regras de Negócio (RN)

- **RN01 - Novo Token a Cada Reenvio:** Cada reenvio gera um novo token de confirmação com nova validade.
- **RN02 - Invalidação do Token Anterior:** Ao reenviar, o token anterior é invalidado, evitando múltiplos links válidos simultâneos.
- **RN03 - Aplicável Apenas a Cadastro Pendente e Não Expirado:** O reenvio só se aplica a cadastros no status "Pendente de Confirmação de E-mail" que ainda não expiraram (ver RN12 do UC-USR-01).
- **RN04 - Não Revelação de E-mail:** As mensagens não devem revelar se o e-mail existe na base, evitando enumeração de usuários.
- **RN05 - Limite de Reenvios:** O sistema deve limitar a quantidade/frequência de reenvios por e-mail, como proteção contra abuso.
