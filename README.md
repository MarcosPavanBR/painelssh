# Painel SSH Web

Painel web para gerenciamento de usuários, servidores, payloads, portas, aplicações e operações SSH. Este repositório reúne a versão V3 modernizada a partir do código legado, com hardening de autenticação, sessão, CSRF, uploads e roteamento.

## Estado atual

**Staging / desenvolvimento — ainda não liberado para produção.**

As correções de roteamento por allowlist, hash de senhas, migração de MD5 legado, regeneração de sessão, CSRF e proteção de segredos SSH estão presentes e foram verificadas estaticamente. As consultas SQL interpoladas identificadas nos endpoints Conecta4G foram migradas para prepared statements e validadas por lint/testes estáticos. O módulo `gestorssh/appss/` foi tornado legível, teve o roteador protegido e a consulta de download parametrizada; ainda precisa de testes funcionais em staging.

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

1. Executar testes funcionais do `gestorssh/appss/` em staging com MariaDB e navegador.
3. Gerar e revisar `composer.lock` no ambiente de build.
4. Executar testes end-to-end de autorização, SQLi, CSRF, uploads e fluxos SSH.
5. Reexecutar a auditoria e só então avaliar a liberação para produção.

## Instalação automatizada

A instalação automatizada foi escrita para Ubuntu 24.04 e não deve ser tratada como garantia de produção. Ela pede confirmação, não remove uma instalação existente, instala Apache/PHP/MariaDB, cria um banco e usuário com privilégios limitados, importa o SQL seguro e cria o primeiro administrador.

```bash
git clone https://github.com/MarcosPavanBR/painelssh.git && cd painelssh && sudo bash ops/install.sh
```

O comando exige acesso de leitura ao repositório. Antes de expor o painel, configure HTTPS, altere `FORCE_SECURE_COOKIES` para `1` e valide os fluxos em staging. O instalador foi reproduzido em Ubuntu 24.04 com Apache, MariaDB, PHP 8.3 e Composer; a causa de um HTTP 500 no `appss` foi corrigida. Consulte o [registro da reprodução](docs/auditoria/reproducao-instalacao-2026-10-04.md). Ainda devem ser feitos testes funcionais com dados reais antes de produção.
