# UC-USR-01: Cadastro de Usuário

## Metadados
- **Módulo:** Usuários
- **Dependências:**
  - Módulo de Domínios de E-mail — validação do domínio institucional do e-mail (RN01). UC ainda não documentado (pendente).
  - Módulo de Cursos — o campo Curso (perfil Aluno) é selecionado de uma lista cadastrada, incluindo a opção "OUTRO"; os cursos são mantidos em caixa alta pelo próprio módulo. UC ainda não documentado (pendente).
  - Módulo de Cargos — o campo Cargo (perfis Técnico e Professor) vem da lista do módulo Cargos, com seu próprio CRUD. UC ainda não documentado (pendente).
  - Módulo de Vínculos — o campo Vínculo (perfis Técnico e Professor) vem da lista do módulo Vínculos, incluindo a opção "OUTRO", com seu próprio CRUD. UC ainda não documentado (pendente).
  - Módulo de Perfis de Usuário — definição dos perfis Aluno, Professor e Técnico. UC ainda não documentado (pendente). **Modelagem a decidir** (tabela cadastrável vs enum fixo) — ver NOTA na RN06.
  - `_estados-usuario.md` — modelo de estados do usuário compartilhado no módulo.
- **UCs relacionados:**
  - UC-USR-02 (Confirmação de E-mail) — continua o fluxo após o cadastro.
  - UC-USR-03 (Aprovação/Rejeição de Cadastro) — decisão do Administrador.
  - UC-USR-06 (Reenvio de Confirmação de E-mail) — quando o link expira.
- **Status:** Rascunho

## Informações Gerais
- **Ator Principal:** Aluno / Professor / Técnico
- **Atores Secundários:** Serviço de E-mail
- **Pré-condições:**
  - Os domínios de e-mail institucionais devem estar previamente cadastrados na tabela de domínios permitidos do sistema (ex: `@ufam.edu.br`).
  - Para o perfil **Aluno**: os cursos devem estar previamente cadastrados no módulo de Cursos (incluindo a opção "OUTRO").
  - Para os perfis **Técnico** e **Professor**: os cargos (módulo Cargos) e os vínculos (módulo Vínculos, incluindo a opção "OUTRO") devem estar previamente cadastrados.
- **Pós-condições:** Cadastro gravado com status **"Pendente de Confirmação de E-mail"** e e-mail de confirmação disparado. A verificação do e-mail e a liberação de acesso ocorrem em UCs subsequentes (UC-USR-02, UC-USR-03).

---

## Fluxo Principal

1. O usuário acessa a tela inicial do sistema e clica em **"Realizar Cadastro"**.
2. O usuário seleciona o seu **Perfil**: Aluno, Professor ou Técnico (**RN06**).
3. O sistema exibe o formulário de cadastro com os campos correspondentes ao perfil selecionado:
   - **Comuns a todos os perfis:**
     - Identificação Funcional (**RN07**)
     - Nome Completo
     - E-mail Institucional (**RN01**)
     - Telefone
     - Senha e Confirmação de Senha (**RN11**)
   - **Perfil Aluno:**
     - Curso (selecionado da lista do módulo Cursos; opção "OUTRO" quando não encontrar — **RN08**)
     - Comprovante de Matrícula (upload de arquivo — **RN03**)
   - **Perfil Técnico:**
     - Cargo (selecionado da lista do módulo Cargos — **RN13**)
     - Vínculo (selecionado da lista do módulo Vínculos; opção "OUTRO" — **RN14**)
   - **Perfil Professor:**
     - Cargo (preenchido automaticamente como **"Professor do Magistério Superior"** — **RN13**)
     - Vínculo (selecionado da lista do módulo Vínculos; opção "OUTRO" — **RN14**)
   - **Para os perfis Professor e Técnico:** o campo de comprovante fica oculto/bloqueado; o sistema exibe orientação para comparecer à sala de apoio para validação do cadastro (**RN09**).
4. Conforme o usuário preenche cada campo, o sistema atualiza um **indicador de progresso** exibindo quais campos obrigatórios já foram concluídos e quais ainda estão pendentes, conforme o perfil (**RN10**).
5. O usuário preenche os campos (e, se for Aluno, anexa o comprovante) e clica em **"Cadastrar"**.
6. O sistema realiza as validações de dados, unicidade, domínio do e-mail, arquivo e senha (**RN01**, **RN02**, **RN03**, **RN11**).
7. O sistema salva os dados com o status **"Pendente de Confirmação de E-mail"**.
8. O sistema dispara um e-mail para o usuário contendo um link/token de confirmação com validade temporária (**RN05**).
9. O sistema exibe uma mensagem informando que o cadastro foi iniciado e orientando o usuário a checar sua caixa de entrada.

> Continuação: o usuário confirma o e-mail em **UC-USR-02**. Se o link
> expirar, ver **UC-USR-06** (reenvio).

---

## Fluxos Alternativos e Exceções

### E01: Domínio de E-mail Não Permitido
- **No passo 6:** Se o e-mail informado não pertencer a um dos domínios liberados no sistema (ex: `@gmail.com` em vez de `@ufam.edu.br`):
  1. O sistema bloqueia o envio da requisição.
  2. Exibe a mensagem: *"O e-mail informado deve pertencer a um domínio autorizado (ex: @ufam.edu.br)."*

### E02: Identificação Funcional ou E-mail Já Cadastrado
- **No passo 6:** Se a identificação funcional (matrícula/SIAPE) ou o e-mail já constarem na base de dados em um cadastro válido:
  1. O sistema interrompe o processo.
  2. Exibe a mensagem: *"A matrícula/SIAPE ou o e-mail informado já possui cadastro no sistema."*
- **Exceção:** se o registro existente estiver **"Pendente de Confirmação de E-mail"** e já expirado (**RN12**), ele é removido e o novo cadastro prossegue.

### E03: Arquivo em Formato/Tamanho Inválido (perfil Aluno)
- **No passo 6:** Se o comprovante anexado não for PDF ou imagem (JPG/PNG/JPEG), ou exceder 1 MB:
  1. O sistema interrompe a submissão e alerta o usuário sobre as restrições do arquivo (**RN03**).

### E04: Senha Fraca ou Confirmação Divergente
- **No passo 6:** Se a senha não atender à regra mínima (**RN11**) ou não coincidir com a confirmação:
  1. O sistema interrompe a submissão e informa o requisito não atendido.

---

## Regras de Negócio (RN)

- **RN01 - Validação de Domínio:** O sistema consulta a tabela dinâmica de domínios aceitos (módulo de Domínios de E-mail). Se o e-mail não terminar com um dos sufixos autorizados, a conta não pode ser criada.
- **RN02 - Unicidade:** A identificação funcional (campo/coluna único, independente do perfil) e o e-mail são identificadores únicos e não podem se repetir na base de dados.
- **RN03 - Comprovante de Matrícula (perfil Aluno):** Exigido apenas para o perfil Aluno. Aceita estritamente `.pdf`, `.png`, `.jpg` ou `.jpeg`, com tamanho máximo de **1 MB** por arquivo.
- **RN04 - Estado Inicial e Acesso:** Ao ser gravado, o cadastro assume o status "Pendente de Confirmação de E-mail", que não permite login. A evolução dos estados e o acesso permitido em cada um estão definidos em `_estados-usuario.md` (confirmação em UC-USR-02, aprovação/rejeição em UC-USR-03).
- **RN05 - Expiração do Link de Confirmação:** O link de confirmação enviado por e-mail expira após 24 horas. Após expirar, o usuário deve solicitar o reenvio (UC-USR-06).
- **RN06 - Perfil do Usuário:** No cadastro, o usuário informa o perfil entre Aluno, Professor ou Técnico. Os campos e regras exibidos dependem do perfil selecionado.
  Perfil e permissões são conceitos **distintos**: o perfil define os
  campos e regras do cadastro; as permissões vêm de grupos + ajuste
  individual (ver `docs/referencias/permissoes-sistema.md`). O perfil, por
  si, **não** concede permissões — na aprovação (UC-USR-03), o
  Administrador escolhe manualmente o(s) grupo(s) do usuário,
  independentemente do perfil.
  > **NOTA (pendente de decisão):** falta definir a modelagem dos perfis —
  > (A) registro em tabela cadastrável no módulo de Perfis de Usuário, ou
  > (B) conjunto fixo no sistema (enum). Decisão ao iniciar o módulo de
  > Perfis de Usuário.
- **RN07 - Identificação Funcional (rótulo dinâmico):** Persistida em coluna única, independente do perfil. Na interface, o rótulo é **"Matrícula"** para Aluno e **"SIAPE"** para Professor e Técnico.
- **RN08 - Curso vinculado à lista de Cursos (perfil Aluno):** O curso é exigido apenas para o perfil Aluno e é sempre selecionado da lista do módulo de Cursos, sem digitação livre. Quando o usuário não encontrar o seu curso, seleciona a opção **"OUTRO"** (um registro previamente cadastrado na tabela de cursos), preservando o vínculo. O tratamento de caixa alta é responsabilidade do módulo de Cursos.
- **RN09 - Validação Presencial (Professor/Técnico):** Professor e Técnico não enviam comprovante. Para esses perfis, o sistema exibe orientação para comparecer à sala de apoio, onde a validação ocorre antes da aprovação (UC-USR-03).
- **RN10 - Indicador de Progresso de Preenchimento:** Durante o preenchimento, o sistema indica em tempo real quais campos obrigatórios já foram concluídos e quais estão pendentes, conforme o perfil.
- **RN11 - Força de Senha:** A senha deve ter no mínimo 8 caracteres, contendo ao menos uma letra e um número. A confirmação de senha deve coincidir com a senha.
- **RN12 - Expiração de Cadastro Não Confirmado:** Um cadastro que permaneça no status "Pendente de Confirmação de E-mail" por mais de 24 horas é considerado expirado e removido, liberando a identificação funcional e o e-mail para novo cadastro.
- **RN13 - Cargo (perfis Técnico e Professor):** O cargo é exigido para Técnico e Professor e é selecionado da lista do módulo de Cargos. Para o perfil **Professor**, o cargo é preenchido automaticamente como **"Professor do Magistério Superior"** e não é editável pelo próprio usuário no cadastro (podendo ser ajustado posteriormente por um Administrador). O perfil Aluno não possui cargo.
- **RN14 - Vínculo (perfis Técnico e Professor):** O vínculo é exigido para Técnico e Professor e é sempre selecionado da lista do módulo de Vínculos, sem digitação livre. Quando o usuário não encontrar o seu vínculo, seleciona a opção **"OUTRO"** (um registro previamente cadastrado), preservando o vínculo com o módulo. O perfil Aluno não possui vínculo.
