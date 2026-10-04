# Painel Web V2

## Instalação segura

1. Use PHP 8.5.x com o último patch disponível.
2. Use MariaDB/MySQL suportado e um usuário dedicado sem privilégios administrativos.
3. Copie `.env.example` para `.env` fora do webroot ou injete as variáveis pelo serviço.
4. Gere `APP_ACCESS_KEY` e `APP_SECRET_KEY` aleatórios.
5. Importe `database/bdgestorssh.secure.sql` em um banco novo ou faça backup/restauração controlada.
6. Rode `composer install --no-dev --prefer-dist --optimize-autoloader`.
7. Rode `php bin/create_admin.php admin 'UMA-SENHA-FORTE-DE-12+' admin@dominio.com`.
8. Rode `php bin/migrate_secrets.php` para criptografar credenciais SSH legadas.
9. Publique apenas `gestorssh/` como webroot se sua configuração permitir, mantendo `.env`, backups e scripts fora da raiz pública.
10. Ative HTTPS e `FORCE_SECURE_COOKIES=1`.

## Não fazer

- Não usar `root` no banco.
- Não colocar senha/API key em PHP, JS ou shell.
- Não executar `wget ... | bash`.
- Não usar `chmod 777`.
- Não expor `/var`, backups, `.env`, dumps SQL ou arquivos de instalação.
- Não habilitar instalação remota legada.

## Auditoria

Execute:

```bash
bash security/audit.sh .
```

Depois execute os testes de autenticação, autorização, CSRF, upload, SQLi, IDOR e SSRF em ambiente de homologação.
