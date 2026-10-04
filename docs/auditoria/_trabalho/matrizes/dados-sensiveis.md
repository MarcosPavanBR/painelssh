# Dados sensíveis

- Senhas de login: `password_hash()` nos fluxos principais revisados.
- Credenciais SSH: envelope libsodium em `security/secrets.php`; migração via `bin/migrate_secrets.php`.
- Tokens de recuperação: armazenados por hash SHA-256 e expiração no fluxo V2.
- Legado `conecta4g` foi migrado do MD5 no login, mas requer revisão dos demais fluxos de credenciais.
