# Decisões Técnicas (ADR)

Registro enxuto de decisões técnicas do projeto, no formato ADR
(Architecture Decision Record). Cada entrada documenta uma decisão de
implementação/arquitetura: o contexto, a decisão tomada, o motivo e as
alternativas descartadas.

> **Escopo:** este documento trata de **como** o sistema será
> implementado (modelagem de dados, arquitetura, tecnologia). Os casos de
> uso (`docs/casos-de-uso/`) tratam de **comportamento** e não devem
> conter detalhes de implementação — quando um UC precisar referenciar
> uma decisão, aponta para a entrada correspondente aqui.
>
> As decisões abaixo são o entendimento **atual**; podem ser revisadas na
> fase de implementação se surgirem fatos novos. Ao revisar, atualize a
> entrada (ou adicione uma nova que a substitua) em vez de apagar o
> histórico.

---

## ADR-001 — Modelagem de Usuário e Perfil: tabela única + enum

- **Data:** 2026-09-11
- **Status:** Aceita (a validar na implementação)
- **Relacionado:** UC-USR-01 (RN06), UC-USR-07, UC-USR-09; módulo Perfis de Usuário.

### Contexto
Os usuários têm um **perfil** (Aluno, Professor, Técnico). A maior parte
dos campos é **comum** a todos (identificação funcional, nome, e-mail,
telefone, senha); poucos campos variam por perfil (Aluno: curso e
comprovante; Técnico/Professor: cargo e vínculo). Além disso:
- É possível **listar todos os usuários** juntos, com filtros (UC-USR-07).
- É possível **alterar o perfil** de um usuário existente (UC-USR-09).

### Decisão
Armazenar os usuários em uma **tabela única**, com o perfil representado
por um **enum** (`Aluno` / `Professor` / `Técnico`). Os campos específicos
de cada perfil ficam na mesma tabela, preenchidos apenas quando se
aplicam ao perfil do usuário. **Não** usar herança de tabelas.

O **comportamento do cadastro** varia por perfil (formulário e validação
ramificados — ex: Form Requests distintos na stack Laravel), mas isso é
lógica de aplicação, não modelagem de dados.

### Motivo
- Os campos são majoritariamente comuns; herança para isolar poucos campos
  não compensa a complexidade.
- **Listagem unificada** (UC-USR-07) é natural em tabela única; com
  herança exigiria JOINs entre tabela base e tabelas filhas.
- **Alteração de perfil** (UC-USR-09) é trivial com enum (troca o valor e
  ajusta os campos específicos); com herança exigiria mover a linha entre
  tabelas filhas — complexo e frágil.
- Alinha-se com a stack de referência (SIGEA/SIGEB), que já usa enum de
  papel em coluna do `User`, com Enums PHP nativos.

### Alternativas descartadas
- **Herança (class table inheritance):** tabela base + tabelas por perfil.
  Descartada por complicar listagem unificada e alteração de perfil, sem
  ganho relevante dado que os campos são quase todos comuns.
- **Campos flexíveis (JSON/EAV) para os específicos:** descartada por
  perder tipagem/validação no banco e dificultar consultas por campos
  específicos (ex: buscar por curso); overkill para poucos campos.

### Pendência associada
~~Resta decidir se o conjunto de perfis em si é fixo no código (enum) ou
uma tabela cadastrável.~~ Resolvido na **ADR-002**.

---

## ADR-002 — Conjunto de Perfis: enum fixo (sem módulo cadastrável)

- **Data:** 2026-09-11
- **Status:** Aceita
- **Relacionado:** UC-USR-01 (RN06); ADR-001.

### Contexto
Faltava decidir se os perfis (Aluno, Professor, Técnico) são um conjunto
fixo definido no código, ou uma tabela cadastrável (um módulo "Perfis de
Usuário" com CRUD, como Cargos/Cursos/Vínculos).

### Decisão
O conjunto de perfis é **fixo no código**, representado como **enum**
(`Aluno` / `Professor` / `Técnico`). **Não** haverá um módulo cadastrável
de Perfis de Usuário com CRUD.

### Motivo
- Cada perfil carrega **comportamento próprio e fixo** no cadastro
  (campos exibidos, validações, rótulo Matrícula/SIAPE, exigência de
  comprovante, cargo automático do Professor — ver UC-USR-01). Um perfil
  "novo" cadastrado dinamicamente numa tabela não haveria de onde vir
  esse comportamento sem alteração de código; a flexibilidade de um CRUD
  seria ilusória.
- Diferente de Cargos/Cursos/Vínculos/Domínios (listas de apoio sem
  comportamento próprio, apenas dados), perfil define **regras de
  negócio inteiras**. Adicionar um perfil novo é, na prática, trabalho de
  desenvolvimento — o que é honesto de refletir no modelo (enum,
  alterado via código/migração), em vez de sugerir uma flexibilidade que
  não existe de fato.
- Alinha-se com a stack de referência (SIGEA/SIGEB), que usa Enums PHP
  nativos para papéis.
- Coerente com a **ADR-001** (tabela única de usuário): o enum de perfil
  já é a mesma coluna que a ADR-001 assume.

### Alternativas descartadas
- **Tabela cadastrável (módulo Perfis de Usuário):** descartada porque
  criaria uma falsa flexibilidade — um perfil cadastrado pela tela não
  teria os campos/regras de formulário que o cadastro de usuário exige
  por perfil, a menos que também se alterasse código. Não haveria ganho
  real sobre o enum, apenas complexidade extra (CRUD, tela, validação de
  nome único) sem benefício.

### Consequência
Não existirá módulo "Perfis de Usuário" com UC de gerenciamento. O
backlog é atualizado para refletir que este módulo não será desenvolvido
como CRUD — os perfis são mantidos no código (enum) e qualquer perfil
novo entra via desenvolvimento, não via tela administrativa.

---

## ADR-003 — Ambiente Referenciado por Agendamento (FK, não desacoplado)

- **Data:** 2026-09-11
- **Status:** Aceita (sujeita a revisão ao escrever o módulo de Agendamento)
- **Relacionado:** UC-AMB-01 (RN09); módulo de Agendamento (futuro).

### Contexto
As listas de apoio já documentadas (Cargos, Cursos, Vínculos, Domínios de
E-mail) usam desacoplamento por cópia de texto: quem seleciona um item da
lista grava uma cópia, sem chave estrangeira, para manter os módulos
independentes. Era preciso decidir se o Ambiente seguiria o mesmo padrão
em relação ao módulo de Agendamento (futuro).

### Decisão
O Ambiente será **referenciado por chave estrangeira (FK)** pelos
agendamentos. A tabela de agendamentos carrega a referência ao ambiente;
o ambiente não referencia agendamentos (relação um-para-muitos, um
ambiente para vários agendamentos).

### Motivo
- A funcionalidade central de um sistema de agendamento é verificar
  **disponibilidade e conflito de horário** em um mesmo espaço físico.
  Isso exige identificar o ambiente com certeza (o mesmo registro), o que
  correspondência por texto não garante de forma confiável (typos,
  renomeações, nomes parecidos).
- Diferente de curso/cargo/vínculo/domínio — onde a "verdade" que importa
  é uma fotografia do que foi escolhido no momento (histórico
  intencionalmente congelado) — o ambiente é um espaço físico que
  continua existindo e sendo consultado ativamente (agenda, capacidade
  atual), o que favorece uma referência viva ao registro.

### Alternativas descartadas
- **Desacoplado (cópia de texto), como as demais listas de apoio:**
  descartada porque comparar agendamentos por texto do nome do ambiente
  seria frágil para checar conflito de horário — o cerne da
  funcionalidade de agendamento.

### Consequências
- Editar os dados de um ambiente reflete no histórico de agendamentos
  associados a ele (não fica "congelado" como nas listas desacopladas).
- Desativar um ambiente exige verificar o impacto em agendamentos
  futuros/pendentes (UC-AMB-01, RN09) — regra a detalhar no módulo de
  Agendamento (ex: cancelamento, aviso, ou bloqueio da desativação).
- O sistema não exclui ambientes, preservando a integridade referencial.

---

## ADR-004 — SuperAdmin: conta única com hierarquia superior

- **Data:** 2026-09-11
- **Status:** Aceita
- **Relacionado:** `permissoes-sistema.md` (inicialização); UC-USR-08,
  UC-USR-09, UC-USR-11, UC-USR-13 (ações administrativas sobre usuários).

### Contexto
O modelo de permissões é "plano": qualquer usuário com a permissão
adequada (ex: `usuarios.editar.qualquer`) pode agir sobre qualquer outro
usuário. Isso significa que administradores do grupo "Administradores"
poderiam alterar uns aos outros — inclusive a conta de inicialização.
Faltava um nível de proteção para a conta suprema do sistema.

### Decisão
Renomear a conta de inicialização de "Administrador" para **"SuperAdmin"**
e conferir a ela uma **hierarquia superior**, com regras que se sobrepõem
ao modelo normal de permissões:
- Nenhum outro usuário pode alterar/desativar o SuperAdmin, independente
  de suas permissões.
- O SuperAdmin pode agir sobre qualquer usuário, inclusive outros
  administradores.
- O próprio SuperAdmin mantém a autogestão (troca/recupera a própria
  senha), evitando lockout.
É uma **conta única** (não um grupo de vários superadmins).

### Motivo
- Proteger a conta suprema de ser alterada, desativada ou ter permissões
  removidas por outro administrador (acidental ou malicioso).
- Garantir que sempre exista um usuário capaz de recuperar o controle do
  sistema (resolver disputas, corrigir permissões de admins).
- O nome "SuperAdmin" comunica a hierarquia e evita confusão com o ator
  genérico "Administrador" (qualquer usuário com permissão administrativa).

### Alternativas descartadas
- **Manter todos os admins iguais (sem hierarquia):** descartada por não
  proteger a conta suprema — qualquer admin poderia rebaixar/alterar os
  demais e a própria conta de inicialização.
- **Grupo "SuperAdmins" com vários membros:** descartada por ora; abriria
  a questão "um superadmin altera outro?" sem necessidade atual. Mantém-se
  conta única.

### Consequências
- As ações administrativas sobre usuários (editar — UC-USR-11; alterar
  perfil — UC-USR-09; gerenciar permissões — UC-USR-08; desativar —
  UC-USR-13) devem, ao alvejar o SuperAdmin, **recusar** a operação para
  qualquer ator que não seja o próprio SuperAdmin.
- É uma exceção explícita ao modelo de permissões plano; deve ser tratada
  de forma centralizada na implementação (ex: uma verificação única
  "alvo é SuperAdmin e ator não é o próprio").
