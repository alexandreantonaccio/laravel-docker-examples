# Implementação dos casos de uso pendentes

Este documento registra, em sequência, o que foi implementado para os casos
de uso além dos módulos de usuários e grupos de permissões.

## Passo a passo da implementação

1. **Persistência e relações**
   - Criadas tabelas para ambientes, grupos de ambientes, tipos de agendamento,
     docentes, regras, bloqueios, séries e ocorrências de agendamento.
   - Adicionado o indicador de domínio de e-mail padrão.
   - Criados modelos Eloquent para os recursos e as relações necessárias para
     consultar ambiente, grupo, tipo, solicitante, docente e série.

2. **Permissões e dados iniciais**
   - Ampliado o `PermissionSeeder` com permissões dos catálogos e de cada ação
     de agendamento.
   - Mantida a criação configurada do domínio institucional padrão e do cargo
     de Professor. Cursos e vínculos (inclusive `OUTRO`) ficam para cadastro
     administrativo, conforme as regras documentadas.

3. **Cadastros de apoio**
   - Adicionada gestão de cursos, cargos e vínculos com filtros por status,
     contagem de usuários, edição e desativação/reativação.
   - Cursos são normalizados para caixa alta. As alterações nas opções não
     reescrevem valores já copiados para os usuários.
   - Adicionada gestão de domínios de e-mail, domínio padrão, status e contagem
     de endereços já existentes.
   - O cadastro público e a edição administrativa de usuário passaram a compor
     o e-mail usando parte local e domínio permitido; endereços existentes
     continuam independentes do registro do domínio.

4. **Ambientes e grupos de ambientes**
   - Implementados cadastro, edição, foto opcional (JPG/PNG até 1 MB),
     filtro/status e desativação de ambientes sem exclusão.
   - Implementados grupos com e-mails de notificação e associação de ambientes.
     A associação impede que um ambiente pertença a mais de um grupo; ao
     desativar um grupo, os ambientes associados ficam sem grupo.

5. **Tipos e docentes de agendamento**
   - Implementado cadastro, edição e ativação/desativação de tipos, com cor
     validada para apresentação na agenda.
   - Implementada gestão de docentes de referência, sem vínculo com a conta
     dos usuários.

6. **Regras, bloqueios e agenda**
   - Adicionadas regras globais e específicas por ambiente, com dias da semana
     e múltiplas faixas horárias.
   - Adicionados bloqueios globais ou por ambiente: data, data e horário,
     dia semanal recorrente ou dia semanal com faixa horária. Bloqueios podem
     ser editados e removidos; agendamentos aprovados não são cancelados
     automaticamente.
   - Criada agenda autenticada com intervalo e filtro por ambiente, detalhe do
     agendamento e lista administrativa separada entre ocorrências normais e
     recorrentes.

7. **Solicitações, decisões e recorrências**
   - Solicitações validam ambiente/tipo ativos, período permitido, bloqueios,
     horários e conflitos com agendamentos aprovados. Solicitações pendentes
     podem coexistir.
   - Aprovação revalida conflitos e rejeição exige motivo. Notificações por
     e-mail são enviadas ao solicitante e, quando aplicável, ao grupo do
     ambiente.
   - Séries recorrentes apresentam prévia das ocorrências válidas e
     bloqueadas/conflitantes; após confirmação, as válidas são criadas como
     aprovadas.
   - Cancelamento exige motivo e respeita proprietário/permissão; séries
     permitem cancelar a ocorrência selecionada ou esta e as seguintes.
     Edição revalida regras e conflitos e também pode atingir as ocorrências
     futuras da série.

8. **Interface e rotas**
   - Adicionadas telas Blade para os cadastros, agenda, formulários,
     configurações e detalhes.
   - Incluídos atalhos para os módulos na navegação e na página inicial.
   - Rotas de escrita e gestão são protegidas pelas permissões correspondentes;
     a agenda geral e o detalhe permanecem disponíveis a usuários autenticados.

## Executar e validar

Após configurar a conexão do banco e os parâmetros do administrador:

```sh
php artisan migrate --seed
php artisan test
```

Na validação deste incremento, `php artisan route:list --except-vendor`,
`php artisan view:cache` e `php -l` nos arquivos PHP alterados foram executados
com sucesso. A suíte de testes não conseguiu acessar o banco de testes neste
ambiente: o PHP instalado não possui o driver PDO SQLite (`could not find
driver`). Os testes devem ser repetidos em um ambiente PHP com `pdo_sqlite`
habilitado.
