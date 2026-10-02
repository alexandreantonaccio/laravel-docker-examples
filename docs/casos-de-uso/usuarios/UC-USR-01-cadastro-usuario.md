# UC-USR-01: Cadastro de Usuário

## Metadados
- **Módulo:** Usuários
- **Dependências:**
  - UC-DOM-01 (Domínios de E-mail) — validação/dropdown de domínio do e-mail (RN01).
  - UC-CUR-01 (Cursos) — o campo Curso (perfil Aluno) é selecionado de uma lista, incluindo a opção "OUTRO"; cursos mantidos em caixa alta.
  - UC-CAR-01 (Cargos) — o campo Cargo (perfis Técnico e Professor) vem da lista de Cargos.
  - UC-VIN-01 (Vínculos) — o campo Vínculo (perfis Técnico e Professor) vem da lista de Vínculos, incluindo a opção "OUTRO".
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
  - Para o perfil **Professor**: o cargo "Professor do Magistério Superior" já vem cadastrado na inicialização do sistema (UC-CAR-01, RN07) — não depende de cadastro prévio manual.
  - Para o perfil **Técnico**: um cargo (módulo Cargos) deve estar previamente cadastrado.
  - Para os perfis **Técnico** e **Professor**: um vínculo (módulo Vínculos, incluindo a opção "OUTRO") deve estar previamente cadastrado — o módulo de Vínculos não tem inicialização automática (UC-VIN-01, RN07).
- **Pós-condições:** Cadastro gravado com status **"Pendente de Confirmação de E-mail"** e e-mail de confirmação disparado. A verificação do e-mail e a liberação de acesso ocorrem em UCs subsequentes (UC-USR-02, UC-USR-03).

---

## Fluxo Principal

1. O usuário acessa a tela inicial do sistema e clica em **"Realizar Cadastro"**.
2. O usuário seleciona o seu **Perfil**: Aluno, Professor ou Técnico (**RN06**).
3. O sistema exibe o formulário de cadastro com os campos correspondentes ao perfil selecionado:
   - **Comuns a todos os perfis:**
     - Identificação Funcional (**RN07**)
     - Nome Completo
     - E-mail Institucional — parte local digitada + domínio escolhido em **dropdown** (**RN01**)
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

### E01: Domínio Inválido na Submissão
- **No passo 6:** Como o domínio é escolhido em dropdown de domínios ativos, o usuário não consegue informar um domínio não autorizado pela interface. Ainda assim, se a requisição chegar com um domínio fora da lista de ativos (ex: manipulação da requisição, ou domínio desativado entre a abertura da tela e o envio):
  1. O servidor rejeita o cadastro (**RN01**, RN08 do UC-DOM-01).
  2. Exibe mensagem orientando a selecionar um domínio válido da lista.

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

- **RN01 - E-mail com Domínio de Lista (dropdown):** O usuário **não digita** o domínio. Informa apenas a parte local (ex: `ricardo.melo`) e escolhe o domínio em um **dropdown** alimentado pelos domínios **ativos** do módulo de Domínios de E-mail (UC-DOM-01), em ordem alfabética, com o **domínio padrão** pré-selecionado quando houver (sem padrão, abre sem pré-seleção). Ao salvar, o sistema **concatena** parte local + `@` + domínio e grava o e-mail como **texto**, sem vínculo com o registro do domínio (ver RN11 do UC-DOM-01). Como a escolha vem de lista fechada, não há como informar um domínio não autorizado pela interface; o servidor ainda revalida que o domínio pertence à lista de ativos (RN08 do UC-DOM-01). Se não houver domínio ativo, o cadastro não pode prosseguir (RN12 do UC-DOM-01).
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
  > **NOTA:** a modelagem de dados do usuário/perfil (tabela única + enum,
  > sem herança) está registrada em `docs/referencias/decisoes-tecnicas.md`
  > (ADR-001). O conjunto de perfis (Aluno/Professor/Técnico) é **fixo no
  > código** (enum) — não há módulo cadastrável de Perfis de Usuário
  > (ADR-002).
- **RN07 - Identificação Funcional (rótulo dinâmico):** Persistida em coluna única, independente do perfil. Na interface, o rótulo é **"Matrícula"** para Aluno e **"SIAPE"** para Professor e Técnico.
- **RN08 - Curso da Lista de Cursos (perfil Aluno):** O curso é exigido apenas para o perfil Aluno e é sempre selecionado da lista do módulo de Cursos (UC-CUR-01), sem digitação livre. Quando o usuário não encontrar o seu curso, seleciona a opção **"OUTRO"** (um curso cadastrado como qualquer outro). O curso é salvo no usuário como texto (cópia), sem chave estrangeira para o registro do curso (RN06 do UC-CUR-01) — editar/desativar um curso não afeta usuários já cadastrados. O tratamento de caixa alta é responsabilidade do módulo de Cursos.
- **RN09 - Validação Presencial (Professor/Técnico):** Professor e Técnico não enviam comprovante. Para esses perfis, o sistema exibe orientação para comparecer à sala de apoio, onde a validação ocorre antes da aprovação (UC-USR-03).
- **RN10 - Indicador de Progresso de Preenchimento:** Durante o preenchimento, o sistema indica em tempo real quais campos obrigatórios já foram concluídos e quais estão pendentes, conforme o perfil.
- **RN11 - Força de Senha:** A senha deve ter no mínimo 8 caracteres, contendo ao menos uma letra e um número. A confirmação de senha deve coincidir com a senha.
- **RN12 - Expiração de Cadastro Não Confirmado:** Um cadastro que permaneça no status "Pendente de Confirmação de E-mail" por mais de 24 horas é considerado expirado e removido, liberando a identificação funcional e o e-mail para novo cadastro.
- **RN13 - Cargo (perfis Técnico e Professor):** O cargo é exigido para Técnico e Professor e é selecionado da lista do módulo de Cargos (UC-CAR-01). Para o perfil **Professor**, o cargo é preenchido automaticamente como **"Professor do Magistério Superior"** e não é editável pelo próprio usuário no cadastro. O cargo é salvo no usuário como texto (cópia), sem chave estrangeira para o registro do cargo (RN06 do UC-CAR-01). Um Administrador pode posteriormente alterar o cargo salvo neste usuário específico (UC-USR-11) — o que é distinto de editar o nome do cargo na lista (UC-CAR-01), que não afeta usuários já cadastrados. O perfil Aluno não possui cargo.
- **RN14 - Vínculo (perfis Técnico e Professor):** O vínculo é exigido para Técnico e Professor e é sempre selecionado da lista do módulo de Vínculos (UC-VIN-01), sem digitação livre. Quando o usuário não encontrar o seu vínculo, seleciona a opção **"OUTRO"** (um vínculo cadastrado como qualquer outro). O vínculo é salvo no usuário como texto (cópia), sem chave estrangeira para o registro na lista de vínculos (RN06 do UC-VIN-01) — editar/desativar um vínculo da lista não afeta usuários já cadastrados. O perfil Aluno não possui vínculo.
