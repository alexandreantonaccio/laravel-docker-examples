# UC-USR-13: Desativar / Reativar Usuário

## Metadados
- **Módulo:** Usuários
- **Dependências:**
  - `docs/referencias/permissoes-sistema.md` — permissão `usuarios.desativar`.
  - `_estados-usuario.md` — a desativação é a dimensão ativo/desativado, separada do status do cadastro.
  - `docs/referencias/notificacoes-sistema.md` — notificação ao usuário ao ser desativado/reativado.
- **UCs relacionados:**
  - UC-USR-07 (Listar) / UC-USR-12 (Detalhe) — ponto de partida.
  - UC-USR-04 (Login) — usuário desativado não consegue autenticar.
  - UC-USR-10 (Gestão de Sessão) — a desativação encerra sessões ativas.
- **Status:** Rascunho

## Informações Gerais
- **Ator Principal:** Administrador (ator com `usuarios.desativar`)
- **Atores Secundários:** Nenhum
- **Pré-condições:** O usuário-alvo existe. O ator possui `usuarios.desativar`.
- **Pós-condições:** Usuário desativado (impedido de acessar, com registro preservado) ou reativado (acesso restaurado).

---

## Fluxo Principal — Desativar

1. O Administrador localiza o usuário (UC-USR-07/12) e seleciona **"Desativar"**.
2. O sistema solicita confirmação e, opcionalmente, um motivo (**RN02**).
3. O sistema marca o usuário como **desativado**, preservando todo o registro e o histórico (**RN01**).
4. A partir daí, o usuário não consegue efetuar login (**RN03**); sessões ativas são encerradas (UC-USR-10).
5. O sistema notifica o usuário sobre a desativação (ver `notificacoes-sistema.md`).

---

## Fluxo Alternativo — Reativar

A1. O Administrador localiza um usuário desativado e seleciona **"Reativar"**.
A2. O sistema restaura o acesso do usuário, mantendo grupos e permissões que ele possuía antes da desativação (**RN01**).
A3. O sistema notifica o usuário sobre a reativação.

---

## Fluxos Alternativos e Exceções

### E01: Tentativa de Login de Usuário Desativado
- **Fora deste fluxo (em UC-USR-04):** um usuário desativado que tente logar é recusado com mensagem informando que a conta está desativada e orientando a procurar a sala de apoio (**RN03**).

---

## Regras de Negócio (RN)

- **RN01 - Desativação Preserva o Registro:** Desativar mantém todos os dados, grupos e permissões do usuário; é uma medida reversível (reativar restaura o acesso).
- **RN02 - Registro da Ação:** A desativação/reativação é registrada (autor, data/hora e motivo, quando informado) para auditoria.
- **RN03 - Bloqueio de Acesso:** Um usuário desativado não pode efetuar login (UC-USR-04); suas sessões ativas são encerradas (UC-USR-10).
- **RN04 - Ortogonal ao Status do Cadastro:** A desativação é independente do status do ciclo de cadastro (Pendente/E-mail Confirmado/Aprovado/Rejeitado). Ao reativar, o usuário retoma o status que tinha antes.
- **RN05 - Sistema Não Exclui Usuários:** O sistema **não** oferece exclusão de usuários. A desativação é a medida máxima disponível na aplicação (preserva histórico e integridade referencial com agendamentos, empréstimos etc.). Caso uma exclusão definitiva seja realmente necessária, ela é feita **manualmente no banco de dados**, fora do escopo do sistema, por decisão administrativa.
- **RN06 - Sem Auto-Encerramento de Conta:** O próprio usuário **não** pode desativar nem excluir a própria conta. O encerramento/desativação é sempre uma ação de um Administrador (com `usuarios.desativar`).

> **Pendência de integração:** o tratamento das solicitações ativas de um
> usuário desativado (agendamentos futuros, empréstimos em aberto) será
> definido nos módulos de Agendamento e Empréstimo. Por ora, o registro do
> usuário e de suas solicitações é sempre preservado.
