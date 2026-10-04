# Endpoints / entradas

- `/index.php` — login principal; POST de autenticação via `validacao.php`.
- `/admin/login.php` — login administrativo; POST via `admin/validacao.php`.
- `/conecta4g/index.php` — login legado endurecido; POST via `conecta4g/login.php`.
- `/home.php?page=...` — roteador de usuário com allowlist.
- `/admin/home.php?page=...` — roteador administrativo com allowlist.
- `/appss/index.php` — catálogo de apps/downloads; V3 removeu `page` arbitrário.
