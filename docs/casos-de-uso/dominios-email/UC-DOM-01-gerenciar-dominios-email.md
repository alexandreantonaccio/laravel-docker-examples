# UC-DOM-01: Gerenciar Domínios de E-mail Permitidos

## Metadados
- **Módulo:** Domínios de E-mail
- **Dependências:**
  - `docs/referencias/permissoes-sistema.md` — permissões `dominios_email.*`.
- **UCs relacionados:**
  - UC-USR-01 (Cadastro de Usuário) — o e-mail é montado com parte local + domínio escolhido de um dropdown alimentado por esta lista (RN01 do UC-USR-01).
  - UC-USR-11 (Editar Usuário) — a alteração de e-mail pelo Administrador também usa o dropdown de domínios.
- **Status:** Rascunho

## Informações Gerais
- **Ator Principal:** Administrador (ator com `dominios_email.criar`/`editar`/`desativar`/`visualizar`/`listar`)
- **Atores Secundários:** Nenhum
- **Pré-condições:** O ator está autenticado e possui as permissões de gestão de domínios.
- **Pós-condições:** Lista de domínios permitidos atualizada (domínio criado, editado, ativado/desativado, ou padrão definido), refletindo imediatamente no dropdown de e-mail dos demais fluxos.

---

## Conceito

O usuário **nunca digita** o domínio do e-mail. Em qualquer fluxo que peça
e-mail (cadastro, edição de e-mail pelo admin), o usuário informa apenas a
**parte local** (ex: `ricardo.melo`) e escolhe o **domínio** em um
**dropdown** alimentado pelos domínios **ativos** desta lista. Um dos
domínios pode ser marcado como **padrão**, aparecendo pré-selecionado no
dropdown.

O sistema já nasce (na inicialização) com o domínio institucional
`ufam.edu.br` cadastrado, **ativo** e marcado como **padrão**, para que o
primeiro cadastro de usuário já disponha de um domínio no dropdown
(**RN09**).

> **Importante (desacoplamento):** o dropdown de domínios serve apenas
> para (a) aceitar somente cadastros com domínio permitido e (b) facilitar
> a digitação do e-mail. No momento de salvar, o sistema **concatena** a
> parte local digitada + `@` + o domínio escolhido e grava o resultado
> como **texto** na coluna de e-mail do usuário. O e-mail do usuário
> **não** mantém vínculo (chave estrangeira) com o registro do domínio.
> Por isso, editar ou desativar um domínio **nunca** altera os e-mails de
> usuários já cadastrados (ver **RN11**) — mantendo os módulos
> independentes.

---

## Fluxo Principal

1. O ator acessa a área de **"Domínios de E-mail Permitidos"**.
2. O sistema lista os domínios cadastrados, indicando quais estão **ativos**, **desativados** e qual é o **padrão**, e exibindo, para cada domínio, a **quantidade de usuários** que o utilizam (**RN06**, **RN10**). O ator pode **filtrar** a lista por status: **todos**, **ativos** ou **desativados** (**RN10**).
3. O ator cria um novo domínio informando o **sufixo de domínio** (ex: `ufam.edu.br`) (**RN01**).
4. O sistema valida o formato e a unicidade (**RN01**, **RN02**) e o persiste como **ativo**.
5. Opcionalmente, o ator marca um domínio ativo como **padrão** (**RN03**).
6. A partir daí, os domínios ativos passam a aparecer no dropdown de e-mail dos fluxos de cadastro (UC-USR-01) e edição de e-mail (UC-USR-11), com o padrão pré-selecionado (**RN05**).

---

## Fluxos Alternativos e Exceções

### E01: Editar Domínio
- **No passo 3:** O ator seleciona um domínio existente e altera seu sufixo.
  1. O sistema revalida formato e unicidade (**RN01**, **RN02**) e persiste.
  2. A mudança passa a valer imediatamente no dropdown para **novos** cadastros (**RN05**).
  3. Usuários já cadastrados **não** são afetados, pois o e-mail deles é texto sem vínculo com o registro do domínio (**RN11**).

### E02: Definir/Alterar o Domínio Padrão
- O ator marca um domínio ativo como padrão.
  1. O sistema define esse domínio como padrão e **remove** a marcação de padrão do anterior (só há um padrão por vez — **RN03**).

### E03: Desativar Domínio
- O ator desativa um domínio.
  1. O sistema marca o domínio como **desativado**; ele deixa de aparecer no dropdown e de aceitar **novos** e-mails com esse domínio (**RN04**).
  2. Os **usuários já cadastrados** com esse domínio **permanecem válidos** (**RN04**).
  3. O sistema informa quantos usuários usam esse domínio, para ciência do ator (**RN07**).
  4. Se o domínio desativado era o **padrão**, o sistema fica **sem padrão** definido até que o ator marque outro; nesse caso, o dropdown passa a abrir sem pré-seleção (**RN03**).

### E04: Reativar Domínio
- O ator reativa um domínio desativado.
  1. O sistema volta a exibi-lo no dropdown e a aceitar e-mails com esse domínio.

### E05: Formato de Domínio Inválido
- **No passo 4:** Se o sufixo informado não tiver formato válido de domínio (ex: sem ponto, com espaços, com `@`):
  1. O sistema recusa e orienta o formato esperado (ex: `ufam.edu.br`, sem `@` — **RN01**).

### E06: Domínio Duplicado
- **No passo 4:** Se o sufixo já existir na lista:
  1. O sistema interrompe e informa que o domínio já está cadastrado (**RN02**).

### E07: Nenhum Domínio Ativo Disponível
- **Nos fluxos que usam o dropdown (UC-USR-01, UC-USR-11):** se não houver **nenhum** domínio ativo:
  1. O dropdown fica vazio e o sistema exibe uma mensagem informando que **não há domínios de e-mail ativos**, impedindo prosseguir com o cadastro/alteração de e-mail até que um Administrador ative/cadastre um domínio (**RN12**).

---

## Regras de Negócio (RN)

- **RN01 - Formato do Domínio:** O domínio é armazenado como **sufixo**, sem o `@` (ex: `ufam.edu.br`), com formato válido de domínio (rótulos separados por ponto). No dropdown, é exibido com o `@` (ex: `@ufam.edu.br`).
- **RN02 - Unicidade:** Não pode haver dois domínios com o mesmo sufixo na lista. Cada domínio é um **item independente** — subdomínios (ex: `icomp.ufam.edu.br`) são cadastrados como registros próprios, não derivados automaticamente de `ufam.edu.br`.
- **RN03 - Domínio Padrão:** No máximo **um** domínio pode ser o padrão por vez. O padrão vem pré-selecionado no dropdown de e-mail. Ao definir um novo padrão, o anterior deixa de ser. É permitido não haver padrão (nesse caso o dropdown abre sem pré-seleção); se o padrão for desativado, o sistema fica sem padrão até que outro seja marcado.
- **RN04 - Desativação Preserva Cadastros Existentes:** Desativar um domínio o remove do dropdown e impede **novos** e-mails com ele, mas **não** invalida usuários já cadastrados com esse domínio. O sistema não exclui domínios (preserva histórico); a medida disponível é a desativação.
- **RN05 - Efeito Imediato:** Alterações na lista (criação, edição, ativação/desativação, mudança de padrão) refletem imediatamente no dropdown de e-mail dos fluxos que o utilizam (UC-USR-01, UC-USR-11).
- **RN06 - Visibilidade de Status:** A listagem distingue domínios ativos, desativados e indica qual é o padrão.
- **RN07 - Aviso de Impacto na Desativação:** Ao desativar um domínio, o sistema informa quantos usuários possuem e-mail com esse domínio, para ciência do Administrador.
- **RN08 - Validação no Servidor:** Como o domínio vem de uma lista fechada (dropdown), a interface não permite domínios inválidos; ainda assim, o servidor revalida que o domínio escolhido pertence à lista de ativos, por segurança.
- **RN09 - Domínio de Inicialização:** O sistema é inicializado com o domínio `ufam.edu.br` já cadastrado, ativo e marcado como padrão, garantindo que o primeiro cadastro de usuário tenha um domínio disponível no dropdown. Esse domínio pode ser editado/desativado posteriormente como qualquer outro (mas ver RN03 quanto ao padrão).
- **RN10 - Filtro e Contagem de Uso:** A listagem exibe, para cada domínio, a quantidade de usuários que o utilizam, e permite filtrar por status (todos / ativos / desativados). A contagem por domínio é o mesmo dado usado no aviso de impacto da desativação (RN07).
- **RN11 - E-mail Desacoplado do Domínio (string concatenada):** No cadastro/edição de e-mail, o sistema concatena a parte local + `@` + o domínio escolhido e grava o e-mail como **texto** no usuário. Não há chave estrangeira entre o e-mail do usuário e o registro do domínio. Consequentemente, editar ou desativar um domínio **não** afeta e-mails de usuários já cadastrados. A contagem de uso (RN07/RN10) é obtida por correspondência textual do sufixo nos e-mails, não por vínculo relacional.
- **RN12 - Nenhum Domínio Ativo:** Se não houver nenhum domínio ativo, os fluxos que usam o dropdown (cadastro, edição de e-mail) exibem mensagem informando a ausência de domínios ativos e não permitem prosseguir até que um Administrador ative/cadastre um domínio.
- **RN13 - Ordenação do Dropdown:** No dropdown de e-mail, os domínios ativos são exibidos em **ordem alfabética**. Quando houver domínio padrão, ele vem **pré-selecionado** (independe da ordem de exibição); quando não houver padrão, o dropdown abre **sem pré-seleção** e o usuário deve escolher.
- **RN14 - Auditoria:** As ações de gerenciamento de domínios (criar, editar, desativar/reativar, definir/alterar padrão) são registradas com autor e data/hora, por serem sensíveis (afetam quem pode se cadastrar no sistema).
