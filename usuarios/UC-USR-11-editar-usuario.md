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
A4. Se o **e-mail** for alterado, o novo e-mail deve respeitar a regra de domínio autorizado (**RN05**); a mudança é registrada para auditoria e o usuário é notificado (**N07** — ver `docs/referencias/notificacoes-sistema.md`).

---

## Fluxos Alternativos e Exceções

### E01: Violação de Unicidade (edição por Administrador)
- **No passo A3:** Se uma alteração gerar duplicidade de identificação funcional ou e-mail (RN02 do UC-USR-01):
  1. O sistema interrompe e informa o conflito.

### E02: E-mail com Domínio Não Autorizado (edição por Administrador)
- **No passo A4:** Se o Administrador tentar alterar o e-mail para um domínio não autorizado (ex: `@gmail.com` quando o institucional é `@ufam.edu.br`):
  1. O sistema bloqueia a alteração e exibe aviso de que o e-mail deve pertencer a um domínio autorizado (**RN05**).

---

## Regras de Negócio (RN)

- **RN01 - Autoatendimento Restrito à Senha:** Com `usuarios.editar.proprio`, o único autoatendimento do usuário é a troca da própria senha, realizada no UC-USR-05 (C2). Nenhum outro dado é editável em autoatendimento.
- **RN02 - Ajustes fora da Senha (próprio):** Para alterar qualquer outro dado, o usuário deve comparecer à sala de apoio; o sistema exibe essa orientação e não realiza o ajuste em autoatendimento.
- **RN03 - Edição Ampla (Administrador):** Com `usuarios.editar.qualquer`, o Administrador edita todos os campos do cadastro conforme o perfil, **exceto** o perfil (UC-USR-09) e a senha (UC-USR-05).
- **RN04 - Validações na Edição:** Alterações feitas pelo Administrador respeitam as mesmas validações do cadastro (unicidade, domínio de e-mail, formatos — ver UC-USR-01).
- **RN05 - Alteração de E-mail (Administrador):** O Administrador pode alterar o e-mail, mas o novo e-mail **deve** pertencer a um domínio autorizado (RN01 do UC-USR-01) e ser único (RN02). Como o e-mail é usado no login, a alteração é registrada para auditoria e o usuário é notificado. O próprio usuário **não** altera o e-mail em autoatendimento (deve procurar a sala de apoio).
