# Painel SSH Web

Painel web para gerenciamento de usuários, servidores, payloads, portas, aplicações e operações SSH. Este repositório reúne a versão V3 modernizada a partir do código legado, com hardening de autenticação, sessão, CSRF, uploads e roteamento.

## Estado atual

**Staging / desenvolvimento — ainda não liberado para produção.**

As correções de roteamento por allowlist, hash de senhas, migração de MD5 legado, regeneração de sessão, CSRF e proteção de segredos SSH estão presentes e foram verificadas estaticamente. A auditoria ainda identifica consultas SQL legadas com interpolação e o módulo `gestorssh/appss/` permanece ofuscado; esses pontos precisam ser tratados antes de declarar ausência de SQL Injection ou prontidão para produção.

Consulte:

- [`README_V3.md`](README_V3.md) — visão geral da entrega;
- [`INSTALL_V3.md`](INSTALL_V3.md) — instalação e operação segura;
- [`SECURITY_V3.md`](SECURITY_V3.md) — controles e limitações;
- [`docs/auditoria/relatorio-auditoria.md`](docs/auditoria/relatorio-auditoria.md) — relatório técnico;
- [`SECURITY_AUDIT_RESULTS.md`](SECURITY_AUDIT_RESULTS.md) — resultados e débitos conhecidos.

## Requisitos recomendados

- Ubuntu **24.04 LTS** ou outra distribuição Linux atualmente suportada;
- PHP 8.3 ou superior, com compatibilidade declarada a partir do PHP 8.1;
- extensões PHP `pdo`, `pdo_mysql`, `mbstring`, `openssl`, `sodium`, `json` e `curl`;
- MariaDB/MySQL com usuário de aplicação e privilégios mínimos;
- Composer 2.x;
- HTTPS em qualquer ambiente exposto à internet.

Ubuntu 18.04 e Ubuntu 20.04 **não são mais a base recomendada**. Os scripts históricos de instalação e sincronização foram mantidos somente em `quarantine/legacy/` para referência e não devem ser executados.

## Instalação segura

1. Use um ambiente de staging antes de qualquer publicação.
2. Siga [`INSTALL_V3.md`](INSTALL_V3.md).
3. Configure as variáveis de ambiente sem versionar segredos.
4. Instale dependências com `composer install --no-dev --prefer-dist --optimize-autoloader`.
5. Não exponha `.git`, `docs`, `tests`, `quarantine`, dumps SQL ou scripts CLI pelo webroot.
6. Execute os testes estáticos e funcionais com MariaDB antes do deploy.

Não há instalador remoto ativo neste repositório. Desconfie de comandos antigos que baixem e executem scripts diretamente da internet.

## Validação local

```bash
find . -type f -name '*.php' -not -path './vendor/*' -print0 | xargs -0 -n1 php -l
find . -type f -name '*.sh' -not -path './vendor/*' -print0 | xargs -0 -n1 bash -n
php tests/security_v3.php
composer validate --no-check-publish
```

A validação completa ainda depende de Composer, MariaDB/pdo_mysql e um ambiente de staging funcional.

## Próximos passos de modernização

1. Migrar as consultas dinâmicas restantes para prepared statements parametrizados.
2. Tornar `gestorssh/appss/` legível e versionável após testes funcionais.
3. Gerar e revisar `composer.lock` no ambiente de build.
4. Executar testes end-to-end de autorização, SQLi, CSRF, uploads e fluxos SSH.
5. Reexecutar a auditoria e só então avaliar a liberação para produção.
