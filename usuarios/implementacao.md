# Implementação do módulo de usuários e permissões

## Inicialização

Configure no `.env` os domínios institucionais e o endereço que recebe avisos de novos cadastros:

```dotenv
USERS_ALLOWED_EMAIL_DOMAINS=ufam.edu.br
USERS_ADMINISTRATOR_EMAIL=administracao@ufam.edu.br
```

Aplique as migrations e cadastre o catálogo inicial, domínios e opções `OUTRO`:

```bash
docker compose -f compose.dev.yaml exec workspace php artisan migrate --seed
```

Cadastros novos não recebem permissões automaticamente. Para preparar o primeiro operador, ele deve se cadastrar e confirmar o e-mail; então um operador de infraestrutura concede explicitamente a permissão de criação de grupos:

```bash
docker compose -f compose.dev.yaml exec workspace php artisan access:grant operador@ufam.edu.br grupos_permissoes.criar
```

Conceda também `grupos_permissoes.editar`, `grupos_permissoes.excluir` e `grupos_permissoes.atribuir_usuarios` conforme a responsabilidade do operador. Depois, os grupos e suas permissões podem ser geridos pela interface. O comando `access:grant {email} {permission}` também pode ser usado para conceder outras permissões individualmente. Nenhum grupo ou usuário administrador é criado pelo seeder.

Em desenvolvimento, o e-mail usa `MAIL_MAILER=log` por padrão; os links aparecem em `storage/logs/laravel.log`. Configure um transporte de e-mail antes de disponibilizar o sistema aos usuários. Para executar a expiração horária de cadastros que não confirmaram o e-mail em 24 horas, mantenha o scheduler ativo:

```bash
docker compose -f compose.dev.yaml exec workspace php artisan schedule:work
```

Em produção, configure o cron do Laravel para executar `php artisan schedule:run` a cada minuto.

## Catálogos de cadastro

Os UCs de Cursos, Cargos, Vínculos e Domínios ainda não foram especificados. A implementação deixa seus registros em `user_profile_options` e `email_domains`; o seeder fornece `OUTRO` para curso/vínculo/cargo e o cargo fixo de Professor. Popule os registros válidos a partir dos módulos responsáveis antes de abrir o cadastro ao público. O domínio configurado em `USERS_ALLOWED_EMAIL_DOMAINS` é usado como fallback até existir catálogo no banco; depois disso, o catálogo ativo é a fonte de validação.

Comprovantes de matrícula ficam no disco privado e só são baixados por usuários com `usuarios.aprovar`. A aprovação exige um ou mais grupos e registro de validação documental. Perfil e permissões permanecem independentes.

## Verificação

Execute a suíte no container de desenvolvimento, que contém os drivers PHP necessários:

```bash
docker compose -f compose.dev.yaml exec workspace php artisan test
```

```bash
docker compose -f compose.dev.yaml exec workspace php artisan access:grant usermail@ufam.edu.br usuarios.aprovar 
```