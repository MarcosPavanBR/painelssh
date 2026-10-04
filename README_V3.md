# Painel Web V3

## Status
**Não pronto para produção.** Este pacote contém hardening verificável e um relatório de auditoria, mas ainda possui SQL dinâmico legado que precisa de migração individual.

## Validações executadas
- `php -l` em todos os PHPs: PASS.
- `bash -n` em todos os Shells: PASS.
- `php tests/security_v3.php`: PASS.
- Integridade dos ZIPs de origem: PASS.

## Bloqueadores
- SQL dinâmico em `gestorssh/conecta4g/` e módulos históricos.
- MariaDB/pdo_mysql ausentes no ambiente de auditoria.
- Composer ausente e `composer.lock` não gerado.

## Próxima validação
Executar em ambiente local/staging com MariaDB e Composer, converter todo o catálogo SQL, rodar testes de autorização/SQLi/CSRF/upload e só então reexecutar o gate estrito.
