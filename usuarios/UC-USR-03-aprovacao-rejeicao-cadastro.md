# UC-USR-03: Aprovação/Rejeição de Cadastro

## Metadados
- **Módulo:** Usuários
- **Dependências:**
  - `_estados-usuario.md` — modelo de estados do usuário.
  - `docs/referencias/permissoes-sistema.md` — modelo de permissões (grupos criados manualmente; sem grupo automático).
  - UC-GRP-02 (Atribuir Usuários a um Grupo) — na aprovação, o Administrador escolhe o(s) grupo(s) do usuário.
- **UCs relacionados:**
  - UC-USR-02 (Confirmação de E-mail) — pré-requisito: o cadastro precisa estar em "E-mail Confirmado".
  - UC-USR-01 (Cadastro de Usuário) — origem dos dados analisados (perfil, comprovante).
- **Status:** Rascunho

## Informações Gerais
- **Ator Principal:** Administrador do Sistema
- **Atores Secundários:** Serviço de E-mail, Usuário (destinatário da notificação)
- **Pré-condições:** Existe um cadastro no status **"E-mail Confirmado"** aguardando análise.
- **Pós-condições:** Cadastro no status **"Aprovado"** (associado ao(s) grupo(s) escolhido(s) pelo Administrador) ou **"Rejeitado"** (sem associação a grupos por este fluxo); usuário notificado por e-mail.

---

## Fluxo Principal

1. O Administrador efetua login e navega até a área de **"Gerenciamento de Usuários Pendentes"**.
2. O sistema lista os cadastros no status "E-mail Confirmado" aguardando análise.
3. O Administrador seleciona um cadastro e analisa os dados:
   - Para o perfil **Aluno**: visualiza o comprovante de matrícula anexado (PDF ou imagem).
   - Para os perfis **Professor/Técnico**: a validação ocorre presencialmente na sala de apoio (**RN02**, RN09 do UC-USR-01).
4. O Administrador verifica se o **perfil declarado** pelo usuário é compatível com os dados/documentos apresentados (**RN03**).
5. O Administrador registra a **validação da documentação** (com data e observação — **RN05**), seleciona **pelo menos um grupo de permissões** ao qual o usuário será associado (obrigatório — **RN01**) e clica em **"Aprovar Cadastro"**.
6. O sistema altera o status do usuário para **"Aprovado"** e o associa ao(s) grupo(s) escolhido(s), concedendo as permissões correspondentes (**RN01**).
7. O sistema dispara um e-mail notificando o usuário de que seu acesso foi liberado.

---

## Fluxos Alternativos e Exceções

### E01: Rejeição do Cadastro
- **No passo 5:** Caso o cadastro não seja validado (comprovante inválido/ilegível, não comparecimento à sala de apoio, ou perfil declarado incompatível — **RN03**), o Administrador clica em **"Rejeitar Cadastro"** e informa o motivo.
  1. O sistema altera o status do usuário para **"Rejeitado"**.
  2. O usuário continua podendo efetuar login, mas **não** é associado a grupos de permissões por este fluxo (permanece sem permissões de ação até que o Administrador o associe a algum grupo).
  3. O sistema envia um e-mail ao usuário notificando a rejeição com a justificativa informada.

### E02: Perfil Declarado Incompatível
- **No passo 4:** Se o Administrador identificar que o perfil declarado não condiz com os documentos (ex: aluno declarado como professor):
  1. O Administrador pode rejeitar o cadastro (E01) informando o motivo, ou
  2. Corrigir o perfil do usuário antes de aprovar, conforme política administrativa (**RN03**).

### E03: Aprovação sem Grupo
- **No passo 5:** Se o Administrador tentar aprovar sem selecionar nenhum grupo de permissões:
  1. O sistema impede a aprovação e informa que é necessário associar o usuário a pelo menos um grupo (**RN01**).

---

## Regras de Negócio (RN)

- **RN01 - Efeito da Aprovação:** A aprovação não é pré-requisito para login (ver UC-USR-02 e `_estados-usuario.md`). No momento da aprovação, o Administrador deve escolher manualmente **pelo menos um** grupo de permissões ao qual o usuário será associado (ver `docs/referencias/permissoes-sistema.md` e UC-GRP-02); não há associação automática a um grupo padrão, e não é possível aprovar sem associar a nenhum grupo (**E03**). As permissões concedidas dependem dos grupos escolhidos, podendo ainda ser complementadas ou restringidas por ajuste individual (UC-USR-08).
- **RN02 - Base da Análise por Perfil:** Para Aluno, a análise se apoia no comprovante de matrícula. Para Professor/Técnico, apoia-se na validação presencial na sala de apoio, já que não há comprovante.
- **RN03 - Validação do Perfil Declarado:** O perfil é auto-declarado no cadastro. O Administrador deve validar, na análise, se o perfil declarado é compatível com os dados/documentos, podendo rejeitar ou corrigir o perfil em caso de divergência.
- **RN04 - Efeito da Rejeição:** A rejeição mantém o acesso de login, mas não associa o usuário a nenhum grupo por este fluxo (sem permissões de ação até uma associação posterior). A decisão e a justificativa ficam registradas.
- **RN05 - Registro de Validação da Documentação:** Independentemente do canal (comprovante anexado do Aluno, validação presencial de Professor/Técnico na sala de apoio, ou documento recebido por e-mail), o Administrador registra que a validação foi realizada, com **data** e **observação**. Não é obrigatório anexar o documento ao sistema; o objetivo é a rastreabilidade da decisão. Este mesmo registro é usado na alteração de perfil (UC-USR-09).
