# Stack Técnica do SIGEA/SIGEB

Contexto técnico para entender rapidamente a stack utilizada no
SIGEA/SIGEB e servir de referência para replicar decisões equivalentes
em novos projetos. Não significa que tudo aqui será reaproveitado — é
um documento de consulta para lembrar de usar caso necessário.

## Backend

- **PHP 8.2+** (testado em CI também em 8.3 e 8.4)
- **Laravel 12** (framework MVC completo — Eloquent ORM, Blade, roteamento, filas, notificações, eventos, agendador)
- **Arquitetura:** MVC clássico do Laravel
  - Controllers finos
  - Lógica de negócio em Services (`app/Services/`)
  - Form Requests para validação (`app/Http/Requests/`)
  - Models Eloquent com Enums nativos PHP 8.1+ (`app/Enums/`)
- **Autenticação:** sistema de auth nativo do Laravel (sessão, guards, `MustVerifyEmail`), sem Breeze/Jetstream/Fortify — implementado manualmente
- **Autorização:** baseada em coluna `role` no model `User` (admin/support/common) + policies quando necessário
- **Fila de jobs:** driver `database` (tabela `jobs`), notificações assíncronas via `ShouldQueue`
- **Cache:** driver `database`
- **Sessão:** driver `database`
- **E-mail:** notificações Laravel (`Illuminate\Notifications`) via SMTP (Gmail em produção)
- **Testes:** PHPUnit 11 (não Pest), com `RefreshDatabase`, Factories e Faker (`fakerphp/faker`) — banco de teste SQLite in-memory
- **Code style:** Laravel Pint (PSR-12 + convenções Laravel)

## Frontend

- **Templating:** Blade (server-side rendering puro, sem SPA)
- **CSS:** Tailwind CSS 4 (via plugin oficial `@tailwindcss/vite`)
- **JS:** vanilla JavaScript puro — sem framework de componentes (sem Alpine.js, sem Vue, sem React, sem jQuery, sem htmx). Interatividade feita com JS nativo inline/módulos simples
- **Build tool:** Vite 7, integrado via `laravel-vite-plugin`
- **Tipografia:** Google Fonts (Inter para corpo, Instrument Serif para display/títulos, JetBrains Mono para labels técnicas/monoespaçado) — design system próprio documentado ("Editorial Meridional": paleta navy + âmbar + linho)
- Sem preprocessador CSS além do Tailwind (sem Sass/Less)

## Banco de dados

- MySQL (produção) / SQLite (testes automatizados)
- Migrations versionadas do Laravel (schema-as-code, sem alterações manuais em produção)
- Seeders para dados de bootstrap (ex.: admin inicial)

## Infraestrutura / DevOps

- Deploy via FTP puro (hospedagem compartilhada sem SSH) — implica em soluções específicas: endpoint HTTP protegido por token para rodar comandos Artisan remotamente (`php artisan migrate`, `queue:work`, etc.) simulando acesso a terminal
- Cron externo (cron-job.org) chamando endpoints HTTP para suprir a ausência de cron do sistema operacional (`schedule:run` e `queue:work` via HTTP)
- CI: GitHub Actions, rodando `php artisan test` em matriz de versões PHP (8.2/8.3/8.4) a cada push/PR e diariamente
- Sem Docker em produção (existe suporte a Laravel Sail como opção de dev, mas não é o ambiente principal)
- Servidor web: Apache com mod_rewrite (via `.htaccess` do Laravel), PHP rodando via PHP-FPM/CGI (suporta `.user.ini` por diretório)

## Padrões de projeto notáveis

- **Feature flags:** via arquivo de config próprio (`app_name.php`) + variável de ambiente, combinado com condicionais em Blade (`@if(config(...))`) e carregamento condicional de arquivos de rotas — usado para ocultar módulos incompletos sem remover código
- **Dispatcher dedicado:** centraliza a lógica de envio de notificação (quem recebe, quando, log padronizado) em vez de espalhar `Notification::send` pelos controllers
- **Separação de e-mail:** clara distinção entre e-mail para "solicitante/responsável" (dado dinâmico por registro) e "equipe administrativa" (endereço fixo de config)
- **Enums PHP nativos:** (não strings soltas) para status e papéis, com métodos auxiliares de label/validação no próprio enum ou model

## Nota final

Se o novo projeto for para outro domínio (não laboratório acadêmico), a
parte que vale mais a pena replicar como está é a combinação **Laravel 12
+ Blade + Tailwind 4 + Vite + PHPUnit + MySQL**, já que é uma escolha
moderna, coesa e sem dependências desnecessárias (nada de framework JS
pesado quando o projeto não precisa de SPA).
