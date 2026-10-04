# Painel Web V3 — hardening verificável

## Mudanças desta V3

- Roteadores `home.php` e `admin/home.php` passaram de inclusão baseada em `file_exists()` para allowlist explícita.
- Login Conecta4G deixou de comparar MD5; usa `password_verify()` e migra hashes MD5 legados após autenticação bem-sucedida.
- Sessão Conecta4G é regenerada no login, usa identificador aleatório e expiração de 30 minutos.
- Login/logout Conecta4G passaram a exigir CSRF; logout passou para POST.
- Edição administrativa de usuários usa prepared statements, `password_hash()` e CSRF.
- Cadastro administrativo de usuários usa prepared statements e `password_hash()`.
- Roteador público `appss/index.php` não aceita mais `page` arbitrário.
- Instaladores/scripts legados com download remoto e `chmod 777` foram movidos para `quarantine/legacy/` e não fazem parte do caminho executável normal.

## Limitação importante

A auditoria estática ainda encontra SQL dinâmico legado em módulos antigos, especialmente `conecta4g/` e páginas administrativas históricas. Esses pontos permanecem listados como dívida de segurança e não permitem declarar o pacote como livre de SQL Injection.

## Validação

- `php -l` em todos os PHPs do pacote: deve ser executado após cada alteração.
- `bash -n` em todos os `.sh`.
- `php tests/security_v3.php`.
- `composer validate --no-check-publish` se Composer estiver disponível.
- Testes end-to-end com MariaDB/staging não foram considerados executados sem o serviço correspondente.
