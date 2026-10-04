# Reprodução da instalação — 2026-10-04

## Ambiente

- Ubuntu 24.04.5 LTS
- Apache 2.4.58
- MariaDB 10.11.14
- PHP 8.3.6 com `pdo_mysql`, `mbstring`, `xml`, `curl` e `zip`
- Composer 2.7.1

## Falha reproduzida

A instalação original concluía a criação do banco, Composer e administrador, mas `appss/index.php` retornava HTTP 500. O log do Apache mostrava:

```text
Call to undefined function getConfig() in gestorssh/config/conteudo.php
```

A causa era uma dependência circular: `funcoes.php` carregava `config.php`, `config.php` renderizava `conteudo.php`, e `conteudo.php` chamava `getConfig()` antes de `funcoes.php` terminar de declarar suas funções.

Também foram encontrados dois defeitos funcionais no módulo `appss`:

- consulta administrativa fixada em `id_administrador = 1`, embora o primeiro administrador criado possa receber outro ID;
- caminhos antigos `../apps/...`, que apontavam para fora do diretório correto do módulo.

## Correções

- Criado `gestorssh/config/render_helpers.php` e carregado antes de `conteudo.php`.
- Evitada redeclaração de `getConfig()` em `funcoes.php`.
- Consulta administrativa alterada para selecionar o primeiro administrador existente.
- Fallback do roteador alterado para `downloads.php` local.
- AJAX de downloads alterado para `ajax_downloads.php` local.

## Resultado após reinstalação limpa

A instalação foi executada novamente do zero com o instalador, sem cópia manual de arquivos. Todos os endpoints retornaram HTTP 200:

- `/`
- `/conecta4g/index.php`
- `/appss/index.php`
- `/appss/ajax_downloads.php?tipo=0`
- `/appss/termos.php`

Além disso, todos os arquivos PHP passaram no lint e o teste `tests/security_v3.php` permaneceu aprovado.

## Limite

O teste confirma a instalação e o carregamento HTTP básico. Ainda é necessário testar login real, criação/edição de registros, download de arquivo e fluxos SSH com dados reais em staging antes de produção.
