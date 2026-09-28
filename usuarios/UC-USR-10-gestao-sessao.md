# UC-USR-10: Gestão de Sessão

## Metadados
- **Módulo:** Usuários
- **Dependências:**
  - UC-USR-04 (Login/Autenticação) — a sessão é criada no login.
  - `_estados-usuario.md` — apenas usuários que podem logar têm sessão.
- **UCs relacionados:**
  - UC-USR-05 (Gestão de Senha) — a redefinição/troca de senha pode encerrar sessões ativas.
  - UC-USR-13 (Desativar/Reativar) — a desativação encerra as sessões ativas do usuário.
- **Status:** Rascunho

## Informações Gerais
- **Ator Principal:** Usuário autenticado
- **Atores Secundários:** Sistema (expiração automática)
- **Pré-condições:** Existe uma sessão autenticada (criada no login — UC-USR-04).
- **Pós-condições:** Sessão encerrada (por ação do usuário, por expiração ou por evento de segurança), exigindo novo login para voltar a acessar.

---

## Fluxo Principal (Logout)

1. O usuário autenticado aciona a opção **"Sair"**.
2. O sistema encerra a sessão atual e invalida os dados de sessão (**RN01**).
3. O sistema redireciona o usuário para a tela de Login e impede o acesso a áreas protegidas sem novo login (**RN02**).

---

## Fluxos Alternativos e Exceções

### E01: Expiração por Inatividade
- O sistema encerra automaticamente a sessão após um período de inatividade (**RN03**).
  1. Na próxima interação, o usuário é redirecionado ao Login e informado de que a sessão expirou.

### E02: Encerramento por Evento de Segurança
- Ao redefinir/trocar a senha (UC-USR-05, cenários C1/C2/C3), as demais sessões ativas do usuário podem ser encerradas (**RN04**).

### E03: Acesso a Área Protegida sem Sessão Válida
- Se um usuário sem sessão válida (ou com sessão expirada) tentar acessar uma área protegida:
  1. O sistema recusa o acesso e redireciona ao Login (**RN02**).

---

## Regras de Negócio (RN)

- **RN01 - Encerramento de Sessão:** O logout invalida a sessão atual; os dados de sessão deixam de ser válidos imediatamente.
- **RN02 - Proteção de Áreas Autenticadas:** Áreas protegidas exigem sessão válida; sem ela, o acesso é recusado e o usuário é levado ao Login (UC-USR-04).
- **RN03 - Expiração por Inatividade:** A sessão expira após um período de inatividade definido por configuração de segurança.
- **RN04 - Encerramento por Alteração de Senha:** A redefinição/troca de senha (UC-USR-05) pode encerrar as demais sessões ativas do usuário, como medida de segurança.
