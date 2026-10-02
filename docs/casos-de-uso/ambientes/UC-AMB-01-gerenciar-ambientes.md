# UC-AMB-01: Gerenciar Ambientes

## Metadados
- **Módulo:** Ambientes
- **Dependências:**
  - `docs/referencias/permissoes-sistema.md` — permissões `ambientes.*`.
- **UCs relacionados:**
  - UC-GAM-01 (Gerenciar Grupos de Ambientes) — um ambiente pode pertencer a um grupo para centralizar e-mails de notificação.
  - UC-AGE-05 (Solicitar Agendamento) — ambientes são o recurso agendável (FK).
- **Status:** Rascunho

## Informações Gerais
- **Ator Principal:** Administrador (ator com `ambientes.criar`/`editar`/`desativar`/`visualizar`/`listar`)
- **Atores Secundários:** Nenhum
- **Pré-condições:** O ator está autenticado e possui as permissões de gestão de ambientes.
- **Pós-condições:** Lista de ambientes atualizada (ambiente criado, editado, ativado/desativado).

---

> **Acoplamento com Agendamento (decidido — RN09):** diferente das listas
> de apoio já documentadas (Cargos, Cursos, Vínculos, Domínios), que são
> desacopladas por cópia de texto, o Ambiente será **referenciado por
> chave estrangeira** pelo módulo de Agendamento (quando escrito). O
> agendamento aponta para o registro real do ambiente — isso é necessário
> para verificar disponibilidade/conflito de horário de forma confiável.
> A tabela de agendamentos é que carrega a referência ao ambiente; o
> ambiente não referencia agendamentos. Consequência: editar um ambiente
> reflete em todo o histórico de agendamentos dele (não fica "congelado"),
> e desativar um ambiente exige checar impacto (ver **RN09**). Decisão
> sujeita a revisão ao escrever o módulo de Agendamento, caso surjam
> fatos novos.

---

## Fluxo Principal

1. O ator acessa a área de **"Ambientes"**.
2. O sistema lista os ambientes cadastrados, indicando quais estão **ativos** e **desativados**, com filtro por status (todos / ativos / desativados) (**RN08**).
3. O ator cria um novo ambiente informando (**RN01**–**RN05**):
   - **Nome**
   - **Sigla** (ex: "LAB01")
   - **Foto** (opcional)
   - **Quantidade de Cadeiras**
   - **Quantidade de Bancadas**
   - **Descrição**
   - **Grupo de Ambientes** (opcional — selecionar da lista de grupos cadastrados em UC-GAM-01 — **RN10**)
4. O sistema valida os campos (formato, unicidade da sigla, arquivo de foto) e persiste o ambiente como **ativo**.
5. A partir daí, o ambiente ativo fica disponível para os fluxos que o utilizam (ex: futuro módulo de Agendamento).

---

## Fluxos Alternativos e Exceções

### E01: Editar Ambiente
- **No passo 3:** O ator seleciona um ambiente existente e altera seus dados.
  1. O sistema revalida os campos (**RN01**–**RN05**) e persiste.

### E02: Desativar Ambiente
- O ator desativa um ambiente.
  1. O sistema verifica se existem agendamentos futuros/pendentes vinculados a esse ambiente e informa o ator sobre o impacto antes de confirmar (**RN09** — regra detalhada no módulo de Agendamento).
  2. O sistema marca o ambiente como **desativado**; ele deixa de estar disponível para **novos** agendamentos (**RN07**).
  3. O sistema não exclui ambientes (preserva histórico e a integridade referencial com agendamentos existentes); a medida disponível é a desativação.

### E03: Reativar Ambiente
- O ator reativa um ambiente desativado, que volta a ficar disponível.

### E04: Sigla Duplicada
- **No passo 4:** Se já existir um ambiente com a mesma sigla:
  1. O sistema interrompe e informa a duplicidade (**RN02**).

### E05: Foto em Formato/Tamanho Inválido
- **No passo 4:** Se a foto anexada não for JPG/PNG/JPEG, ou exceder **1 MB**:
  1. O sistema interrompe a submissão e alerta sobre as restrições do arquivo (**RN03**).

### E06: Campos Obrigatórios Não Preenchidos
- **No passo 4:** Se nome, sigla, quantidade de cadeiras, quantidade de bancadas ou descrição não forem informados (a foto é opcional):
  1. O sistema interrompe e indica os campos pendentes (**RN01**, **RN04**, **RN05**).

---

## Regras de Negócio (RN)

- **RN01 - Campos Obrigatórios:** Nome, Sigla, Quantidade de Cadeiras, Quantidade de Bancadas e Descrição são obrigatórios. Foto é opcional.
- **RN02 - Sigla Única:** A sigla do ambiente é única entre os ambientes cadastrados.
- **RN03 - Foto (opcional):** Quando informada, a foto deve estar em formato `.jpg`, `.jpeg` ou `.png`, com tamanho máximo de **1 MB**.
- **RN04 - Quantidades Numéricas:** Quantidade de Cadeiras e Quantidade de Bancadas são valores numéricos inteiros não negativos. Por ora, são apenas informativos (não alimentam regra de capacidade máxima de agendamento).
- **RN05 - Descrição:** Campo de texto livre para detalhar o ambiente.
- **RN06 - Sem Exclusão:** O sistema não exclui ambientes; a medida disponível é a desativação, preservando o registro e o eventual histórico associado.
- **RN07 - Efeito da Desativação:** Um ambiente desativado deixa de estar disponível para novos usos (ex: solicitação de agendamento), mas o registro é preservado. O impacto sobre agendamentos futuros/existentes será detalhado no módulo de Agendamento.
- **RN08 - Filtro por Status:** A listagem permite filtrar por status (todos / ativos / desativados).
- **RN09 - Referenciado por Agendamento (FK):** Diferente das demais listas de apoio do sistema, o Ambiente é referenciado por chave estrangeira pelos agendamentos (módulo de Agendamento, a documentar). Consequências: (a) editar os dados de um ambiente reflete no histórico de agendamentos associados a ele; (b) antes de desativar um ambiente, o sistema verifica e informa se há agendamentos futuros/pendentes vinculados; (c) o sistema não exclui ambientes, preservando a integridade referencial com os agendamentos existentes. As regras específicas de disponibilidade/conflito de horário serão detalhadas no módulo de Agendamento.
- **RN10 - Grupo de Ambientes (opcional):** Um ambiente pode ser associado a no máximo um Grupo de Ambientes (UC-GAM-01). O grupo centraliza os e-mails de notificação para agendamentos daquele ambiente. Um ambiente sem grupo pode ser agendado normalmente, mas sem disparar notificações de e-mail (ver UC-GAM-01, RN05).
