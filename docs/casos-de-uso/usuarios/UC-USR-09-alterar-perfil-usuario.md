# UC-USR-09: Alterar Perfil do Usuário

## Metadados
- **Módulo:** Usuários
- **Dependências:**
  - UC-USR-01 (Cadastro de Usuário) — define os campos de cada perfil (Aluno/Professor/Técnico) e suas regras (RN06–RN14).
  - Módulos de Cursos, Cargos e Vínculos — origem dos campos que passam a existir conforme o novo perfil.
- **UCs relacionados:**
  - UC-USR-07 (Listar e Pesquisar Usuários) — ponto de partida para localizar o usuário.
  - UC-USR-03 (Aprovação/Rejeição de Cadastro) — trata a correção de perfil no momento da aprovação; este UC trata a alteração de perfil de um usuário já existente.
- **Status:** Rascunho

## Informações Gerais
- **Ator Principal:** Administrador (ator com `usuarios.alterar_perfil`)
- **Atores Secundários:** Nenhum
- **Pré-condições:** O usuário-alvo existe. O ator possui a permissão `usuarios.alterar_perfil`.
- **Pós-condições:** Perfil do usuário alterado, com os campos coerentes ao novo perfil (campos que passam a existir preenchidos; campos que deixam de existir arquivados — **RN03**).

---

## Contexto: campos por perfil

Cada perfil tem um conjunto de campos próprios (ver UC-USR-01):

- **Aluno:** Curso, Comprovante de Matrícula (além dos comuns).
- **Técnico:** Cargo (da lista), Vínculo.
- **Professor:** Cargo (fixo "Professor do Magistério Superior"), Vínculo.

Os campos comuns (identificação funcional, nome, e-mail, telefone, senha)
permanecem em qualquer transição. A alteração de perfil precisa tratar os
campos **específicos** que passam a existir ou deixam de existir.

---

## Fluxo Principal

1. O ator localiza e seleciona um usuário (via UC-USR-07).
2. O sistema exibe o perfil atual e os dados do usuário.
3. O ator seleciona o **novo perfil** (**RN01**).
4. O sistema identifica os campos que **passam a existir** no novo perfil e solicita o preenchimento dos obrigatórios (**RN02**):
   - Para **Aluno**: Curso (o comprovante não é exigido no sistema; a documentação é validada externamente pelo Administrador — **RN04**).
   - Para **Técnico**: Cargo e Vínculo.
   - Para **Professor**: Vínculo (o Cargo é preenchido automaticamente como "Professor do Magistério Superior").
5. O ator preenche os campos requeridos e confirma.
6. O sistema valida, altera o perfil e **arquiva** os campos que deixaram de existir, preservando-os no histórico do usuário (**RN03**).
7. O sistema registra a alteração (quem alterou, quando, perfil anterior → novo) para auditoria (**RN05**) e notifica o usuário sobre a mudança de perfil (N10 — ver `docs/referencias/notificacoes-sistema.md`).

---

## Fluxos Alternativos e Exceções

### E01: Campos Obrigatórios do Novo Perfil Não Preenchidos
- **No passo 5:** Se algum campo obrigatório do novo perfil não for informado:
  1. O sistema interrompe a alteração e indica os campos pendentes.

### E02: Rótulo da Identificação Funcional Muda
- **Na transição de/para Aluno:** o rótulo da identificação funcional muda entre "Matrícula" (Aluno) e "SIAPE" (Professor/Técnico), sem alterar a coluna no banco (RN07 do UC-USR-01). O valor é mantido; apenas o rótulo exibido muda (**RN06**).

### E03: Transição Não Permitida
- **No passo 3:** Caso alguma transição de perfil seja considerada não permitida por política administrativa, o sistema a impede e informa o motivo (**RN01**).

---

## Regras de Negócio (RN)

- **RN01 - Transições de Perfil:** O Administrador pode alterar o perfil entre Aluno, Professor e Técnico. Restrições de transição, se houver, seguem política administrativa; por padrão, todas as transições são permitidas.
- **RN02 - Preenchimento dos Campos que Passam a Existir:** Ao mudar para um perfil que exige campos que o usuário não possui (ex: virar Aluno exige Curso; virar Técnico/Professor exige Cargo/Vínculo), o sistema exige o preenchimento dos obrigatórios antes de concluir.
- **RN03 - Arquivamento dos Campos que Deixam de Existir:** Os campos específicos do perfil anterior que não se aplicam ao novo perfil (ex: Curso/Comprovante ao deixar de ser Aluno) são **arquivados** (preservados no histórico), não apagados, para fins de auditoria.
- **RN04 - Validação Externa na Transição para Aluno:** A alteração de perfil é feita apenas pelo Administrador (o usuário solicita por canal externo — presencial ou e-mail). Ao tornar um usuário Aluno, **não** é exigido o upload do comprovante no sistema: o Administrador valida a documentação por canal externo e **registra a validação** (data + observação, ex: "comprovante recebido por e-mail em DD/MM"), sem necessidade de anexar o arquivo. O mesmo registro de validação externa é usado no UC-USR-03.
  > **NOTA (assimetria intencional):** um Aluno que se auto-cadastra
  > anexa o comprovante no sistema (UC-USR-01, RN03); um usuário que se
  > torna Aluno por alteração de perfil (este UC) tem a documentação
  > validada externamente, sem arquivo no sistema. Logo, nem todo Aluno
  > possui comprovante anexado — telas que listam/exibem comprovante devem
  > tratar sua ausência.
- **RN05 - Auditoria:** Toda alteração de perfil é registrada com autor, data/hora e a transição (perfil anterior → novo).
- **RN06 - Identificação Funcional Preservada:** A alteração de perfil não altera o valor da identificação funcional (coluna única); apenas o rótulo exibido pode mudar (Matrícula/SIAPE).
- **RN07 - Proteção do SuperAdmin:** O perfil da conta **SuperAdmin** não pode ser alterado por nenhum outro usuário, independentemente das permissões (ver ADR-004 em `docs/referencias/decisoes-tecnicas.md`). O sistema recusa a alteração de perfil que tenha o SuperAdmin como alvo, exceto pelo próprio SuperAdmin.

> **NOTA:** a alteração de perfil não altera automaticamente os grupos de
> permissões do usuário (perfil e permissões são independentes — ver
> `docs/referencias/permissoes-sistema.md`). Ajustes de permissão, se
> necessários após a mudança de perfil, são feitos via UC-GRP-02 / UC-USR-08.
