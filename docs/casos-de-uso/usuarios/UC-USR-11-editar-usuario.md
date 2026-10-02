# UC-USR-11: Editar Usuário

## Metadados
- **Módulo:** Usuários
- **Dependências:**
  - `docs/referencias/permissoes-sistema.md` — permissões `usuarios.editar.proprio` e `usuarios.editar.qualquer`.
  - UC-USR-01 (Cadastro de Usuário) — validações e campos por perfil.
- **UCs relacionados:**
  - UC-USR-05 (Gestão de Senha) — toda troca/reset de senha é tratada lá, não aqui.
  - UC-USR-09 (Alterar Perfil) — mudança de perfil é operação própria, fora deste UC.
  - UC-USR-12 (Visualizar Detalhe) — ponto de entrada.
- **Status:** Rascunho

## Informações Gerais
- **Ator Principal:** Usuário autenticado (edição própria) ou Administrador (edição de qualquer usuário)
- **Atores Secundários:** Nenhum
- **Pré-condições:** O ator está autenticado. Para editar terceiros, possui `usuarios.editar.qualquer`; para o próprio, `usuarios.editar.proprio`.
- **Pós-condições:** Dados do usuário atualizados conforme o escopo da permissão.

> **Nota:** a senha **não** é editada por este UC. Troca de senha (própria)
> e reset (pelo Administrador) são tratados no **UC-USR-05 (Gestão de
> Senha)**.

---

## Fluxo Principal — Edição pelo próprio usuário (escopo `proprio`)

1. O usuário autenticado acessa a tela do próprio perfil ("Meus Dados" — UC-USR-12).
2. O sistema informa que, em autoatendimento, o único ajuste disponível é a **troca de senha** (realizada via UC-USR-05, cenário C2) (**RN01**).
3. Para **qualquer outro ajuste** de cadastro (nome, telefone, curso, cargo, vínculo, e-mail etc.), o sistema exibe orientação para o usuário **comparecer à sala de apoio** e solicitar o ajuste (**RN02**).

---

## Fluxo Alternativo — Edição por Administrador (escopo `qualquer`)

A1. O Administrador (ator com `usuarios.editar.qualquer` — pessoal da sala de apoio) localiza o usuário (via UC-USR-07/12) e abre a edição.
A2. O sistema permite editar **todos os campos** do cadastro conforme o perfil (dados comuns e específicos — ver UC-USR-01), **exceto** o **perfil** (alterado no UC-USR-09) e a **senha** (tratada no UC-USR-05) (**RN03**).
A3. O Administrador altera os campos necessários e salva. O sistema valida (unicidade — RN02 do UC-USR-01; **domínio de e-mail** — RN01 do UC-USR-01; formatos) e persiste (**RN04**).
A4. Se o **e-mail** for alterado, o Administrador informa a parte local e escolhe o domínio no **dropdown** de domínios ativos (mesmo mecanismo do cadastro — UC-DOM-01); a mudança é registrada para auditoria e o usuário é notificado (**N07** — ver `docs/referencias/notificacoes-sistema.md`) (**RN05**).

---

## Fluxos Alternativos e Exceções

### E01: Violação de Unicidade (edição por Administrador)
- **No passo A3:** Se uma alteração gerar duplicidade de identificação funcional ou e-mail (RN02 do UC-USR-01):
  1. O sistema interrompe e informa o conflito.

### E02: Domínio Inválido na Alteração de E-mail (edição por Administrador)
- **No passo A4:** O domínio vem do dropdown de domínios ativos, então não há como escolher um não autorizado pela interface. Se a requisição chegar com domínio fora da lista de ativos:
  1. O servidor bloqueia a alteração e orienta a selecionar um domínio válido (**RN05**).

---

## Regras de Negócio (RN)

- **RN01 - Autoatendimento Restrito à Senha:** Com `usuarios.editar.proprio`, o único autoatendimento do usuário é a troca da própria senha, realizada no UC-USR-05 (C2). Nenhum outro dado é editável em autoatendimento.
- **RN02 - Ajustes fora da Senha (próprio):** Para alterar qualquer outro dado, o usuário deve comparecer à sala de apoio; o sistema exibe essa orientação e não realiza o ajuste em autoatendimento.
- **RN03 - Edição Ampla (Administrador):** Com `usuarios.editar.qualquer`, o Administrador edita todos os campos do cadastro conforme o perfil, **exceto** o perfil (UC-USR-09) e a senha (UC-USR-05).
- **RN04 - Validações na Edição:** Alterações feitas pelo Administrador respeitam as mesmas validações do cadastro (unicidade, domínio de e-mail, formatos — ver UC-USR-01).
- **RN05 - Alteração de E-mail (Administrador):** O Administrador altera o e-mail informando a parte local e escolhendo o domínio no dropdown de domínios ativos (UC-DOM-01); o e-mail deve ser único (RN02 do UC-USR-01). Como o e-mail é usado no login, a alteração é registrada para auditoria e o usuário é notificado. O próprio usuário **não** altera o e-mail em autoatendimento (deve procurar a sala de apoio).
- **RN06 - Proteção do SuperAdmin:** A conta **SuperAdmin** não pode ter seus dados editados por nenhum outro usuário, independentemente das permissões (ver ADR-004 em `docs/referencias/decisoes-tecnicas.md`). Apenas o próprio SuperAdmin edita a si mesmo. Ao alvejar o SuperAdmin, o sistema recusa a edição para qualquer outro ator.
